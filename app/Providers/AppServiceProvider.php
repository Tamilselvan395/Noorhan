<?php

namespace App\Providers;


use App\Events\Auth\AccountLocked;
use App\Events\Auth\UserLoggedIn;
use App\Listeners\Auth\NotifyAccountLocked;
use App\Listeners\Auth\SendNewDeviceAlert;
use App\Services\Dashboard\WidgetRegistry;
use App\Services\Dashboard\Widgets\ActiveNowWidget;
use App\Services\Dashboard\Widgets\SignInsWidget;
use App\Services\Dashboard\Widgets\SuccessRateWidget;
use App\Services\Dashboard\Widgets\UsersWidget;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(WidgetRegistry::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(UserLoggedIn::class, SendNewDeviceAlert::class);
        Event::listen(AccountLocked::class, NotifyAccountLocked::class);
        $registry = $this->app->make(WidgetRegistry::class);

        $registry->register(UsersWidget::class);
        $registry->register(SignInsWidget::class);
        $registry->register(SuccessRateWidget::class);
        $registry->register(ActiveNowWidget::class);

        $registry->register(\App\Services\Dashboard\Widgets\OpenLeadsWidget::class);
        $registry->register(\App\Services\Dashboard\Widgets\PipelineValueWidget::class);

        // Future modules register their widgets here, e.g.:
        // $registry->register(OpenLeadsWidget::class);
        // $registry->register(MonthlyRevenueWidget::class);
    }
}
