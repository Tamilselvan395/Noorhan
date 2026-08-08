<?php

namespace App\Listeners\Notifications;

use App\Events\Invoices\InvoicePaid;
use App\Events\Payments\PaymentCreated;
use App\Notifications\Finance\InvoicePaidNotification;

class NotifyFinanceEvents
{
    public function handleInvoicePaid(InvoicePaid $event): void
    {
        $event->invoice->creator?->notify(new InvoicePaidNotification($event->invoice));
    }

    public function handlePaymentCreated(PaymentCreated $event): void
    {
        foreach ($event->payment->invoices as $invoice) {
            if ($invoice->creator && $invoice->created_by !== $event->payment->created_by) {
                $invoice->creator->notify(new InvoicePaidNotification($invoice));
            }
        }
    }
}