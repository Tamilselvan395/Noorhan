<?php

namespace App\Http\Controllers;

use App\Models\SupplierEnquiry;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class SupplierEnquiryController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', SupplierEnquiry::class);

        return view('suppliers.enquiries.index');
    }

    public function show(SupplierEnquiry $enquiry): View
    {
        Gate::authorize('view', $enquiry);

        return view('suppliers.enquiries.show', ['enquiry' => $enquiry]);
    }
}