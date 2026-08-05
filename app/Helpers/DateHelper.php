<?php

namespace App\Helpers;

use Carbon\Carbon;

class DateHelper
{
    public static function format(?Carbon $date, string $format = 'M d, Y'): ?string
    {
        return $date?->format($format);
    }

    public static function formatWithTime(?Carbon $date): ?string
    {
        return $date?->format('M d, Y h:i A');
    }
    
    public static function toDatabase(string $date, string $format = 'Y-m-d'): ?string
    {
        try {
            return Carbon::createFromFormat($format, $date)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}