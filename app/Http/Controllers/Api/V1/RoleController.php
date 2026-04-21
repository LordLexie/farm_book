<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage   = (int) $request->query('per_page', 15);
        $paginated = Role::with('permissions')->orderBy('name')->paginate($perPage);

        return response()->json([
            'roles' => $paginated->items(),
            'meta'  => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $role = Role::create(['name' => $request->input('name'), 'guard_name' => 'web']);

        return response()->json(['message' => 'Role created.', 'role' => $role->load('permissions')], 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(['role' => Role::with('permissions')->findOrFail($id)]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $role = Role::findOrFail($id);
        $role->update($request->only(['name']));

        return response()->json(['message' => 'Role updated.', 'role' => $role->load('permissions')]);
    }

    public function syncPermissions(Request $request, int $id): JsonResponse
    {
        $role = Role::findOrFail($id);
        $role->syncPermissions($request->input('permissions', []));

        return response()->json(['message' => 'Permissions synced.', 'role' => $role->load('permissions')]);
    }

    public function destroy(int $id): JsonResponse
    {
        Role::findOrFail($id)->delete();

        return response()->json(['message' => 'Role deleted.']);
    }
}
