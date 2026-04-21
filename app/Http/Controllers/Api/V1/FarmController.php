<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Farm;
use App\Models\Status;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FarmController extends Controller
{
    public function index(): JsonResponse
    {
        $farms = Farm::with('status')->orderBy('name')->get();

        return response()->json(['farms' => $farms]);
    }

    public function store(Request $request): JsonResponse
    {
        $count  = Farm::count() + 1;
        $code   = 'FARM-' . str_pad($count, 4, '0', STR_PAD_LEFT);
        $active = Status::where('code', 'ACT')->first();

        $farm = Farm::create([
            'code'      => $code,
            'name'      => $request->input('name'),
            'latitude'  => $request->input('latitude'),
            'longitude' => $request->input('longitude'),
            'status_id' => $active?->id,
        ]);

        return response()->json(['message' => 'Farm created.', 'farm' => $farm->load('status')], 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(['farm' => Farm::with('status')->findOrFail($id)]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $farm = Farm::findOrFail($id);
        $farm->update($request->only(['name', 'latitude', 'longitude', 'status_id']));

        return response()->json(['message' => 'Farm updated.', 'farm' => $farm->load('status')]);
    }

    public function destroy(int $id): JsonResponse
    {
        Farm::findOrFail($id)->delete();

        return response()->json(['message' => 'Farm deleted.']);
    }
}
