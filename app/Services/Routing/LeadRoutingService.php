<?php

namespace App\Services\Routing;

use App\DTOs\Routing\RoutingDecision;
use App\Models\Lead;
use App\Models\LeadRoutingRule;

class LeadRoutingService
{
    /**
     * Evaluate rules by priority for the lead's division.
     * Vehicle brand rules serve Automotive; customer-type rules serve
     * the manufacturing divisions (Distributor / Dealer / Garage Managers).
     */
    public function decide(Lead $lead): RoutingDecision
    {
        $rules = LeadRoutingRule::query()
            ->where('division', $lead->division)
            ->where('is_active', true)
            ->orderByDesc('priority')
            ->get();

        foreach ($rules as $rule) {
            if ($rule->matches($lead)) {
                return new RoutingDecision($rule);
            }
        }

        return new RoutingDecision(null); // → Triage Queue
    }
}