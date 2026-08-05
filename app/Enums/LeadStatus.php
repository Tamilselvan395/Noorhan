<?php

namespace App\Enums;

enum LeadStatus: string
{
    case New        = 'new';
    case Contacted  = 'contacted';
    case Qualified  = 'qualified';
    case Quoted     = 'quoted';
    case Negotiation = 'negotiation';
    case Won        = 'won';
    case Lost       = 'lost';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::Contacted => 'Contacted',
            self::Qualified => 'Qualified',
            self::Quoted => 'Quoted',
            self::Negotiation => 'Negotiation',
            self::Won => 'Won',
            self::Lost => 'Lost',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::New => 'bg-blue-500',
            self::Contacted => 'bg-cyan-500',
            self::Qualified => 'bg-violet-500',
            self::Quoted => 'bg-amber-500',
            self::Negotiation => 'bg-orange-500',
            self::Won => 'bg-green-500',
            self::Lost => 'bg-red-500',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::New => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400',
            self::Contacted => 'bg-cyan-100 text-cyan-700 dark:bg-cyan-900/40 dark:text-cyan-400',
            self::Qualified => 'bg-violet-100 text-violet-700 dark:bg-violet-900/40 dark:text-violet-400',
            self::Quoted => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400',
            self::Negotiation => 'bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-400',
            self::Won => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400',
            self::Lost => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400',
        };
    }

    public function isClosed(): bool
    {
        return in_array($this, [self::Won, self::Lost], true);
    }
}