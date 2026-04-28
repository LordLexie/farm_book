<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\BillingCycle;
use App\Models\Currency;
use App\Models\Tax;
use App\Models\FarmSessionTemplate;
use App\Models\PaymentMode;
use App\Models\Gender;
use App\Models\ItemCategory;
use App\Models\LivestockType;
use App\Models\RatePlan;
use App\Models\ServiceType;
use App\Models\Status;
use App\Models\UnitOfMeasure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LookupController extends Controller
{
    public function statuses(Request $request): JsonResponse
    {
        $query = Status::orderBy('name');

        if ($request->has('category')) {
            $query->where('category', $request->query('category'));
        }

        return response()->json(['statuses' => $query->get()]);
    }

    public function genders(): JsonResponse
    {
        return response()->json(['genders' => Gender::orderBy('name')->get()]);
    }

    public function currencies(): JsonResponse
    {
        return response()->json(['currencies' => Currency::orderBy('name')->get()]);
    }

    public function taxes(): JsonResponse
    {
        return response()->json(['taxes' => Tax::orderBy('value')->get()]);
    }

    public function billingCycles(): JsonResponse
    {
        return response()->json(['billing_cycles' => BillingCycle::orderBy('name')->get()]);
    }

    public function livestockTypes(): JsonResponse
    {
        return response()->json(['livestock_types' => LivestockType::orderBy('name')->get()]);
    }

    public function paymentModes(): JsonResponse
    {
        return response()->json(['payment_modes' => PaymentMode::orderBy('name')->get()]);
    }

    public function ratePlans(): JsonResponse
    {
        return response()->json(['rate_plans' => RatePlan::orderBy('name')->get()]);
    }

    public function serviceTypes(Request $request): JsonResponse
    {
        $perPage   = (int) $request->query('per_page', 15);
        $paginated = ServiceType::orderBy('name')->paginate($perPage);

        return response()->json([
            'service_types' => $paginated->items(),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
            ],
        ]);
    }

    public function itemCategories(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 15);
        $paginated = ItemCategory::orderBy('name')->paginate($perPage);

        return response()->json([
            'item_categories' => $paginated->items(),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
            ],
        ]);
    }

    public function farmSessions(): JsonResponse
    {
        return response()->json(['farm_sessions' => FarmSessionTemplate::orderBy('id')->get()]);
    }

    public function unitOfMeasures(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 15);
        $paginated = UnitOfMeasure::orderBy('name')->paginate($perPage);

        return response()->json([
            'unit_of_measures' => $paginated->items(),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
            ],
        ]);
    }

    public function store(Request $request, string $resource): JsonResponse
    {
        $table = str_replace('-', '_', $resource);
        $items = $request->input('items', []);

        $rows = collect($items)->map(fn($item) => [
            'code'       => strtoupper(str_replace([' ', '-'], '_', $item['code'] ?? $item['name'])),
            'name'       => $item['name'],
            'created_at' => now(),
            'updated_at' => now(),
        ])->toArray();

        DB::table($table)->insertOrIgnore($rows);

        $names    = collect($items)->pluck('name')->toArray();
        $inserted = DB::table($table)->whereIn('name', $names)->get();

        $responseKey = str_replace('-', '_', $resource);

        return response()->json([$responseKey => $inserted], 201);
    }

    public function update(Request $request, string $resource, int $id): JsonResponse
    {
        $table = str_replace('-', '_', $resource);
        $data  = array_filter($request->only(['code', 'name']), fn($v) => $v !== null);
        $data['updated_at'] = now();

        DB::table($table)->where('id', $id)->update($data);

        $row = DB::table($table)->where('id', $id)->first();

        return response()->json([
            'message' => ucfirst(str_replace('-', ' ', $resource)).' updated.',
            rtrim(str_replace('-', '_', $resource), 's') => $row,
        ]);
    }

    private function emptyMeta(): array
    {
        return ['current_page' => 1, 'last_page' => 1, 'per_page' => 15, 'total' => 0];
    }
}
