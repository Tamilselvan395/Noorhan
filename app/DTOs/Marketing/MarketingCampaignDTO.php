<?php

namespace App\DTOs\Marketing;

use App\DTOs\BaseDTO;

readonly class MarketingCampaignDTO extends BaseDTO
{
    public function __construct(
        public string $name,
        public string $division = 'automotive',
        public string $channel = 'whatsapp',
        public string $status = 'planned',
        public string $utm_campaign = '',
        public float $budget = 0,
        public float $spent = 0,
        public ?string $start_date = null,
        public ?string $end_date = null,
        public ?string $goals = null,
        public ?string $notes = null,
    ) {}
}