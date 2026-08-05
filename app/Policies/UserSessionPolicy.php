<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserSession;

class UserSessionPolicy
{
    public function revoke(User $user, UserSession $session): bool
    {
        // Ownership only for now — admin override arrives with the Roles module.
        return $session->user_id === $user->id;
    }
}