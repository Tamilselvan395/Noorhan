<?php

namespace App\Events\SalesOrders;

use App\Enums\SalesOrderStatus;
use App\Models\SalesOrder;
use Illuminate\Foundation\Events\Dispatchable;

class SalesOrderStatusChanged
{
    use Dispatchable;

    public function __construct(public SalesOrder $order, public SalesOrderStatus $from, public SalesOrderStatus $to) {}
}