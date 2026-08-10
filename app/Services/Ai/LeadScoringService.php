<?php

namespace App\Services\Ai;

use App\Models\Lead;

class LeadScoringService
{
    /** 0–100 propensity score from CRM signals. */
    public function score(Lead $lead): int
    {
        $score = 20;
        $value = (float) ($lead->estimated_value ?? 0);

        $score += match (true) {
            $value > 10000 => 25,
            $value > 5000 => 15,
            $value > 1000 => 10,
            $value > 0 => 5,
            default => 0,
        };

        $score += match ($lead->source) {
            'networking', 'exhibition', 'international_travel' => 15,
            'whatsapp', 'website' => 10,
            'facebook_ads', 'instagram_ads', 'google_ads' => 8,
            default => 5,
        };

        if ($lead->email) $score += 5;
        if ($lead->phone) $score += 5;
        if ($lead->company_name) $score += 5;
        if ($lead->vehicle_brand_category && $lead->vehicle_brand_category !== 'unknown') $score += 10;
        if ($lead->customer_type) $score += 5;

        $activities = $lead->activities()->count();
        $score += $activities >= 3 ? 10 : ($activities >= 1 ? 5 : 0);

        if ($lead->needs_triage) $score -= 10;

        return max(0, min(100, $score));
    }
}