<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Status;
use App\Services\CreditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    private function relations(): array
    {
        return ['customer', 'status', 'currency', 'tax', 'items.unitOfMeasure', 'items.invoiceable'];
    }

    public function index(Request $request): JsonResponse
    {
        $perPage   = (int) $request->query('per_page', 15);
        $paginated = Invoice::with($this->relations())
            ->orderBy('date', 'desc')->orderBy('id', 'desc')->paginate($perPage);

        return response()->json([
            'invoices' => $paginated->items(),
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
        $code   = 'INV-' . str_pad(Invoice::count() + 1, 4, '0', STR_PAD_LEFT);
        $active = Status::where('code', 'ACT')->value('id');

        $invoice = Invoice::create([
            'code'        => $code,
            'customer_id' => $request->input('customer_id'),
            'status_id'   => $request->input('status_id', $active),
            'currency_id' => $request->input('currency_id'),
            'tax_id'      => $request->input('tax_id'),
            'date'        => $request->input('date'),
            'discount'    => $request->input('discount', 0),
            'total'       => 0,
            'amount_paid' => 0,
            'balance'     => 0,
            'created_by'  => $request->user()?->id,
        ]);

        $taxRate = $invoice->tax ? (float) $invoice->tax->value : 0;
        [$total] = $this->syncItems($invoice->id, $request->input('items', []), (float) $invoice->discount, $taxRate);

        $customer = $invoice->customer;
        ['applied' => $applied, 'balance' => $balance] = CreditService::apply($customer, $total);

        $invoice->update(['total' => $total, 'amount_paid' => $applied, 'balance' => $balance]);

        if ($balance > 0) {
            $customer->increment('amount_due', $balance);
        }

        return response()->json(['invoice' => $invoice->load($this->relations())], 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(['invoice' => Invoice::with($this->relations())->findOrFail($id)]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $invoice  = Invoice::findOrFail($id);
        $oldTotal = (float) $invoice->total;
        $fields   = array_filter(
            $request->only(['customer_id', 'status_id', 'currency_id', 'tax_id', 'date', 'discount']),
            fn($v) => $v !== null,
        );
        $invoice->update($fields);

        if ($request->has('items')) {
            $invoice->items()->delete();
            $fresh   = $invoice->fresh(['tax']);
            $taxRate = $fresh->tax ? (float) $fresh->tax->value : 0;
            [$total] = $this->syncItems($invoice->id, $request->input('items'), (float) $fresh->discount, $taxRate);
            $balance = round($total - (float) $invoice->amount_paid, 2);
            $invoice->update(['total' => $total, 'balance' => $balance]);
            $invoice->customer()->increment('amount_due', round($total - $oldTotal, 2));
        }

        return response()->json(['invoice' => $invoice->load($this->relations())]);
    }

    public function destroy(int $id): JsonResponse
    {
        $invoice = Invoice::findOrFail($id);
        $invoice->customer()->decrement('amount_due', (float) $invoice->balance);
        $invoice->delete();
        return response()->json(['message' => 'Invoice deleted.']);
    }

    public function printUrl(int $id): JsonResponse
    {
        return response()->json(['url' => url("/invoices/{$id}/print")]);
    }

    private function syncItems(int $invoiceId, array $rows, float $discountPct, float $taxRate = 0): array
    {
        $subtotal = 0;
        foreach ($rows as $row) {
            $lineTotal = round($row['quantity'] * $row['unit_price'], 2);
            $subtotal += $lineTotal;
            InvoiceItem::create([
                'invoice_id'         => $invoiceId,
                'invoiceable_type'   => $row['invoiceable_type'],
                'invoiceable_id'     => $row['invoiceable_id'],
                'unit_of_measure_id' => $row['unit_of_measure_id'],
                'quantity'           => $row['quantity'],
                'unit_price'         => $row['unit_price'],
                'total'              => $lineTotal,
            ]);
        }
        $afterDiscount = round($subtotal * (1 - $discountPct / 100), 2);
        $total         = round($afterDiscount * (1 + $taxRate / 100), 2);
        return [$total, $subtotal];
    }
}
