<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;


class PermissionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $permission = Permission::create([
            'name'       => $request->input('name'),
            'guard_name' => 'web',
        ]);

        return response()->json(['message' => 'Permission created.', 'permission' => $permission], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $permission = Permission::findOrFail($id);
        $permission->update(['name' => $request->input('name')]);

        return response()->json(['message' => 'Permission updated.', 'permission' => $permission]);
    }

    public function index(Request $request): JsonResponse
    {
        $perPage   = (int) $request->query('per_page', 15);
        $paginated = Permission::orderBy('name')->paginate($perPage);

        return response()->json([
            'permissions' => $paginated->items(),
            'meta'        => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
            ],
        ]);
    }
}
