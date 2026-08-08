<?php

namespace App\Actions\Quotations;

use App\Enums\QuotationStatus;
use App\Models\Quotation;
use App\Models\User;
use RuntimeException;

class ApproveQuotationAction
{
    public function execute(Quotation $quotation, User $approver, ?string $notes = null): void
    {
        if ($quotation->status() !== QuotationStatus::PendingApproval) {
            throw new RuntimeException('Only pending quotations can be approved.');
        }

        $quotation->update([
            'status' => QuotationStatus::Approved->value,
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'approval_notes' => $notes,
        ]);

        $quotation->logActivity("approved the quotation (margin {$quotation->margin_percent}%)");

        event(new \App\Events\Quotations\QuotationApproved($quotation));
    }
}