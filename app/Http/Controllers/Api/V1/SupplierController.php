<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Status;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    private function relations(): array
    {
        return ['status', 'gender'];
    }

    public function index(): JsonResponse
    {
        $suppliers = Supplier::with($this->relations())->orderBy('name')->get();
        return response()->json(['suppliers' => $suppliers]);
    }

    public function store(Request $request): JsonResponse
    {
        $count  = Supplier::count();
        $code   = 'SUP-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
        $active = Status::where('code', 'ACT')->value('id');

        $supplier = Supplier::create([
            'code'                => $code,
            'type'                => $request->input('type', 'individual'),
            'name'                => $request->input('name'),
            'email'               => $request->input('email'),
            'phone'               => $request->input('phone'),
            'address'             => $request->input('address'),
            'first_name'          => $request->input('first_name'),
            'last_name'           => $request->input('last_name'),
            'gender_id'           => $request->input('gender_id'),
            'registration_number' => $request->input('registration_number'),
            'contact_person'      => $request->input('contact_person'),
            'status_id'           => $request->input('status_id', $active),
        ]);

        return response()->json(['supplier' => $supplier->load($this->relations())], 201);
    }

    public function show(int $id): JsonResponse
    {
        $supplier = Supplier::with($this->relations())->findOrFail($id);
        return response()->json(['supplier' => $supplier]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->update($request->only([
            'type', 'name', 'email', 'phone', 'address',
            'first_name', 'last_name', 'gender_id',
            'registration_number', 'contact_person', 'status_id',
        ]));
        return response()->json(['supplier' => $supplier->load($this->relations())]);
    }

    public function destroy(int $id): JsonResponse
    {
        Supplier::findOrFail($id)->delete();
        return response()->json(['message' => 'Supplier deleted.']);
    }
}
