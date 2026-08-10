<?php

namespace App\Actions\Communications;

use App\Mail\TemplatedEmail;
use App\Models\Customer;
use App\Models\EmailTemplate;
use App\Models\User;
use App\Services\Communications\TemplateRendererService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;

class SendTemplatedEmailAction
{
    public function __construct(private TemplateRendererService $renderer) {}

    /** Returns false when skipped (opt-out / no address). */
    public function execute(
        string $to,
        EmailTemplate $template,
        array $context,
        ?Customer $customer = null,
        ?Model $logEntity = null,
        ?User $sender = null,
    ): bool {
        if ($customer?->email_opted_out) {
            return false;
        }

        if (! $to) {
            return false;
        }

        ['subject' => $subject, 'body' => $body] = $this->renderer->render($template, $context);

        Mail::to($to)->send(new TemplatedEmail($subject, $body, $context['unsubscribe_url'] ?? null));

        $entity = $logEntity ?? $customer;

        if ($entity && method_exists($entity, 'communications')) {
            $entity->communications()->create([
                'channel' => 'email',
                'direction' => 'outbound',
                'subject' => $subject,
                'body' => $body,
                'user_id' => $sender?->id,
                'occurred_at' => now(),
                'metadata' => ['scenario' => 'templated_email', 'template' => $template->key],
            ]);
        }

        return true;
    }
}