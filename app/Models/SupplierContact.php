<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierContact extends Model
{
    protected $guarded = [];

    protected $casts = ['is_primary' => 'bool'];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}