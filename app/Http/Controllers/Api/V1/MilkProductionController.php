<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\MilkProduction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MilkProductionController extends Controller
{
    private function relations(): array
    {
        return ['livestock', 'farmSession', 'creator'];
    }

    public function index(Request $request): JsonResponse
    {
        $perPage   = (int) $request->query('per_page', 15);
        $paginated = MilkProduction::with($this->relations())
            ->orderBy('date', 'desc')->orderBy('id', 'desc')->paginate($perPage);

        return response()->json([
            'milk_productions' => $paginated->items(),
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
        $items = $request->input('data', []);
        $created = [];

        foreach ($items as $item) {
            $record = MilkProduction::create([
                'livestock_id'    => $item['livestock_id'],
                'farm_session_id' => $item['farm_session_id'],
                'date'            => $item['date'],
                'quantity'        => $item['quantity'],
                'created_by'      => $request->user()?->id,
            ]);
            $created[] = $record->load($this->relations());
        }

        return response()->json(['milk_productions' => $created], 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(['milk_production' => MilkProduction::with($this->relations())->findOrFail($id)]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $record = MilkProduction::findOrFail($id);
        $fields = array_filter(
            $request->only(['livestock_id', 'farm_session_id', 'date', 'quantity']),
            fn($v) => $v !== null,
        );
        $record->update($fields);

        return response()->json(['milk_production' => $record->load($this->relations())]);
    }

    public function destroy(int $id): JsonResponse
    {
        MilkProduction::findOrFail($id)->delete();
        return response()->json(['message' => 'Milk production deleted.']);
    }
}
