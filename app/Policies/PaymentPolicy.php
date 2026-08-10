<?php

namespace App\Policies;

use App\Concerns\AuthorizesWithRoles;
use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    use AuthorizesWithRoles;

    public function viewAny(User $user): bool
    {
        return $this->roleGate($user, 'payments.view', true);
    }

    public function view(User $user, Payment $payment): bool
    {
        return $this->roleGate($user, 'payments.view', true);
    }

    public function create(User $user): bool
    {
        return $this->roleGate($user, 'payments.create', true);
    }

    public function update(User $user, Payment $payment): bool
    {
        return $this->roleGate($user, 'payments.update', true);
    }

    public function delete(User $user, Payment $payment): bool
    {
        return $this->roleGate($user, 'payments.delete', true);
    }
}