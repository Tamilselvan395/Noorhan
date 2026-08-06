<?php

namespace App\Events\Suppliers;

use App\Models\Supplier;
use Illuminate\Foundation\Events\Dispatchable;

class SupplierUpdated
{
    use Dispatchable;

    public function __construct(public Supplier $supplier) {}
}