<?php

namespace App\Services\WhatsApp;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Lead;

class WhatsAppAutomationService
{
    public function __construct(private WhatsAppMessenger $messenger) {}

    public function welcome($entity): void
    {
        $this->messenger->sendTemplate($entity, 'welcome', [$entity->name], 'welcome');
    }

    public function leadFollowUp(Lead $lead): void
    {
        $this->messenger->sendTemplate($lead, 'follow_up', [$lead->name], 'follow_up');
    }

    public function quotationReminder($entity, string $reference, string $url): void
    {
        $this->messenger->sendTemplate($entity, 'quotation_reminder', [$reference, $url], 'quotation_reminder');
    }

    public function paymentReminder(Invoice $invoice): void
    {
        $this->messenger->sendTemplate($invoice->customer, 'payment_reminder', [
            $invoice->reference,
            number_format((float) $invoice->balance_due, 2),
            $invoice->due_date->format('d M Y'),
        ], 'payment_reminder');
    }

    public function serviceReminder(Customer $customer): void
    {
        $this->messenger->sendTemplate($customer, 'service_reminder', [$customer->name], 'service_reminder');
    }

    public function crossSell(Customer $customer): void
    {
        $this->messenger->sendTemplate($customer, 'cross_sell', [$customer->name], 'cross_sell');
    }

    public function dormantReactivation(Customer $customer): void
    {
        $this->messenger->sendTemplate($customer, 'dormant_reactivation', [$customer->name], 'dormant_reactivation');
    }

    /** Scenario dedupe: has this customer received this scenario within $days? */
    public function recentlySent(Customer $customer, string $scenario, int $days): bool
    {
        return $customer->communications()
            ->where('channel', 'whatsapp')
            ->where('created_at', '>=', now()->subDays($days))
            ->get()
            ->contains(fn ($c) => ($c->metadata['scenario'] ?? null) === $scenario);
    }
}