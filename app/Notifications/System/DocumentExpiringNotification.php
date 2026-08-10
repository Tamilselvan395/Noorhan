<?php

namespace App\Notifications\System;

use App\Concerns\HasNotificationChannels;
use App\Models\Document;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DocumentExpiringNotification extends Notification
{
    use HasNotificationChannels;

    public function __construct(public Document $document) {}

    public function category(): string
    {
        return 'system';
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => $this->document->isExpired()
                ? "Document expired: {$this->document->name}"
                : "Document expiring soon: {$this->document->name} ({$this->document->expires_at->format('M d, Y')})",
            'url' => route('documents.index'),
            'document_id' => $this->document->id,
            'category' => 'system',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)->subject('Document expiry alert')
            ->line($this->toArray($notifiable)['message']);
    }
}