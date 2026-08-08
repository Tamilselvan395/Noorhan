<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Lead;
use App\Services\WhatsApp\WhatsAppAutomationService;
use Illuminate\Console\Command;

class SendWhatsAppAutomations extends Command
{
    protected $signature = 'whatsapp:automations';
    protected $description = 'Send due WhatsApp automations: payment reminders, follow-ups, service reminders, dormant reactivation.';

    public function handle(WhatsAppAutomationService $automation): int
    {
        if (! config('whatsapp.enabled')) {
            $this->warn('WhatsApp is disabled.');
            return self::SUCCESS;
        }

        // 1 — Payment reminders (due within 3 days or overdue, not reminded in 72h)
        Invoice::query()->outstanding()
            ->where('due_date', '<=', now()->addDays(3))
            ->with('customer')
            ->get()
            ->filter(fn (Invoice $i) => $i->customer && ! $automation->recentlySent($i->customer, 'payment_reminder', 3))
            ->each(fn (Invoice $i) => $automation->paymentReminder($i));

        // 2 — Lead follow-ups due
        Lead::query()->followUpDue()->get()
            ->each(fn (Lead $lead) => $automation->leadFollowUp($lead));

        // 3 — Otozaar service reminders (inactive 90+ days)
        Customer::query()->active()->where('division', 'otozaar')
            ->where(fn ($q) => $q->whereNull('last_activity_at')->orWhere('last_activity_at', '<=', now()->subDays(90)))
            ->get()
            ->filter(fn (Customer $c) => ! $automation->recentlySent($c, 'service_reminder', 30))
            ->each(fn (Customer $c) => $automation->serviceReminder($c));

        // 4 — Dormant customer reactivation (inactive 180+ days)
        Customer::query()->active()
            ->where('last_activity_at', '<=', now()->subDays(180))
            ->get()
            ->filter(fn (Customer $c) => ! $automation->recentlySent($c, 'dormant_reactivation', 60))
            ->each(fn (Customer $c) => $automation->dormantReactivation($c));

        $this->info('WhatsApp automations processed.');

        return self::SUCCESS;
    }
}