<?php

namespace App\Listeners\Accounting;

use App\Events\Quotations\QuotationSent;
use App\Services\Accounting\ZohoSyncDispatcher;

class SyncEstimateToZohoListener
{
    public function __construct(private ZohoSyncDispatcher $dispatcher) {}

    public function handle(QuotationSent $event): void
    {
        $this->dispatcher->queue($event->quotation);
    }
}