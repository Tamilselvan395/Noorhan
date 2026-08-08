<?php

namespace App\Concerns;

trait HasNotificationChannels
{
    /** Channels respect each recipient's category preferences. */
    public function via(object $notifiable): array
    {
        if (! method_exists($notifiable, 'prefersChannel')) {
            return ['database', 'mail'];
        }

        $channels = [];
        $category = $this->category();

        if ($notifiable->prefersChannel($category, 'database')) $channels[] = 'database';
        if ($notifiable->prefersChannel($category, 'mail')) $channels[] = 'mail';

        return $channels;
    }
}