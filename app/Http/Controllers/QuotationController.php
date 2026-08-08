<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class QuotationController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', Quotation::class);

        return view('quotations.index');
    }

    public function create(): View
    {
        return view('quotations.builder');
    }

    public function edit(Quotation $quotation): View
    {
        Gate::authorize('update', $quotation);

        return view('quotations.builder', ['quotation' => $quotation]);
    }

    public function show(Quotation $quotation): View
    {
        Gate::authorize('view', $quotation);

        return view('quotations.show', ['quotation' => $quotation]);
    }
}