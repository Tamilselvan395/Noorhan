<?php

namespace App\Livewire\Capture;

use App\Enums\LeadSource;
use App\Helpers\UploadHelper;
use App\Services\Capture\LeadCaptureService;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

class BusinessCardUpload extends Component
{
    use WithFileUploads;

    public bool $open = false;
    public $file = null;
    public string $note = '';

    public function openModal(): void
    {
        $this->reset(['file', 'note']);
        $this->resetValidation();
        $this->open = true;
    }

    public function save(LeadCaptureService $service): void
    {
        $this->validate([
            'file' => ['required', 'file', 'image', 'max:5120'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $path = UploadHelper::upload($this->file, 'business-cards');

        $service->ingest(LeadSource::BusinessCard, [
            'path' => $path,
            'note' => $this->note ?: null,
        ], async: false);

        $this->open = false;
        $this->dispatch('lead-saved');
        $this->dispatch('notify', message: 'Business card captured — sent to Triage for completion.', type: 'success');
    }

    public function render(): View
    {
        return view('livewire.capture.business-card-upload');
    }
}