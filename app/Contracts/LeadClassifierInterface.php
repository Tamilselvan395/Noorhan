<?php

namespace App\Contracts;

use App\DTOs\Routing\ClassificationResult;
use App\Models\Lead;

interface LeadClassifierInterface
{
    /** Classify free-text enquiries; recommend routing attributes. */
    public function classify(Lead $lead): ClassificationResult;
}