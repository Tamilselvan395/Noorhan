<?php

namespace App\Models;

use App\Enums\CustomerStatus;
use App\Enums\CustomerType;
use App\Enums\Division;
use App\Traits\HasActivityLog;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Document;

class Company extends Model
{
    use HasFactory, HasActivityLog, HasAuditLog, SoftDeletes;

    public array $auditExclude = ['password', 'remember_token'];

    protected $fillable = [
        'name', 'trade_license_no', 'tax_number', 'type', 'status', 'division',
        'email', 'phone', 'website', 'address', 'city', 'country', 'owner_id', 'notes','last_activity_at',
    ];

    protected $casts = [
        'last_activity_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
    /* ---- Relations ---- */

    public function contacts(): HasMany
    {
        return $this->hasMany(Customer::class, 'company_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function communications(): MorphMany
    {
        return $this->morphMany(Communication::class, 'communicable');
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    /* ---- Enum accessors ---- */

    public function type(): CustomerType
    {
        return CustomerType::from($this->type);
    }

    public function status(): CustomerStatus
    {
        return CustomerStatus::from($this->status);
    }

    public function division(): Division
    {
        return Division::from($this->division);
    }

    /* ---- Scopes ---- */

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $query->when($term, fn (Builder $q) => $q->where(function (Builder $q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%")
                ->orWhere('trade_license_no', 'like', "%{$term}%");
        }));
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', CustomerStatus::Active->value);
    }
}