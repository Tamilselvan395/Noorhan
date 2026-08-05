<?php

namespace App\Events\Companies;

use App\Models\Company;
use Illuminate\Foundation\Events\Dispatchable;

class CompanyUpdated
{
    use Dispatchable;

    public function __construct(public Company $company) {}
}