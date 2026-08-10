<?php

namespace App\Policies;

use App\Concerns\AuthorizesWithRoles;
use App\Models\SalesOrder;
use App\Models\User;

class SalesOrderPolicy
{
    use AuthorizesWithRoles;

    public function viewAny(User $user): bool
    {
        return $this->roleGate($user, 'orders.view', true);
    }

    public function view(User $user, SalesOrder $order): bool
    {
        return $this->roleGate($user, 'orders.view', true);
    }

    public function create(User $user): bool
    {
        return $this->roleGate($user, 'orders.create', true);
    }

    public function update(User $user, SalesOrder $order): bool
    {
        return $this->roleGate(
            $user,
            'orders.update',
            $order->isOpen()
        );
    }
}