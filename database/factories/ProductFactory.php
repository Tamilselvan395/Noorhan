<?php

namespace Database\Factories;

use App\Enums\Division;
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        $cost = $this->faker->randomFloat(2, 10, 500);

        return [
            'sku' => 'SKU-'.$this->faker->unique()->bothify('#####'),
            'name' => $this->faker->words(3, true),
            'category_id' => ProductCategory::factory(),
            'division' => Division::Automotive->value,
            'brand' => $this->faker->company(),
            'unit' => 'pcs',
            'cost_price' => $cost,
            'sale_price' => round($cost * 1.3, 2),
            'tax_rate' => 5.00,
            'is_active' => true,
        ];
    }
}