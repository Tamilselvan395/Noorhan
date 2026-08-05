<?php

namespace App\Livewire\Capture;

use App\Actions\Leads\CreateLeadAction;
use App\DTOs\Leads\LeadDTO;
use Illuminate\View\View;
use Livewire\Component;

class QuickCapture extends Component
{
    public bool $open = false;
    public string $name = '';
    public string $phone = '';
    public string $source = 'walk_in';
    public string $division = 'automotive';
    public string $vehicle_brand_category = '';

    public function openModal(): void
    {
        $this->reset(['name', 'phone', 'vehicle_brand_category']);
        $this->resetValidation();
        $this->open = true;
    }

    public function save(CreateLeadAction $create): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        $create->execute(new LeadDTO(
            name: $this->name,
            division: $this->division,
            source: $this->source,
            phone: $this->phone ?: null,
            vehicle_brand_category: $this->vehicle_brand_category ?: null,
        ), auth()->user());

        $this->open = false;
        $this->dispatch('lead-saved');
        $this->dispatch('notify', message: 'Lead captured.', type: 'success');
    }

    public function render(): View
    {
        return view('livewire.capture.quick-capture');
    }
}