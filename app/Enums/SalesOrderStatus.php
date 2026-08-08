<?php

namespace App\Enums;

enum SalesOrderStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Processing = 'processing';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function badge(): string
    {
        return match ($this) {
            self::Pending => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
            self::Confirmed => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400',
            self::Processing => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400',
            self::Delivered => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400',
            self::Cancelled => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400',
        };
    }
}