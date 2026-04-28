<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\MilkRate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MilkRateController extends Controller
{
    public function index(): JsonResponse
    {
        $rates = MilkRate::with('ratePlan')->orderBy('id')->get();

        return response()->json(['milk_rates' => $rates]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'rate_plan_id' => 'required|integer|exists:rate_plans,id',
            'price'        => 'required|numeric|min:0',
        ]);

        $rate = MilkRate::create($data);

        return response()->json(['milk_rate' => $rate->load('ratePlan')], 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(['milk_rate' => MilkRate::with('ratePlan')->findOrFail($id)]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $rate = MilkRate::findOrFail($id);

        $data = $request->validate([
            'price' => 'required|numeric|min:0',
        ]);

        $rate->update($data);

        return response()->json(['message' => 'Milk rate updated.', 'milk_rate' => $rate->load('ratePlan')]);
    }

    public function destroy(int $id): JsonResponse
    {
        MilkRate::findOrFail($id)->delete();

        return response()->json(['message' => 'Milk rate deleted.']);
    }
}
