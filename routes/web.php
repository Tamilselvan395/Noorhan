<?php

use Illuminate\Support\Facades\Route;

require __DIR__.'/auth.php';

Route::redirect('/', '/dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    Route::view('/settings/profile', 'settings.profile')->name('settings.profile');
    Route::view('/settings/security', 'settings.security')->name('settings.security');

    Route::get('/leads/triage', [App\Http\Controllers\LeadTriageController::class, 'index'])->name('leads.triage');
    Route::get('/settings/routing-rules', [App\Http\Controllers\RoutingRuleController::class, 'index'])->name('settings.routing-rules');

    Route::get('/leads', [App\Http\Controllers\LeadController::class, 'index'])->name('leads.index');
    Route::get('/leads/{lead}', [App\Http\Controllers\LeadController::class, 'show'])->name('leads.show');

    Route::get('/customers', [App\Http\Controllers\CustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/{customer}', [App\Http\Controllers\CustomerController::class, 'show'])->name('customers.show');

    Route::get('/companies', [App\Http\Controllers\CompanyController::class, 'index'])->name('companies.index');
    Route::get('/companies/{company}', [App\Http\Controllers\CompanyController::class, 'show'])->name('companies.show');

    Route::get('/suppliers', [App\Http\Controllers\SupplierController::class, 'index'])->name('suppliers.index');
    Route::get('/suppliers/compare', [App\Http\Controllers\SupplierController::class, 'compare'])->name('suppliers.compare');
    Route::get('/suppliers/{supplier}', [App\Http\Controllers\SupplierController::class, 'show'])->name('suppliers.show');

    Route::get('/products', [App\Http\Controllers\ProductController::class, 'index'])->name('products.index');
    Route::get('/products/{product}', [App\Http\Controllers\ProductController::class, 'show'])->name('products.show');

    Route::get('/supplier-enquiries', [App\Http\Controllers\SupplierEnquiryController::class, 'index'])->name('supplier-enquiries.index');
    Route::get('/supplier-enquiries/{enquiry}', [App\Http\Controllers\SupplierEnquiryController::class, 'show'])->name('supplier-enquiries.show');

    Route::get('/sales-orders', [App\Http\Controllers\SalesOrderController::class, 'index'])->name('sales-orders.index');
    Route::get('/sales-orders/{order}', [App\Http\Controllers\SalesOrderController::class, 'show'])->name('sales-orders.show');

    Route::get('/payments', [App\Http\Controllers\PaymentController::class, 'index'])->name('payments.index');

    Route::get('/whatsapp', [App\Http\Controllers\WhatsAppController::class, 'index'])->name('whatsapp.index');

    Route::get('/marketing', [App\Http\Controllers\MarketingController::class, 'index'])->name('marketing.index');
    Route::get('/marketing/campaigns/{campaign}', [App\Http\Controllers\MarketingController::class, 'show'])->name('marketing.show');

    Route::get('/reports', [App\Http\Controllers\ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export/{key}', [App\Http\Controllers\ReportController::class, 'export'])->name('reports.export');

    Route::view('/settings/notifications', 'settings.notifications')->name('settings.notifications');

    Route::view('/settings', 'settings.index')->name('settings.index');
    
});

// Public lead capture
Route::middleware('guest')->group(function () {
    Route::get('/capture/lead', [App\Http\Controllers\Capture\WebFormController::class, 'show'])->name('capture.web');
    Route::post('/capture/lead', [App\Http\Controllers\Capture\WebFormController::class, 'store'])->name('capture.web.store')->middleware('throttle:10,1');
    Route::get('/capture/success', [App\Http\Controllers\Capture\WebFormController::class, 'success'])->name('capture.web.success');
});
Route::middleware('auth')->group(function () {
    Route::get('/two-factor', [App\Http\Controllers\Auth\TwoFactorController::class, 'settings'])->name('two-factor.settings');
    Route::post('/two-factor/enable', [App\Http\Controllers\Auth\TwoFactorController::class, 'enable'])->name('two-factor.enable');
    Route::post('/two-factor/confirm', [App\Http\Controllers\Auth\TwoFactorController::class, 'confirm'])->name('two-factor.confirm');
    Route::post('/two-factor/disable', [App\Http\Controllers\Auth\TwoFactorController::class, 'disable'])->name('two-factor.disable');
    Route::get('/two-factor/challenge', [App\Http\Controllers\Auth\TwoFactorController::class, 'challenge'])->name('two-factor.challenge');
    Route::post('/two-factor/verify', [App\Http\Controllers\Auth\TwoFactorController::class, 'verify'])->name('two-factor.verify');
});
// Public signed quotation view (guest)
Route::get('/quotations/public/{quotation}', [App\Http\Controllers\QuotationPublicController::class, 'show'])->name('quotations.public');
Route::post('/quotations/public/{quotation}/accept', [App\Http\Controllers\QuotationPublicController::class, 'accept'])->name('quotations.public.accept');

// Inside auth group:
Route::get('/quotations', [App\Http\Controllers\QuotationController::class, 'index'])->name('quotations.index');
Route::get('/quotations/create', [App\Http\Controllers\QuotationController::class, 'create'])->name('quotations.create');
Route::get('/quotations/{quotation}/edit', [App\Http\Controllers\QuotationController::class, 'edit'])->name('quotations.edit');
Route::get('/quotations/{quotation}', [App\Http\Controllers\QuotationController::class, 'show'])->name('quotations.show');

// Public signed invoice view (guest)
Route::get('/invoices/public/{invoice}', [App\Http\Controllers\InvoicePublicController::class, 'show'])->name('invoices.public');

// Inside auth group:
Route::get('/invoices', [App\Http\Controllers\InvoiceController::class, 'index'])->name('invoices.index');
Route::get('/invoices/{invoice}', [App\Http\Controllers\InvoiceController::class, 'show'])->name('invoices.show');

// Zoho OAuth + console (auth)
Route::get('/zoho/connect', [App\Http\Controllers\Accounting\ZohoConnectController::class, 'redirect'])->name('zoho.connect');
Route::get('/zoho/callback', [App\Http\Controllers\Accounting\ZohoConnectController::class, 'callback'])->name('zoho.callback');
Route::get('/settings/zoho', fn () => view('settings.zoho'))->middleware(['auth', 'verified'])->name('settings.zoho');

// Zoho inbound webhook (secret-verified, no auth)
Route::post('/api/zoho/webhook', [App\Http\Controllers\Api\Accounting\ZohoWebhookController::class, 'handle']);

Route::prefix('system')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/activity', [App\Http\Controllers\SystemLogController::class, 'activity'])->name('system.activity');
    Route::get('/activity/export', [App\Http\Controllers\SystemLogController::class, 'exportActivity'])->name('system.activity.export');
    Route::get('/audit', [App\Http\Controllers\SystemLogController::class, 'audit'])->name('system.audit');
    Route::get('/audit/export', [App\Http\Controllers\SystemLogController::class, 'exportAudit'])->name('system.audit.export');
    Route::get('/scheduler', fn () => view('system.scheduler'))->name('system.scheduler');
});