<?php

namespace App\Livewire\Communications;

use App\Actions\Communications\SendTemplatedEmailAction;
use App\Models\Customer;
use App\Models\EmailTemplate;
use App\Models\Lead;
use App\Services\Communications\EmailContextService;
use App\Services\Communications\TemplateRendererService;
use Illuminate\View\View;
use Livewire\Component;

class ComposeEmail extends Component
{
    public string $entityType = 'customer';
    public ?int $entityId = null;
    public string $to = '';
    public ?string $templateKey = null;
    public string $subject = '';
    public string $body = '';

    public function updatedEntityType(): void
    {
        $this->entityId = null;
        $this->to = '';
    }

    public function updatedEntityId(): void
    {
        $entity = $this->entity();

        $this->to = (string) ($entity?->email ?? '');

        $this->preview();
    }

    public function updatedTemplateKey(): void
    {
        $this->preview();
    }

    public function preview(TemplateRendererService $renderer = null, EmailContextService $context = null): void
    {
        if (! $this->templateKey) {
            return;
        }

        $template = EmailTemplate::where('key', $this->templateKey)->first();

        if (! $template) {
            return;
        }

        $rendered = ($renderer ?? app(TemplateRendererService::class))->render(
            $template,
            ($context ?? app(EmailContextService::class))->build(
                $this->entityType === 'customer' ? $this->entity() : null,
                $this->entityType === 'lead' ? $this->entity() : null,
                ['user' => ['name' => auth()->user()->name]],
            ),
        );

        $this->subject = $rendered['subject'];
        $this->body = $rendered['body'];
    }

    public function send(SendTemplatedEmailAction $action, EmailContextService $context): void
    {
        $this->validate([
            'to' => ['required', 'email'],
            'subject' => ['required', 'string', 'max:200'],
            'body' => ['required', 'string'],
        ]);

        $template = $this->templateKey
            ? EmailTemplate::where('key', $this->templateKey)->firstOrFail()
            : new EmailTemplate(['key' => 'ad_hoc', 'subject' => $this->subject, 'body' => $this->body]);

        $entity = $this->entity();

        $sent = $action->execute(
            $this->to,
            $template,
            $context->build(
                $this->entityType === 'customer' ? $entity : null,
                $this->entityType === 'lead' ? $entity : null,
                ['user' => ['name' => auth()->user()->name]],
            ),
            $this->entityType === 'customer' ? $entity : null,
            $entity,
            auth()->user(),
        );

        $this->dispatch('notify', message: $sent ? 'Email sent & logged.' : 'Skipped — recipient opted out or no address.', type: $sent ? 'success' : 'error');

        if ($sent) {
            $this->reset(['to', 'subject', 'body', 'templateKey', 'entityId']);
        }
    }

    private function entity(): Customer|Lead|null
    {
        if (! $this->entityId) {
            return null;
        }

        return $this->entityType === 'customer'
            ? Customer::find($this->entityId)
            : Lead::find($this->entityId);
    }

    public function render(): View
    {
        return view('livewire.communications.compose-email', [
            'templates' => EmailTemplate::where('is_active', true)->orderBy('name')->get(),
            'customers' => Customer::orderBy('name')->limit(300)->get(),
            'leads' => Lead::open()->latest()->limit(200)->get(),
        ]);
    }
}