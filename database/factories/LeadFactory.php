<?php

namespace Database\Factories;

use App\Enums\Division;
use App\Enums\LeadPriority;
use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Enums\VehicleBrandCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeadFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'company_name' => $this->faker->company(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'division' => Division::Automotive->value,
            'source' => LeadSource::Manual->value,
            'customer_type' => null,
            'vehicle_brand_category' => VehicleBrandCategory::Japanese->value,
            'status' => LeadStatus::New->value,
            'priority' => LeadPriority::Medium->value,
            'subject' => $this->faker->sentence(4),
            'requirements' => $this->faker->paragraph(),
            'estimated_value' => $this->faker->randomFloat(2, 500, 50000),
            'needs_triage' => false,
            'created_by' => User::factory(),
        ];
    }
}