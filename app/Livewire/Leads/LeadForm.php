<?php

namespace App\Livewire\Leads;

use App\Actions\Leads\AssignLeadAction;
use App\Actions\Leads\CreateLeadAction;
use App\Actions\Leads\UpdateLeadAction;
use App\DTOs\Leads\LeadDTO;
use App\Http\Requests\Leads\StoreLeadRequest;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class LeadForm extends Component
{
    public bool $open = false;
    public ?int $leadId = null;

    public string $name = '';
    public string $company_name = '';
    public string $email = '';
    public string $phone = '';
    public string $division = 'automotive';
    public string $source = 'manual';
    public string $customer_type = '';
    public string $vehicle_brand_category = '';
    public string $priority = 'medium';
    public string $subject = '';
    public string $requirements = '';
    public string $estimated_value = '';
    public string $next_follow_up_at = '';
    public ?int $assigned_to = null;

    #[On('open-lead-form')]
    public function openForm(?int $leadId = null): void
    {
        $this->reset(['leadId']);
        $this->resetValidation();

        if ($leadId) {
            $lead = Lead::findOrFail($leadId);
            Gate::authorize('update', $lead);

            $this->leadId = $lead->id;
            foreach (['name','company_name','email','phone','division','source','customer_type','vehicle_brand_category','priority','subject','requirements','assigned_to'] as $field) {
                $this->{$field} = $lead->{$field} ?? '';
            }
            $this->estimated_value = $lead->estimated_value ? (string) $lead->estimated_value : '';
            $this->next_follow_up_at = $lead->next_follow_up_at?->format('Y-m-d\TH:i') ?? '';
        } else {
            Gate::authorize('create', Lead::class);
            $this->reset(['name','company_name','email','phone','subject','requirements','estimated_value','next_follow_up_at','assigned_to','leadId']);
            $this->division = 'automotive';
            $this->source = 'manual';
            $this->priority = 'medium';
            $this->customer_type = '';
            $this->vehicle_brand_category = '';
        }

        $this->open = true;
    }

    public function save(CreateLeadAction $create, UpdateLeadAction $update, AssignLeadAction $assign): void
    {
        $data = $this->validate(StoreLeadRequest::rules());

        $data['customer_type'] = $data['customer_type'] ?: null;
        $data['vehicle_brand_category'] = $data['vehicle_brand_category'] ?: null;
        $data['next_follow_up_at'] = $data['next_follow_up_at'] ?: null;

        $dto = LeadDTO::fromArray($data);

        if ($this->leadId) {
            $lead = Lead::findOrFail($this->leadId);
            $previousAssignee = $lead->assigned_to;

            $update->execute($lead, $dto);

            if ((int) ($data['assigned_to'] ?? 0) !== (int) $previousAssignee) {
                $assign->execute($lead, User::find($data['assigned_to'] ?? null), auth()->user());
            }
        } else {
            $create->execute($dto, auth()->user());
        }

        $this->open = false;
        $this->dispatch('lead-saved');
        $this->dispatch('notify', message: 'Lead saved successfully.', type: 'success');
    }

    public function render(): View
    {
        return view('livewire.leads.lead-form', ['users' => User::orderBy('name')->get()]);
    }
}