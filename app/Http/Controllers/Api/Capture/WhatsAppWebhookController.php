<?php

namespace App\Http\Controllers\Api\Capture;

use App\Enums\LeadSource;
use App\Http\Controllers\Controller;
use App\Services\Capture\LeadCaptureService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WhatsAppWebhookController extends Controller
{
    public function verify(Request $request): string
    {
        if ($request->query('hub_mode') === 'subscribe'
            && $request->query('hub_verify_token') === config('noorhan.capture.whatsapp_verify_token')) {
            return $request->query('hub_challenge');
        }

        abort(403, 'Invalid verification token.');
    }

    public function handle(Request $request, LeadCaptureService $service): JsonResponse
    {
        $payload = $request->json()->all();

        $hasMessage = isset($payload['entry'][0]['changes'][0]['value']['messages'][0]);

        if ($hasMessage) {
            $value = $payload['entry'][0]['changes'][0]['value'];

            event(new \App\Events\WhatsApp\WhatsAppMessageReceived(
                (string) ($value['messages'][0]['from'] ?? ''),
                $value['messages'][0]['text']['body'] ?? null,
            ));

            $service->ingest(LeadSource::WhatsApp, $payload);
        }

        // Status/delivery callbacks are acknowledged and ignored.
        return response()->json(['status' => 'accepted'], 202);
    }
}