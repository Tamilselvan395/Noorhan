<?php

namespace App\Actions\Invoices;

use App\Events\Invoices\InvoicePaid;
use App\Models\Invoice;
use RuntimeException;

class RecordInvoicePaymentAction
{
    public function execute(Invoice $invoice, float $amount): void
    {
        if ($amount <= 0 || $amount > (float) $invoice->balance_due) {
            throw new RuntimeException('Invalid payment amount.');
        }

        $invoice->increment('paid_amount', $amount);
        $invoice->updateBalances();

        // Decrease customer outstanding balance
        if ($invoice->customer) {
            $invoice->customer->decrement('outstanding_balance', $amount);
        }

        $invoice->logActivity("recorded payment of {$amount} {$invoice->currency}");

        if ($invoice->fresh()->status->value === 'paid') {
            event(new InvoicePaid($invoice));
        }
    }
}