<?php

namespace App\Services\Reports;

use App\Contracts\ReportInterface;
use App\Models\SalesOrderItem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ProductsReport implements ReportInterface
{
    public function key(): string { return 'products'; }
    public function label(): string { return 'Products'; }
    public function group(): string { return 'Sales'; }

    public function columns(): array
    {
        return ['Product', 'SKU', 'Qty Sold', 'Revenue', 'Profit'];
    }

    public function rows(Carbon $from, Carbon $to): array
    {
        return SalesOrderItem::query()->with('product')
            ->whereHas('order', fn ($q) => $q->whereBetween('created_at', [$from, $to]))
            ->get()
            ->groupBy(fn ($i) => $i->product_id ?? $i->description)
            ->map(function ($items) {
                $first = $items->first();

                return [
                    $first->product?->name ?? $first->description,
                    $first->product?->sku ?? 'CUSTOM',
                    (int) $items->sum('quantity'),
                    round((float) $items->sum('line_total'), 2),
                    round((float) $items->sum(fn ($i) => $i->line_total - ($i->quantity * $i->cost_price)), 2),
                ];
            })->values()->all();
    }

    public function summary(Carbon $from, Carbon $to): array
    {
        $items = SalesOrderItem::query()->whereHas('order', fn ($q) => $q->whereBetween('created_at', [$from, $to]))->get();

        return [
            'Units Sold' => number_format((int) $items->sum('quantity')),
            'Product Revenue' => number_format((float) $items->sum('line_total'), 2),
        ];
    }
}