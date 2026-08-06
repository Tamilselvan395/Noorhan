<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', Supplier::class);

        return view('suppliers.index');
    }

    public function show(Supplier $supplier): View
    {
        Gate::authorize('view', $supplier);

        return view('suppliers.show', ['supplier' => $supplier]);
    }

    public function compare(): View
    {
        return view('suppliers.compare');
    }
}