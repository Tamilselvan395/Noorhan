<?php

namespace App\Enums;

enum LeadPriority: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
    case Urgent = 'urgent';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function badge(): string
    {
        return match ($this) {
            self::Low => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
            self::Medium => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400',
            self::High => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400',
            self::Urgent => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400',
        };
    }
}