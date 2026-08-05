<?php

namespace App\DTOs\Customers;

use App\DTOs\BaseDTO;

readonly class CustomerDTO extends BaseDTO
{
    public function __construct(
        public string $name,
        public string $type = 'retail',
        public string $division = 'automotive',
        public string $status = 'active',
        public ?string $company_name = null,
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $whatsapp = null,
        public ?string $address = null,
        public ?string $city = null,
        public ?string $country = null,
        public ?string $vehicle_brand_category = null,
        public ?int $owner_id = null,
        public ?float $credit_limit = null,
        public ?string $notes = null,
        public ?int $company_id = null,
    ) {}
}