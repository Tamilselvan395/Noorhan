<?php

namespace Database\Factories;

use App\Enums\Division;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductCategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->words(2, true),
            'division' => Division::Automotive->value,
            'is_active' => true,
        ];
    }
}