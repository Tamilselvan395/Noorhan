<?php

namespace App\Jobs\WhatsApp;

use App\Models\WhatsAppCampaignRecipient;
use App\Services\WhatsApp\WhatsAppMessenger;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Throwable;

class SendCampaignMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function backoff(): array
    {
        return [30, 120, 300];
    }

    public function __construct(public WhatsAppCampaignRecipient $recipient) {}

    public function handle(WhatsAppMessenger $messenger): void
    {
        $campaign = $this->recipient->campaign;
        $customer = $this->recipient->customer;

        $result = match ($campaign->message_type) {
            'template' => $messenger->sendTemplate($customer, $campaign->template_name ?? 'welcome', [$customer->name], 'campaign:'.$campaign->name),
            'media' => $messenger->sendMedia($customer, (string) $campaign->media_url, $campaign->media_kind ?? 'image', $campaign->body, 'campaign:'.$campaign->name),
            default => $messenger->sendText($customer, (string) $campaign->body, 'campaign:'.$campaign->name),
        };

        if ($result === null) {
            // Opted out / no number — treat as skipped, not failed.
            $this->recipient->update(['status' => 'failed', 'error' => 'Skipped (opted out or no number)']);
            $campaign->increment('failed_count');
            return;
        }

        $this->recipient->update(['status' => 'sent', 'sent_at' => now()]);
        $campaign->increment('sent_count');
    }

    public function failed(Throwable $e): void
    {
        $this->recipient->update(['status' => 'failed', 'error' => Str::limit($e->getMessage(), 300)]);
        $this->recipient->campaign->increment('failed_count');
    }
}