<?php

namespace App\Actions\Suppliers;

use App\Enums\CommunicationChannel;
use App\Enums\SupplierEnquiryStatus;
use App\Events\Suppliers\SupplierEnquirySent;
use App\Models\SupplierEnquiry;
use RuntimeException;

class SendSupplierEnquiryAction
{
    public function execute(SupplierEnquiry $enquiry, CommunicationChannel $via): SupplierEnquiry
    {
        if ($enquiry->status() !== SupplierEnquiryStatus::Draft) {
            throw new RuntimeException("Only draft enquiries can be sent. Current status: {$enquiry->status}");
        }

        if ($enquiry->items()->count() === 0) {
            throw new RuntimeException('Enquiry has no items to send.');
        }

        $enquiry->update([
            'status' => SupplierEnquiryStatus::Sent->value,
            'sent_via' => $via->value,
            'sent_at' => now(),
        ]);

        $enquiry->logActivity("sent the enquiry via {$via->label()}");

        event(new SupplierEnquirySent($enquiry, $via)); // WhatsApp/Email automation modules listen to this

        return $enquiry;
    }
}