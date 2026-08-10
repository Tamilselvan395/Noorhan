<?php

namespace App\Livewire\Documents;

use App\Actions\Documents\UploadDocumentAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

class DocumentUploader extends Component
{
    use WithFileUploads;

    public Model $entity;

    public $file = null;
    public string $category = 'other';
    public string $expires_at = '';

    public function mount(Model $entity): void
    {
        $this->entity = $entity;
    }

    public function upload(UploadDocumentAction $action): void
    {
        $this->validate([
            'file' => ['required', 'file', 'max:10240'],
            'category' => ['required', 'string'],
            'expires_at' => ['nullable', 'date'],
        ]);

        $action->execute($this->entity, $this->file, auth()->user(), $this->category, $this->expires_at ?: null);

        $this->reset(['file', 'expires_at']);
        $this->category = 'other';

        $this->dispatch('document-added');
        $this->dispatch('notify', message: 'Document uploaded.', type: 'success');
    }

    public function render(): View
    {
        return view('livewire.documents.document-uploader');
    }
}