<?php

namespace App\Notifications\Finance;

use App\Concerns\HasNotificationChannels;
use App\Models\Invoice;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OverdueInvoiceNotification extends Notification
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
            'message' => "Invoice {$this->invoice->reference} is overdue. Balance: {$this->invoice->currency} {$this->invoice->balance_due}.",
            'url' => route('invoices.show', $this->invoice),
            'category' => 'finance',
            'invoice_id' => $this->invoice->id, // used by the daily digest for dedupe
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Overdue invoice — '.$this->invoice->reference)
            ->line($this->toArray($notifiable)['message'])
            ->action('View Invoice', route('invoices.show', $this->invoice));
    }
}