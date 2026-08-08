<?php

namespace App\Listeners\Accounting;

use App\Events\Customers\CustomerCreated;
use App\Events\Customers\CustomerUpdated;
use App\Services\Accounting\ZohoSyncDispatcher;

class SyncCustomerToZohoListener
{
    public function __construct(private ZohoSyncDispatcher $dispatcher) {}

    public function handle(CustomerCreated|CustomerUpdated $event): void
    {
        $this->dispatcher->queue($event->customer);
    }
}