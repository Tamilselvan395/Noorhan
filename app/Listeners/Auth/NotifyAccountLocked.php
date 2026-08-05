<?php

namespace App\Listeners\Auth;

use App\Events\Auth\AccountLocked;
use App\Notifications\AccountLockedNotification;

class NotifyAccountLocked
{
    public function handle(AccountLocked $event): void
    {
        $event->user->notify(new AccountLockedNotification($event->lockedUntil));
    }
}