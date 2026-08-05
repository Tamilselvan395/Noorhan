<?php

namespace Database\Factories;

use App\Enums\CustomerStatus;
use App\Enums\Division;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'contact_person' => $this->faker->name(),
            'email' => $this->faker->unique()->companyEmail(),
            'phone' => $this->faker->phoneNumber(),
            'country' => $this->faker->country(),
            'division' => Division::Automotive->value,
            'status' => CustomerStatus::Active->value,
            'payment_terms' => $this->faker->randomElement(['Advance', '30% advance', 'Net 30', 'Net 60']),
            'currency' => 'USD',
        ];
    }
}