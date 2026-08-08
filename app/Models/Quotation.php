<?php

namespace App\Models;

use App\Concerns\HasCommercialTotals;
use App\Enums\QuotationStatus;
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

class Quotation extends Model
{
    use HasFactory, HasActivityLog, HasAuditLog, SoftDeletes, HasCommercialTotals;

    public array $auditExclude = ['password', 'remember_token'];

    protected $fillable = [
        'reference', 'lead_id', 'customer_id', 'division', 'status', 'version', 'parent_id', 'converted_order_id',
        'currency', 'discount_type', 'discount_value', 'tax_rate', 'subtotal', 'discount_amount',
        'tax_amount', 'total', 'total_cost', 'margin_percent', 'requires_approval',
        'approval_notes', 'approved_by', 'approved_at', 'sent_via', 'sent_at', 'accepted_at',
        'rejected_at', 'rejected_reason', 'valid_until', 'notes', 'terms', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'discount_value' => 'decimal:2', 'tax_rate' => 'decimal:2',
            'subtotal' => 'decimal:2', 'discount_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2', 'total' => 'decimal:2', 'total_cost' => 'decimal:2',
            'margin_percent' => 'decimal:2', 'requires_approval' => 'bool',
            'valid_until' => 'date', 'approved_at' => 'datetime', 'sent_at' => 'datetime',
            'accepted_at' => 'datetime', 'rejected_at' => 'datetime',
        ];
    }

    /* ---- Relations ---- */

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class)->orderBy('sort');
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

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Quotation::class, 'parent_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(Quotation::class, 'parent_id');
    }

    public function convertedOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class, 'converted_order_id');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    /* ---- Accessors ---- */

    public function status(): QuotationStatus
    {
        return QuotationStatus::from($this->status);
    }

    public function division(): Division
    {
        return Division::from($this->division);
    }

    public function isExpired(): bool
    {
        return $this->valid_until !== null
            && $this->valid_until->isPast()
            && in_array($this->status(), [QuotationStatus::Sent, QuotationStatus::Approved], true);
    }

    /** Below minimum margin or heavy discount → management approval required. */
    public function computeRequiresApproval(): bool
    {
        return $this->margin_percent < (float) config('noorhan.quotation.min_margin', 10)
            || ($this->discount_type === 'percent' && (float) $this->discount_value > (float) config('noorhan.quotation.max_discount', 5));
    }

    /* ---- Scopes ---- */

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', [
            QuotationStatus::Draft->value, QuotationStatus::PendingApproval->value,
            QuotationStatus::Approved->value, QuotationStatus::Sent->value,
        ]);
    }
}