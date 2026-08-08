<?php

namespace App\Actions\Marketing;

use App\DTOs\Marketing\MarketingCampaignDTO;
use App\Models\MarketingCampaign;

class UpdateMarketingCampaignAction
{
    public function execute(MarketingCampaign $campaign, MarketingCampaignDTO $dto): void
    {
        $campaign->update($dto->toArray());

        $campaign->logActivity('updated the marketing campaign');
    }
}