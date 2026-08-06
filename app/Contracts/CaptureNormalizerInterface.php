<?php

namespace App\Contracts;

use App\DTOs\Capture\LeadCaptureDTO;
use App\Enums\LeadSource;

interface CaptureNormalizerInterface
{
    public static function source(): LeadSource;

    /** Transform a raw channel payload into a normalized capture DTO. */
    public function normalize(array $payload): LeadCaptureDTO;
}
