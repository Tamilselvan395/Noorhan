<?php

namespace App\Enums;

enum CustomerStatus: string
{
    case Active     = 'active';
    case Inactive   = 'inactive';
    case Blacklisted = 'blacklisted';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function badge(): string
    {
        return match ($this) {
            self::Active => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400',
            self::Inactive => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
            self::Blacklisted => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400',
        };
    }
}