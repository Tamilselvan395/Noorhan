<?php

namespace App\Services\Auth;

use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class LoginThrottleService
{
    private function key(string $email, string $ip): string
    {
        return 'login:'.md5(Str::lower($email).'|'.$ip);
    }

    public function isThrottled(string $email, string $ip): bool
    {
        return RateLimiter::tooManyAttempts($this->key($email, $ip), (int) config('noorhan.auth.rate_limit_max', 10));
    }

    public function hit(string $email, string $ip): void
    {
        RateLimiter::hit($this->key($email, $ip), 60);
    }

    public function availableIn(string $email, string $ip): int
    {
        return RateLimiter::availableIn($this->key($email, $ip));
    }

    public function clear(string $email, string $ip): void
    {
        RateLimiter::clear($this->key($email, $ip));
    }
}