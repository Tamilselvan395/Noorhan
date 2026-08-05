<?php

namespace App\Actions\Leads;

use App\Events\Leads\LeadAssigned;
use App\Models\Lead;
use App\Models\User;
use App\Notifications\LeadAssignedNotification;

class AssignLeadAction
{
    public function execute(Lead $lead, ?User $assignee, User $assignedBy): void
    {
        $lead->update(['assigned_to' => $assignee?->id, 'needs_triage' => $assignee ? false : $lead->needs_triage]);

        $lead->logActivity($assignee ? "assigned the lead to {$assignee->name}" : 'unassigned the lead');

        if ($assignee && $assignee->id !== $assignedBy->id) {
            $assignee->notify(new LeadAssignedNotification($lead));
        }

        event(new LeadAssigned($lead, $assignee, $assignedBy));
    }
}