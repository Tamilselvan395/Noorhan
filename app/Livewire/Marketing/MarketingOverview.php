<?php

namespace App\Livewire\Marketing;

use App\Actions\Marketing\CreateMarketingCampaignAction;
use App\DTOs\Marketing\MarketingCampaignDTO;
use App\Http\Requests\Marketing\StoreMarketingCampaignRequest;
use App\Models\MarketingCampaign;
use App\Services\Marketing\MarketingMetricsService;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class MarketingOverview extends Component
{
    public bool $formOpen = false;

    public string $name = '';
    public string $division = 'automotive';
    public string $channel = 'whatsapp';
    public string $status = 'planned';
    public string $utm_campaign = '';
    public string $budget = '';
    public string $spent = '';
    public string $start_date = '';
    public string $end_date = '';
    public string $goals = '';

    #[On('open-marketing-form')]
    public function openForm(): void
    {
        $this->reset(['name', 'utm_campaign', 'budget', 'spent', 'start_date', 'end_date', 'goals']);
        $this->formOpen = true;
    }

    public function save(CreateMarketingCampaignAction $create): void
    {
        $data = $this->validate(StoreMarketingCampaignRequest::rules());

        $create->execute(new MarketingCampaignDTO(
            name: $data['name'],
            division: $data['division'],
            channel: $data['channel'],
            status: $data['status'],
            utm_campaign: $data['utm_campaign'],
            budget: (float) ($data['budget'] ?? 0),
            spent: (float) ($data['spent'] ?? 0),
            start_date: $data['start_date'] ?? null,
            end_date: $data['end_date'] ?? null,
            goals: $data['goals'] ?? null,
        ), auth()->user());

        $this->formOpen = false;
        $this->dispatch('marketing-saved');
        $this->dispatch('notify', message: 'Campaign created.', type: 'success');
    }

    public function stats(MarketingMetricsService $metrics): array
    {
        $campaigns = MarketingCampaign::query()->get();

        $totalLeads = $campaigns->sum(fn ($c) => $c->leads()->count());
        $totalSpent = $campaigns->sum('spent');

        return [
            'campaigns' => $campaigns->count(),
            'active' => $campaigns->where('status', 'active')->count(),
            'attributed_leads' => $totalLeads,
            'total_spent' => (float) $totalSpent,
            'avg_cpl' => $totalLeads ? round(((float) $totalSpent) / $totalLeads, 2) : 0.0,
        ];
    }

    public function chartPayload(MarketingMetricsService $metrics): array
    {
        return [
            'sources' => $metrics->leadsBySource(),
            'monthly' => $metrics->leadsByMonth(),
            'top' => $metrics->topCampaigns(),
        ];
    }

    public function render(): View
    {
        return view('livewire.marketing.marketing-overview', [
            'campaigns' => MarketingCampaign::with('creator')->withCount('leads')->latest()->get(),
        ]);
    }
}