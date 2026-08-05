<?php

namespace Database\Factories;

use App\Enums\CustomerStatus;
use App\Enums\CustomerType;
use App\Enums\Division;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'company_name' => $this->faker->company(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'whatsapp' => $this->faker->phoneNumber(),
            'type' => CustomerType::Garage->value,
            'status' => CustomerStatus::Active->value,
            'city' => $this->faker->city(),
            'country' => 'UAE',
            'division' => Division::Automotive->value,
        ];
    }
}