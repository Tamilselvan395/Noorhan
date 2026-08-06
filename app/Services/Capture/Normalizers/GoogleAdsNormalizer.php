<?php

namespace App\Services\Capture\Normalizers;

use App\Contracts\CaptureNormalizerInterface;
use App\DTOs\Capture\LeadCaptureDTO;
use App\Enums\LeadSource;
use InvalidArgumentException;

class GoogleAdsNormalizer implements CaptureNormalizerInterface
{
    public static function source(): LeadSource
    {
        return LeadSource::GoogleAds;
    }

    public function normalize(array $payload): LeadCaptureDTO
    {
        $lead = $payload['leadData'] ?? $payload;

        $name = trim($lead['name'] ?? $lead['full_name'] ?? '');

        if ($name === '') {
            throw new InvalidArgumentException('Google Ads payload is missing a contact name.');
        }

        return new LeadCaptureDTO(
            name: $name,
            source: self::source(),
            email: $lead['email'] ?? null,
            phone: $lead['phone'] ?? null,
            subject: 'Google Ads lead',
            requirements: $lead['message'] ?? $lead['comment'] ?? null,
            utm_source: 'google',
            utm_medium: 'cpc',
            utm_campaign: $lead['campaign_name'] ?? null,
            landing_url: isset($lead['gclid']) ? 'google://gclid/'.$lead['gclid'] : null,
        );
    }
}
