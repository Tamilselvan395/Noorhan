<?php

namespace App\Events\Suppliers;

use App\Enums\CommunicationChannel;
use App\Models\SupplierEnquiry;
use Illuminate\Foundation\Events\Dispatchable;

class SupplierEnquirySent
{
    use Dispatchable;

    public function __construct(public SupplierEnquiry $enquiry, public CommunicationChannel $via) {}
}