<?php

namespace App\Jobs\WhatsApp;

use App\Models\WhatsAppCampaign;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessCampaignJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public WhatsAppCampaign $campaign) {}

    public function handle(): void
    {
        $this->campaign->update(['status' => 'sending']);

        foreach ($this->campaign->audience() as $customer) {
            $recipient = $this->campaign->recipients()->firstOrCreate(['customer_id' => $customer->id]);

            if ($recipient->status === 'sent') {
                continue;
            }

            // Stagger delivery to respect Meta rate limits (~50 msgs / batch)
            SendCampaignMessageJob::dispatch($recipient)->delay(now()->addSeconds(intdiv($recipient->id, 50)));
        }
    }
}