<?php

namespace App\Notifications;

use App\Helpers\AgentHelper;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewDeviceLoginNotification extends Notification
{
    public function __construct(public string $ip) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $agent = AgentHelper::parse(request()->userAgent());

        return (new MailMessage)
            ->subject('New sign-in to your Noorhan CRM account')
            ->greeting("Hello {$notifiable->name},")
            ->line('We detected a new sign-in to your account.')
            ->line("Device: {$agent['browser']} on {$agent['platform']} ({$agent['device']})")
            ->line("IP Address: {$this->ip}")
            ->line('If this was you, no action is needed. Otherwise, change your password immediately.');
    }
}