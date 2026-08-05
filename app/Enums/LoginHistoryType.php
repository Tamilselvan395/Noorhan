<?php

namespace App\Enums;

enum LoginHistoryType: string
{
    case Login          = 'login';
    case Logout         = 'logout';
    case FailedLogin    = 'failed_login';
    case SessionRevoked = 'session_revoked';

    public function label(): string
    {
        return ucwords(str_replace('_', ' ', $this->value));
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Login          => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400',
            self::Logout         => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
            self::FailedLogin    => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400',
            self::SessionRevoked => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400',
        };
    }
}