<?php

namespace App\Livewire\Communications;

use App\Models\EmailTemplate;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class TemplateManager extends Component
{
    public bool $formOpen = false;
    public ?int $editingId = null;

    public string $name = '';
    public string $key = '';
    public string $category = 'general';
    public string $subject = '';
    public string $body = '';
    public bool $is_active = true;

    #[On('open-template-form')]
    public function openForm(?int $templateId = null): void
    {
        $this->editingId = $templateId;

        if ($templateId) {
            $template = EmailTemplate::findOrFail($templateId);
            [$this->name, $this->key, $this->category, $this->subject, $this->body, $this->is_active] =
                [$template->name, $template->key, $template->category, $template->subject, $template->body, $template->is_active];
        } else {
            $this->reset(['name', 'key', 'subject', 'body']);
            $this->category = 'general';
            $this->is_active = true;
        }

        $this->formOpen = true;
    }

    public function save(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:120'],
            'key' => ['required', 'string', 'max:80', 'unique:email_templates,key,'.($this->editingId ?? 'null')],
            'subject' => ['required', 'string', 'max:200'],
            'body' => ['required', 'string'],
        ]);

        EmailTemplate::updateOrCreate(['id' => $this->editingId], [
            'name' => $this->name,
            'key' => $this->editingId ? EmailTemplate::find($this->editingId)->key : Str::slug($this->key),
            'category' => $this->category,
            'subject' => $this->subject,
            'body' => $this->body,
            'is_active' => $this->is_active,
            'created_by' => auth()->id(),
        ]);

        $this->formOpen = false;
        $this->dispatch('notify', message: 'Template saved.', type: 'success');
    }

    public function toggle(int $id): void
    {
        $template = EmailTemplate::findOrFail($id);
        $template->update(['is_active' => ! $template->is_active]);
    }

    public function render(): View
    {
        return view('livewire.communications.template-manager', [
            'templates' => EmailTemplate::orderBy('name')->get(),
        ]);
    }
}