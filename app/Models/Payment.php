<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Traits\HasActivityLog;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, HasActivityLog, HasAuditLog, SoftDeletes;

    protected $fillable = [
        'reference', 'customer_id', 'amount', 'currency', 'payment_date','zoho_id',
        'method', 'reference_number', 'status', 'notes', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payment_date' => 'date',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function invoices(): BelongsToMany
    {
        return $this->belongsToMany(Invoice::class)->withPivot('allocated_amount')->withTimestamps();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function method(): PaymentMethod
    {
        return PaymentMethod::from($this->method);
    }

    public function status(): PaymentStatus
    {
        return PaymentStatus::from($this->status);
    }

    /** Sum of all active allocations to invoices. */
    public function allocatedAmount(): float
    {
        return (float) $this->invoices()->wherePivot('allocated_amount', '>', 0)->sum('invoice_payment.allocated_amount');
    }

    /** Unallocated amount (customer credit). */
    public function unallocatedAmount(): float
    {
        return round((float) $this->amount - $this->allocatedAmount(), 2);
    }
}