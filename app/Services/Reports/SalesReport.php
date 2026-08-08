<?php

namespace App\Services\Reports;

use App\Contracts\ReportInterface;
use App\Models\SalesOrder;
use Illuminate\Support\Carbon;

class SalesReport implements ReportInterface
{
    public function key(): string { return 'sales'; }
    public function label(): string { return 'Sales'; }
    public function group(): string { return 'Sales'; }

    public function columns(): array
    {
        return ['Reference', 'Date', 'Customer', 'Division', 'Status', 'Total', 'Margin %'];
    }

    public function rows(Carbon $from, Carbon $to): array
    {
        return SalesOrder::query()->with('customer')
            ->whereBetween('created_at', [$from, $to])
            ->get()
            ->map(fn (SalesOrder $o) => [
                $o->reference, $o->created_at->format('Y-m-d'), $o->customer?->name ?? '—',
                $o->division()->label(), $o->status()->label(), (float) $o->total, (float) $o->margin_percent,
            ])->all();
    }

    public function summary(Carbon $from, Carbon $to): array
    {
        $orders = SalesOrder::query()->whereBetween('created_at', [$from, $to])->get();

        return [
            'Orders' => number_format($orders->count()),
            'Sales Value' => number_format((float) $orders->sum('total'), 2),
            'Avg Margin' => ($orders->avg('margin_percent') !== null ? round($orders->avg('margin_percent'), 1) : 0).'%',
        ];
    }
}