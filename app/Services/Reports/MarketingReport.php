<?php

namespace App\Services\Reports;

use App\Contracts\ReportInterface;
use App\Models\MarketingCampaign;
use App\Services\Marketing\MarketingMetricsService;
use Illuminate\Support\Carbon;

class MarketingReport implements ReportInterface
{
    public function __construct(private MarketingMetricsService $metrics) {}

    public function key(): string { return 'marketing'; }
    public function label(): string { return 'Marketing Spend'; }
    public function group(): string { return 'Marketing'; }

    public function columns(): array
    {
        return ['Campaign', 'Channel', 'Budget', 'Spent', 'Leads', 'Cost / Lead'];
    }

    public function rows(Carbon $from, Carbon $to): array
    {
        return MarketingCampaign::query()->get()->map(function (MarketingCampaign $c) {
            $perf = $this->metrics->campaignPerformance($c);

            return [$c->name, $c->channel()->label(), (float) $c->budget, (float) $c->spent, $perf['leads'], $perf['cost_per_lead']];
        })->all();
    }

    public function summary(Carbon $from, Carbon $to): array
    {
        $campaigns = MarketingCampaign::query()->get();
        $leads = $campaigns->sum(fn ($c) => $c->leads()->count());

        return [
            'Campaigns' => number_format($campaigns->count()),
            'Total Spent' => number_format((float) $campaigns->sum('spent'), 2),
            'Attributed Leads' => number_format($leads),
        ];
    }
}