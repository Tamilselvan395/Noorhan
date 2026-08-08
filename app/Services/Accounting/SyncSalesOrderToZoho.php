<?php

namespace App\Services\Accounting;

use App\Models\SalesOrder;
use RuntimeException;

class SyncSalesOrderToZoho
{
    public function __construct(private ZohoBooksClient $client) {}

    public function execute(SalesOrder $order): string
    {
        $customer = $order->customer;

        if (! $customer?->zoho_id) {
            throw new RuntimeException('Customer must be synced to Zoho before the sales order.');
        }

        $payload = [
            'customer_id' => $customer->zoho_id,
            'date' => now()->format('Y-m-d'),
            'shipment_date' => $order->expected_delivery_date?->format('Y-m-d'),
            'discount' => (float) $order->discount_amount,
            'notes' => $order->notes,
            'line_items' => $order->items->map(fn ($item) => [
                'description' => $item->description,
                'rate' => (float) $item->unit_price * (1 - ((float) $item->discount_percent) / 100),
                'quantity' => $item->quantity,
            ])->values()->all(),
        ];

        $result = $this->client->post('/salesorders', $payload);

        $zohoId = $result['salesorder']['salesorder_id'];

        $order->update(['zoho_id' => $zohoId]);

        return $zohoId;
    }
}