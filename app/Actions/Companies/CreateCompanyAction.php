<?php

namespace App\Actions\Companies;

use App\DTOs\Companies\CompanyDTO;
use App\Events\Companies\CompanyCreated;
use App\Models\Company;
use App\Models\User;

class CreateCompanyAction
{
    public function execute(CompanyDTO $dto, ?User $creator = null): Company
    {
        $company = Company::create($dto->toArray());

        $company->logActivity('created the company record');

        event(new CompanyCreated($company));

        return $company;
    }
}