<?php

namespace App\Services\Dashboard\Widgets;

use App\Contracts\DashboardWidgetInterface;
use App\DTOs\Dashboard\WidgetData;
use App\Enums\DashboardPeriod;
use App\Enums\LoginHistoryType;
use App\Models\LoginHistory;

class SuccessRateWidget implements DashboardWidgetInterface
{
    public function key(): string
    {
        return 'success_rate';
    }

    public function sortOrder(): int
    {
        return 30;
    }

    public function data(DashboardPeriod $period): WidgetData
    {
        [$from, $to] = $period->range();
        [$pFrom, $pTo] = $period->previousRange();

        $rate = fn ($a, $b) => $this->rate(
            $this->count($a, $b, true),
            $this->count($a, $b, false),
        );

        $current = $rate($from, $to);
        $previous = $rate($pFrom, $pTo);

        $attempts = $this->count($from, $to, true) + $this->count($from, $to, false);

        return new WidgetData(
            key: 'success_rate',
            label: 'Login Success Rate',
            value: $current.'%',
            delta: round($current - $previous, 1),
            hint: number_format($attempts).' attempts',
            icon: 'shield',
            accent: 'bg-amber-500/10 text-amber-600 dark:text-amber-400',
        );
    }

    private function count($from, $to, bool $successful): int
    {
        return LoginHistory::query()
            ->where('type', LoginHistoryType::Login->value)
            ->where('successful', $successful)
            ->whereBetween('created_at', [$from, $to])
            ->count();
    }

    private function rate(int $success, int $failed): float
    {
        $total = $success + $failed;

        return $total === 0 ? 100.0 : round(($success / $total) * 100, 1);
    }
}