<?php

namespace App\Livewire\Leads;

use App\Actions\Leads\AssignLeadAction;
use App\Actions\Leads\MoveLeadStageAction;
use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Models\User;
use App\Services\Leads\LeadPipelineService;
use DomainException;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;

class LeadShow extends Component
{
    public Lead $lead;

    public ?int $assignTo = null;

    public bool $lostModal = false;
    public string $lostReason = '';

    public string $note = '';

    public function mount(Lead $lead): void
    {
        $this->lead = $lead;
        $this->assignTo = $lead->assigned_to;
    }

    public function moveStage(string $status, MoveLeadStageAction $move): void
    {
        Gate::authorize('update', $this->lead);

        try {
            $move->execute($this->lead, LeadStatus::from($status));
            $this->dispatch('notify', message: 'Stage updated.', type: 'success');
        } catch (DomainException $e) {
            $this->dispatch('notify', message: $e->getMessage(), type: 'error');
        }
    }

    public function openLostModal(): void
    {
        $this->lostReason = '';
        $this->lostModal = true;
    }

    public function confirmLost(MoveLeadStageAction $move): void
    {
        Gate::authorize('update', $this->lead);

        try {
            $move->execute($this->lead, LeadStatus::Lost, $this->lostReason ?: null);
            $this->lostModal = false;
            $this->dispatch('notify', message: 'Lead marked as lost.', type: 'success');
        } catch (DomainException $e) {
            $this->dispatch('notify', message: $e->getMessage(), type: 'error');
        }
    }

    public function assign(AssignLeadAction $assign): void
    {
        Gate::authorize('update', $this->lead);

        $assign->execute($this->lead, User::find($this->assignTo), auth()->user());

        $this->dispatch('notify', message: 'Assignment updated.', type: 'success');
    }

    public function addNote(): void
    {
        $this->validate(['note' => 'required|string|max:2000']);

        $this->lead->addNote($this->note);
        $this->note = '';

        $this->dispatch('notify', message: 'Note added.', type: 'success');
    }

    public function deleteLead(): void
    {
        Gate::authorize('delete', $this->lead);

        $this->lead->delete();

        session()->flash('notify', 'Lead deleted.');

        $this->redirect(route('leads.index'), navigate: true);
    }

    public function allowedTransitions(): array
    {
        return app(LeadPipelineService::class)->allowedNext($this->lead->status());
    }

    public function render(): View
    {
        return view('livewire.leads.lead-show', [
            'users' => User::orderBy('name')->get(),
            'timeline' => $this->lead->activities()->with('user')->latest()->get(),
        ]);
    }
    public function convertToCustomer(\App\Actions\Customers\ConvertLeadToCustomerAction $convert): void
    {
        Gate::authorize('update', $this->lead);

        $customer = $convert->execute($this->lead);

        $this->dispatch('notify', message: "Converted to customer: {$customer->displayName()}", type: 'success');
    }
}