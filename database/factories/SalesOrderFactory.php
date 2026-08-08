<?php

namespace Database\Factories;

use App\Enums\Division;
use App\Enums\SalesOrderStatus;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class SalesOrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'reference' => 'SO-'.$this->faker->unique()->numerify('#####'),
            'customer_id' => Customer::factory(),
            'division' => Division::Automotive->value,
            'status' => SalesOrderStatus::Pending->value,
            'tax_rate' => 5,
            'total' => 0,
        ];
    }
}