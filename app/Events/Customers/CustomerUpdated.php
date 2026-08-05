<?php

namespace App\Events\Customers;

use App\Models\Customer;
use Illuminate\Foundation\Events\Dispatchable;

class CustomerUpdated
{
    use Dispatchable;

    public function __construct(public Customer $customer) {}
}