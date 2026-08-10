<?php

namespace App\Policies;

use App\Concerns\AuthorizesWithRoles;
use App\Enums\QuotationStatus;
use App\Models\Quotation;
use App\Models\User;

class QuotationPolicy
{
    use AuthorizesWithRoles;

    public function viewAny(User $user): bool
    {
        return $this->roleGate($user, 'quotations.view', true);
    }

    public function view(User $user, Quotation $quotation): bool
    {
        return $this->roleGate($user, 'quotations.view', true);
    }

    public function create(User $user): bool
    {
        return $this->roleGate($user, 'quotations.create', true);
    }

    public function update(User $user, Quotation $quotation): bool
    {
        return $this->roleGate(
            $user,
            'quotations.update',
            $quotation->status() === QuotationStatus::Draft
        );
    }

    /** Separation of duties: the author cannot self-approve. */
    public function approve(User $user, Quotation $quotation): bool
    {
        return $this->roleGate(
            $user,
            'quotations.approve',
            $user->id !== $quotation->created_by
        ) && $user->id !== $quotation->created_by; // self-approval never allowed
    }

    public function send(User $user, Quotation $quotation): bool
    {
        return $this->roleGate($user, 'quotations.send', true);
    }
}