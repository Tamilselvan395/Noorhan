<?php

namespace App\DTOs\Auth;

use App\DTOs\BaseDTO;
use App\Http\Requests\Auth\LoginRequest;

readonly class LoginDTO extends BaseDTO
{
    public function __construct(
        public string $email,
        public string $password,
        public bool $remember = false,
    ) {}

    public static function fromRequest(LoginRequest $request): self
    {
        return new self(
            email: $request->validated('email'),
            password: $request->validated('password'),
            remember: $request->boolean('remember'),
        );
    }
}