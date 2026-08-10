<?php

namespace App\Policies;

use App\Concerns\AuthorizesWithRoles;
use App\Models\Company;
use App\Models\User;

class CompanyPolicy
{
    use AuthorizesWithRoles;

    public function viewAny(User $user): bool
    {
        return $this->roleGate($user, 'companies.view', true);
    }

    public function view(User $user, Company $company): bool
    {
        return $this->roleGate($user, 'companies.view', true);
    }

    public function create(User $user): bool
    {
        return $this->roleGate($user, 'companies.create', true);
    }

    public function update(User $user, Company $company): bool
    {
        return $this->roleGate(
            $user,
            'companies.update',
            $company->owner_id === null || $company->owner_id === $user->id
        );
    }

    public function delete(User $user, Company $company): bool
    {
        return $this->roleGate(
            $user,
            'companies.delete',
            $company->owner_id === $user->id
        );
    }
}