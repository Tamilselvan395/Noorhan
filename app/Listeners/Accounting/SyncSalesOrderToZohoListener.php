<?php

namespace App\Listeners\Accounting;

use App\Events\SalesOrders\SalesOrderCreated;
use App\Services\Accounting\ZohoSyncDispatcher;

class SyncSalesOrderToZohoListener
{
    public function __construct(private ZohoSyncDispatcher $dispatcher) {}

    public function handle(SalesOrderCreated $event): void
    {
        $this->dispatcher->queue($event->order);
    }
}