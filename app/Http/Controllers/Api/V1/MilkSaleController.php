<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\MilkSale;
use App\Services\CreditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MilkSaleController extends Controller
{
    private function relations(): array
    {
        return ['customer', 'currency', 'creator'];
    }

    public function index(Request $request): JsonResponse
    {
        $perPage   = (int) $request->query('per_page', 15);
        $paginated = MilkSale::with($this->relations())
            ->orderBy('date', 'desc')->orderBy('id', 'desc')->paginate($perPage);

        return response()->json([
            'milk_sales' => $paginated->items(),
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
        $quantity  = (float) $request->input('quantity');
        $unitPrice = (float) $request->input('unit_price');
        $total     = round($quantity * $unitPrice, 2);

        $customer = Customer::findOrFail($request->input('customer_id'));
        ['applied' => $applied, 'balance' => $balance] = CreditService::apply($customer, $total);

        $sale = MilkSale::create([
            'code'        => 'MKS-' . str_pad(MilkSale::count() + 1, 4, '0', STR_PAD_LEFT),
            'customer_id' => $customer->id,
            'currency_id' => $request->input('currency_id'),
            'date'        => $request->input('date'),
            'quantity'    => $quantity,
            'unit_price'  => $unitPrice,
            'total'       => $total,
            'amount_paid' => $applied,
            'balance'     => $balance,
            'created_by'  => $request->user()?->id,
        ]);

        if ($balance > 0) {
            $customer->increment('amount_due', $balance);
        }

        return response()->json(['milk_sale' => $sale->load($this->relations())], 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(['milk_sale' => MilkSale::with($this->relations())->findOrFail($id)]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $sale     = MilkSale::findOrFail($id);
        $oldTotal = (float) $sale->total;

        $fields = array_filter(
            $request->only(['customer_id', 'currency_id', 'date', 'quantity', 'unit_price']),
            fn($v) => $v !== null,
        );

        $quantity  = (float) ($fields['quantity']  ?? $sale->quantity);
        $unitPrice = (float) ($fields['unit_price'] ?? $sale->unit_price);
        $total     = round($quantity * $unitPrice, 2);

        $fields['total']   = $total;
        $fields['balance'] = max(0, $total - (float) $sale->amount_paid);

        $sale->update($fields);

        $delta = round($total - $oldTotal, 2);
        if ($delta !== 0.0) {
            $sale->customer()->increment('amount_due', $delta);
        }

        return response()->json(['milk_sale' => $sale->load($this->relations())]);
    }

    public function destroy(int $id): JsonResponse
    {
        $sale = MilkSale::findOrFail($id);
        $sale->customer()->decrement('amount_due', (float) $sale->balance);
        $sale->delete();

        return response()->json(['message' => 'Milk sale deleted.']);
    }
}
