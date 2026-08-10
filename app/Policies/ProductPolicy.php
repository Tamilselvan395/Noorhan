<?php

namespace App\Policies;

use App\Concerns\AuthorizesWithRoles;
use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    use AuthorizesWithRoles;

    public function viewAny(User $user): bool
    {
        return $this->roleGate($user, 'products.view', true);
    }

    public function view(User $user, Product $product): bool
    {
        return $this->roleGate($user, 'products.view', true);
    }

    public function create(User $user): bool
    {
        return $this->roleGate($user, 'products.create', true);
    }

    public function update(User $user, Product $product): bool
    {
        return $this->roleGate($user, 'products.update', true);
    }

    public function delete(User $user, Product $product): bool
    {
        return $this->roleGate($user, 'products.delete', true);
    }
}