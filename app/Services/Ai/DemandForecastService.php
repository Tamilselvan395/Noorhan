<?php

namespace App\Services\Ai;

use App\Models\Product;
use App\Models\SalesOrderItem;

class DemandForecastService
{
    /** @return array{avg_monthly: float, trend: string, suggested_order: int} */
    public function forProduct(Product $product): array
    {
        $months = [];

        for ($i = 3; $i >= 1; $i--) {
            $month = now()->subMonths($i);

            $months[] = (float) SalesOrderItem::where('product_id', $product->id)
                ->whereHas('order', fn ($q) => $q->whereYear('created_at', $month->year)->whereMonth('created_at', $month->month))
                ->sum('quantity');
        }

        $avg = array_sum($months) / count($months);

        return [
            'avg_monthly' => round($avg, 1),
            'trend' => $months[2] >= $months[0] ? 'up' : 'down',
            'suggested_order' => (int) ceil($avg * 1.2), // 20% buffer
        ];
    }
}