<?php

namespace App\Actions\Marketing;

use App\DTOs\Marketing\MarketingCampaignDTO;
use App\Models\MarketingCampaign;
use App\Models\User;
use Illuminate\Support\Str;

class CreateMarketingCampaignAction
{
    public function execute(MarketingCampaignDTO $dto, ?User $creator): MarketingCampaign
    {
        $campaign = MarketingCampaign::create(array_merge($dto->toArray(), [
            'utm_campaign' => $dto->utm_campaign ?: Str::slug($dto->name),
            'created_by' => $creator?->id,
        ]));

        $campaign->logActivity('created the marketing campaign');

        return $campaign;
    }
}