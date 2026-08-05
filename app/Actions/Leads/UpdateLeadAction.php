<?php

namespace App\Actions\Leads;

use App\DTOs\Leads\LeadDTO;
use App\Models\Lead;

class UpdateLeadAction
{
    public function __construct(private CreateLeadAction $create) {}

    public function execute(Lead $lead, LeadDTO $dto): void
    {
        $data = $dto->toArray();
        unset($data['assigned_to']); // assignment flows through AssignLeadAction

        $lead->update(array_merge($data, [
            'needs_triage' => $this->create->needsTriage($dto),
        ]));

        $lead->logActivity('updated the lead');
    }
}