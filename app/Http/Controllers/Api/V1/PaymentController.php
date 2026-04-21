<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\MilkSale;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    private function relations(): array
    {
        return ['customer', 'currency', 'paymentMode', 'creator'];
    }

    public function index(Request $request): JsonResponse
    {
        $perPage   = (int) $request->query('per_page', 15);
        $paginated = Payment::with($this->relations())
            ->orderBy('date', 'desc')->orderBy('id', 'desc')->paginate($perPage);

        return response()->json([
            'payments' => $paginated->items(),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $customerId = $request->input('customer_id');
        $amount     = round((float) $request->input('amount'), 2);

        $payment = Payment::create([
            'code'            => 'PAY-' . str_pad(Payment::count() + 1, 4, '0', STR_PAD_LEFT),
            'customer_id'     => $customerId,
            'currency_id'     => $request->input('currency_id'),
            'payment_mode_id' => $request->input('payment_mode_id'),
            'amount'          => $amount,
            'date'            => $request->input('date'),
            'notes'           => $request->input('notes'),
            'created_by'      => $request->user()?->id,
        ]);

        $remaining = $amount;

        // Drain invoices oldest-first
        Invoice::where('customer_id', $customerId)
            ->where('balance', '>', 0)
            ->orderBy('date')->orderBy('id')
            ->each(function ($invoice) use (&$remaining) {
                if ($remaining <= 0) {
                    return false;
                }
                $apply = min($remaining, round((float) $invoice->balance, 2));
                $invoice->increment('amount_paid', $apply);
                $invoice->decrement('balance', $apply);
                $remaining = round($remaining - $apply, 2);
            });

        // Drain milk sales oldest-first
        MilkSale::where('customer_id', $customerId)
            ->where('balance', '>', 0)
            ->orderBy('date')->orderBy('id')
            ->each(function ($milkSale) use (&$remaining) {
                if ($remaining <= 0) {
                    return false;
                }
                $apply = min($remaining, round((float) $milkSale->balance, 2));
                $milkSale->increment('amount_paid', $apply);
                $milkSale->decrement('balance', $apply);
                $remaining = round($remaining - $apply, 2);
            });

        $applied = round($amount - $remaining, 2);
        if ($applied > 0) {
            $payment->customer()->update([
                'amount_due' => DB::raw("GREATEST(amount_due - {$applied}, 0)"),
            ]);
        }

        if ($remaining > 0) {
            $payment->customer()->increment('credit', $remaining);
        }

        return response()->json(['payment' => $payment->load($this->relations())], 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(['payment' => Payment::with($this->relations())->findOrFail($id)]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $payment = Payment::findOrFail($id);
        $fields  = array_filter(
            $request->only(['date', 'payment_mode_id', 'notes']),
            fn($v) => $v !== null,
        );
        $payment->update($fields);

        return response()->json(['payment' => $payment->load($this->relations())]);
    }

    public function destroy(int $id): JsonResponse
    {
        Payment::findOrFail($id)->delete();
        return response()->json(['message' => 'Payment deleted.']);
    }

    public function printUrl(int $id): JsonResponse
    {
        Payment::findOrFail($id);
        return response()->json(['url' => url("/payments/{$id}/receipt")]);
    }
}
