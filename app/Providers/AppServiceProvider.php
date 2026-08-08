<?php

namespace App\Providers;

use App\Events\Auth\AccountLocked;
use App\Events\Auth\UserLoggedIn;
use App\Events\Customers\CustomerCreated;
use App\Events\Customers\CustomerUpdated;
use App\Events\Invoices\InvoicePaid;
use App\Events\Invoices\InvoiceSent;
use App\Events\Leads\LeadCreated;
use App\Events\Leads\LeadUpdated;
use App\Events\Payments\PaymentCreated;
use App\Events\Quotations\QuotationAccepted;
use App\Events\Quotations\QuotationApproved;
use App\Events\Quotations\QuotationRejected;
use App\Events\Quotations\QuotationSent;
use App\Events\Quotations\QuotationSubmitted;
use App\Events\SalesOrders\SalesOrderCreated;
use App\Events\SalesOrders\SalesOrderStatusChanged;
use App\Events\WhatsApp\WhatsAppMessageReceived;
use App\Listeners\Accounting\SyncCustomerToZohoListener;
use App\Listeners\Accounting\SyncEstimateToZohoListener;
use App\Listeners\Accounting\SyncInvoiceToZohoListener;
use App\Listeners\Accounting\SyncPaymentToZohoListener;
use App\Listeners\Accounting\SyncSalesOrderToZohoListener;
use App\Listeners\Auth\NotifyAccountLocked;
use App\Listeners\Auth\SendNewDeviceAlert;
use App\Listeners\Notifications\NotifyFinanceEvents;
use App\Listeners\Notifications\NotifyOrderDelivered;
use App\Listeners\Notifications\NotifyQuotationLifecycle;
use App\Listeners\Routing\RouteNewLead;
use App\Listeners\Routing\RouteOnUpdate;
use App\Listeners\WhatsApp\DeliverInvoiceViaWhatsApp;
use App\Listeners\WhatsApp\DeliverQuotationViaWhatsApp;
use App\Listeners\WhatsApp\HandleInboundWhatsApp;
use App\Listeners\WhatsApp\SendCrossSellOnPaidInvoice;
use App\Listeners\WhatsApp\SendWelcomeToNewCustomer;
use App\Services\Capture\CaptureNormalizerRegistry;
use App\Services\Capture\Normalizers\BusinessCardNormalizer;
use App\Services\Capture\Normalizers\FacebookNormalizer;
use App\Services\Capture\Normalizers\GenericNormalizer;
use App\Services\Capture\Normalizers\GoogleAdsNormalizer;
use App\Services\Capture\Normalizers\WebNormalizer;
use App\Services\Capture\Normalizers\WhatsAppNormalizer;
use App\Services\Dashboard\WidgetRegistry;
use App\Services\Dashboard\Widgets\ActiveNowWidget;
use App\Services\Dashboard\Widgets\CustomersWidget;
use App\Services\Dashboard\Widgets\OpenLeadsWidget;
use App\Services\Dashboard\Widgets\OpenOrdersWidget;
use App\Services\Dashboard\Widgets\PipelineValueWidget;
use App\Services\Dashboard\Widgets\SignInsWidget;
use App\Services\Dashboard\Widgets\SuccessRateWidget;
use App\Services\Dashboard\Widgets\UsersWidget;
use App\Services\Reports\ReportRegistry;
use App\Services\Reports\AiAccuracyReport;
use App\Services\Reports\CampaignPerformanceReport;
use App\Services\Reports\EmployeesReport;
use App\Services\Reports\LeadSourcesReport;
use App\Services\Reports\MarketingReport;
use App\Services\Reports\OutstandingReport;
use App\Services\Reports\PaymentsReport;
use App\Services\Reports\ProductsReport;
use App\Services\Reports\ProfitReport;
use App\Services\Reports\RevenueReport;
use App\Services\Reports\SalesReport;
use App\Services\Reports\SuppliersReport;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Module 3: Dashboard widget registry
        $this->app->singleton(WidgetRegistry::class);

        // Module 5: Capture normalizer registry
        $this->app->singleton(CaptureNormalizerRegistry::class);

        // Module 6: Lead classifier binding (keyword → swap for LLM in AI Engine module)
        $this->app->bind(
            \App\Contracts\LeadClassifierInterface::class,
            \App\Services\Routing\Classification\KeywordClassifier::class
        );

        // Module 19: Report registry
        $this->app->singleton(ReportRegistry::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        /*
        |--------------------------------------------------------------------------
        | Runtime Settings → Config merge (DB overrides .env defaults)
        |--------------------------------------------------------------------------
        */
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
                config([
                    'noorhan.quotation.default_valid_days' => (int) \App\Models\Setting::get(
                        'defaults.quotation_valid_days', config('noorhan.quotation.default_valid_days')),
                    'noorhan.theme.default' => \App\Models\Setting::get(
                        'appearance.default_theme', config('noorhan.theme.default')),
                    'app.name' => \App\Models\Setting::get('general.company_name', config('app.name')),
                    'app.timezone' => \App\Models\Setting::get('general.timezone', config('app.timezone')),
                ]);
            }
        } catch (\Throwable) {
            // Settings table not migrated yet — fall back to .env values.
        }
        /*
        |--------------------------------------------------------------------------
        | Module 2: Authentication Event Listeners
        |--------------------------------------------------------------------------
        */

        Event::listen(UserLoggedIn::class, SendNewDeviceAlert::class);
        Event::listen(AccountLocked::class, NotifyAccountLocked::class);

        /*
        |--------------------------------------------------------------------------
        | Module 3, 4, 7, 13: Dashboard Widgets
        |--------------------------------------------------------------------------
        */

        $widgets = $this->app->make(WidgetRegistry::class);

        $widgets->register(UsersWidget::class);           // Module 3
        $widgets->register(SignInsWidget::class);         // Module 3
        $widgets->register(SuccessRateWidget::class);     // Module 3
        $widgets->register(ActiveNowWidget::class);       // Module 3
        $widgets->register(OpenLeadsWidget::class);       // Module 4
        $widgets->register(PipelineValueWidget::class);   // Module 4
        $widgets->register(CustomersWidget::class);       // Module 7
        $widgets->register(OpenOrdersWidget::class);      // Module 13

        /*
        |--------------------------------------------------------------------------
        | Module 5: Capture Normalizers (instances — not class strings)
        |--------------------------------------------------------------------------
        */

        $normalizers = $this->app->make(CaptureNormalizerRegistry::class);

        $normalizers->register(new WebNormalizer());
        $normalizers->register(new FacebookNormalizer());
        $normalizers->register(new WhatsAppNormalizer());
        $normalizers->register(new GoogleAdsNormalizer());
        $normalizers->register(new BusinessCardNormalizer());
        $normalizers->register(new GenericNormalizer());

        /*
        |--------------------------------------------------------------------------
        | Module 6: Lead Routing Auto-Listeners
        |--------------------------------------------------------------------------
        */

        Event::listen(LeadCreated::class, RouteNewLead::class);
        Event::listen(LeadUpdated::class, RouteOnUpdate::class);

        /*
        |--------------------------------------------------------------------------
        | Module 16: Zoho Books Sync Listeners
        |--------------------------------------------------------------------------
        */

        Event::listen(CustomerCreated::class, SyncCustomerToZohoListener::class);
        Event::listen(CustomerUpdated::class, SyncCustomerToZohoListener::class);
        Event::listen(QuotationSent::class, SyncEstimateToZohoListener::class);
        Event::listen(SalesOrderCreated::class, SyncSalesOrderToZohoListener::class);
        Event::listen(InvoiceSent::class, SyncInvoiceToZohoListener::class);
        Event::listen(PaymentCreated::class, SyncPaymentToZohoListener::class);

        /*
        |--------------------------------------------------------------------------
        | Module 17: WhatsApp CRM Automations
        |--------------------------------------------------------------------------
        */

        Event::listen(CustomerCreated::class, SendWelcomeToNewCustomer::class);
        Event::listen(QuotationSent::class, DeliverQuotationViaWhatsApp::class);
        Event::listen(InvoiceSent::class, DeliverInvoiceViaWhatsApp::class);
        Event::listen(InvoicePaid::class, SendCrossSellOnPaidInvoice::class);
        Event::listen(WhatsAppMessageReceived::class, HandleInboundWhatsApp::class);

        /*
        |--------------------------------------------------------------------------
        | Module 19: Reports
        |--------------------------------------------------------------------------
        */

        $reports = $this->app->make(ReportRegistry::class);

        $reports->register(SalesReport::class);
        $reports->register(RevenueReport::class);
        $reports->register(ProfitReport::class);
        $reports->register(MarketingReport::class);
        $reports->register(LeadSourcesReport::class);
        $reports->register(CampaignPerformanceReport::class);
        $reports->register(ProductsReport::class);
        $reports->register(SuppliersReport::class);
        $reports->register(PaymentsReport::class);
        $reports->register(OutstandingReport::class);
        $reports->register(EmployeesReport::class);
        $reports->register(AiAccuracyReport::class);

        /*
        |--------------------------------------------------------------------------
        | Module 20: Notification Lifecycle Listeners
        |--------------------------------------------------------------------------
        */

        Event::listen(QuotationSubmitted::class, [NotifyQuotationLifecycle::class, 'handleSubmitted']);
        Event::listen(QuotationRejected::class, [NotifyQuotationLifecycle::class, 'handleRejected']);
        Event::listen(QuotationApproved::class, [NotifyQuotationLifecycle::class, 'handleApproved']);
        Event::listen(QuotationAccepted::class, [NotifyQuotationLifecycle::class, 'handleAccepted']);
        Event::listen(InvoicePaid::class, [NotifyFinanceEvents::class, 'handleInvoicePaid']);
        Event::listen(PaymentCreated::class, [NotifyFinanceEvents::class, 'handlePaymentCreated']);
        Event::listen(SalesOrderStatusChanged::class, NotifyOrderDelivered::class);

        /*
        |--------------------------------------------------------------------------
        | Future modules can register here
        |--------------------------------------------------------------------------
        */
    }
}