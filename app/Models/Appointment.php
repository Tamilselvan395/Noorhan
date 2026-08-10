<?php

namespace App\Models;

use App\Enums\AppointmentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    protected $fillable = [
        'reference', 'customer_id', 'product_id', 'vehicle_make', 'vehicle_model',
        'vehicle_year', 'plate', 'scheduled_at', 'estimated_minutes', 'status',
        'assigned_to', 'price_estimate', 'notes', 'completed_at', 'sales_order_id', 'created_by',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
        'price_estimate' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function status(): AppointmentStatus
    {
        return AppointmentStatus::from($this->status);
    }

    public function vehicle(): string
    {
        return trim("{$this->vehicle_make} {$this->vehicle_model} ({$this->vehicle_year}) · {$this->plate}");
    }
}