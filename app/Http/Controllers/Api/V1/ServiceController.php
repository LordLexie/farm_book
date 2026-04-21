<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Status;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    private function relations(): array
    {
        return ['serviceType', 'unitOfMeasure', 'status'];
    }

    public function index(Request $request): JsonResponse
    {
        $perPage   = (int) $request->query('per_page', 15);
        $paginated = Service::with($this->relations())
            ->orderBy('name')
            ->paginate($perPage);

        return response()->json([
            'services' => $paginated->items(),
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
        $rows    = $request->input('services', []);
        $base    = Service::count();
        $active  = Status::where('code', 'ACT')->value('id');
        $created = [];

        foreach ($rows as $i => $row) {
            $code      = 'SVC-' . str_pad($base + $i + 1, 4, '0', STR_PAD_LEFT);
            $created[] = Service::create([
                'code'               => $code,
                'name'               => $row['name'],
                'description'        => $row['description'] ?? null,
                'service_type_id'    => $row['service_type_id'],
                'unit_of_measure_id' => $row['unit_of_measure_id'] ?? null,
                'status_id'          => $row['status_id'] ?? $active,
            ]);
        }

        $ids    = collect($created)->pluck('id');
        $result = Service::with($this->relations())->whereIn('id', $ids)->get();

        return response()->json(['services' => $result], 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(['service' => Service::with($this->relations())->findOrFail($id)]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $service = Service::findOrFail($id);
        $service->update($request->only(['name', 'description', 'service_type_id', 'unit_of_measure_id', 'status_id']));

        return response()->json(['message' => 'Service updated.', 'service' => $service->load($this->relations())]);
    }

    public function destroy(int $id): JsonResponse
    {
        Service::findOrFail($id)->delete();

        return response()->json(['message' => 'Service deleted.']);
    }
}
