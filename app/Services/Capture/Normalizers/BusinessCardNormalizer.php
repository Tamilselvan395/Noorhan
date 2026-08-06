<?php

namespace App\Services\Capture\Normalizers;

use App\Contracts\CaptureNormalizerInterface;
use App\DTOs\Capture\LeadCaptureDTO;
use App\Enums\LeadSource;

class BusinessCardNormalizer implements CaptureNormalizerInterface
{
    public static function source(): LeadSource
    {
        return LeadSource::BusinessCard;
    }

    public function normalize(array $payload): LeadCaptureDTO
    {
        return new LeadCaptureDTO(
            name: $payload['name'] ?? 'Business Card Scan · '.now()->format('M d, h:i A'),
            source: self::source(),
            subject: 'Scanned business card',
            requirements: $payload['note'] ?? null,
            business_card_path: $payload['path'] ?? null,
            needs_triage: true, // OCR extraction arrives with the AI Engine
        );
    }
}
