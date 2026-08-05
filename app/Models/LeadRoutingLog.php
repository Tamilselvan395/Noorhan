<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadRoutingLog extends Model
{
    protected $guarded = [];

    protected $casts = ['classification' => 'array'];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(LeadRoutingRule::class, 'lead_routing_rule_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}