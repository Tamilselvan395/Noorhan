<?php

namespace App\Enums;

use Illuminate\Support\Carbon;

enum DashboardPeriod: string
{
    case Today   = 'today';
    case Week    = '7d';
    case Month   = '30d';
    case Quarter = '90d';
    case Year    = 'ytd';

    public function label(): string
    {
        return match ($this) {
            self::Today   => 'Today',
            self::Week    => '7D',
            self::Month   => '30D',
            self::Quarter => '90D',
            self::Year    => 'YTD',
        };
    }

    /** @return array{0: Carbon, 1: Carbon} */
    public function range(): array
    {
        return [
            match ($this) {
                self::Today   => now()->startOfDay(),
                self::Week    => now()->subDays(6)->startOfDay(),
                self::Month   => now()->subDays(29)->startOfDay(),
                self::Quarter => now()->subDays(89)->startOfDay(),
                self::Year    => now()->startOfYear(),
            },
            now(),
        ];
    }

    /** @return array{0: Carbon, 1: Carbon} */
    public function previousRange(): array
    {
        return match ($this) {
            self::Today   => [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()],
            self::Week    => [now()->subDays(13)->startOfDay(), now()->subDays(7)->endOfDay()],
            self::Month   => [now()->subDays(59)->startOfDay(), now()->subDays(30)->endOfDay()],
            self::Quarter => [now()->subDays(179)->startOfDay(), now()->subDays(90)->endOfDay()],
            self::Year    => [now()->subYear()->startOfYear(), now()->subYear()->endOfYear()],
        };
    }

    public function isHourly(): bool
    {
        return $this === self::Today;
    }
}