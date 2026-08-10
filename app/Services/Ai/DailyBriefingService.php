<?php

namespace App\Services\Ai;

use App\Models\AiBriefing;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Lead;

class DailyBriefingService
{
    public function __construct(
        private LeadScoringService $leadScore,
        private ChurnPredictionService $churn,
        private SalesForecastService $forecast,
    ) {}

    public function generate(): array
    {
        $openLeads = Lead::open()->get();

        $topLeads = $openLeads->map(fn ($l) => ['id' => $l->id, 'name' => $l->name, 'score' => $this->leadScore->score($l)])
            ->sortByDesc('score')->take(5)->values()->all();

        $atRisk = Customer::active()->get()
            ->map(fn ($c) => ['id' => $c->id, 'name' => $c->name] + $this->churn->predict($c))
            ->filter(fn ($c) => $c['level'] !== 'low')
            ->sortByDesc('score')->take(5)->values()->all();

        $content = [
            'follow_ups_due' => Lead::followUpDue()->count(),
            'overdue_invoices' => [
                'count' => Invoice::outstanding()->where('due_date', '<', now())->count(),
                'value' => (float) Invoice::outstanding()->where('due_date', '<', now())->sum('balance_due'),
            ],
            'top_leads' => $topLeads,
            'at_risk_customers' => $atRisk,
            'sales_forecast' => $this->forecast->nextMonth(),
            'dormant_customers' => Customer::active()->where('last_activity_at', '<', now()->subDays(180))->count(),
        ];

        AiBriefing::updateOrCreate(['briefing_date' => today()], ['content' => $content]);

        return $content;
    }

    public function today(): array
    {
        return AiBriefing::whereDate('briefing_date', today())->first()?->content ?? $this->generate();
    }
}