<?php

namespace App\Http\Controllers\Api\Capture;

use App\Enums\LeadSource;
use App\Http\Controllers\Controller;
use App\Services\Capture\LeadCaptureService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FacebookLeadWebhookController extends Controller
{
    /** Meta webhook subscription handshake. */
    public function verify(Request $request): JsonResponse|string
    {
        if ($request->query('hub_mode') === 'subscribe'
            && $request->query('hub_verify_token') === config('noorhan.capture.meta_verify_token')) {
            return $request->query('hub_challenge');
        }

        abort(403, 'Invalid verification token.');
    }

    public function handle(Request $request, LeadCaptureService $service): JsonResponse
    {
        $payload = $request->json()->all();

        $isLeadgen = ($payload['object'] ?? '') === 'leadgen'
            || isset($payload['entry'][0]['changes'][0]['value']['field_data']);

        if ($isLeadgen) {
            $service->ingest(LeadSource::FacebookAds, $payload);
        }

        // Instagram lead ads share the Meta leadgen object.
        return response()->json(['status' => 'accepted'], 202);
    }
}