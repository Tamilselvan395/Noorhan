<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CompanyController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', Company::class);

        return view('companies.index');
    }

    public function show(Company $company): View
    {
        Gate::authorize('view', $company);

        return view('companies.show', ['company' => $company]);
    }
}