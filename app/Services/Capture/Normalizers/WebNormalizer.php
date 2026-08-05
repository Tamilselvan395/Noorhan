<?php

namespace App\Services\Capture\Normalizers;

use App\Contracts\CaptureNormalizerInterface;
use App\DTOs\Capture\LeadCaptureDTO;
use App\Enums\LeadSource;
use InvalidArgumentException;

class WebNormalizer implements CaptureNormalizerInterface
{
    public function source(): LeadSource
    {
        return LeadSource::Website;
    }

    public function normalize(array $payload): LeadCaptureDTO
    {
        $name = trim($payload['name'] ?? '');

        if ($name === '') {
            throw new InvalidArgumentException('Web capture payload is missing a contact name.');
        }

        return new LeadCaptureDTO(
            name: $name,
            source: $this->source(),
            email: $payload['email'] ?? null,
            phone: $payload['phone'] ?? null,
            company_name: $payload['company_name'] ?? null,
            division: $payload['division'] ?? 'automotive',
            subject: $payload['subject'] ?? 'Website enquiry',
            requirements: $payload['message'] ?? null,
            vehicle_brand_category: $payload['vehicle_brand_category'] ?: null,
            utm_source: $payload['utm_source'] ?? null,
            utm_medium: $payload['utm_medium'] ?? null,
            utm_campaign: $payload['utm_campaign'] ?? null,
            landing_url: $payload['landing_url'] ?? null,
            needs_triage: ($payload['vehicle_brand_category'] ?? null) === null,
        );
    }
}