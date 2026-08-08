<?php

namespace App\Actions\Quotations;

use App\Models\Quotation;

class NewQuotationVersionAction
{
    /** Negotiation revision: clone into a new draft version linked to the original. */
    public function execute(Quotation $original): Quotation
    {
        $version = $original->replicate([
            'reference', 'status', 'sent_at', 'sent_via', 'accepted_at', 'rejected_at',
            'rejected_reason', 'approved_at', 'approved_by', 'approval_notes', 'created_at', 'updated_at',
        ]);

        $version->status = 'draft';
        $version->version = $original->version + 1;
        $version->parent_id = $original->id;
        $version->save();

        $version->update(['reference' => $original->reference.'-V'.$version->version]);

        foreach ($original->items as $item) {
            $replica = $item->replicate();
            $replica->quotation_id = $version->id;
            $replica->save();
        }

        $version->logActivity("created version {$version->version} for negotiation");

        return $version;
    }
}