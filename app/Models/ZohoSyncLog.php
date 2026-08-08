<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ZohoSyncLog extends Model
{
    protected $guarded = [];

    protected $casts = ['payload' => 'array', 'last_attempted_at' => 'datetime'];

    public function syncable(): MorphTo
    {
        return $this->morphTo();
    }
}