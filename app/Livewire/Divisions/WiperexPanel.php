<?php

namespace App\Livewire\Divisions;

use App\Models\MarketingCampaign;
use App\Services\Divisions\WiperexInsightsService;
use Illuminate\View\View;
use Livewire\Component;

class WiperexPanel extends Component
{
    public function createDraftCampaign(WiperexInsightsService $insights): void
    {
        $suggestion = $insights->seasonalSuggestion();

        MarketingCampaign::firstOrCreate(
            ['utm_campaign' => $insights->draftCampaignUtm()],
            [
                'name' => $insights->draftCampaignName(),
                'division' => 'wiperex',
                'channel' => 'social',
                'status' => 'planned',
                'budget' => 0,
                'spent' => 0,
                'goals' => $suggestion['message'].' Focus: '.$suggestion['focus'],
                'created_by' => auth()->id(),
            ],
        );

        $this->dispatch('notify', message: 'Draft campaign created in Marketing.', type: 'success');
    }

    public function render(WiperexInsightsService $insights): View
    {
        return view('livewire.divisions.wiperex-panel', [
            'sizeMix' => $insights->sizeMix(),
            'candidates' => $insights->replenishmentCandidates(),
            'season' => $insights->seasonalSuggestion(),
        ]);
    }
}