<?php

namespace App\DTOs\Companies;

use App\DTOs\BaseDTO;

readonly class CompanyDTO extends BaseDTO
{
    public function __construct(
        public string $name,
        public string $type = 'garage',
        public string $status = 'active',
        public string $division = 'automotive',
        public ?string $trade_license_no = null,
        public ?string $tax_number = null,
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $website = null,
        public ?string $address = null,
        public ?string $city = null,
        public ?string $country = null,
        public ?int $owner_id = null,
        public ?string $notes = null,
    ) {}
}