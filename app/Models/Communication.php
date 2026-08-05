<?php

namespace App\Models;

use App\Enums\CommunicationChannel;
use App\Enums\CommunicationDirection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Communication extends Model
{
    protected $guarded = [];

    protected $casts = ['metadata' => 'array', 'occurred_at' => 'datetime'];

    public function communicable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function channel(): CommunicationChannel
    {
        return CommunicationChannel::from($this->channel);
    }

    public function direction(): CommunicationDirection
    {
        return CommunicationDirection::from($this->direction);
    }
}