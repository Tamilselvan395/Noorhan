<?php

namespace App\Notifications\Finance;

use App\Concerns\HasNotificationChannels;
use App\Models\Invoice;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoicePaidNotification extends Notification
{
    use HasNotificationChannels;

    public function __construct(public Invoice $invoice) {}

    public function category(): string
    {
        return 'finance';
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => "Invoice {$this->invoice->reference} fully paid by {$this->invoice->customer?->name}.",
            'url' => route('invoices.show', $this->invoice),
            'category' => 'finance',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Invoice paid — '.$this->invoice->reference)
            ->line($this->toArray($notifiable)['message'])
            ->action('View Invoice', route('invoices.show', $this->invoice));
    }
}