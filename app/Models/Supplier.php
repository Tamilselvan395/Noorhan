<?php

namespace App\Models;

use App\Enums\CustomerStatus;
use App\Enums\Division;
use App\Traits\HasActivityLog;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory, HasActivityLog, HasAuditLog, SoftDeletes;

    public array $auditExclude = ['password', 'remember_token'];

    protected $fillable = [
        'name', 'contact_person', 'email', 'phone', 'whatsapp', 'website',
        'country', 'city', 'address', 'division', 'status', 'payment_terms',
        'currency', 'owner_id', 'notes',
    ];

    /* ---- Relations ---- */

    public function contacts(): HasMany
    {
        return $this->hasMany(SupplierContact::class);
    }

    public function priceLists(): HasMany
    {
        return $this->hasMany(SupplierPriceList::class);
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(SupplierRating::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function enquiries(): HasMany
    {
        return $this->hasMany(SupplierEnquiry::class);
    }

    /* ---- Accessors ---- */

    public function division(): Division
    {
        return Division::from($this->division);
    }

    public function status(): CustomerStatus
    {
        return CustomerStatus::from($this->status);
    }

    /* ---- Scopes ---- */

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $query->when($term, fn (Builder $q) => $q->where(function (Builder $q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('contact_person', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('country', 'like', "%{$term}%");
        }));
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', CustomerStatus::Active->value);
    }

    /* ---- Performance ---- */

    /** Average of the four rating dimensions across all ratings (0–5). */
    public function overallRating(): ?float
    {
        $ratings = $this->ratings()->get();

        if ($ratings->isEmpty()) {
            return null;
        }

        $score = $ratings->avg(fn (SupplierRating $r) => ($r->quality + $r->price + $r->delivery + $r->service) / 4);

        return round($score, 1);
    }

    public function ratingBreakdown(): array
    {
        $ratings = $this->ratings()->get();

        if ($ratings->isEmpty()) {
            return ['quality' => null, 'price' => null, 'delivery' => null, 'service' => null];
        }

        return [
            'quality' => round($ratings->avg('quality'), 1),
            'price' => round($ratings->avg('price'), 1),
            'delivery' => round($ratings->avg('delivery'), 1),
            'service' => round($ratings->avg('service'), 1),
        ];
    }

    public function averageLeadTime(): ?float
    {
        $avg = $this->priceLists()->whereNotNull('lead_time_days')->avg('lead_time_days');

        return $avg !== null ? round($avg, 1) : null;
    }
}