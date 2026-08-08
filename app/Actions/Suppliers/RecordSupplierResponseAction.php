<?php

namespace App\Actions\Suppliers;

use App\Enums\EnquiryItemStatus;
use App\Enums\SupplierEnquiryStatus;
use App\Events\Suppliers\SupplierResponseReceived;
use App\Models\SupplierEnquiryItem;

class RecordSupplierResponseAction
{
    public function execute(
        SupplierEnquiryItem $item,
        EnquiryItemStatus $status,
        ?float $offeredPrice = null,
        ?int $leadTimeDays = null,
        ?string $validUntil = null,
        ?string $supplierNotes = null,
    ): void {
        if ($status === EnquiryItemStatus::Quoted && $offeredPrice === null) {
            throw new \InvalidArgumentException('A quoted item requires an offered price.');
        }

        $item->update([
            'status' => $status->value,
            'offered_price' => $offeredPrice,
            'offered_currency' => $offeredPrice !== null ? ($item->enquiry->supplier->currency) : $item->offered_currency,
            'lead_time_days' => $leadTimeDays,
            'valid_until' => $validUntil,
            'supplier_notes' => $supplierNotes,
        ]);

        $enquiry = $item->enquiry;
        $items = $enquiry->items()->get();

        $allResponded = $items->every(fn (SupplierEnquiryItem $i) => $i->isResponded());

        $enquiry->update([
            'status' => $allResponded
                ? SupplierEnquiryStatus::Quoted->value
                : SupplierEnquiryStatus::Partial->value,
            'responded_at' => $enquiry->responded_at ?? now(),
        ]);

        $enquiry->logActivity("recorded a supplier response for: {$item->description}");

        event(new SupplierResponseReceived($enquiry, $item));
    }
}