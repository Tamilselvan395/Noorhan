<?php

namespace App\Http\Controllers\Api\Accounting;

use App\Http\Controllers\Controller;
use App\Jobs\Accounting\ProcessZohoWebhookJob;
use App\Models\ZohoWebhookEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ZohoWebhookController extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        $secret = config('zoho.webhook_secret');

        if ($secret && $request->query('secret') !== $secret) {
            abort(403, 'Invalid webhook secret.');
        }

        $event = ZohoWebhookEvent::create([
            'event' => (string) $request->json('event', 'unknown'),
            'payload' => $request->json()->all(),
            'status' => 'received',
        ]);

        ProcessZohoWebhookJob::dispatch($event);

        return response()->json(['status' => 'accepted'], 202);
    }
}