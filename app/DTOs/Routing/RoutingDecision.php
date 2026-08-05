<?php

namespace App\DTOs\Routing;

use App\Models\LeadRoutingRule;

readonly class RoutingDecision
{
    public function __construct(public ?LeadRoutingRule $rule) {}

    public function isTriage(): bool
    {
        return $this->rule === null || $this->rule->user_id === null;
    }
}