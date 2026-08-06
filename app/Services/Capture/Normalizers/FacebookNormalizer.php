<?php

namespace App\Services\Capture\Normalizers;

use App\Contracts\CaptureNormalizerInterface;
use App\DTOs\Capture\LeadCaptureDTO;
use App\Enums\LeadSource;
use Illuminate\Support\Collection;

class FacebookNormalizer implements CaptureNormalizerInterface
{
    public static function source(): LeadSource
    {
        return LeadSource::FacebookAds;
    }

    public function normalize(array $payload): LeadCaptureDTO
    {
        $value = $payload['entry'][0]['changes'][0]['value'] ?? [];

        /** @var Collection $fields */
        $fields = collect($value['field_data'] ?? [])->mapWithKeys(
            fn (array $field) => [$field['name'] => $field['values'][0] ?? null]
        );

        return new LeadCaptureDTO(
            name: $fields->get('full_name') ?? $fields->get('name') ?? 'Facebook Lead',
            source: self::source(),
            email: $fields->get('email'),
            phone: $fields->get('phone_number'),
            company_name: $fields->get('company_name'),
            subject: 'Meta Lead Form '.($value['form_id'] ?? ''),
            requirements: $fields->only(['city_name', 'country', 'job_title'])
                ->filter()->map(fn ($v, $k) => "{$k}: {$v}")->implode("\n") ?: null,
            utm_source: 'facebook',
            utm_medium: 'paid_social',
            utm_campaign: $payload['entry'][0]['changes'][0]['value']['adset_name'] ?? null,
            landing_url: $fields->get('leadgen_import_id') ? 'meta://leadgen/'.$value['leadgen_id'] : null,
        );
    }
}
