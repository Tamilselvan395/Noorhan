<?php

namespace App\Enums;

enum MarketingCampaignStatus: string
{
    case Planned = 'planned';
    case Active = 'active';
    case Paused = 'paused';
    case Completed = 'completed';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function badge(): string
    {
        return match ($this) {
            self::Planned => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
            self::Active => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400',
            self::Paused => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400',
            self::Completed => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400',
        };
    }
}