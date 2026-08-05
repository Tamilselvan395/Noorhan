<?php

namespace App\Events\Leads;

use App\Enums\RoutingOutcome;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

class LeadRouted
{
    use Dispatchable;

    public function __construct(public Lead $lead, public ?User $assignee, public RoutingOutcome $outcome) {}
}