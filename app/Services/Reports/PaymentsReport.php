<?php

namespace App\Services\Reports;

use App\Contracts\ReportInterface;
use App\Models\Payment;
use Illuminate\Support\Carbon;

class PaymentsReport implements ReportInterface
{
    public function key(): string { return 'payments'; }
    public function label(): string { return 'Payments'; }
    public function group(): string { return 'Finance'; }

    public function columns(): array
    {
        return ['Reference', 'Date', 'Customer', 'Method', 'Status', 'Amount'];
    }

    public function rows(Carbon $from, Carbon $to): array
    {
        return Payment::query()->with('customer')->whereBetween('payment_date', [$from, $to])
            ->get()
            ->map(fn (Payment $p) => [
                $p->reference, $p->payment_date->format('Y-m-d'), $p->customer?->name ?? '—',
                $p->method()->label(), $p->status()->label(), (float) $p->amount,
            ])->all();
    }

    public function summary(Carbon $from, Carbon $to): array
    {
        $byMethod = Payment::query()->where('status', 'completed')->whereBetween('payment_date', [$from, $to])
            ->selectRaw('method, SUM(amount) total')->groupBy('method')->pluck('total', 'method');

        return $byMethod->map(fn ($total, $method) => number_format((float) $total, 2))
            ->mapWithKeys(fn ($v, $k) => [ucwords(str_replace('_', ' ', $k)) => $v])
            ->all();
    }
}