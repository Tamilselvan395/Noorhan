<?php

namespace App\Http\Controllers;

use App\Models\MarketingCampaign;
use Illuminate\View\View;

class MarketingController extends Controller
{
    public function index(): View
    {
        return view('marketing.index');
    }

    public function show(MarketingCampaign $campaign): View
    {
        return view('marketing.show', ['campaign' => $campaign]);
    }
}