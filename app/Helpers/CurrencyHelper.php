<?php

namespace App\Helpers;

class CurrencyHelper
{
    public static function format(float $amount, string $currency = 'USD', string $locale = 'en_US'): string
    {
        $formatter = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);
        return $formatter->formatCurrency($amount, $currency);
    }

    public static function parse(string $amount): float
    {
        return (float) preg_replace('/[^0-9.]/', '', $amount);
    }
}