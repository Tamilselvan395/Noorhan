<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LoginHistoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => 'login',
            'successful' => true,
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/126.0',
            'browser' => 'Chrome',
            'platform' => 'Windows',
            'device' => 'Desktop',
        ];
    }
}