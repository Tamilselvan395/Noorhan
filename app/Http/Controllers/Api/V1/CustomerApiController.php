<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Customers\CreateCustomerAction;
use App\DTOs\Customers\CustomerDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customers\StoreCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerApiController extends Controller
{
    public function index(Request $request)
    {
        $customers = Customer::query()
            ->when($request->query('type'), fn ($q) => $q->where('type', $request->query('type')))
            ->when($request->query('status'), fn ($q) => $q->where('status', $request->query('status')))
            ->search($request->query('search'))
            ->latest()
            ->paginate(min((int) $request->query('per_page', 25), 100));

        return CustomerResource::collection($customers);
    }

    public function store(StoreCustomerRequest $request, CreateCustomerAction $action)
    {
        $data = $request->validated();

        $customer = $action->execute(CustomerDTO::fromArray($data + ['status' => $data['status'] ?? 'active']), $request->user());

        return (new CustomerResource($customer))->response()->setStatusCode(201);
    }

    public function show(Customer $customer)
    {
        return new CustomerResource($customer);
    }
}