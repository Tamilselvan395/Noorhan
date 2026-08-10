<?php

namespace App\Enums;

enum AppointmentStatus: string
{
    case Booked = 'booked';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case NoShow = 'no_show';

    public function label(): string
    {
        return ucwords(str_replace('_', ' ', $this->value));
    }

    public function badge(): string
    {
        return match ($this) {
            self::Booked => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400',
            self::InProgress => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400',
            self::Completed => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400',
            self::Cancelled => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
            self::NoShow => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400',
        };
    }
}