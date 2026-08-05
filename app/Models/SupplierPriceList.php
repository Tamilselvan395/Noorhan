<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierPriceList extends Model
{
    protected $guarded = [];

    protected $casts = ['price' => 'decimal:2', 'valid_until' => 'date'];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeCurrent(Builder $query): Builder
    {
        return $query->where(fn (Builder $q) => $q->whereNull('valid_until')->orWhere('valid_until', '>=', now()->toDateString()));
    }

    public function isExpired(): bool
    {
        return $this->valid_until !== null && $this->valid_until->isPast();
    }
}