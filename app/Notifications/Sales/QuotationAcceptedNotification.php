<?php

namespace App\Notifications\Sales;

use App\Concerns\HasNotificationChannels;
use App\Models\Quotation;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class QuotationAcceptedNotification extends Notification
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
            'message' => "Customer accepted quotation {$this->quotation->reference}. Ready to convert to a Sales Order.",
            'url' => route('quotations.show', $this->quotation),
            'category' => 'sales',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Quotation accepted — '.$this->quotation->reference)
            ->line($this->toArray($notifiable)['message'])
            ->action('View Quotation', route('quotations.show', $this->quotation));
    }
}