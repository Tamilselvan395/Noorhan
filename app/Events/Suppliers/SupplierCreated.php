<?php

namespace App\Events\Suppliers;

use App\Models\Supplier;
use Illuminate\Foundation\Events\Dispatchable;

class SupplierCreated
{
    use Dispatchable;

    public function __construct(public Supplier $supplier) {}
}