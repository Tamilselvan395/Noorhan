<?php

namespace App\Services\Accounting;

use App\Models\Quotation;
use RuntimeException;

class SyncEstimateToZoho
{
    public function __construct(private ZohoBooksClient $client) {}

    public function execute(Quotation $quotation): string
    {
        $customer = $quotation->customer;

        if (! $customer?->zoho_id) {
            throw new RuntimeException('Customer must be synced to Zoho before the estimate.');
        }

        $payload = [
            'customer_id' => $customer->zoho_id,
            'estimate_date' => now()->format('Y-m-d'),
            'expiry_date' => $quotation->valid_until?->format('Y-m-d'),
            'discount' => (float) $quotation->discount_amount,
            'notes' => $quotation->notes,
            'line_items' => $quotation->items->map(fn ($item) => [
                'description' => $item->description,
                'rate' => (float) $item->unit_price * (1 - ((float) $item->discount_percent) / 100),
                'quantity' => $item->quantity,
            ])->values()->all(),
        ];

        $result = $this->client->post('/estimates', $payload);

        $zohoId = $result['estimate']['estimate_id'];

        $quotation->update(['zoho_id' => $zohoId]);

        return $zohoId;
    }
}