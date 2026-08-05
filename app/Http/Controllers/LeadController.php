<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class LeadController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', Lead::class);

        return view('leads.index');
    }

    public function show(Lead $lead): View
    {
        Gate::authorize('view', $lead);

        return view('leads.show', ['lead' => $lead]);
    }
}