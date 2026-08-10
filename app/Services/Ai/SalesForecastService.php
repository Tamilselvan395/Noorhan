<?php

namespace App\Services\Ai;

use App\Models\SalesOrder;

class SalesForecastService
{
    /** Weighted moving-average forecast for next month. */
    public function nextMonth(int $historyMonths = 6): array
    {
        $history = [];

        for ($i = $historyMonths; $i >= 1; $i--) {
            $month = now()->subMonths($i);

            $history[] = [
                'label' => $month->format('M y'),
                'value' => (float) SalesOrder::whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)->sum('total'),
            ];
        }

        $values = collect($history)->pluck('value');
        $weights = collect(range(1, $historyMonths)); // newer months weigh more
        $denominator = $weights->sum();

        $forecast = $values->reverse()->map(fn ($v, $i) => $v * $weights[$i])->sum() / $denominator;

        $trend = $values->last() >= $values->first() ? 'up' : 'down';

        return ['forecast' => round($forecast, 2), 'trend' => $trend, 'history' => $history];
    }
}