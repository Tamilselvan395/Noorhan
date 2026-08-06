<?php

namespace App\Services\Capture\Normalizers;

use App\Contracts\CaptureNormalizerInterface;
use App\DTOs\Capture\LeadCaptureDTO;
use App\Enums\LeadSource;
use InvalidArgumentException;

class WhatsAppNormalizer implements CaptureNormalizerInterface
{
    public static function source(): LeadSource
    {
        return LeadSource::WhatsApp;
    }

    public function normalize(array $payload): LeadCaptureDTO
    {
        $value = $payload['entry'][0]['changes'][0]['value'] ?? [];
        $message = $value['messages'][0] ?? null;

        if (! $message) {
            throw new InvalidArgumentException('WhatsApp payload contains no inbound message.');
        }

        $body = $message['text']['body']
            ?? $message['button']['text']
            ?? '[Non-text message received]';

        return new LeadCaptureDTO(
            name: $value['contacts'][0]['profile']['name'] ?? 'WhatsApp Enquiry',
            source: self::source(),
            phone: isset($message['from']) ? '+'.$message['from'] : null,
            subject: 'WhatsApp enquiry',
            requirements: "WhatsApp message:\n{$body}",
            utm_source: 'whatsapp',
            utm_medium: 'chat',
            needs_triage: true, // AI Engine classifies free-text later
        );
    }
}
