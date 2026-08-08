<?php

namespace App\Events\Suppliers;

use App\Models\SupplierEnquiry;
use Illuminate\Foundation\Events\Dispatchable;

class SupplierEnquiryCreated
{
    use Dispatchable;

    public function __construct(public SupplierEnquiry $enquiry) {}
}