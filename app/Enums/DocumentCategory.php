<?php

namespace App\Enums;

enum DocumentCategory: string
{
    case Contract = 'contract';
    case License = 'license';
    case Insurance = 'insurance';
    case InvoiceScan = 'invoice_scan';
    case Drawing = 'drawing';
    case Identity = 'identity';
    case Other = 'other';

    public function label(): string
    {
        return ucwords(str_replace('_', ' ', $this->value));
    }

    public function badge(): string
    {
        return match ($this) {
            self::Contract => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400',
            self::License => 'bg-violet-100 text-violet-700 dark:bg-violet-900/40 dark:text-violet-400',
            self::Insurance => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400',
            self::InvoiceScan => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400',
            default => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
        };
    }
}