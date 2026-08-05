<?php

namespace App\DTOs\Products;

use App\DTOs\BaseDTO;

readonly class ProductDTO extends BaseDTO
{
    public function __construct(
        public string $sku,
        public string $name,
        public string $division = 'automotive',
        public string $unit = 'pcs',
        public ?int $category_id = null,
        public ?string $brand = null,
        public ?string $description = null,
        public float $cost_price = 0,
        public float $sale_price = 0,
        public float $tax_rate = 5.0,
        public ?array $attributes = null,
        public bool $is_active = true,
    ) {}
}