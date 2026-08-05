<?php

namespace App\Contracts;

use App\DTOs\Dashboard\WidgetData;
use App\Enums\DashboardPeriod;

interface DashboardWidgetInterface
{
    public function key(): string;

    public function sortOrder(): int;

    public function data(DashboardPeriod $period): WidgetData;
}