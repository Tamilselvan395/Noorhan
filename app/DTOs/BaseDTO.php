<?php

namespace App\DTOs;

readonly class BaseDTO
{
    public function toArray(): array
    {
        return get_object_vars($this);
    }

    public static function fromArray(array $data): static
    {
        // PHP 8.4+ supports spreading arrays into named arguments seamlessly
        return new static(...$data);
    }
}