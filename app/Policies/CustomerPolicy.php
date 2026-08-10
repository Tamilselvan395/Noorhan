<?php

namespace App\Policies;

use App\Concerns\AuthorizesWithRoles;
use App\Models\Customer;
use App\Models\User;

class CustomerPolicy
{
    use AuthorizesWithRoles;

    public function viewAny(User $user): bool
    {
        return $this->roleGate($user, 'customers.view', true);
    }

    public function view(User $user, Customer $customer): bool
    {
        return $this->roleGate($user, 'customers.view', true);
    }

    public function create(User $user): bool
    {
        return $this->roleGate($user, 'customers.create', true);
    }

    public function update(User $user, Customer $customer): bool
    {
        return $this->roleGate(
            $user,
            'customers.update',
            $customer->owner_id === null || $customer->owner_id === $user->id
        );
    }

    public function delete(User $user, Customer $customer): bool
    {
        return $this->roleGate(
            $user,
            'customers.delete',
            $customer->owner_id === $user->id
        );
    }
}