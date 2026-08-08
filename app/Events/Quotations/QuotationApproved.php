<?php

namespace App\Events\Quotations;

use App\Models\Quotation;
use Illuminate\Foundation\Events\Dispatchable;

class QuotationApproved
{
    use Dispatchable;

    public function __construct(public Quotation $quotation) {}
}