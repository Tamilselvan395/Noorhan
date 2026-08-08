<?php

namespace App\Enums;

enum MarketingChannel: string
{
    case WhatsApp = 'whatsapp';
    case Email = 'email';
    case Social = 'social';
    case Ads = 'ads';
    case Exhibition = 'exhibition';
    case Sms = 'sms';

    public function label(): string
    {
        return match ($this) {
            self::WhatsApp => 'WhatsApp',
            self::Email => 'Email',
            self::Social => 'Social Media',
            self::Ads => 'Paid Ads',
            self::Exhibition => 'Exhibition',
            self::Sms => 'SMS',
        };
    }
}