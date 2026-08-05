<?php

namespace App\Enums;

enum Division: string
{
    case Automotive = 'automotive';
    case Swiftec    = 'swiftec';
    case Wiperex    = 'wiperex';
    case Otozaar    = 'otozaar';

    public function label(): string
    {
        return match ($this) {
            self::Automotive => 'Automotive Parts',
            self::Swiftec    => 'Swiftec Lubricants',
            self::Wiperex    => 'Wiperex',
            self::Otozaar    => 'Otozaar Service',
        };
    }
}