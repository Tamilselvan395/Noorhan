<?php

namespace App\Actions\Customers;

use App\DTOs\Customers\CustomerDTO;
use App\Events\Customers\CustomerCreated;
use App\Models\Customer;
use App\Models\User;

class CreateCustomerAction
{
    public function execute(CustomerDTO $dto, ?User $creator = null): Customer
    {
        $customer = Customer::create($dto->toArray());

        $customer->logActivity('created the customer record');

        event(new CustomerCreated($customer));

        return $customer;
    }
}