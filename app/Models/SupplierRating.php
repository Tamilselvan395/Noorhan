<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierRating extends Model
{
    protected $guarded = [];

    protected $casts = [
        'quality' => 'int', 'price' => 'int', 'delivery' => 'int', 'service' => 'int',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function overall(): float
    {
        return round(($this->quality + $this->price + $this->delivery + $this->service) / 4, 1);
    }
}