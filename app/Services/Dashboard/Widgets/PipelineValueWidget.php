<?php

namespace App\Services\Dashboard\Widgets;

use App\Contracts\DashboardWidgetInterface;
use App\DTOs\Dashboard\WidgetData;
use App\Enums\DashboardPeriod;
use App\Helpers\CurrencyHelper;
use App\Models\Lead;

class PipelineValueWidget implements DashboardWidgetInterface
{
    public function key(): string
    {
        return 'pipeline_value';
    }

    public function sortOrder(): int
    {
        return 60;
    }

    public function data(DashboardPeriod $period): WidgetData
    {
        return new WidgetData(
            key: 'pipeline_value',
            label: 'Pipeline Value',
            value: CurrencyHelper::format((float) Lead::query()->open()->sum('estimated_value')),
            delta: null,
            hint: 'open pipeline',
            icon: 'chart',
            accent: 'bg-green-500/10 text-green-600 dark:text-green-400',
        );
    }
}