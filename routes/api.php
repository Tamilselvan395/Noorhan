<?php

use App\Http\Controllers\Api\Capture\FacebookLeadWebhookController;
use App\Http\Controllers\Api\Capture\GenericLeadApiController;
use App\Http\Controllers\Api\Capture\GoogleAdsWebhookController;
use App\Http\Controllers\Api\Capture\WhatsAppWebhookController;
use App\Services\Capture\LeadCaptureService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('capture')->group(function () {

    Route::match(['get', 'post'], 'facebook/webhook', function (Request $request) {
        return $request->isMethod('get')
            ? app(FacebookLeadWebhookController::class)->verify($request)
            : app(FacebookLeadWebhookController::class)->handle(
                $request,
                app(LeadCaptureService::class)
            );
    });

    Route::match(['get', 'post'], 'whatsapp/webhook', function (Request $request) {
        return $request->isMethod('get')
            ? app(WhatsAppWebhookController::class)->verify($request)
            : app(WhatsAppWebhookController::class)->handle(
                $request,
                app(LeadCaptureService::class)
            );
    });

    Route::post('google/webhook', [GoogleAdsWebhookController::class, 'handle']);
});

Route::middleware('auth:sanctum')->post('/leads', GenericLeadApiController::class);