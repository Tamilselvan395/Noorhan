<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', Customer::class);

        return view('customers.index');
    }

    public function show(Customer $customer): View
    {
        Gate::authorize('view', $customer);

        return view('customers.show', ['customer' => $customer]);
    }
}