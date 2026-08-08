<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\Lead;
use App\Notifications\Finance\OverdueInvoiceNotification;
use App\Notifications\Leads\FollowUpDueNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SendNotificationDigest extends Command
{
    protected $signature = 'notifications:digest';
    protected $description = 'Daily digest: follow-ups due today and overdue invoices.';

    public function handle(): int
    {
        // 1 — Follow-ups due
        Lead::query()->followUpDue()->whereNotNull('assigned_to')->with('assignee')->get()
            ->filter(fn (Lead $lead) => ! $this->alreadySent($lead->assignee, FollowUpDueNotification::class, 'lead_id', $lead->id))
            ->each(fn (Lead $lead) => $lead->assignee->notify(new FollowUpDueNotification($lead)));

        // 2 — Overdue invoices
        Invoice::query()->outstanding()->where('due_date', '<', now())->with('creator')->get()
            ->filter(fn (Invoice $i) => $i->creator && ! $this->alreadySent($i->creator, OverdueInvoiceNotification::class, 'invoice_id', $i->id))
            ->each(fn (Invoice $i) => $i->creator->notify(new OverdueInvoiceNotification($i)));

        $this->info('Digest processed.');

        return self::SUCCESS;
    }

    private function alreadySent($user, string $type, string $key, int $id): bool
    {
        return DB::table('notifications')
            ->where('notifiable_type', $user->getMorphClass())
            ->where('notifiable_id', $user->id)
            ->where('type', $type)
            ->where("data->{$key}", $id)
            ->whereDate('created_at', today())
            ->exists();
    }
}