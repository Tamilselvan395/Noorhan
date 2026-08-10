<?php

namespace App\Livewire\Ai;

use App\Models\Customer;
use App\Services\Ai\DailyBriefingService;
use App\Services\Ai\DemandForecastService;
use App\Services\Ai\NaturalLanguageSearchService;
use App\Services\Ai\RecommendationService;
use App\Services\Ai\SummaryService;
use Illuminate\View\View;
use Livewire\Component;

class AiDashboard extends Component
{
    public string $query = '';
    public array $searchResults = [];
    public ?int $customerId = null;

    public function search(NaturalLanguageSearchService $search): void
    {
        $this->searchResults = $this->query ? $search->search($this->query) : [];
    }

    public function selectCustomer(int $id): void
    {
        $this->customerId = $id;
    }

    public function render(
        DailyBriefingService $briefing,
        RecommendationService $recommendations,
        SummaryService $summary,
        DemandForecastService $demand,
    ): View {
        $customer = $this->customerId ? Customer::find($this->customerId) : null;

        return view('livewire.ai.ai-dashboard', [
            'brief' => $briefing->today(),
            'customer' => $customer,
            'recs' => $customer ? $recommendations->forCustomer($customer) : collect(),
            'custSummary' => $customer ? $summary->summarizeCustomer($customer) : null,
            'customers' => Customer::active()->orderBy('name')->limit(200)->get(),
        ]);
    }
}