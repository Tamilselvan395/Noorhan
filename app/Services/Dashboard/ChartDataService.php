<?php

namespace App\Services\Dashboard;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class ChartDataService
{
    /**
     * Zero-filled time-series buckets (hourly or daily) for any query.
     *
     * @return array{labels: array<int, string>, values: array<int, int>}
     */
    public function counts(Builder $query, Carbon $from, Carbon $to, string $column = 'created_at', bool $hourly = false): array
    {
        $expr = $hourly ? "LPAD(HOUR({$column}), 2, '0')" : "DATE({$column})";

        $rows = (clone $query)
            ->whereBetween($column, [$from, $to])
            ->selectRaw("{$expr} AS bucket, COUNT(*) AS total")
            ->groupBy('bucket')
            ->pluck('total', 'bucket');

        $labels = [];
        $values = [];

        if ($hourly) {
            for ($h = 0; $h < 24; $h++) {
                $key = str_pad((string) $h, 2, '0', STR_PAD_LEFT);
                $labels[] = $key.':00';
                $values[] = (int) ($rows[$key] ?? 0);
            }
        } else {
            for ($d = $from->copy()->startOfDay(); $d->lte($to); $d->addDay()) {
                $labels[] = $d->format('M d');
                $values[] = (int) ($rows[$d->format('Y-m-d')] ?? 0);
            }
        }

        return ['labels' => $labels, 'values' => $values];
    }

    /** Percentage change between periods. */
    public function delta(int|float $current, int|float $previous): float
    {
        if ($previous == 0) {
            return $current == 0 ? 0.0 : 100.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}