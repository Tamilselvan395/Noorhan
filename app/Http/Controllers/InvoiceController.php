<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', Invoice::class);
        return view('invoices.index');
    }

    public function show(Invoice $invoice): View
    {
        Gate::authorize('view', $invoice);
        return view('invoices.show', ['invoice' => $invoice]);
    }
}