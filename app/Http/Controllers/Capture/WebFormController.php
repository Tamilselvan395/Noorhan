<?php

namespace App\Http\Controllers\Capture;

use App\Enums\LeadSource;
use App\Http\Controllers\Controller;
use App\Http\Requests\Capture\WebLeadRequest;
use App\Services\Capture\LeadCaptureService;
use Illuminate\View\View;

class WebFormController extends Controller
{
    public function show(): View
    {
        return view('capture.web-form');
    }

    public function store(WebLeadRequest $request, LeadCaptureService $service)
    {
        // Honeypot: bots fill the hidden field; humans never see it.
        if ($request->filled('website')) {
            return redirect()->route('capture.web.success');
        }

        $service->ingest(LeadSource::Website, $request->validated());

        return redirect()->route('capture.web.success');
    }

    public function success(): View
    {
        return view('capture.success');
    }
}