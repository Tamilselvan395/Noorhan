<?php

namespace App\Enums;

enum LeadSource: string
{
    case Website           = 'website';
    case WhatsApp          = 'whatsapp';
    case FacebookAds       = 'facebook_ads';
    case InstagramAds      = 'instagram_ads';
    case GoogleAds         = 'google_ads';
    case WalkIn            = 'walk_in';
    case Exhibition        = 'exhibition';
    case Networking        = 'networking';
    case InternationalTravel = 'international_travel';
    case Manual            = 'manual';
    case BusinessCard      = 'business_card';

    public function label(): string
    {
        return ucwords(str_replace('_', ' ', $this->value));
    }
}