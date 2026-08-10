<?php

namespace App\Policies;

use App\Concerns\AuthorizesWithRoles;
use App\Models\Lead;
use App\Models\User;

class LeadPolicy
{
    use AuthorizesWithRoles;

    public function viewAny(User $user): bool
    {
        return $this->roleGate($user, 'leads.view', true);
    }

    public function view(User $user, Lead $lead): bool
    {
        return $this->roleGate($user, 'leads.view', true);
    }

    public function create(User $user): bool
    {
        return $this->roleGate($user, 'leads.create', true);
    }

    public function update(User $user, Lead $lead): bool
    {
        return $this->roleGate($user, 'leads.update',
            $lead->assigned_to === null || $lead->assigned_to === $user->id || $lead->created_by === $user->id);
    }

    public function delete(User $user, Lead $lead): bool
    {
        return $this->roleGate($user, 'leads.delete', $lead->created_by === $user->id || $lead->assigned_to === $user->id);
    }
}