<?php

namespace App\Enums;

enum QuotationStatus: string
{
    case Draft = 'draft';
    case PendingApproval = 'pending_approval';
    case Approved = 'approved';
    case Sent = 'sent';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case Expired = 'expired';
    case Converted = 'converted';

    public function label(): string
    {
        return ucwords(str_replace('_', ' ', $this->value));
    }

    public function badge(): string
    {
        return match ($this) {
            self::Draft => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
            self::PendingApproval => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400',
            self::Approved => 'bg-violet-100 text-violet-700 dark:bg-violet-900/40 dark:text-violet-400',
            self::Sent => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400',
            self::Accepted => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400',
            self::Rejected => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400',
            self::Expired => 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400',
            self::Converted => 'bg-cyan-100 text-cyan-700 dark:bg-cyan-900/40 dark:text-cyan-400',
        };
    }
}