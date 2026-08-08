<?php

namespace App\Events\SalesOrders;

use App\Models\Quotation;
use App\Models\SalesOrder;
use Illuminate\Foundation\Events\Dispatchable;

class SalesOrderCreated
{
    use Dispatchable;

    public function __construct(public SalesOrder $order, public ?Quotation $sourceQuotation = null) {}
}