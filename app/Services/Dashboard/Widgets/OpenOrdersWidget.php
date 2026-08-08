<?php

namespace App\Services\Dashboard\Widgets;

use App\Contracts\DashboardWidgetInterface;
use App\DTOs\Dashboard\WidgetData;
use App\Enums\DashboardPeriod;
use App\Helpers\CurrencyHelper;
use App\Models\SalesOrder;
use App\Services\Dashboard\ChartDataService;

class OpenOrdersWidget implements DashboardWidgetInterface
{
    public function __construct(private ChartDataService $charts) {}

    public function key(): string
    {
        return 'open_orders';
    }

    public function sortOrder(): int
    {
        return 80;
    }

    public function data(DashboardPeriod $period): WidgetData
    {
        [$from, $to] = $period->range();
        [$pFrom, $pTo] = $period->previousRange();

        $created = SalesOrder::query()->whereBetween('created_at', [$from, $to])->count();
        $prevCreated = SalesOrder::query()->whereBetween('created_at', [$pFrom, $pTo])->count();

        return new WidgetData(
            key: 'open_orders',
            label: 'Open Orders',
            value: number_format(SalesOrder::query()->open()->count()),
            delta: $this->charts->delta($created, $prevCreated),
            hint: CurrencyHelper::format((float) SalesOrder::query()->open()->sum('total')).' open value',
            icon: 'chart',
            accent: 'bg-blue-500/10 text-blue-600 dark:text-blue-400',
        );
    }
}