<?php

namespace App\Actions\SalesOrders;

use App\Enums\SalesOrderStatus;
use App\Events\SalesOrders\SalesOrderStatusChanged;
use App\Models\SalesOrder;
use RuntimeException;

class AdvanceSalesOrderStatusAction
{
    /** @return array<string, array<int, SalesOrderStatus>> */
    public function transitions(): array
    {
        return [
            SalesOrderStatus::Pending->value => [SalesOrderStatus::Confirmed, SalesOrderStatus::Cancelled],
            SalesOrderStatus::Confirmed->value => [SalesOrderStatus::Processing, SalesOrderStatus::Cancelled],
            SalesOrderStatus::Processing->value => [SalesOrderStatus::Delivered, SalesOrderStatus::Cancelled],
            SalesOrderStatus::Delivered->value => [],
            SalesOrderStatus::Cancelled->value => [],
        ];
    }

    public function execute(SalesOrder $order, SalesOrderStatus $to): void
    {
        $from = $order->status();

        if (! in_array($to, $this->transitions()[$from->value] ?? [], true)) {
            throw new RuntimeException("Invalid order move: {$from->label()} → {$to->label()}.");
        }

        $order->update([
            'status' => $to->value,
            'delivered_at' => $to === SalesOrderStatus::Delivered ? now() : $order->delivered_at,
        ]);

        $order->logActivity("moved the order from {$from->label()} to {$to->label()}");

        event(new SalesOrderStatusChanged($order, $from, $to));
    }
}