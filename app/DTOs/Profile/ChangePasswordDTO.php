<?php

namespace App\DTOs\Profile;

use App\DTOs\BaseDTO;

readonly class ChangePasswordDTO extends BaseDTO
{
    public function __construct(public string $currentPassword, public string $newPassword) {}
}