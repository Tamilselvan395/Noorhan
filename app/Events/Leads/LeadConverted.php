<?php

namespace App\Events\Leads;

use App\Models\Customer;
use App\Models\Lead;
use Illuminate\Foundation\Events\Dispatchable;

class LeadConverted
{
    use Dispatchable;

    public function __construct(public Lead $lead, public Customer $customer) {}
}