<?php

namespace App\Events\Leads;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

class LeadAssigned
{
    use Dispatchable;

    public function __construct(public Lead $lead, public ?User $assignee, public User $assignedBy) {}
}