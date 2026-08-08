<?php

namespace App\Notifications\Sales;

use App\Concerns\HasNotificationChannels;
use App\Models\Quotation;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class QuotationRejectedNotification extends Notification
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
            'message' => "Quotation {$this->quotation->reference} was rejected: {$this->quotation->rejected_reason}",
            'url' => route('quotations.show', $this->quotation),
            'category' => 'sales',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Quotation rejected — '.$this->quotation->reference)
            ->line($this->toArray($notifiable)['message'])
            ->action('View Quotation', route('quotations.show', $this->quotation));
    }
}