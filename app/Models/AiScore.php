<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AiScore extends Model
{
    protected $guarded = [];

    protected $casts = ['metadata' => 'array', 'score' => 'decimal:2', 'computed_at' => 'datetime'];

    public function scoreable(): MorphTo
    {
        return $this->morphTo();
    }
}