<?php

namespace App\Actions\Capture;

use App\Actions\Leads\CreateLeadAction;
use App\DTOs\Capture\LeadCaptureDTO;
use App\DTOs\Leads\LeadDTO;
use App\Models\Lead;

class CaptureLeadAction
{
    public function __construct(private CreateLeadAction $create) {}

    public function execute(LeadCaptureDTO $dto): Lead
    {
        $lead = $this->create->execute(new LeadDTO(
            name: $dto->name,
            division: $dto->division,
            source: $dto->source->value,
            company_name: $dto->company_name,
            email: $dto->email,
            phone: $dto->phone,
            customer_type: $dto->customer_type,
            vehicle_brand_category: $dto->vehicle_brand_category,
            subject: $dto->subject,
            requirements: $dto->requirements,
        ), null);

        $lead->update([
            'utm_source' => $dto->utm_source,
            'utm_medium' => $dto->utm_medium,
            'utm_campaign' => $dto->utm_campaign,
            'landing_url' => $dto->landing_url,
            'business_card_path' => $dto->business_card_path,
            'needs_triage' => $dto->needs_triage,
        ]);

        return $lead;
    }
}