<?php

namespace App\Livewire\Leads;

use App\Models\Lead;
use App\Services\Leads\LeadPipelineService;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class LeadIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $status = 'all';
    public string $division = 'all';
    public string $source = 'all';
    public string $priority = 'all';
    public string $assignment = 'all'; // all | mine | unassigned
    public bool $triageOnly = false;
    public string $view = 'table'; // table | kanban

    public function updated($property): void
    {
        if (! in_array($property, ['view'])) {
            $this->resetPage();
        }
    }

    public function toggleView(string $view): void
    {
        $this->view = $view;
    }

    public function stats(): array
    {
        return app(LeadPipelineService::class)->stats();
    }

    public function render(): View
    {
        $leads = Lead::query()
            ->with(['assignee', 'creator'])
            ->search($this->search ?: null)
            ->when($this->status !== 'all', fn ($q) => $q->where('status', $this->status))
            ->when($this->division !== 'all', fn ($q) => $q->where('division', $this->division))
            ->when($this->source !== 'all', fn ($q) => $q->where('source', $this->source))
            ->when($this->priority !== 'all', fn ($q) => $q->where('priority', $this->priority))
            ->when($this->triageOnly, fn ($q) => $q->triage())
            ->when($this->assignment === 'mine', fn ($q) => $q->where('assigned_to', auth()->id()))
            ->when($this->assignment === 'unassigned', fn ($q) => $q->whereNull('assigned_to'))
            ->latest()
            ->paginate(12);

        return view('livewire.leads.lead-index', ['leads' => $leads]);
    }
}