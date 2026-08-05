<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class RoutingRuleController extends Controller
{
    public function index(): View
    {
        return view('settings.routing-rules');
    }
}