<?php

namespace App\Http\Controllers\Api\Capture;

use App\Enums\LeadSource;
use App\Http\Controllers\Controller;
use App\Services\Capture\LeadCaptureService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GoogleAdsWebhookController extends Controller
{
    public function handle(Request $request, LeadCaptureService $service): JsonResponse
    {
        $secret = config('noorhan.capture.google_ads_shared_secret');

        if ($secret && $request->header('X-Shared-Secret') !== $secret) {
            abort(403, 'Invalid shared secret.');
        }

        $service->ingest(LeadSource::GoogleAds, $request->json()->all());

        return response()->json(['status' => 'accepted'], 202);
    }
}