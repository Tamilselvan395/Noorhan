<?php

namespace App\DTOs\Profile;

use App\DTOs\BaseDTO;

readonly class ProfileUpdateDTO extends BaseDTO
{
    public function __construct(public string $name, public string $email) {}
}