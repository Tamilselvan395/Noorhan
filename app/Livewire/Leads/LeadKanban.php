<?php

namespace App\Livewire\Leads;

use App\Enums\Division;
use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Actions\Leads\MoveLeadStageAction;
use App\Repositories\LeadRepository;
use DomainException;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;

class LeadKanban extends Component
{
    public string $division = 'all';

    public function move(int $leadId, string $status, MoveLeadStageAction $move): void
    {
        $lead = Lead::findOrFail($leadId);

        Gate::authorize('update', $lead);

        try {
            $move->execute($lead, LeadStatus::from($status));
            $this->dispatch('notify', message: "Lead moved to ".LeadStatus::from($status)->label().'.', type: 'success');
        } catch (DomainException $e) {
            $this->dispatch('notify', message: $e->getMessage(), type: 'error');
        }
    }

    public function render(): View
    {
        $division = $this->division !== 'all' ? Division::from($this->division) : null;

        return view('livewire.leads.lead-kanban', [
            'columns' => app(LeadRepository::class)->forKanban($division),
        ]);
    }
}