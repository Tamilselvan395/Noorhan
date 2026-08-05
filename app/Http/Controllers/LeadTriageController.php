<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class LeadTriageController extends Controller
{
    public function index(): View
    {
        return view('leads.triage');
    }
}