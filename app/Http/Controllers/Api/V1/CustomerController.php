<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Status;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    private function relations(): array
    {
        return ['status', 'gender', 'billingCycle'];
    }

    public function index(Request $request): JsonResponse
    {
        $perPage   = (int) $request->query('per_page', 15);
        $paginated = Customer::with($this->relations())
            ->orderBy('name')
            ->paginate($perPage);

        return response()->json([
            'customers' => $paginated->items(),
            'meta'      => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $code   = 'CUS-' . str_pad(Customer::count() + 1, 4, '0', STR_PAD_LEFT);
        $active = Status::where('code', 'ACT')->value('id');

        $customer = Customer::create([
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
            'billing_cycle_id'    => $request->input('billing_cycle_id'),
            'status_id'           => $request->input('status_id', $active),
        ]);

        return response()->json(['customer' => $customer->load($this->relations())], 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(['customer' => Customer::with($this->relations())->findOrFail($id)]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $customer = Customer::findOrFail($id);
        $customer->update(array_filter($request->only([
            'type', 'name', 'email', 'phone', 'address',
            'first_name', 'last_name', 'gender_id',
            'registration_number', 'contact_person',
            'billing_cycle_id', 'status_id',
        ]), fn($v) => $v !== null));

        return response()->json(['customer' => $customer->load($this->relations())]);
    }

    public function destroy(int $id): JsonResponse
    {
        Customer::findOrFail($id)->delete();
        return response()->json(['message' => 'Customer deleted.']);
    }
}
