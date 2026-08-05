<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadRoutingRule extends Model
{
    protected $guarded = [];

    protected $casts = ['is_active' => 'bool', 'priority' => 'int'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function matches(Lead $lead): bool
    {
        return match ($this->condition_type) {
            'vehicle_brand' => $lead->vehicle_brand_category === $this->condition_value,
            'customer_type' => $lead->customer_type === $this->condition_value,
            'default'       => true,
            default         => false,
        };
    }

    public function describe(): string
    {
        return match ($this->condition_type) {
            'vehicle_brand' => 'Vehicle: '.ucfirst((string) $this->condition_value),
            'customer_type' => 'Type: '.ucfirst(str_replace('_', ' ', (string) $this->condition_value)),
            default         => 'Default / fallback',
        };
    }
}