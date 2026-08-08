<?php

namespace App\Services\Reports;

use App\Contracts\ReportInterface;
use App\Models\Invoice;
use Illuminate\Support\Carbon;

class OutstandingReport implements ReportInterface
{
    public function key(): string { return 'outstanding'; }
    public function label(): string { return 'Outstanding (AR Aging)'; }
    public function group(): string { return 'Finance'; }

    public function columns(): array
    {
        return ['Invoice', 'Customer', 'Due Date', 'Days Overdue', 'Total', 'Balance Due'];
    }

    public function rows(Carbon $from, Carbon $to): array
    {
        return Invoice::query()->with('customer')->outstanding()->orderBy('due_date')->get()
            ->map(fn (Invoice $i) => [
                $i->reference, $i->customer?->name ?? '—', $i->due_date->format('Y-m-d'),
                $i->due_date->isPast() ? $i->due_date->diffInDays(now()) : 0,
                (float) $i->total, (float) $i->balance_due,
            ])->all();
    }

    public function summary(Carbon $from, Carbon $to): array
    {
        $open = Invoice::query()->outstanding()->get();

        return [
            'Outstanding' => number_format((float) $open->sum('balance_due'), 2),
            'Overdue' => number_format((float) $open->filter(fn ($i) => $i->due_date->isPast())->sum('balance_due'), 2),
            'Open Invoices' => number_format($open->count()),
        ];
    }
}