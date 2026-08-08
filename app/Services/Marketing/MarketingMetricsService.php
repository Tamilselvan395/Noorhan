<?php

namespace App\Services\Marketing;

use App\Models\Lead;
use App\Models\MarketingCampaign;
use Illuminate\Support\Carbon;

class MarketingMetricsService
{
    /** Full-funnel performance for a single campaign. */
    public function campaignPerformance(MarketingCampaign $campaign): array
    {
        $leads = $campaign->leads()->get();

        $count = $leads->count();
        $converted = $leads->count(fn (Lead $l) => $l->customer_id !== null);
        $won = $leads->count(fn (Lead $l) => $l->status === 'won');
        $wa = $campaign->whatsappCampaigns()->get();

        return [
            'leads' => $count,
            'converted' => $converted,
            'won' => $won,
            'conversion_rate' => $count ? round(($converted / $count) * 100, 1) : 0.0,
            'win_rate' => $count ? round(($won / $count) * 100, 1) : 0.0,
            'pipeline_value' => (float) $leads->sum('estimated_value'),
            'spent' => (float) $campaign->spent,
            'cost_per_lead' => $count ? round(((float) $campaign->spent) / $count, 2) : 0.0,
            'wa_sent' => (int) $wa->sum('sent_count'),
            'wa_failed' => (int) $wa->sum('failed_count'),
        ];
    }

    /** @return array<int, array{name: string, value: int}> */
    public function leadsBySource(?Carbon $from = null, ?Carbon $to = null): array
    {
        return Lead::query()
            ->when($from, fn ($q) => $q->whereBetween('created_at', [$from, $to ?? now()]))
            ->selectRaw('source, COUNT(*) AS total')
            ->groupBy('source')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => ['name' => \App\Enums\LeadSource::from($row->source)->label(), 'value' => (int) $row->total])
            ->all();
    }

    /** @return array{labels: array, values: array} */
    public function leadsByMonth(int $months = 6): array
    {
        $labels = [];
        $values = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $month = now()->subMonths($i);

            $labels[] = $month->format('M y');
            $values[] = Lead::query()
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
        }

        return ['labels' => $labels, 'values' => $values];
    }

    /** @return array<int, array{name: string, leads: int, cpl: float}> */
    public function topCampaigns(int $limit = 5): array
    {
        return MarketingCampaign::query()
            ->withCount('leads')
            ->orderByDesc('leads_count')
            ->limit($limit)
            ->get()
            ->map(fn (MarketingCampaign $c) => [
                'name' => $c->name,
                'leads' => $c->leads_count,
                'cpl' => $c->leads_count ? round(((float) $c->spent) / $c->leads_count, 2) : 0.0,
            ])
            ->all();
    }
}