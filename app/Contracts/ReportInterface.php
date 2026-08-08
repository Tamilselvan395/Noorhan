<?php

namespace App\Contracts;

use Illuminate\Support\Carbon;

interface ReportInterface
{
    public function key(): string;

    public function label(): string;

    /** Grouping: Sales | Finance | Marketing | Operations | AI */
    public function group(): string;

    /** @return array<int, string> column headers */
    public function columns(): array;

    /** @return array<int, array<int, mixed>> row data aligned with columns() */
    public function rows(Carbon $from, Carbon $to): array;

    /** @return array<string, string> summary KPI label => value */
    public function summary(Carbon $from, Carbon $to): array;
}