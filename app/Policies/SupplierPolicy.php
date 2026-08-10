<?php

namespace App\Policies;

use App\Concerns\AuthorizesWithRoles;
use App\Models\Supplier;
use App\Models\User;

class SupplierPolicy
{
    use AuthorizesWithRoles;

    public function viewAny(User $user): bool
    {
        return $this->roleGate($user, 'suppliers.view', true);
    }

    public function view(User $user, Supplier $supplier): bool
    {
        return $this->roleGate($user, 'suppliers.view', true);
    }

    public function create(User $user): bool
    {
        return $this->roleGate($user, 'suppliers.create', true);
    }

    public function update(User $user, Supplier $supplier): bool
    {
        return $this->roleGate(
            $user,
            'suppliers.update',
            $supplier->owner_id === null || $supplier->owner_id === $user->id
        );
    }

    public function delete(User $user, Supplier $supplier): bool
    {
        return $this->roleGate(
            $user,
            'suppliers.delete',
            $supplier->owner_id === $user->id
        );
    }
}