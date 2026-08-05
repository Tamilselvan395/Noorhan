<?php

namespace App\DTOs\Auth;

use App\Enums\LoginStatus;

readonly class LoginResult
{
    private function __construct(public LoginStatus $status, public string $message) {}

    public static function success(): self
    {
        return new self(LoginStatus::Success, 'Authenticated.');
    }

    public static function failed(string $message): self
    {
        return new self(LoginStatus::Failed, $message);
    }

    public static function locked(string $message): self
    {
        return new self(LoginStatus::Locked, $message);
    }

    public static function throttled(string $message): self
    {
        return new self(LoginStatus::Throttled, $message);
    }
}