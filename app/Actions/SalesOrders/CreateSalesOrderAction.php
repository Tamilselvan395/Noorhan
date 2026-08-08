<?php

namespace App\Actions\SalesOrders;

use App\Events\SalesOrders\SalesOrderCreated;
use App\Models\SalesOrder;
use App\Models\User;

class CreateSalesOrderAction
{
    /**
     * @param array<int, array{product_id: ?int, description: string, quantity: int, unit_price: float, cost_price: float, discount_percent: float}> $items
     */
    public function execute(array $data, array $items, ?User $creator): SalesOrder
    {
        $order = new SalesOrder(array_merge($data, ['created_by' => $creator?->id]));
        $order->save();

        $order->update(['reference' => 'SO-'.str_pad((string) $order->id, 5, '0', STR_PAD_LEFT)]);

        foreach (array_values($items) as $i => $item) {
            $order->items()->create($item + ['sort' => $i * 10]);
        }

        $order->recalculate();

        $order->logActivity("created sales order {$order->reference}");

        event(new SalesOrderCreated($order->fresh()));

        return $order->fresh();
    }
}