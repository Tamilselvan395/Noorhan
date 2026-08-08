<?php

namespace App\Services\Accounting;

use App\Models\Invoice;
use RuntimeException;

class SyncInvoiceToZoho
{
    public function __construct(private ZohoBooksClient $client) {}

    public function execute(Invoice $invoice): string
    {
        $customer = $invoice->customer;

        if (! $customer?->zoho_id) {
            throw new RuntimeException('Customer must be synced to Zoho before the invoice.');
        }

        $taxId = config('zoho.sales_tax_ids.'.$invoice->division);

        $lineItems = $invoice->items->map(function ($item) use ($taxId) {
            $line = [
                'description' => $item->description,
                'rate' => (float) $item->unit_price * (1 - ((float) $item->discount_percent) / 100),
                'quantity' => $item->quantity,
            ];

            if ($taxId) {
                $line['tax_id'] = $taxId;
            }

            return $line;
        })->values()->all();

        $payload = [
            'customer_id' => $customer->zoho_id,
            'invoice_date' => $invoice->issue_date->format('Y-m-d'),
            'due_date' => $invoice->due_date->format('Y-m-d'),
            'discount' => (float) $invoice->discount_amount,
            'notes' => $invoice->notes,
            'terms' => $invoice->terms,
            'line_items' => $lineItems,
        ];

        $result = $invoice->zoho_id
            ? $this->client->put('/invoices/'.$invoice->zoho_id, $payload)
            : $this->client->post('/invoices', $payload);

        $zohoId = $invoice->zoho_id ?? $result['invoice']['invoice_id'];

        $invoice->update(['zoho_id' => $zohoId]);

        return $zohoId;
    }
}