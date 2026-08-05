<?php

namespace App\Models;

use App\Enums\Division;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'division', 'description', 'is_active', 'sort'];

    protected $casts = ['is_active' => 'bool'];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    public function division(): Division
    {
        return Division::from($this->division);
    }

    public function scopeDivision(Builder $query, Division $division): Builder
    {
        return $query->where('division', $division->value);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}