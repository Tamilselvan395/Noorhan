<?php

namespace App\Services\Dashboard\Widgets;

use App\Contracts\DashboardWidgetInterface;
use App\DTOs\Dashboard\WidgetData;
use App\Enums\DashboardPeriod;
use App\Models\Lead;
use App\Services\Dashboard\ChartDataService;

class OpenLeadsWidget implements DashboardWidgetInterface
{
    public function __construct(private ChartDataService $charts) {}

    public function key(): string
    {
        return 'open_leads';
    }

    public function sortOrder(): int
    {
        return 50;
    }

    public function data(DashboardPeriod $period): WidgetData
    {
        [$from, $to] = $period->range();
        [$pFrom, $pTo] = $period->previousRange();

        $created = Lead::query()->whereBetween('created_at', [$from, $to])->count();
        $prevCreated = Lead::query()->whereBetween('created_at', [$pFrom, $pTo])->count();

        return new WidgetData(
            key: 'open_leads',
            label: 'Open Leads',
            value: number_format(Lead::query()->open()->count()),
            delta: $this->charts->delta($created, $prevCreated),
            hint: "+{$created} this period",
            icon: 'bolt',
            accent: 'bg-blue-500/10 text-blue-600 dark:text-blue-400',
        );
    }
}