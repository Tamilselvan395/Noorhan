<?php

namespace App\DTOs\Routing;

use App\DTOs\BaseDTO;

readonly class ClassificationResult extends BaseDTO
{
    public function __construct(
        public ?string $vehicle_brand_category = null,
        public ?string $division = null,
        public ?string $customer_type = null,
        public float $confidence = 0.0,
    ) {}

    public static function empty(): self
    {
        return new self();
    }

    public function hasSuggestions(): bool
    {
        return $this->vehicle_brand_category !== null
            || $this->division !== null
            || $this->customer_type !== null;
    }
}