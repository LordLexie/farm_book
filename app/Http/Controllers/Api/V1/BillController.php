<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\BillItem;
use App\Models\Service;
use App\Models\Status;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BillController extends Controller
{
    private function relations(): array
    {
        return ['supplier', 'status', 'currency', 'items.service', 'items.unitOfMeasure'];
    }

    public function index(Request $request): JsonResponse
    {
        $perPage   = (int) $request->query('per_page', 15);
        $paginated = Bill::with($this->relations())
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($perPage);

        return response()->json([
            'bills' => $paginated->items(),
            'meta'  => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'supplier_id'          => 'required|integer|exists:suppliers,id',
            'currency_id'          => 'required|integer|exists:currencies,id',
            'date'                 => 'required|date',
            'items'                => 'required|array|min:1',
            'items.*.service_id'   => 'required|integer|exists:services,id',
            'items.*.quantity'     => 'required|numeric|min:0',
            'items.*.unit_price'   => 'required|numeric|min:0',
        ]);

        $active = Status::where('code', 'ACT')->value('id');

        $bill = DB::transaction(function () use ($request, $active) {
            $code = 'BIL-' . str_pad(Bill::count() + 1, 4, '0', STR_PAD_LEFT);

            $bill = Bill::create([
                'code'        => $code,
                'supplier_id' => $request->input('supplier_id'),
                'status_id'   => $request->input('status_id', $active),
                'currency_id' => $request->input('currency_id'),
                'date'        => $request->input('date'),
                'notes'       => $request->input('notes'),
                'created_by'  => $request->user()?->id,
                'total'       => 0,
            ]);

            $total = 0;
            foreach ($request->input('items') as $row) {
                $uomId     = $row['unit_of_measure_id'] ?: Service::find($row['service_id'])?->unit_of_measure_id;
                $lineTotal = round($row['quantity'] * $row['unit_price'], 2);
                $total    += $lineTotal;
                BillItem::create([
                    'bill_id'            => $bill->id,
                    'service_id'         => $row['service_id'],
                    'unit_of_measure_id' => $uomId,
                    'quantity'           => $row['quantity'],
                    'unit_price'         => $row['unit_price'],
                    'total'              => $lineTotal,
                    'notes'              => $row['notes'] ?? null,
                ]);
            }

            $bill->update(['total' => round($total, 2)]);
            return $bill;
        });

        return response()->json(['bill' => $bill->load($this->relations())], 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(['bill' => Bill::with($this->relations())->findOrFail($id)]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $bill = Bill::findOrFail($id);

        $fields = array_filter($request->only([
            'supplier_id', 'status_id', 'currency_id', 'date', 'notes',
        ]), fn($v) => $v !== null);

        $bill->update($fields);

        if ($request->has('items')) {
            $bill->items()->delete();
            $total = 0;
            foreach ($request->input('items') as $row) {
                $lineTotal = round($row['quantity'] * $row['unit_price'], 2);
                $total    += $lineTotal;
                BillItem::create([
                    'bill_id'            => $bill->id,
                    'service_id'         => $row['service_id'],
                    'unit_of_measure_id' => $row['unit_of_measure_id'],
                    'quantity'           => $row['quantity'],
                    'unit_price'         => $row['unit_price'],
                    'total'              => $lineTotal,
                    'notes'              => $row['notes'] ?? null,
                ]);
            }
            $bill->update(['total' => round($total, 2)]);
        }

        return response()->json(['bill' => $bill->load($this->relations())]);
    }

    public function destroy(int $id): JsonResponse
    {
        Bill::findOrFail($id)->delete();
        return response()->json(['message' => 'Bill deleted.']);
    }
}
