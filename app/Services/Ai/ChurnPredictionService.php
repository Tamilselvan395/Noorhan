<?php

namespace App\Services\Ai;

use App\Models\Customer;

class ChurnPredictionService
{
    public function __construct(private CustomerHealthService $health) {}

    /** @return array{score: int, level: string, reasons: array<int, string>} */
    public function predict(Customer $customer): array
    {
        $health = $this->health->score($customer);
        $risk = 100 - $health;
        $reasons = [];

        if ($customer->last_activity_at === null || $customer->last_activity_at->lt(now()->subDays(120))) {
            $reasons[] = 'No activity in 120+ days';
            $risk = min(100, $risk + 10);
        }

        if ($customer->orders()->where('created_at', '>=', now()->subMonths(6))->count() === 0) {
            $reasons[] = 'No orders in the last 6 months';
        }

        if ($customer->invoices()->outstanding()->where('due_date', '<', now())->exists()) {
            $reasons[] = 'Has overdue invoices (friction risk)';
        }

        $level = match (true) {
            $risk >= (int) config('noorhan.ai.thresholds.churn_high', 65) => 'high',
            $risk >= (int) config('noorhan.ai.thresholds.churn_medium', 35) => 'medium',
            default => 'low',
        };

        return ['score' => (int) $risk, 'level' => $level, 'reasons' => $reasons];
    }
}