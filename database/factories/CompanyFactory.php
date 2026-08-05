<?php

namespace Database\Factories;

use App\Enums\CustomerStatus;
use App\Enums\CustomerType;
use App\Enums\Division;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'trade_license_no' => $this->faker->bothify('TL-######'),
            'type' => $this->faker->randomElement([CustomerType::Garage->value, CustomerType::Distributor->value, CustomerType::Dealer->value]),
            'status' => CustomerStatus::Active->value,
            'division' => Division::Automotive->value,
            'email' => $this->faker->unique()->companyEmail(),
            'phone' => $this->faker->phoneNumber(),
            'city' => $this->faker->city(),
            'country' => 'UAE',
        ];
    }
}