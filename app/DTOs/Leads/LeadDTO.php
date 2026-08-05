<?php

namespace App\DTOs\Leads;

use App\DTOs\BaseDTO;

readonly class LeadDTO extends BaseDTO
{
    public function __construct(
        public string $name,
        public string $division = 'automotive',
        public string $source = 'manual',
        public string $priority = 'medium',
        public ?string $company_name = null,
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $customer_type = null,
        public ?string $vehicle_brand_category = null,
        public ?string $subject = null,
        public ?string $requirements = null,
        public ?float $estimated_value = null,
        public ?string $next_follow_up_at = null,
        public ?int $assigned_to = null,
    ) {}
}