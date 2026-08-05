<?php

namespace App\Actions\Companies;

use App\Models\Company;
use App\Models\Customer;

class AttachContactAction
{
    public function execute(Company $company, Customer $customer): void
    {
        $customer->update(['company_id' => $company->id]);

        $company->logActivity("linked contact {$customer->name}");
        $customer->logActivity("was linked to company {$company->name}");
    }
}