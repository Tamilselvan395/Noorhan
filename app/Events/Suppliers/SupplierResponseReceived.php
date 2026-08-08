<?php

namespace App\Events\Suppliers;

use App\Models\SupplierEnquiry;
use App\Models\SupplierEnquiryItem;
use Illuminate\Foundation\Events\Dispatchable;

class SupplierResponseReceived
{
    use Dispatchable;

    public function __construct(public SupplierEnquiry $enquiry, public SupplierEnquiryItem $item) {}
}