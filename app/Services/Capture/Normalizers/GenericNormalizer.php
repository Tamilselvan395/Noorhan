<?php

namespace App\Services\Capture\Normalizers;

use App\Contracts\CaptureNormalizerInterface;
use App\DTOs\Capture\LeadCaptureDTO;
use App\Enums\LeadSource;
use InvalidArgumentException;

class GenericNormalizer implements CaptureNormalizerInterface
{
    public static function source(): LeadSource
    {
        return LeadSource::Manual;
    }

    public function normalize(array $payload): LeadCaptureDTO
    {
        $name = trim($payload['name'] ?? $payload['full_name'] ?? '');

        if ($name === '') {
            throw new InvalidArgumentException('Generic payload is missing a contact name.');
        }

        return new LeadCaptureDTO(
            name: $name,
            source: LeadSource::from($payload['source'] ?? 'manual'),
            email: $payload['email'] ?? null,
            phone: $payload['phone'] ?? null,
            company_name: $payload['company_name'] ?? $payload['company'] ?? null,
            division: $payload['division'] ?? 'automotive',
            subject: $payload['subject'] ?? null,
            requirements: $payload['requirements'] ?? $payload['message'] ?? null,
            vehicle_brand_category: $payload['vehicle_brand_category'] ?: null,
            customer_type: $payload['customer_type'] ?: null,
            needs_triage: ($payload['vehicle_brand_category'] ?? null) === null,
        );
    }
}
