<?php

namespace App\Actions\Invoices;

use App\Enums\CommunicationChannel;
use App\Enums\InvoiceStatus;
use App\Events\Invoices\InvoiceSent;
use App\Mail\InvoiceMail;
use App\Models\Invoice;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use RuntimeException;

class SendInvoiceAction
{
    public function execute(Invoice $invoice, CommunicationChannel $via): string
    {
        if ($invoice->status() !== InvoiceStatus::Draft) {
            throw new RuntimeException('Only draft invoices can be sent.');
        }

        $publicUrl = URL::temporarySignedRoute(
            'invoices.public',
            now()->addDays(45),
            ['invoice' => $invoice->id],
        );

        $invoice->update([
            'status' => InvoiceStatus::Sent->value,
            'sent_via' => $via->value,
            'sent_at' => now(),
        ]);

        if ($via === CommunicationChannel::Email && $invoice->customer?->email) {
            Mail::to($invoice->customer->email)->queue(new InvoiceMail($invoice, $publicUrl));
        }

        $invoice->logActivity("sent invoice {$invoice->reference} via {$via->label()}");

        event(new InvoiceSent($invoice, $via, $publicUrl));

        // Update customer outstanding balance (Module 7 reserved field)
        if ($invoice->customer) {
            $invoice->customer->increment('outstanding_balance', (float) $invoice->balance_due);
        }

        return $publicUrl;
    }
}