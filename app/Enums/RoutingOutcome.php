<?php

namespace App\Enums;

enum RoutingOutcome: string
{
    case RuleMatch       = 'rule_match';
    case AiRecommendation = 'ai_recommendation';
    case Triage          = 'triage';
    case Manual          = 'manual';

    public function label(): string
    {
        return ucwords(str_replace('_', ' ', $this->value));
    }
}