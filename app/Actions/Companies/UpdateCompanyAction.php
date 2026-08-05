<?php

namespace App\Actions\Companies;

use App\DTOs\Companies\CompanyDTO;
use App\Events\Companies\CompanyUpdated;
use App\Models\Company;

class UpdateCompanyAction
{
    public function execute(Company $company, CompanyDTO $dto): void
    {
        $company->update($dto->toArray());

        $company->logActivity('updated the company record');

        event(new CompanyUpdated($company));
    }
}