<?php

namespace App\Actions\Quotations;

use App\Enums\QuotationStatus;
use App\Models\Quotation;
use RuntimeException;

class AcceptQuotationAction
{
    public function execute(Quotation $quotation): void
    {
        if ($quotation->status() !== QuotationStatus::Sent) {
            throw new RuntimeException('Only sent quotations can be accepted.');
        }

        if ($quotation->isExpired()) {
            $quotation->update(['status' => QuotationStatus::Expired->value]);
            throw new RuntimeException('This quotation has expired.');
        }

        $quotation->update(['status' => QuotationStatus::Accepted->value, 'accepted_at' => now()]);

        $quotation->logActivity('quotation was accepted by the customer');

        event(new \App\Events\Quotations\QuotationAccepted($quotation));
    }
}