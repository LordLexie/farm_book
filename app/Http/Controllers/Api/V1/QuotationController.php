<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Status;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuotationController extends Controller
{
    private function relations(): array
    {
        return ['customer', 'status', 'items.unitOfMeasure'];
    }

    public function index(Request $request): JsonResponse
    {
        $perPage   = (int) $request->query('per_page', 15);
        $paginated = Quotation::with($this->relations())
            ->orderBy('date', 'desc')->orderBy('id', 'desc')->paginate($perPage);

        return response()->json([
            'quotations' => $paginated->items(),
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
        $code   = 'QUO-' . str_pad(Quotation::count() + 1, 4, '0', STR_PAD_LEFT);
        $active = Status::where('code', 'ACT')->value('id');

        $quotation = Quotation::create([
            'code'        => $code,
            'customer_id' => $request->input('customer_id'),
            'status_id'   => $active,
            'date'        => $request->input('date'),
            'valid_until' => $request->input('valid_until'),
            'notes'       => $request->input('notes'),
            'created_by'  => $request->user()?->id,
            'total'       => 0,
        ]);

        $total = 0;
        foreach ($request->input('items', []) as $row) {
            $lineTotal = round($row['quantity'] * $row['unit_price'], 2);
            $total += $lineTotal;
            QuotationItem::create([
                'quotation_id'       => $quotation->id,
                'name'               => $row['name'],
                'description'        => $row['description'] ?? null,
                'unit_of_measure_id' => $row['unit_of_measure_id'],
                'quantity'           => $row['quantity'],
                'unit_price'         => $row['unit_price'],
                'total'              => $lineTotal,
            ]);
        }
        $quotation->update(['total' => round($total, 2)]);

        return response()->json(['quotation' => $quotation->load($this->relations())], 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(['quotation' => Quotation::with($this->relations())->findOrFail($id)]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $quotation = Quotation::findOrFail($id);
        $fields    = array_filter(
            $request->only(['customer_id', 'status_id', 'date', 'valid_until', 'notes']),
            fn($v) => $v !== null,
        );
        $quotation->update($fields);

        if ($request->has('items')) {
            $quotation->items()->delete();
            $total = 0;
            foreach ($request->input('items') as $row) {
                $lineTotal = round($row['quantity'] * $row['unit_price'], 2);
                $total += $lineTotal;
                QuotationItem::create([
                    'quotation_id'       => $quotation->id,
                    'name'               => $row['name'],
                    'description'        => $row['description'] ?? null,
                    'unit_of_measure_id' => $row['unit_of_measure_id'],
                    'quantity'           => $row['quantity'],
                    'unit_price'         => $row['unit_price'],
                    'total'              => $lineTotal,
                ]);
            }
            $quotation->update(['total' => round($total, 2)]);
        }

        return response()->json(['quotation' => $quotation->load($this->relations())]);
    }

    public function destroy(int $id): JsonResponse
    {
        Quotation::findOrFail($id)->delete();
        return response()->json(['message' => 'Quotation deleted.']);
    }

    public function printUrl(int $id): JsonResponse
    {
        return response()->json(['url' => url("/quotations/{$id}/print")]);
    }
}
