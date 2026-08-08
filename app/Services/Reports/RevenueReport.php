<?php

namespace App\Services\Reports;

use App\Contracts\ReportInterface;
use App\Models\Payment;
use Illuminate\Support\Carbon;

class RevenueReport implements ReportInterface
{
    public function key(): string { return 'revenue'; }
    public function label(): string { return 'Revenue (Collected)'; }
    public function group(): string { return 'Finance'; }

    public function columns(): array
    {
        return ['Reference', 'Date', 'Customer', 'Method', 'Amount'];
    }

    public function rows(Carbon $from, Carbon $to): array
    {
        return Payment::query()->with('customer')
            ->where('status', 'completed')
            ->whereBetween('payment_date', [$from, $to])
            ->get()
            ->map(fn (Payment $p) => [
                $p->reference, $p->payment_date->format('Y-m-d'), $p->customer?->name ?? '—',
                $p->method()->label(), (float) $p->amount,
            ])->all();
    }

    public function summary(Carbon $from, Carbon $to): array
    {
        $payments = Payment::query()->where('status', 'completed')->whereBetween('payment_date', [$from, $to])->get();

        return [
            'Collected' => number_format((float) $payments->sum('amount'), 2),
            'Transactions' => number_format($payments->count()),
            'Avg Ticket' => number_format($payments->count() ? (float) $payments->avg('amount') : 0, 2),
        ];
    }
}