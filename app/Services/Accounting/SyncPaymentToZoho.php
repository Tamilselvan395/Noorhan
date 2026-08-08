<?php

namespace App\Services\Accounting;

use App\Models\Payment;
use RuntimeException;

class SyncPaymentToZoho
{
    public function __construct(private ZohoBooksClient $client) {}

    public function execute(Payment $payment): string
    {
        $customer = $payment->customer;

        if (! $customer?->zoho_id) {
            throw new RuntimeException('Customer must be synced to Zoho before the payment.');
        }

        $invoiceApplications = $payment->invoices
            ->filter(fn ($invoice) => $invoice->zoho_id)
            ->map(fn ($invoice) => [
                'invoice_id' => $invoice->zoho_id,
                'amount_applied' => (float) $invoice->pivot->allocated_amount,
            ])->values()->all();

        $payload = [
            'customer_id' => $customer->zoho_id,
            'amount' => (float) $payment->amount,
            'date' => $payment->payment_date->format('Y-m-d'),
            'payment_mode' => $payment->method,
            'reference_number' => $payment->reference_number,
        ];

        if ($invoiceApplications !== []) {
            $payload['invoices'] = $invoiceApplications;
        }

        $result = $this->client->post('/customerpayments', $payload);

        $zohoId = $result['payment']['payment_id'];

        $payment->update(['zoho_id' => $zohoId]);

        return $zohoId;
    }
}