<?php

namespace App\Models;

use App\Concerns\HasCommercialTotals;
use App\Enums\Division;
use App\Enums\InvoiceStatus;
use App\Traits\HasActivityLog;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Document;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Invoice extends Model
{
    use HasFactory, HasActivityLog, HasAuditLog, SoftDeletes, HasCommercialTotals;

    public array $auditExclude = ['password', 'remember_token'];

    protected $fillable = [
        'reference', 'sales_order_id', 'customer_id','zoho_id', 'division', 'status', 'currency',
        'discount_type', 'discount_value', 'tax_rate', 'subtotal', 'discount_amount',
        'tax_amount', 'total', 'total_cost', 'margin_percent', 'issue_date', 'due_date',
        'paid_amount', 'balance_due', 'sent_via', 'sent_at', 'notes', 'terms', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'discount_value' => 'decimal:2', 'tax_rate' => 'decimal:2', 'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2', 'tax_amount' => 'decimal:2', 'total' => 'decimal:2',
            'total_cost' => 'decimal:2', 'margin_percent' => 'decimal:2',
            'paid_amount' => 'decimal:2', 'balance_due' => 'decimal:2',
            'issue_date' => 'date', 'due_date' => 'date', 'sent_at' => 'datetime',
        ];
    }

    /* ---- Relations ---- */

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('sort');
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function payments(): BelongsToMany
    {
        return $this->belongsToMany(Payment::class)->withPivot('allocated_amount')->withTimestamps();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }
    /* ---- Accessors & Scopes ---- */

    public function status(): InvoiceStatus
    {
        return InvoiceStatus::from($this->status);
    }

    public function division(): Division
    {
        return Division::from($this->division);
    }

    public function isOverdue(): bool
    {
        return in_array($this->status(), [InvoiceStatus::Sent, InvoiceStatus::Partial], true)
            && $this->due_date !== null
            && $this->due_date->isPast();
    }

    public function scopeOutstanding(Builder $query): Builder
    {
        return $query->whereIn('status', [
            InvoiceStatus::Sent->value, InvoiceStatus::Partial->value,
        ]);
    }

    /* ---- Balance Engine ---- */

    /** Recalculates paid/balance and auto-transitions status. */
    public function updateBalances(): void
    {
        $balance = round((float) $this->total - (float) $this->paid_amount, 2);
        $status = $this->status;

        if (! in_array($status, [InvoiceStatus::Paid, InvoiceStatus::Cancelled], true)) {
            if ($balance <= 0) {
                $status = InvoiceStatus::Paid->value;
            } elseif ((float) $this->paid_amount > 0) {
                $status = InvoiceStatus::Partial->value;
            }
        }

        $this->update([
            'balance_due' => max($balance, 0),
            'status' => $status,
        ]);
    }
}