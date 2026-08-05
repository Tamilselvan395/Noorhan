<?php

namespace App\Services\Dashboard\Widgets;

use App\Contracts\DashboardWidgetInterface;
use App\DTOs\Dashboard\WidgetData;
use App\Enums\DashboardPeriod;
use App\Enums\LoginHistoryType;
use App\Models\LoginHistory;
use App\Services\Dashboard\ChartDataService;

class SignInsWidget implements DashboardWidgetInterface
{
    public function __construct(private ChartDataService $charts) {}

    public function key(): string
    {
        return 'sign_ins';
    }

    public function sortOrder(): int
    {
        return 20;
    }

    public function data(DashboardPeriod $period): WidgetData
    {
        [$from, $to] = $period->range();
        [$pFrom, $pTo] = $period->previousRange();

        $base = LoginHistory::query()
            ->where('type', LoginHistoryType::Login->value)
            ->where('successful', true);

        $current = (clone $base)->whereBetween('created_at', [$from, $to])->count();
        $previous = (clone $base)->whereBetween('created_at', [$pFrom, $pTo])->count();

        return new WidgetData(
            key: 'sign_ins',
            label: 'Sign-ins',
            value: number_format($current),
            delta: $this->charts->delta($current, $previous),
            hint: 'successful logins',
            icon: 'login',
            accent: 'bg-green-500/10 text-green-600 dark:text-green-400',
        );
    }
}