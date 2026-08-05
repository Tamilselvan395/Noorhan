<?php

namespace App\Livewire\Leads;

use App\Actions\Leads\AssignLeadAction;
use App\Actions\Routing\RouteLeadAction;
use App\Contracts\LeadClassifierInterface;
use App\DTOs\Routing\ClassificationResult;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;

class TriageQueue extends Component
{
    public array $manual = [];

    public function queue(): \Illuminate\Support\Collection
    {
        return Lead::query()->open()->triage()->with('creator')->latest()->get();
    }

    public function suggestion(Lead $lead): ClassificationResult
    {
        return app(LeadClassifierInterface::class)->classify($lead);
    }

    public function applyAndRoute(int $leadId, RouteLeadAction $route): void
    {
        $lead = Lead::findOrFail($leadId);
        Gate::authorize('update', $lead);

        $route->execute($lead, true, true);

        $this->dispatch('notify', message: $lead->fresh()->assigned_to ? 'AI applied & lead routed.' : 'AI applied — still needs a rule.', type: 'success');
    }

    public function routeOnly(int $leadId, RouteLeadAction $route): void
    {
        $lead = Lead::findOrFail($leadId);
        Gate::authorize('update', $lead);

        $route->execute($lead, false);

        $this->dispatch('notify', message: 'Routing rules evaluated.', type: 'success');
    }

    public function assignManual(int $leadId, AssignLeadAction $assign): void
    {
        $lead = Lead::findOrFail($leadId);
        Gate::authorize('update', $lead);

        $user = User::find($this->manual[$leadId] ?? null);

        abort_unless($user !== null, 422);

        $assign->execute($lead, $user, auth()->user());

        $this->dispatch('notify', message: 'Lead manually assigned.', type: 'success');
    }

    public function render(): View
    {
        return view('livewire.leads.triage-queue', ['users' => User::orderBy('name')->get()]);
    }
}