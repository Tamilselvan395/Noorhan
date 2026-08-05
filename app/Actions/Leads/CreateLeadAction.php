<?php

namespace App\Actions\Leads;

use App\DTOs\Leads\LeadDTO;
use App\Enums\Division;
use App\Enums\LeadStatus;
use App\Enums\VehicleBrandCategory;
use App\Events\Leads\LeadCreated;
use App\Models\Lead;
use App\Models\User;

class CreateLeadAction
{
    public function __construct(private AssignLeadAction $assign) {}

    public function execute(LeadDTO $dto, ?User $creator = null): Lead
    {
        $lead = Lead::create(array_merge($dto->toArray(), [
            'name' => $dto->name,
            'status' => LeadStatus::New->value,
            'needs_triage' => $this->needsTriage($dto),
            'created_by' => $creator?->id,
            'assigned_to' => null,
        ]));

        $lead->logActivity('created the lead');

        event(new LeadCreated($lead));

        if ($dto->assigned_to) {
            $this->assign->execute($lead, User::find($dto->assigned_to), $creator ?? User::first());
        }

        return $lead;
    }

    /** Unknown enquiries route to the Triage Queue (auto-routing refines this in Module 6). */
    public function needsTriage(LeadDTO $dto): bool
    {
        if ($dto->division === Division::Automotive->value) {
            return in_array($dto->vehicle_brand_category, [null, VehicleBrandCategory::Unknown->value], true);
        }

        return $dto->customer_type === null;
    }
}