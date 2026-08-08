<?php

namespace App\Livewire\Notifications;

use Illuminate\View\View;
use Livewire\Component;

class NotificationBell extends Component
{
    public bool $open = false;

    public function unreadCount(): int
    {
        return auth()->user()->unreadNotifications()->count();
    }

    public function toggle(): void
    {
        $this->open = ! $this->open;
    }

    public function markRead(string $id): void
    {
        auth()->user()->notifications()->where('id', $id)->first()?->markAsRead();
    }

    public function markAllRead(): void
    {
        auth()->user()->unreadNotifications->markAsRead();
        $this->open = false;
    }

    public function render(): View
    {
        return view('livewire.notifications.notification-bell', [
            'notifications' => auth()->user()->notifications()->latest()->limit(8)->get(),
        ]);
    }
}