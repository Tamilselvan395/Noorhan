<?php

namespace App\Enums;

enum EnquiryItemStatus: string
{
    case Pending = 'pending';
    case Quoted = 'quoted';
    case Declined = 'declined';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function badge(): string
    {
        return match ($this) {
            self::Pending => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
            self::Quoted => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400',
            self::Declined => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400',
        };
    }
}