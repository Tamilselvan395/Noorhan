<?php

namespace App\Actions\Quotations;

use App\Enums\QuotationStatus;
use App\Events\Quotations\QuotationRejected;
use App\Models\Quotation;
use App\Models\User;
use RuntimeException;

class RejectQuotationAction
{
    public function execute(Quotation $quotation, User $rejector, string $reason): void
    {
        if (! in_array($quotation->status(), [QuotationStatus::PendingApproval, QuotationStatus::Draft], true)) {
            throw new RuntimeException('Quotation cannot be rejected in its current state.');
        }

        $quotation->update([
            'status' => QuotationStatus::Rejected->value,
            'rejected_at' => now(),
            'rejected_reason' => $reason,
            'approval_notes' => "Rejected by {$rejector->name}: {$reason}",
        ]);

        $quotation->logActivity("rejected the quotation: {$reason}");

        // Notifies the quotation author with the rejection reason
        event(new QuotationRejected($quotation, $reason));
    }
}