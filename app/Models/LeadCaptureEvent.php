<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadCaptureEvent extends Model
{
    protected $guarded = [];

    protected $casts = ['payload' => 'array'];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function isProcessed(): bool
    {
        return $this->status === 'processed';
    }
}