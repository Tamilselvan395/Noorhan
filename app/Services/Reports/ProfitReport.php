<?php

namespace App\Services\Reports;

use App\Contracts\ReportInterface;
use App\Models\SalesOrder;
use Illuminate\Support\Carbon;

class ProfitReport implements ReportInterface
{
    public function key(): string { return 'profit'; }
    public function label(): string { return 'Profit'; }
    public function group(): string { return 'Finance'; }

    public function columns(): array
    {
        return ['Reference', 'Customer', 'Revenue', 'Cost', 'Profit', 'Margin %'];
    }

    public function rows(Carbon $from, Carbon $to): array
    {
        return SalesOrder::query()->with('customer')
            ->whereBetween('created_at', [$from, $to])
            ->get()
            ->map(fn (SalesOrder $o) => [
                $o->reference, $o->customer?->name ?? '—', (float) $o->total, (float) $o->total_cost,
                round((float) $o->total - (float) $o->total_cost, 2), (float) $o->margin_percent,
            ])->all();
    }

    public function summary(Carbon $from, Carbon $to): array
    {
        $orders = SalesOrder::query()->whereBetween('created_at', [$from, $to])->get();
        $profit = (float) $orders->sum(fn ($o) => (float) $o->total - (float) $o->total_cost);

        return [
            'Gross Profit' => number_format($profit, 2),
            'Revenue' => number_format((float) $orders->sum('total'), 2),
            'Profit Margin' => ((float) $orders->sum('total') > 0 ? round($profit / (float) $orders->sum('total') * 100, 1) : 0).'%',
        ];
    }
}