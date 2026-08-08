<?php

namespace App\Enums;

enum SupplierEnquiryStatus: string
{
    case Draft = 'draft';
    case Sent = 'sent';
    case Partial = 'partial';
    case Quoted = 'quoted';
    case Closed = 'closed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function badge(): string
    {
        return match ($this) {
            self::Draft => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
            self::Sent => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400',
            self::Partial => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400',
            self::Quoted => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400',
            self::Closed => 'bg-violet-100 text-violet-700 dark:bg-violet-900/40 dark:text-violet-400',
            self::Cancelled => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400',
        };
    }
}