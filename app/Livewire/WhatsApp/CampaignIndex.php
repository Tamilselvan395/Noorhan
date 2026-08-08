<?php

namespace App\Livewire\WhatsApp;

use App\Models\WhatsAppCampaign;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class CampaignIndex extends Component
{
    use WithPagination;

    public string $name = '';
    public string $audience_type = 'all';
    public string $audience_value = '';
    public string $message_type = 'text';
    public string $template_name = '';
    public string $body = '';
    public string $media_url = '';
    public string $media_kind = 'image';
    public string $scheduled_at = '';
    public bool $sendNow = true;
    public bool $builderOpen = false;

    public function openBuilder(): void
    {
        $this->reset(['name', 'body', 'media_url', 'scheduled_at', 'audience_value', 'template_name']);
        $this->sendNow = true;
        $this->builderOpen = true;
    }

    public function launch(\App\Actions\WhatsApp\LaunchCampaignAction $launch): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:160'],
            'message_type' => ['required', 'in:text,template,media'],
            'body' => ['required_if:message_type,text,media', 'nullable', 'string'],
            'template_name' => ['required_if:message_type,template', 'nullable', 'string'],
            'media_url' => ['required_if:message_type,media', 'nullable', 'url'],
        ]);

        $launch->execute([
            'name' => $this->name,
            'audience_type' => $this->audience_type,
            'audience_value' => $this->audience_value ?: null,
            'message_type' => $this->message_type,
            'template_name' => $this->template_name ?: null,
            'body' => $this->body ?: null,
            'media_url' => $this->media_url ?: null,
            'media_kind' => $this->media_kind,
            'scheduled_at' => $this->scheduled_at ?: null,
        ], auth()->user(), $this->sendNow);

        $this->builderOpen = false;
        $this->dispatch('notify', message: 'Campaign launched.', type: 'success');
    }

    public function stats(): array
    {
        return [
            'campaigns' => WhatsAppCampaign::count(),
            'sent' => (int) WhatsAppCampaign::sum('sent_count'),
            'failed' => (int) WhatsAppCampaign::sum('failed_count'),
        ];
    }

    public function render(): View
    {
        return view('livewire.whatsapp.campaign-index', [
            'campaigns' => WhatsAppCampaign::with('creator')->latest()->paginate(10),
        ]);
    }
}  