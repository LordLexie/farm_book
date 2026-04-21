<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage   = (int) $request->query('per_page', 15);
        $paginated = User::with('roles')->orderBy('name')->paginate($perPage);

        return response()->json([
            'users' => $paginated->items(),
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
        $user = User::create([
            'name'     => $request->input('name'),
            'email'    => $request->input('email'),
            'password' => Hash::make($request->input('password')),
        ]);

        if ($role = $request->input('role')) {
            $user->syncRoles([$role]);
        }

        return response()->json(['message' => 'User created.', 'user' => $user->load('roles')], 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(['user' => User::with('roles')->findOrFail($id)]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $user->update($request->only(['name', 'email']));

        if ($role = $request->input('role')) {
            $user->syncRoles([$role]);
        }

        return response()->json(['message' => 'User updated.', 'user' => $user->load('roles')]);
    }

    public function destroy(int $id): JsonResponse
    {
        User::findOrFail($id)->delete();

        return response()->json(['message' => 'User deleted.']);
    }
}
