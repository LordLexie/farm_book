<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\FarmItem;
use App\Models\Status;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FarmItemController extends Controller
{
    private function relations(): array
    {
        return ['farm', 'itemMaster.itemCategory', 'itemMaster.unitOfMeasure', 'status'];
    }

    public function index(Request $request): JsonResponse
    {
        $perPage   = (int) $request->query('per_page', 15);
        $paginated = FarmItem::with($this->relations())
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return response()->json([
            'farm_items' => $paginated->items(),
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
        $rows    = $request->input('items', []);
        $base    = FarmItem::count();
        $active  = Status::where('code', 'ACT')->value('id');
        $created = [];

        foreach ($rows as $i => $row) {
            $code      = 'FI-' . str_pad($base + $i + 1, 4, '0', STR_PAD_LEFT);
            $created[] = FarmItem::create([
                'code'           => $code,
                'farm_id'        => $row['farm_id'],
                'item_master_id' => $row['item_master_id'],
                'quantity'       => $row['quantity'],
                'status_id'      => $row['status_id'] ?? $active,
            ]);
        }

        $ids    = collect($created)->pluck('id');
        $result = FarmItem::with($this->relations())->whereIn('id', $ids)->get();

        return response()->json(['message' => 'Farm item created.', 'farm_items' => $result], 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(['farm_item' => FarmItem::with($this->relations())->findOrFail($id)]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $item = FarmItem::findOrFail($id);
        $item->update($request->only(['farm_id', 'item_master_id', 'quantity', 'status_id']));

        return response()->json(['message' => 'Farm item updated.', 'farm_item' => $item->load($this->relations())]);
    }

    public function destroy(int $id): JsonResponse
    {
        FarmItem::findOrFail($id)->delete();

        return response()->json(['message' => 'Farm item deleted.']);
    }
}
