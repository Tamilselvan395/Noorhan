<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountLockedNotification extends Notification
{
    public function __construct(public \Illuminate\Support\Carbon $lockedUntil) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Noorhan CRM account has been locked')
            ->line('Your account was locked due to multiple failed login attempts.')
            ->line("It will unlock automatically at {$this->lockedUntil->format('M d, Y h:i A')}.")
            ->line('If you did not attempt these logins, contact your administrator.');
    }
}