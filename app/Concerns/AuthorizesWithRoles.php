<?php

namespace App\Concerns;

use App\Models\User;

/**
 * RBAC with graceful rollout:
 *  - Super Admin      → everything (also via Gate::before)
 *  - Users WITH roles → strict Spatie permission check
 *  - Users WITHOUT    → legacy behavior (ownership rules) so operations
 *                       never break before the org assigns roles.
 */
trait AuthorizesWithRoles
{
    protected function roleGate(User $user, string $permission, bool $legacy): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        if ($user->roles()->exists()) {
            return $user->can($permission);
        }

        return $legacy;
    }
}