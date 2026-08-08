<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoicePublicController extends Controller
{
    public function show(Request $request, Invoice $invoice): View
    {
        abort_unless($request->hasValidSignature(), 403);

        return view('invoices.public', ['invoice' => $invoice->load('items', 'customer')]);
    }
}