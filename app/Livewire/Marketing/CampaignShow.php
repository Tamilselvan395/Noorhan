<?php

namespace App\Livewire\Marketing;

use App\Models\MarketingCampaign;
use App\Services\Marketing\MarketingMetricsService;
use Illuminate\View\View;
use Livewire\Component;

class CampaignShow extends Component
{
    public MarketingCampaign $campaign;

    public function mount(MarketingCampaign $campaign): void
    {
        $this->campaign = $campaign;
    }

    public function render(MarketingMetricsService $metrics): View
    {
        return view('livewire.marketing.campaign-show', [
            'perf' => $metrics->campaignPerformance($this->campaign),
            'leads' => $this->campaign->leads()->latest()->limit(10)->get(),
            'whatsapp' => $this->campaign->whatsappCampaigns()->latest()->get(),
        ]);
    }
}