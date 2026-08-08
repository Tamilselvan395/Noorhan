<?php

namespace App\Notifications\Sales;

use App\Concerns\HasNotificationChannels;
use App\Models\Quotation;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApprovalRequiredNotification extends Notification
{
    use HasNotificationChannels;

    public function __construct(public Quotation $quotation) {}

    public function category(): string
    {
        return 'sales';
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => "Quotation {$this->quotation->reference} (margin {$this->quotation->margin_percent}%) awaits your approval.",
            'url' => route('quotations.show', $this->quotation),
            'category' => 'sales',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Approval required — '.$this->quotation->reference)
            ->line($this->toArray($notifiable)['message'])
            ->action('Review Quotation', route('quotations.show', $this->quotation));
    }
}