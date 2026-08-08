<?php

namespace App\Actions\Suppliers;

use App\Events\Suppliers\SupplierEnquiryCreated;
use App\Models\Supplier;
use App\Models\SupplierEnquiry;
use App\Models\User;

class CreateSupplierEnquiryAction
{
    /**
     * @param array<int, array{product_id: ?int, description: string, quantity: int}> $items
     */
    public function execute(Supplier $supplier, array $items, ?User $creator, ?int $leadId = null, ?int $customerId = null, ?string $notes = null): SupplierEnquiry
    {
        $enquiry = new SupplierEnquiry([
            'supplier_id' => $supplier->id,
            'lead_id' => $leadId,
            'customer_id' => $customerId,
            'status' => 'draft',
            'notes' => $notes,
            'created_by' => $creator?->id,
        ]);

        $enquiry->save();

        $enquiry->update(['reference' => 'RFQ-'.str_pad((string) $enquiry->id, 5, '0', STR_PAD_LEFT)]);

        foreach ($items as $item) {
            $enquiry->items()->create([
                'product_id' => $item['product_id'] ?? null,
                'description' => $item['description'],
                'quantity' => $item['quantity'],
            ]);
        }

        $enquiry->logActivity("created supplier enquiry {$enquiry->reference}");

        event(new SupplierEnquiryCreated($enquiry));

        return $enquiry;
    }
}