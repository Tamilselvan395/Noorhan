<?php

namespace App\Listeners\Notifications;

use App\Enums\SalesOrderStatus;
use App\Events\SalesOrders\SalesOrderStatusChanged;
use App\Notifications\Sales\OrderDeliveredNotification;

class NotifyOrderDelivered
{
    public function handle(SalesOrderStatusChanged $event): void
    {
        if ($event->to === SalesOrderStatus::Delivered) {
            $event->order->creator?->notify(new OrderDeliveredNotification($event->order));
        }
    }
}