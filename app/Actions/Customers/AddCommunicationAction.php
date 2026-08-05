<?php

namespace App\Actions\Customers;

use App\Enums\CommunicationChannel;
use App\Enums\CommunicationDirection;
use App\Models\Communication;
use App\Models\Customer;
use App\Models\User;

class AddCommunicationAction
{
    public function execute(
        \App\Models\Customer|\App\Models\Company $entity,
        CommunicationChannel $channel,
        CommunicationDirection $direction,
        ?string $subject,
        ?string $body,
        ?User $user = null,
        array $metadata = [],
    ): Communication {
        $communication = $entity->communications()->create([
            'channel' => $channel->value,
            'direction' => $direction->value,
            'subject' => $subject,
            'body' => $body,
            'user_id' => $user?->id,
            'occurred_at' => now(),
            'metadata' => $metadata ?: null,
        ]);

        $entity->update(['last_activity_at' => now()]);
        $entity->logActivity("logged a {$channel->label()} ({$direction->value})");
        

        return $communication;
    }
}