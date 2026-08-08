<?php

namespace App\Actions\Quotations;

use App\Enums\QuotationStatus;
use App\Models\Quotation;
use RuntimeException;

class SubmitForApprovalAction
{
    public function execute(Quotation $quotation): void
    {
        if ($quotation->status() !== QuotationStatus::Draft) {
            throw new RuntimeException('Only draft quotations can be submitted for approval.');
        }

        $quotation->update([
            'status' => QuotationStatus::PendingApproval->value,
            'requires_approval' => $quotation->computeRequiresApproval(),
        ]);

        $quotation->logActivity('submitted the quotation for approval');
    }
}