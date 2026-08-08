<?php

namespace App\Services\Reports;

use App\Contracts\ReportInterface;
use App\Enums\LeadSource;
use App\Models\Lead;
use Illuminate\Support\Carbon;

class LeadSourcesReport implements ReportInterface
{
    public function key(): string { return 'lead_sources'; }
    public function label(): string { return 'Lead Sources'; }
    public function group(): string { return 'Marketing'; }

    public function columns(): array
    {
        return ['Source', 'Leads', 'Converted', 'Conversion %', 'Pipeline Value'];
    }

    public function rows(Carbon $from, Carbon $to): array
    {
        return Lead::query()->whereBetween('created_at', [$from, $to])->get()
            ->groupBy('source')
            ->map(fn ($leads, $source) => [
                LeadSource::from($source)->label(),
                $leads->count(),
                $leads->count(fn ($l) => $l->customer_id !== null),
                $leads->count() ? round($leads->filter(fn ($l) => $l->customer_id !== null)->count() / $leads->count() * 100, 1) : 0,
                round((float) $leads->sum('estimated_value'), 2),
            ])->values()->all();
    }

    public function summary(Carbon $from, Carbon $to): array
    {
        $leads = Lead::query()->whereBetween('created_at', [$from, $to])->get();

        return [
            'Total Leads' => number_format($leads->count()),
            'Converted' => number_format($leads->count(fn ($l) => $l->customer_id !== null)),
        ];
    }
}