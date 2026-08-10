<?php

namespace App\Livewire\Divisions;

use App\Enums\Division;
use App\Services\Divisions\DivisionMetricsService;
use Illuminate\View\View;
use Livewire\Component;

class DivisionWorkspace extends Component
{
    public Division $division;

    public function mount(string $division): void
    {
        $this->division = Division::from($division);
    }

    public function render(DivisionMetricsService $metrics): View
    {
        return view('livewire.divisions.division-workspace', [
            'm' => $metrics->metrics($this->division),
            'categories' => $metrics->categoryBreakdown($this->division),
            'topProducts' => $metrics->topProducts($this->division),
            'topCustomers' => $metrics->topCustomers($this->division),
            'partners' => $metrics->partners($this->division),
            'reorder' => $metrics->reorderPlan($this->division),
        ]);
    }
}