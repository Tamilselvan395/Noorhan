<?php

namespace App\Services\Dashboard\Widgets;

use App\Contracts\DashboardWidgetInterface;
use App\DTOs\Dashboard\WidgetData;
use App\Enums\DashboardPeriod;
use App\Models\Customer;
use App\Services\Dashboard\ChartDataService;

class CustomersWidget implements DashboardWidgetInterface
{
    public function __construct(private ChartDataService $charts) {}

    public function key(): string
    {
        return 'customers';
    }

    public function sortOrder(): int
    {
        return 70;
    }

    public function data(DashboardPeriod $period): WidgetData
    {
        [$from, $to] = $period->range();
        [$pFrom, $pTo] = $period->previousRange();

        $created = Customer::query()->whereBetween('created_at', [$from, $to])->count();
        $prevCreated = Customer::query()->whereBetween('created_at', [$pFrom, $pTo])->count();

        return new WidgetData(
            key: 'customers',
            label: 'Customers',
            value: number_format(Customer::query()->count()),
            delta: $this->charts->delta($created, $prevCreated),
            hint: "+{$created} this period",
            icon: 'users',
            accent: 'bg-cyan-500/10 text-cyan-600 dark:text-cyan-400',
        );
    }
}