<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ItemMaster;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ItemMasterController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage   = (int) $request->query('per_page', 15);
        $paginated = ItemMaster::with(['itemCategory', 'unitOfMeasure'])
            ->orderBy('name')
            ->paginate($perPage);

        return response()->json([
            'item_masters' => $paginated->items(),
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
        $items   = $request->input('items', []);
        $created = [];
        $base    = ItemMaster::count();

        foreach ($items as $i => $row) {
            $code = 'ITEM-' . str_pad($base + $i + 1, 4, '0', STR_PAD_LEFT);

            $created[] = ItemMaster::create([
                'code'               => $code,
                'name'               => $row['name'],
                'description'        => $row['description'] ?? null,
                'item_category_id'   => $row['item_category_id'],
                'unit_of_measure_id' => $row['unit_of_measure_id'],
            ]);
        }

        $ids    = collect($created)->pluck('id');
        $result = ItemMaster::with(['itemCategory', 'unitOfMeasure'])->whereIn('id', $ids)->get();

        return response()->json(['message' => 'Item master created.', 'item_masters' => $result], 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(['item_master' => ItemMaster::with(['itemCategory', 'unitOfMeasure'])->findOrFail($id)]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $item = ItemMaster::findOrFail($id);
        $item->update($request->only(['name', 'description', 'item_category_id', 'unit_of_measure_id']));

        return response()->json(['message' => 'Item master updated.', 'item_master' => $item->load(['itemCategory', 'unitOfMeasure'])]);
    }

    public function destroy(int $id): JsonResponse
    {
        ItemMaster::findOrFail($id)->delete();

        return response()->json(['message' => 'Item master deleted.']);
    }
}
