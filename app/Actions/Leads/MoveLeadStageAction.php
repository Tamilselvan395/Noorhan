<?php

namespace App\Actions\Leads;

use App\Enums\LeadStatus;
use App\Events\Leads\LeadStageChanged;
use App\Models\Lead;
use App\Services\Leads\LeadPipelineService;

class MoveLeadStageAction
{
    public function __construct(private LeadPipelineService $pipeline) {}

    public function execute(Lead $lead, LeadStatus $to, ?string $lostReason = null): void
    {
        $from = $lead->status();

        $this->pipeline->assertTransition($from, $to);

        $lead->fill([
            'status' => $to->value,
            'lost_reason' => $to === LeadStatus::Lost ? ($lostReason ?: 'Not specified') : null,
            'closed_at' => $to->isClosed() ? now() : null,
            'needs_triage' => false,
            'last_contacted_at' => now(),
        ])->save();

        $lead->logActivity("moved the lead from {$from->label()} to {$to->label()}");

        event(new LeadStageChanged($lead, $from, $to));
    }
}