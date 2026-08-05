<?php

namespace App\Listeners\Routing;

use App\Actions\Routing\RouteLeadAction;
use App\Events\Leads\LeadCreated;

class RouteNewLead
{
    public function __construct(private RouteLeadAction $route) {}

    public function handle(LeadCreated $event): void
    {
        if (! config('noorhan.routing.auto_route_on_create', true)) {
            return;
        }

        if ($event->lead->assigned_to !== null) {
            return; // already manually assigned at creation
        }

        $this->route->execute($event->lead);
    }
}