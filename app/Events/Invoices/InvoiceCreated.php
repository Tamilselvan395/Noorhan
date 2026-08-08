<?php

namespace App\Events\Invoices;

use App\Models\Invoice;
use Illuminate\Foundation\Events\Dispatchable;

class InvoiceCreated { use Dispatchable; public function __construct(public Invoice $invoice) {} }