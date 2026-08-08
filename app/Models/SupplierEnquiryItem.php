<?php

namespace App\Models;

use App\Enums\EnquiryItemStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierEnquiryItem extends Model
{
    protected $guarded = [];

    protected $casts = [
        'offered_price' => 'decimal:2',
        'valid_until' => 'date',
    ];

    public function enquiry(): BelongsTo
    {
        return $this->belongsTo(SupplierEnquiry::class, 'supplier_enquiry_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function status(): EnquiryItemStatus
    {
        return EnquiryItemStatus::from($this->status);
    }

    public function isResponded(): bool
    {
        return $this->status() !== EnquiryItemStatus::Pending;
    }
}