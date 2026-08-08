<?php

namespace App\Models;

use App\Enums\SupplierEnquiryStatus;
use App\Traits\HasActivityLog;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplierEnquiry extends Model
{
    use HasFactory, HasActivityLog, HasAuditLog, SoftDeletes;

    public array $auditExclude = ['password', 'remember_token'];

    protected $fillable = [
        'reference', 'supplier_id', 'lead_id', 'customer_id', 'status',
        'sent_via', 'sent_at', 'responded_at', 'closed_at', 'notes', 'created_by',
    ];

    protected function casts(): array
    {
        return ['sent_at' => 'datetime', 'responded_at' => 'datetime', 'closed_at' => 'datetime'];
    }

    /* ---- Relations ---- */

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SupplierEnquiryItem::class, 'supplier_enquiry_id');
    }

    /* ---- Accessors & scopes ---- */

    public function status(): SupplierEnquiryStatus
    {
        return SupplierEnquiryStatus::from($this->status);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', [
            SupplierEnquiryStatus::Draft->value,
            SupplierEnquiryStatus::Sent->value,
            SupplierEnquiryStatus::Partial->value,
        ]);
    }

    /* ---- Metrics ---- */

    /** Hours between send and first supplier response. */
    public function responseTimeHours(): ?float
    {
        if (! $this->sent_at || ! $this->responded_at) {
            return null;
        }

        return round($this->sent_at->diffInMinutes($this->responded_at) / 60, 1);
    }
}