<?php

namespace App\Models;

use App\Concerns\HasCommercialTotals;
use App\Enums\Division;
use App\Enums\SalesOrderStatus;
use App\Traits\HasActivityLog;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesOrder extends Model
{
    use HasFactory, HasActivityLog, HasAuditLog, SoftDeletes, HasCommercialTotals;

    public array $auditExclude = ['password', 'remember_token'];

    protected $fillable = [
        'reference', 'quotation_id', 'lead_id', 'customer_id', 'division', 'invoice_id','zoho_id', 'status',
        'currency', 'discount_type', 'discount_value', 'tax_rate', 'subtotal',
        'discount_amount', 'tax_amount', 'total', 'total_cost', 'margin_percent',
        'expected_delivery_date', 'delivered_at', 'delivery_address', 'delivery_notes',
        'notes', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'discount_value' => 'decimal:2', 'tax_rate' => 'decimal:2', 'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2', 'tax_amount' => 'decimal:2', 'total' => 'decimal:2',
            'total_cost' => 'decimal:2', 'margin_percent' => 'decimal:2',
            'expected_delivery_date' => 'date', 'delivered_at' => 'datetime',
        ];
    }

    /* ---- Relations ---- */

    public function items(): HasMany
    {
        return $this->hasMany(SalesOrderItem::class)->orderBy('sort');
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /* ---- Accessors & scopes ---- */

    public function status(): SalesOrderStatus
    {
        return SalesOrderStatus::from($this->status);
    }

    public function division(): Division
    {
        return Division::from($this->division);
    }

    public function isOpen(): bool
    {
        return in_array($this->status(), [
            SalesOrderStatus::Pending, SalesOrderStatus::Confirmed, SalesOrderStatus::Processing,
        ], true);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', [
            SalesOrderStatus::Pending->value, SalesOrderStatus::Confirmed->value, SalesOrderStatus::Processing->value,
        ]);
    }
}