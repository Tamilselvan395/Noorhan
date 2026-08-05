<?php

namespace App\Services\Leads;

use App\Enums\LeadStatus;
use App\Models\Lead;
use DomainException;

class LeadPipelineService
{
    /**
     * The single source of truth for allowed stage transitions.
     *
     * @return array<string, array<int, LeadStatus>>
     */
    public function transitions(): array
    {
        return [
            LeadStatus::New->value         => [LeadStatus::Contacted, LeadStatus::Qualified, LeadStatus::Lost],
            LeadStatus::Contacted->value   => [LeadStatus::Qualified, LeadStatus::Lost],
            LeadStatus::Qualified->value   => [LeadStatus::Quoted, LeadStatus::Lost],
            LeadStatus::Quoted->value      => [LeadStatus::Negotiation, LeadStatus::Won, LeadStatus::Lost],
            LeadStatus::Negotiation->value => [LeadStatus::Quoted, LeadStatus::Won, LeadStatus::Lost],
            LeadStatus::Won->value         => [],
            LeadStatus::Lost->value        => [LeadStatus::New], // reopen
        ];
    }

    public function canTransition(LeadStatus $from, LeadStatus $to): bool
    {
        return in_array($to, $this->transitions()[$from->value] ?? [], true);
    }

    public function assertTransition(LeadStatus $from, LeadStatus $to): void
    {
        if (! $this->canTransition($from, $to)) {
            throw new DomainException("Invalid pipeline move: {$from->label()} → {$to->label()}.");
        }
    }

    /** @return array<int, LeadStatus> */
    public function allowedNext(LeadStatus $from): array
    {
        return $this->transitions()[$from->value] ?? [];
    }

    /** Global pipeline KPIs (used by index stats + reports module). */
    public function stats(): array
    {
        $open = Lead::query()->open();
        $won = Lead::query()->status(LeadStatus::Won);
        $lost = Lead::query()->status(LeadStatus::Lost);

        $wonCount = (clone $won)->count();
        $lostCount = (clone $lost)->count();
        $closed = $wonCount + $lostCount;

        return [
            'open_count'      => (clone $open)->count(),
            'pipeline_value'  => (float) (clone $open)->sum('estimated_value'),
            'won_this_month'  => (clone $won)->whereMonth('closed_at', now()->month)->whereYear('closed_at', now()->year)->count(),
            'conversion_rate' => $closed === 0 ? 0.0 : round(($wonCount / $closed) * 100, 1),
            'follow_ups_due'  => Lead::query()->followUpDue()->count(),
            'triage_count'    => Lead::query()->open()->triage()->count(),
        ];
    }
}