<?php

namespace App\Policies;

use App\Concerns\AuthorizesWithRoles;
use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    use AuthorizesWithRoles;

    public function viewAny(User $user): bool
    {
        return $this->roleGate($user, 'invoices.view', true);
    }

    public function view(User $user, Invoice $invoice): bool
    {
        return $this->roleGate($user, 'invoices.view', true);
    }

    public function create(User $user): bool
    {
        return $this->roleGate($user, 'invoices.create', true);
    }

    public function update(User $user, Invoice $invoice): bool
    {
        return $this->roleGate(
            $user,
            'invoices.update',
            $invoice->status() === InvoiceStatus::Draft
        );
    }

    public function send(User $user, Invoice $invoice): bool
    {
        return $this->roleGate(
            $user,
            'invoices.send',
            $invoice->status() === InvoiceStatus::Draft
        );
    }
}