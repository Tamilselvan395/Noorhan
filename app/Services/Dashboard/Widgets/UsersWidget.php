<?php

namespace App\Services\Dashboard\Widgets;

use App\Contracts\DashboardWidgetInterface;
use App\DTOs\Dashboard\WidgetData;
use App\Enums\DashboardPeriod;
use App\Models\User;
use App\Services\Dashboard\ChartDataService;

class UsersWidget implements DashboardWidgetInterface
{
    public function __construct(private ChartDataService $charts) {}

    public function key(): string
    {
        return 'users';
    }

    public function sortOrder(): int
    {
        return 10;
    }

    public function data(DashboardPeriod $period): WidgetData
    {
        [$from, $to] = $period->range();
        [$pFrom, $pTo] = $period->previousRange();

        $current = User::query()->whereBetween('created_at', [$from, $to])->count();
        $previous = User::query()->whereBetween('created_at', [$pFrom, $pTo])->count();

        return new WidgetData(
            key: 'users',
            label: 'New Users',
            value: number_format($current),
            delta: $this->charts->delta($current, $previous),
            hint: number_format(User::query()->count()).' total',
            icon: 'users',
            accent: 'bg-blue-500/10 text-blue-600 dark:text-blue-400',
        );
    }
}