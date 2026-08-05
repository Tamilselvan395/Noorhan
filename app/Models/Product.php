<?php

namespace App\Models;

use App\Enums\Division;
use App\Enums\ProductUnit;
use App\Traits\HasActivityLog;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, HasActivityLog, HasAuditLog, SoftDeletes;

    public array $auditExclude = ['password', 'remember_token'];

    protected $fillable = [
        'sku', 'name', 'category_id', 'division', 'brand', 'description', 'unit',
        'cost_price', 'sale_price', 'tax_rate', 'attributes', 'min_stock',
        'image_path', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'cost_price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'attributes' => 'array',
            'is_active' => 'bool',
        ];
    }

    /* ---- Relations ---- */

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    /* ---- Accessors ---- */

    public function division(): Division
    {
        return Division::from($this->division);
    }

    public function unit(): ProductUnit
    {
        return ProductUnit::from($this->unit);
    }

    /* ---- Scopes ---- */

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $query->when($term, fn (Builder $q) => $q->where(function (Builder $q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('sku', 'like', "%{$term}%")
                ->orWhere('brand', 'like', "%{$term}%");
        }));
    }

    public function scopeDivision(Builder $query, Division $division): Builder
    {
        return $query->where('division', $division->value);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /* ---- Pricing ---- */

    public function margin(): ?float
    {
        $cost = (float) $this->cost_price;

        if ($cost <= 0) {
            return null;
        }

        return round((((float) $this->sale_price) - $cost) / $cost * 100, 1);
    }

    public function priceWithTax(): float
    {
        return round(((float) $this->sale_price) * (1 + ((float) $this->tax_rate) / 100), 2);
    }

    public function marginBadge(): string
    {
        $margin = $this->margin();

        return match (true) {
            $margin === null => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
            $margin < 10 => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400',
            $margin < 25 => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400',
            default => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400',
        };
    }
}