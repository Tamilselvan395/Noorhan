<?php

namespace App\Models;

use App\Enums\CustomerStatus;
use App\Enums\CustomerType;
use App\Enums\Division;
use App\Traits\HasActivityLog;
use App\Traits\HasAuditLog;
use App\Models\SalesOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory, HasActivityLog, HasAuditLog, SoftDeletes;

    public array $auditExclude = ['password', 'remember_token'];

    protected $fillable = [
        'name','zoho_id', 'company_name', 'email', 'phone', 'whatsapp','whatsapp_opted_out', 'whatsapp_last_messaged_at', 'type', 'status',
        'address', 'city', 'country', 'division', 'vehicle_brand_category',
        'company_id', 'lead_id', 'owner_id', 'credit_limit','credit_balance', 'outstanding_balance',
        'notes', 'last_activity_at', 'email_opted_out',
    ];

    protected function casts(): array
    {
        return [
            'credit_limit' => 'decimal:2',
            'credit_balance' => 'decimal:2', 
            'outstanding_balance' => 'decimal:2',
            'last_activity_at' => 'datetime',
            'whatsapp_opted_out' => 'bool',
            'whatsapp_last_messaged_at' => 'datetime',
            'email_opted_out' => 'bool',
        ];
    }

    /* ---- Relations ---- */

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function communications(): MorphMany
    {
        return $this->morphMany(Communication::class, 'communicable');
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(SalesOrder::class);
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
                ->orWhere('company_name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%");
        }));
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', CustomerStatus::Active->value);
    }

    /* ---- Helpers ---- */

    public function isBlacklisted(): bool
    {
        return $this->status() === CustomerStatus::Blacklisted;
    }

    public function displayName(): string
    {
        return $this->company_name ? "{$this->name} ({$this->company_name})" : $this->name;
    }
}