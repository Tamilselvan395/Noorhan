<?php

namespace App\Actions\WhatsApp;

use App\Jobs\WhatsApp\ProcessCampaignJob;
use App\Models\WhatsAppCampaign;
use App\Models\User;

class LaunchCampaignAction
{
    public function execute(array $data, ?User $creator, bool $sendNow): WhatsAppCampaign
    {
        $campaign = WhatsAppCampaign::create(array_merge($data, [
            'status' => $sendNow ? 'scheduled' : 'draft',
            'scheduled_at' => $sendNow ? now() : ($data['scheduled_at'] ?? null),
            'created_by' => $creator?->id,
        ]));

        if ($sendNow) {
            ProcessCampaignJob::dispatch($campaign);
        }

        return $campaign;
    }
}