<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Status;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    private function relations(): array
    {
        return ['supplier', 'status', 'currency', 'items.itemMaster', 'items.unitOfMeasure'];
    }

    public function index(Request $request): JsonResponse
    {
        $perPage   = (int) $request->query('per_page', 15);
        $paginated = Purchase::with($this->relations())
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($perPage);

        return response()->json([
            'purchases' => $paginated->items(),
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
        $count  = Purchase::count();
        $code   = 'PUR-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
        $active = Status::where('code', 'ACT')->value('id');

        $purchase = Purchase::create([
            'code'         => $code,
            'supplier_id'  => $request->input('supplier_id'),
            'quotation_id' => $request->input('quotation_id'),
            'status_id'    => $request->input('status_id', $active),
            'currency_id'  => $request->input('currency_id'),
            'date'         => $request->input('date'),
            'notes'        => $request->input('notes'),
            'created_by'   => $request->user()?->id,
            'total'        => 0,
        ]);

        $total = 0;
        foreach ($request->input('items', []) as $row) {
            $lineTotal = round($row['quantity'] * $row['unit_price'], 2);
            $total    += $lineTotal;
            PurchaseItem::create([
                'purchase_id'        => $purchase->id,
                'item_master_id'     => $row['item_master_id'],
                'unit_of_measure_id' => $row['unit_of_measure_id'],
                'quantity'           => $row['quantity'],
                'unit_price'         => $row['unit_price'],
                'total'              => $lineTotal,
                'notes'              => $row['notes'] ?? null,
            ]);
        }

        $purchase->update(['total' => round($total, 2)]);

        return response()->json(['purchase' => $purchase->load($this->relations())], 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(['purchase' => Purchase::with($this->relations())->findOrFail($id)]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $purchase = Purchase::findOrFail($id);

        $fields = array_filter($request->only([
            'supplier_id', 'quotation_id', 'status_id', 'currency_id', 'date', 'notes',
        ]), fn($v) => $v !== null);

        $purchase->update($fields);

        if ($request->has('items')) {
            $purchase->items()->delete();
            $total = 0;
            foreach ($request->input('items') as $row) {
                $lineTotal = round($row['quantity'] * $row['unit_price'], 2);
                $total    += $lineTotal;
                PurchaseItem::create([
                    'purchase_id'        => $purchase->id,
                    'item_master_id'     => $row['item_master_id'],
                    'unit_of_measure_id' => $row['unit_of_measure_id'],
                    'quantity'           => $row['quantity'],
                    'unit_price'         => $row['unit_price'],
                    'total'              => $lineTotal,
                    'notes'              => $row['notes'] ?? null,
                ]);
            }
            $purchase->update(['total' => round($total, 2)]);
        }

        return response()->json(['purchase' => $purchase->load($this->relations())]);
    }

    public function destroy(int $id): JsonResponse
    {
        Purchase::findOrFail($id)->delete();
        return response()->json(['message' => 'Purchase deleted.']);
    }

    public function printUrl(int $id): JsonResponse
    {
        return response()->json(['print_url' => url("/expenses/purchases/{$id}")]);
    }
}
