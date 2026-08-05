<?php

namespace App\Actions\Customers;

use App\DTOs\Customers\CustomerDTO;
use App\Events\Customers\CustomerUpdated;
use App\Models\Customer;

class UpdateCustomerAction
{
    public function execute(Customer $customer, CustomerDTO $dto): void
    {
        $customer->update($dto->toArray());

        $customer->logActivity('updated the customer record');

        event(new CustomerUpdated($customer));
    }
}