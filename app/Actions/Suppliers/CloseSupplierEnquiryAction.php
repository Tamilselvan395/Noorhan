<?php

namespace App\Actions\Suppliers;

use App\Enums\SupplierEnquiryStatus;
use App\Models\SupplierEnquiry;
use RuntimeException;

class CloseSupplierEnquiryAction
{
    public function execute(SupplierEnquiry $enquiry, bool $cancel = false): void
    {
        if (in_array($enquiry->status(), [SupplierEnquiryStatus::Closed, SupplierEnquiryStatus::Cancelled], true)) {
            throw new RuntimeException('Enquiry is already closed.');
        }

        $enquiry->update([
            'status' => $cancel ? SupplierEnquiryStatus::Cancelled->value : SupplierEnquiryStatus::Closed->value,
            'closed_at' => now(),
        ]);

        $enquiry->logActivity($cancel ? 'cancelled the enquiry' : 'closed the enquiry');
    }
}