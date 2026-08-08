<?php

namespace App\Listeners\Accounting;

use App\Events\Payments\PaymentCreated;
use App\Services\Accounting\ZohoSyncDispatcher;

class SyncPaymentToZohoListener
{
    public function __construct(private ZohoSyncDispatcher $dispatcher) {}

    public function handle(PaymentCreated $event): void
    {
        $this->dispatcher->queue($event->payment);
    }
}