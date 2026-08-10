<?php

use App\Http\Controllers\Api\Capture\FacebookLeadWebhookController;
use App\Http\Controllers\Api\Capture\GoogleAdsWebhookController;
use App\Http\Controllers\Api\Capture\WhatsAppWebhookController;
use App\Http\Controllers\Api\V1\CustomerApiController;
use App\Http\Controllers\Api\V1\LeadApiController;
use App\Http\Controllers\Api\V1\ReadOnlyApiController;
use App\Http\Controllers\Api\Accounting\ZohoWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public channel webhooks (verified inside controllers)
|--------------------------------------------------------------------------
*/
Route::prefix('capture')->group(function () {
    Route::match(['get', 'post'], 'facebook/webhook', function (Illuminate\Http\Request $request) {
        return $request->isMethod('get')
            ? app(FacebookLeadWebhookController::class)->verify($request)
            : app(FacebookLeadWebhookController::class)->handle($request, app(\App\Services\Capture\LeadCaptureService::class));
    });

    Route::match(['get', 'post'], 'whatsapp/webhook', function (Illuminate\Http\Request $request) {
        return $request->isMethod('get')
            ? app(WhatsAppWebhookController::class)->verify($request)
            : app(WhatsAppWebhookController::class)->handle($request, app(\App\Services\Capture\LeadCaptureService::class));
    });

    Route::post('google/webhook', [GoogleAdsWebhookController::class, 'handle']);
});

Route::post('zoho/webhook', [ZohoWebhookController::class, 'handle']);

/*
|--------------------------------------------------------------------------
| Authenticated REST API v1 (Sanctum + abilities + rate limit)
|--------------------------------------------------------------------------
*/
Route::prefix('v1')->middleware(['auth:sanctum', 'throttle:api'])->group(function () {

    // Read scope
    Route::middleware('ability:read,write')->group(function () {
        Route::get('stats', [ReadOnlyApiController::class, 'stats']);

        Route::get('leads', [LeadApiController::class, 'index']);
        Route::get('leads/{lead}', [LeadApiController::class, 'show']);

        Route::get('customers', [CustomerApiController::class, 'index']);
        Route::get('customers/{customer}', [CustomerApiController::class, 'show']);

        Route::get('products', [ReadOnlyApiController::class, 'products']);
        Route::get('suppliers', [ReadOnlyApiController::class, 'suppliers']);

        Route::get('quotations', [ReadOnlyApiController::class, 'quotations']);
        Route::get('quotations/{quotation}', [ReadOnlyApiController::class, 'quotation']);

        Route::get('sales-orders', [ReadOnlyApiController::class, 'salesOrders']);
        Route::get('sales-orders/{order}', [ReadOnlyApiController::class, 'salesOrder']);

        Route::get('invoices', [ReadOnlyApiController::class, 'invoices']);
        Route::get('invoices/{invoice}', [ReadOnlyApiController::class, 'invoice']);

        Route::get('payments', [ReadOnlyApiController::class, 'payments']);
    });

    // Write scope
    Route::middleware('ability:write')->group(function () {
        Route::post('leads', [LeadApiController::class, 'store']);
        Route::post('customers', [CustomerApiController::class, 'store']);
    });
});