<?php

namespace App\Events\Invoices;

use App\Enums\CommunicationChannel;
use App\Models\Invoice;
use Illuminate\Foundation\Events\Dispatchable;

class InvoiceSent { use Dispatchable; public function __construct(public Invoice $invoice, public CommunicationChannel $via, public string $publicUrl) {} }