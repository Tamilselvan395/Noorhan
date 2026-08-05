<?php

namespace App\DTOs\Capture;

use App\DTOs\BaseDTO;
use App\Enums\LeadSource;

readonly class LeadCaptureDTO extends BaseDTO
{
    public function __construct(
        public string $name,
        public LeadSource $source,
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $company_name = null,
        public string $division = 'automotive',
        public ?string $subject = null,
        public ?string $requirements = null,
        public ?string $vehicle_brand_category = null,
        public ?string $customer_type = null,
        public ?string $utm_source = null,
        public ?string $utm_medium = null,
        public ?string $utm_campaign = null,
        public ?string $landing_url = null,
        public ?string $business_card_path = null,
        public bool $needs_triage = true,
    ) {}
}