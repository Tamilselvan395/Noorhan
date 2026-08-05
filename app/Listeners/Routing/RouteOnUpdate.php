<?php

namespace App\Listeners\Routing;

use App\Actions\Routing\RouteLeadAction;
use App\Events\Leads\LeadUpdated;

class RouteOnUpdate
{
    public function __construct(private RouteLeadAction $route) {}

    public function handle(LeadUpdated $event): void
    {
        if ($event->lead->assigned_to !== null || ! $event->lead->isOpen()) {
            return;
        }

        $this->route->execute($event->lead);
    }
}