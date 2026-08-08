<?php

namespace App\Notifications\Sales;

use App\Concerns\HasNotificationChannels;
use App\Models\SalesOrder;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderDeliveredNotification extends Notification
{
    use HasNotificationChannels;

    public function __construct(public SalesOrder $order) {}

    public function category(): string
    {
        return 'sales';
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => "Sales order {$this->order->reference} marked as delivered.",
            'url' => route('sales-orders.show', $this->order),
            'category' => 'sales',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Order delivered — '.$this->order->reference)
            ->line($this->toArray($notifiable)['message'])
            ->action('View Order', route('sales-orders.show', $this->order));
    }
}