<?php

namespace App\Services\Reports;

use App\Contracts\ReportInterface;
use App\Models\MarketingCampaign;
use App\Services\Marketing\MarketingMetricsService;
use Illuminate\Support\Carbon;

class CampaignPerformanceReport implements ReportInterface
{
    public function __construct(private MarketingMetricsService $metrics) {}

    public function key(): string { return 'campaign_performance'; }
    public function label(): string { return 'Campaign Performance'; }
    public function group(): string { return 'Marketing'; }

    public function columns(): array
    {
        return ['Campaign', 'Leads', 'Converted', 'Won', 'Conv %', 'Spent', 'CPL', 'Pipeline'];
    }

    public function rows(Carbon $from, Carbon $to): array
    {
        return MarketingCampaign::query()->get()->map(function (MarketingCampaign $c) {
            $p = $this->metrics->campaignPerformance($c);

            return [$c->name, $p['leads'], $p['converted'], $p['won'], $p['conversion_rate'], $p['spent'], $p['cost_per_lead'], $p['pipeline_value']];
        })->all();
    }

    public function summary(Carbon $from, Carbon $to): array
    {
        return ['Campaigns' => number_format(MarketingCampaign::count())];
    }
}