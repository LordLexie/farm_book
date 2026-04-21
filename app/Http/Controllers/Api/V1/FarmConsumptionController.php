<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\FarmConsumption;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FarmConsumptionController extends Controller
{
    private function relations(): array
    {
        return ['farmItem.itemMaster', 'livestock', 'creator'];
    }

    public function index(Request $request): JsonResponse
    {
        $perPage   = (int) $request->query('per_page', 15);
        $paginated = FarmConsumption::with($this->relations())
            ->orderBy('consumption_date', 'desc')->orderBy('id', 'desc')->paginate($perPage);

        return response()->json([
            'farm_consumptions' => $paginated->items(),
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
        $consumption = FarmConsumption::create([
            'farm_item_id'     => $request->input('farm_item_id'),
            'quantity'         => $request->input('quantity'),
            'consumption_date' => $request->input('consumption_date'),
            'livestock_id'     => $request->input('livestock_id') ?: null,
            'created_by'       => $request->user()?->id,
        ]);

        return response()->json(['farm_consumption' => $consumption->load($this->relations())], 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(['farm_consumption' => FarmConsumption::with($this->relations())->findOrFail($id)]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $consumption = FarmConsumption::findOrFail($id);
        $fields      = array_filter(
            $request->only(['farm_item_id', 'quantity', 'consumption_date', 'livestock_id']),
            fn($v) => $v !== null,
        );
        $consumption->update($fields);

        return response()->json(['farm_consumption' => $consumption->load($this->relations())]);
    }

    public function destroy(int $id): JsonResponse
    {
        FarmConsumption::findOrFail($id)->delete();
        return response()->json(['message' => 'Consumption deleted.']);
    }
}
