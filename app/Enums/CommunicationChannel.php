<?php

namespace App\Enums;

enum CommunicationChannel: string
{
    case Email = 'email';
    case Phone = 'phone';
    case WhatsApp = 'whatsapp';
    case Meeting = 'meeting';
    case Sms = 'sms';

    public function label(): string
    {
        return match ($this) {
            self::Email => 'Email',
            self::Phone => 'Phone Call',
            self::WhatsApp => 'WhatsApp',
            self::Meeting => 'Meeting',
            self::Sms => 'SMS',
        };
    }
}