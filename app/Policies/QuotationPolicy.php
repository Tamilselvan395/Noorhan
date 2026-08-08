<?php

namespace App\Policies;

use App\Enums\QuotationStatus;
use App\Models\Quotation;
use App\Models\User;

class QuotationPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Quotation $quotation): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Quotation $quotation): bool
    {
        return $quotation->status() === QuotationStatus::Draft;
    }

    /** Separation of duties: the author cannot self-approve. */
    public function approve(User $user, Quotation $quotation): bool
    {
        return $user->id !== $quotation->created_by;
    }

    public function send(User $user, Quotation $quotation): bool
    {
        return true;
    }
}