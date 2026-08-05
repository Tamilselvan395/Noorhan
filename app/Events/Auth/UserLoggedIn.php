<?php

namespace App\Events\Auth;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

class UserLoggedIn
{
    use Dispatchable;

    public function __construct(
        public User $user,
        public bool $newDevice,
        public string $ip,
    ) {}
}