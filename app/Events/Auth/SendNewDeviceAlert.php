<?php

namespace App\Listeners\Auth;

use App\Events\Auth\UserLoggedIn;
use App\Notifications\NewDeviceLoginNotification;

class SendNewDeviceAlert
{
    public function handle(UserLoggedIn $event): void
    {
        if ($event->newDevice && config('noorhan.auth.alert_new_device', true)) {
            $event->user->notify(new NewDeviceLoginNotification($event->ip));
        }
    }
}