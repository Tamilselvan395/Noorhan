<?php

namespace Database\Factories;

use App\Enums\Division;
use App\Enums\MarketingCampaignStatus;
use App\Enums\MarketingChannel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class MarketingCampaignFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->words(3, true);

        return [
            'name' => $name,
            'division' => Division::Automotive->value,
            'channel' => MarketingChannel::Ads->value,
            'status' => MarketingCampaignStatus::Active->value,
            'budget' => $this->faker->randomFloat(2, 500, 10000),
            'spent' => $this->faker->randomFloat(2, 100, 900),
            'utm_campaign' => Str::slug($name),
        ];
    }
}