<?php

namespace App\Services\Dashboard\Widgets;

use App\Contracts\DashboardWidgetInterface;
use App\DTOs\Dashboard\WidgetData;
use App\Enums\DashboardPeriod;
use App\Models\UserSession;

class ActiveNowWidget implements DashboardWidgetInterface
{
    public function key(): string
    {
        return 'active_now';
    }

    public function sortOrder(): int
    {
        return 40;
    }

    public function data(DashboardPeriod $period): WidgetData
    {
        $active = UserSession::query()
            ->where('last_activity', '>=', now()->subMinutes(15)->toUnixTimestamp())
            ->count();

        return new WidgetData(
            key: 'active_now',
            label: 'Active Now',
            value: number_format($active),
            delta: null,
            hint: 'last 15 minutes',
            icon: 'bolt',
            accent: 'bg-violet-500/10 text-violet-600 dark:text-violet-400',
        );
    }
}