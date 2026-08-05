<?php

namespace App\Events\Leads;

use App\Enums\LeadStatus;
use App\Models\Lead;
use Illuminate\Foundation\Events\Dispatchable;

class LeadStageChanged
{
    use Dispatchable;

    public function __construct(public Lead $lead, public LeadStatus $from, public LeadStatus $to) {}
}