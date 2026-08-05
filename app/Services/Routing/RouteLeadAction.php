<?php

namespace App\Actions\Routing;

use App\Contracts\LeadClassifierInterface;
use App\DTOs\Routing\RoutingDecision;
use App\Enums\RoutingOutcome;
use App\Events\Leads\LeadRouted;
use App\Actions\Leads\AssignLeadAction;
use App\Models\Lead;
use App\Models\LeadRoutingLog;
use App\Services\Routing\LeadRoutingService;

class RouteLeadAction
{
    public function __construct(
        private LeadRoutingService $routing,
        private LeadClassifierInterface $classifier,
        private AssignLeadAction $assign,
    ) {}

    public function execute(Lead $lead, bool $applyAi = true, bool $applyDivision = false): RoutingDecision
    {
        $classification = null;

        // 1 — AI classification of free-text (recommendation, human-reviewable in Triage)
        if ($applyAi) {
            $result = $this->classifier->classify($lead);

            if ($result->confidence >= (float) config('noorhan.routing.classifier_threshold', 0.5)) {
                $fill = [];

                if ($lead->vehicle_brand_category === null && $result->vehicle_brand_category) {
                    $fill['vehicle_brand_category'] = $result->vehicle_brand_category;
                }
                if ($lead->customer_type === null && $result->customer_type) {
                    $fill['customer_type'] = $result->customer_type;
                }
                if ($applyDivision && $result->division) {
                    $fill['division'] = $result->division;
                }

                if ($fill !== []) {
                    $lead->update($fill);
                }

                $classification = $result;
            }
        }

        // 2 — Rule evaluation
        $decision = $this->routing->decide($lead);

        $outcome = RoutingOutcome::Triage;

        if (! $decision->isTriage()) {
            $this->assign->execute($lead, $decision->rule->user, null);

            $outcome = $classification?->hasSuggestions()
                ? RoutingOutcome::AiRecommendation
                : RoutingOutcome::RuleMatch;
        } else {
            $lead->update(['needs_triage' => true]);
        }

        // 3 — Immutable decision log (feeds the AI Accuracy report later)
        LeadRoutingLog::create([
            'lead_id' => $lead->id,
            'lead_routing_rule_id' => $decision->rule?->id,
            'assigned_to' => $decision->rule?->user_id,
            'outcome' => $outcome->value,
            'classification' => $classification?->toArray(),
        ]);

        event(new LeadRouted($lead, $decision->rule?->user, $outcome));

        return $decision;
    }
}