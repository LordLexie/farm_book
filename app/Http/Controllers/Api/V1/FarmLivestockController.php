<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\FarmLivestock;
use App\Models\Status;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FarmLivestockController extends Controller
{
    private function relations(): array
    {
        return ['farm', 'livestockType', 'status', 'gender'];
    }

    public function index(Request $request): JsonResponse
    {
        $perPage   = (int) $request->query('per_page', 15);
        $paginated = FarmLivestock::with($this->relations())
            ->orderBy('id', 'desc')->paginate($perPage);

        return response()->json([
            'farm_livestocks' => $paginated->items(),
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
        $alive   = Status::where('code', 'ALIVE')->value('id');
        $created = [];

        foreach ($request->input('items', []) as $row) {
            $code       = 'LVS-' . str_pad(FarmLivestock::count() + 1, 4, '0', STR_PAD_LEFT);
            $created[] = FarmLivestock::create([
                'code'              => $code,
                'farm_id'           => $row['farm_id'],
                'livestock_type_id' => $row['livestock_type_id'],
                'gender_id'         => $row['gender_id'],
                'name'              => $row['name'] ?? null,
                'description'       => $row['description'] ?? null,
                'date_of_birth'     => $row['date_of_birth'] ?? null,
                'breed'             => $row['breed'] ?? null,
                'status_id'         => $row['status_id'] ?? $alive,
            ])->load($this->relations());
        }

        return response()->json(['farm_livestocks' => $created], 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(['farm_livestock' => FarmLivestock::with($this->relations())->findOrFail($id)]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $livestock = FarmLivestock::findOrFail($id);
        $fields    = array_filter(
            $request->only([
                'farm_id', 'livestock_type_id', 'gender_id', 'status_id',
                'name', 'description', 'date_of_birth', 'breed',
            ]),
            fn($v) => $v !== null,
        );
        $livestock->update($fields);

        return response()->json(['farm_livestock' => $livestock->load($this->relations())]);
    }

    public function destroy(int $id): JsonResponse
    {
        FarmLivestock::findOrFail($id)->delete();
        return response()->json(['message' => 'Livestock deleted.']);
    }
}
