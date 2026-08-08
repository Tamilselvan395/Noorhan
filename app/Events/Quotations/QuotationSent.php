<?php

namespace App\Events\Quotations;

use App\Enums\CommunicationChannel;
use App\Models\Quotation;
use Illuminate\Foundation\Events\Dispatchable;

class QuotationSent
{
    use Dispatchable;

    public function __construct(public Quotation $quotation, public CommunicationChannel $via, public string $publicUrl) {}
}