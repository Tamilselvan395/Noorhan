<?php

namespace App\Notifications\Sales;

use App\Concerns\HasNotificationChannels;
use App\Models\Quotation;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class QuotationApprovedNotification extends Notification
{
    use HasNotificationChannels;

    public function __construct(public Quotation $quotation) {}

    public function category(): string
    {
        return 'sales';
    }

    public function toArray(object $notifiable): array
    {
        // Extract the ?? expression — it cannot live inside string interpolation.
        $approver = $this->quotation->approver?->name ?? 'management';

        return [
            'message' => "Quotation {$this->quotation->reference} approved by {$approver}.",
            'url' => route('quotations.show', $this->quotation),
            'category' => 'sales',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Quotation approved — '.$this->quotation->reference)
            ->line($this->toArray($notifiable)['message'])
            ->action('View Quotation', route('quotations.show', $this->quotation));
    }
}