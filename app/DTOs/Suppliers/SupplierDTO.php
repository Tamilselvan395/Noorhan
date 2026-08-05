<?php

namespace App\DTOs\Suppliers;

use App\DTOs\BaseDTO;

readonly class SupplierDTO extends BaseDTO
{
    public function __construct(
        public string $name,
        public string $division = 'automotive',
        public string $status = 'active',
        public string $currency = 'USD',
        public ?string $contact_person = null,
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $whatsapp = null,
        public ?string $website = null,
        public ?string $country = null,
        public ?string $city = null,
        public ?string $address = null,
        public ?string $payment_terms = null,
        public ?int $owner_id = null,
        public ?string $notes = null,
    ) {}
}