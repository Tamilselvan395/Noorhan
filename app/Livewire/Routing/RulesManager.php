<?php

namespace App\Livewire\Routing;

use App\Models\LeadRoutingRule;
use App\Models\User;
use Illuminate\View\View;
use Livewire\Component;

class RulesManager extends Component
{
    public ?int $editingId = null;
    public bool $formOpen = false;

    public string $division = 'automotive';
    public string $condition_type = 'vehicle_brand';
    public string $condition_value = '';
    public ?int $user_id = null;
    public int $priority = 100;
    public bool $is_active = true;

    public function openForm(?int $ruleId = null): void
    {
        $this->resetValidation();
        $this->editingId = $ruleId;

        if ($ruleId) {
            $rule = LeadRoutingRule::findOrFail($ruleId);
            [$this->division, $this->condition_type, $this->condition_value, $this->user_id, $this->priority, $this->is_active] =
                [$rule->division, $rule->condition_type, (string) $rule->condition_value, $rule->user_id, $rule->priority, $rule->is_active];
        } else {
            [$this->division, $this->condition_type, $this->condition_value, $this->user_id, $this->priority, $this->is_active] =
                ['automotive', 'vehicle_brand', '', null, 100, true];
        }

        $this->formOpen = true;
    }

    public function save(): void
    {
        $this->validate([
            'division' => ['required', 'string'],
            'condition_type' => ['required', 'in:vehicle_brand,customer_type,default'],
            'condition_value' => ['required_unless:condition_type,default', 'nullable', 'string'],
            'user_id' => ['nullable', 'exists:users,id'],
            'priority' => ['required', 'integer', 'min:1', 'max:999'],
        ]);

        $data = [
            'division' => $this->division,
            'condition_type' => $this->condition_type,
            'condition_value' => $this->condition_type === 'default' ? null : ($this->condition_value ?: null),
            'user_id' => $this->user_id,
            'priority' => $this->priority,
            'is_active' => $this->is_active,
        ];

        $this->editingId
            ? LeadRoutingRule::findOrFail($this->editingId)->update($data)
            : LeadRoutingRule::create($data);

        $this->formOpen = false;
        $this->dispatch('notify', message: 'Routing rule saved.', type: 'success');
    }

    public function toggle(int $ruleId): void
    {
        $rule = LeadRoutingRule::findOrFail($ruleId);
        $rule->update(['is_active' => ! $rule->is_active]);
    }

    public function delete(int $ruleId): void
    {
        LeadRoutingRule::findOrFail($ruleId)->delete();
        $this->dispatch('notify', message: 'Rule deleted.', type: 'success');
    }

    public function render(): View
    {
        return view('livewire.routing.rules-manager', [
            'rules' => LeadRoutingRule::query()->with('user')->orderByDesc('priority')->get(),
            'users' => User::orderBy('name')->get(),
        ]);
    }
}