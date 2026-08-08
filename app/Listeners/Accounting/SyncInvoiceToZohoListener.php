<?php

namespace App\Listeners\Accounting;

use App\Events\Invoices\InvoiceSent;
use App\Services\Accounting\ZohoSyncDispatcher;

class SyncInvoiceToZohoListener
{
    public function __construct(private ZohoSyncDispatcher $dispatcher) {}

    public function handle(InvoiceSent $event): void
    {
        $this->dispatcher->queue($event->invoice);
    }
}