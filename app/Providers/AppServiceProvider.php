<?php

namespace App\Providers;

use App\Events\Auth\AccountLocked;
use App\Events\Auth\UserLoggedIn;
use App\Listeners\Auth\NotifyAccountLocked;
use App\Listeners\Auth\SendNewDeviceAlert;
use App\Services\Capture\CaptureNormalizerRegistry;
use App\Services\Capture\Normalizers\BusinessCardNormalizer;
use App\Services\Capture\Normalizers\FacebookNormalizer;
use App\Services\Capture\Normalizers\GenericNormalizer;
use App\Services\Capture\Normalizers\GoogleAdsNormalizer;
use App\Services\Capture\Normalizers\WebNormalizer;
use App\Services\Capture\Normalizers\WhatsAppNormalizer;
use App\Services\Dashboard\WidgetRegistry;
use App\Services\Dashboard\Widgets\ActiveNowWidget;
use App\Services\Dashboard\Widgets\OpenLeadsWidget;
use App\Services\Dashboard\Widgets\PipelineValueWidget;
use App\Services\Dashboard\Widgets\SignInsWidget;
use App\Services\Dashboard\Widgets\SuccessRateWidget;
use App\Services\Dashboard\Widgets\UsersWidget;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Dashboard widget registry
        $this->app->singleton(WidgetRegistry::class);

        // Capture normalizer registry
        $this->app->singleton(CaptureNormalizerRegistry::class);

        $this->app->bind(\App\Contracts\LeadClassifierInterface::class, \App\Services\Routing\Classification\KeywordClassifier::class);

        
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Authentication Event Listeners
        |--------------------------------------------------------------------------
        */

        Event::listen(UserLoggedIn::class, SendNewDeviceAlert::class);
        Event::listen(AccountLocked::class, NotifyAccountLocked::class);

        /*
        |--------------------------------------------------------------------------
        | Dashboard Widgets
        |--------------------------------------------------------------------------
        */

        $registry = $this->app->make(WidgetRegistry::class);

        $registry->register(UsersWidget::class);
        $registry->register(SignInsWidget::class);
        $registry->register(SuccessRateWidget::class);
        $registry->register(ActiveNowWidget::class);
        $registry->register(OpenLeadsWidget::class);
        $registry->register(PipelineValueWidget::class);

        /*
        |--------------------------------------------------------------------------
        | Capture Normalizers
        |--------------------------------------------------------------------------
        */

        $normalizers = $this->app->make(CaptureNormalizerRegistry::class);

        $normalizers->register(WebNormalizer::class);
        $normalizers->register(FacebookNormalizer::class);
        $normalizers->register(WhatsAppNormalizer::class);
        $normalizers->register(GoogleAdsNormalizer::class);
        $normalizers->register(BusinessCardNormalizer::class);
        $normalizers->register(GenericNormalizer::class);

        Event::listen(\App\Events\Leads\LeadCreated::class, \App\Listeners\Routing\RouteNewLead::class);
        Event::listen(\App\Events\Leads\LeadUpdated::class, \App\Listeners\Routing\RouteOnUpdate::class);

        $registry->register(\App\Services\Dashboard\Widgets\CustomersWidget::class);

        /*
        |--------------------------------------------------------------------------
        | Future modules can register here
        |--------------------------------------------------------------------------
        |
        | Example:
        | $registry->register(MonthlyRevenueWidget::class);
        |
        */
    }
}