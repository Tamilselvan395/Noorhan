<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesOrderItem extends Model
{
    protected $guarded = [];

    protected $casts = [
        'unit_price' => 'decimal:2', 'cost_price' => 'decimal:2',
        'discount_percent' => 'decimal:2', 'tax_rate' => 'decimal:2', 'line_total' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class, 'sales_order_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}