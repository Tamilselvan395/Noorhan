<?php

namespace App\Actions\Leads;

use App\DTOs\Leads\LeadDTO;
use App\Events\Leads\LeadUpdated;
use App\Models\Lead;

class UpdateLeadAction
{
    public function __construct(private CreateLeadAction $create)
    {
    }

    public function execute(Lead $lead, LeadDTO $dto): void
    {
        $data = $dto->toArray();

        // Assignment flows through AssignLeadAction
        unset($data['assigned_to']);

        $lead->update(array_merge($data, [
            'needs_triage' => $this->create->needsTriage($dto),
        ]));

        // 🔥 ENTERPRISE OPTIMIZATION: Only log and route if data actually changed
        if ($lead->wasChanged()) {
            $lead->logActivity('updated the lead');
            
            event(new LeadUpdated($lead));
        }
    }

    
}