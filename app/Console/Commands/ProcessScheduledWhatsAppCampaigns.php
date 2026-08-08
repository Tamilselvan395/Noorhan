<?php

namespace App\Console\Commands;

use App\Jobs\WhatsApp\ProcessCampaignJob;
use App\Models\WhatsAppCampaign;
use Illuminate\Console\Command;

class ProcessScheduledWhatsAppCampaigns extends Command
{
    protected $signature = 'whatsapp:campaigns';
    protected $description = 'Dispatch due scheduled WhatsApp campaigns.';

    public function handle(): int
    {
        WhatsAppCampaign::query()
            ->where('status', 'scheduled')
            ->where('scheduled_at', '<=', now())
            ->get()
            ->each(fn (WhatsAppCampaign $campaign) => ProcessCampaignJob::dispatch($campaign));

        return self::SUCCESS;
    }
}