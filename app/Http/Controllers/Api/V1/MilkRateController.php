<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MilkRateController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['milk_rates' => [], 'meta' => $this->emptyMeta()]);
    }

    public function store(Request $request): JsonResponse
    {
        return response()->json(['milk_rate' => []], 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(['milk_rate' => []]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        return response()->json(['message' => 'Milk rate updated.', 'milk_rate' => []]);
    }

    public function destroy(int $id): JsonResponse
    {
        return response()->json(['message' => 'Milk rate deleted.']);
    }

    private function emptyMeta(): array
    {
        return ['current_page' => 1, 'last_page' => 1, 'per_page' => 15, 'total' => 0];
    }
}
