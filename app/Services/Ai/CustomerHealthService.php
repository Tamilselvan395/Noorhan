<?php

namespace App\Services\Ai;

use App\Models\Customer;

class CustomerHealthService
{
    /** 0–100 relationship health. */
    public function score(Customer $customer): int
    {
        $score = 0;

        // Recency
        $score += match (true) {
            $customer->last_activity_at === null => 0,
            $customer->last_activity_at->gt(now()->subDays(30)) => 30,
            $customer->last_activity_at->gt(now()->subDays(90)) => 20,
            $customer->last_activity_at->gt(now()->subDays(180)) => 10,
            default => 0,
        };

        // Frequency (orders, 12 months)
        $orders = $customer->ordersCount12m ?? $customer->orders()->where('created_at', '>=', now()->subMonths(12))->count();
        $score += $orders >= 3 ? 30 : ($orders >= 1 ? 20 : 0);

        // Monetary
        $revenue = $customer->orders()->where('created_at', '>=', now()->subMonths(12))->sum('total');
        $score += $revenue > 20000 ? 20 : ($revenue > 5000 ? 15 : ($revenue > 0 ? 10 : 0));

        // Risk penalties
        if ($customer->invoices()->outstanding()->where('due_date', '<', now())->exists()) $score -= 20;
        if ((float) $customer->outstanding_balance > (float) ($customer->credit_limit ?: PHP_FLOAT_MAX)) $score -= 10;

        // Engagement
        if ($customer->communications()->where('created_at', '>=', now()->subDays(60))->exists()) $score += 10;

        return max(0, min(100, $score));
    }
}