<?php

namespace App\Events\Auth;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

class AccountLocked
{
    use Dispatchable;

    public function __construct(public User $user, public \Illuminate\Support\Carbon $lockedUntil) {}
}