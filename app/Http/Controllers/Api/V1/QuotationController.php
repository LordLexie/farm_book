<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Status;
use App\Models\Tax;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuotationController extends Controller
{
    private function relations(): array
    {
        return ['customer', 'status', 'currency', 'tax', 'items.unitOfMeasure'];
    }

    private function computeTotal(float $subtotal, float $discount, ?int $taxId): float
    {
        $afterDiscount = $subtotal * (1 - $discount / 100);
        $tax = $taxId ? Tax::find($taxId) : null;
        $taxAmount = $tax ? $afterDiscount * ($tax->value / 100) : 0;
        return round($afterDiscount + $taxAmount, 2);
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

        $discount = (float) $request->input('discount', 0);
        $taxId    = $request->input('tax_id');

        $quotation = Quotation::create([
            'code'        => $code,
            'customer_id' => $request->input('customer_id'),
            'status_id'   => $active,
            'currency_id' => $request->input('currency_id'),
            'tax_id'      => $taxId,
            'discount'    => $discount,
            'date'        => $request->input('date'),
            'valid_until' => $request->input('valid_until'),
            'notes'       => $request->input('notes'),
            'created_by'  => $request->user()?->id,
            'total'       => 0,
        ]);

        $subtotal = 0;
        foreach ($request->input('items', []) as $row) {
            $lineTotal = round($row['quantity'] * $row['unit_price'], 2);
            $subtotal += $lineTotal;
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
        $quotation->update(['total' => $this->computeTotal($subtotal, $discount, $taxId)]);

        return response()->json(['quotation' => $quotation->load($this->relations())], 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(['quotation' => Quotation::with($this->relations())->findOrFail($id)]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $quotation = Quotation::findOrFail($id);
        $fields = array_filter(
            $request->only(['customer_id', 'status_id', 'currency_id', 'tax_id', 'discount', 'date', 'valid_until', 'notes']),
            fn($v) => $v !== null,
        );
        if ($request->has('tax_id') && $request->input('tax_id') === null) {
            $fields['tax_id'] = null;
        }
        $quotation->update($fields);

        if ($request->has('items')) {
            $quotation->items()->delete();
            $subtotal = 0;
            foreach ($request->input('items') as $row) {
                $lineTotal = round($row['quantity'] * $row['unit_price'], 2);
                $subtotal += $lineTotal;
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
            $discount = (float) ($fields['discount'] ?? $quotation->discount);
            $taxId    = $quotation->fresh()->tax_id;
            $quotation->update(['total' => $this->computeTotal($subtotal, $discount, $taxId)]);
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
