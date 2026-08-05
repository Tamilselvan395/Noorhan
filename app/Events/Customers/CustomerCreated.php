<?php

namespace App\Events\Customers;

use App\Models\Customer;
use Illuminate\Foundation\Events\Dispatchable;

class CustomerCreated
{
    use Dispatchable;

    public function __construct(public Customer $customer) {}
}