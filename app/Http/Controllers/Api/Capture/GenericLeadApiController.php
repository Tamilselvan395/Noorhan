<?php

namespace App\Http\Controllers\Api\Capture;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Capture\GenericApiLeadRequest;
use App\Services\Capture\LeadCaptureService;
use Illuminate\Http\JsonResponse;

class GenericLeadApiController extends Controller
{
    public function __invoke(GenericApiLeadRequest $request, LeadCaptureService $service): JsonResponse
    {
        $event = $service->ingest(
            \App\Enums\LeadSource::from($request->validated('source', 'manual')),
            $request->validated(),
            async: false,
        );

        return ResponseHelper::success([
            'event_id' => $event->id,
            'lead_id' => $event->refresh()->lead_id,
        ], 'Lead captured.', 201);
    }
}