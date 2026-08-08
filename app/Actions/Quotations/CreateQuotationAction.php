<?php

namespace App\Actions\Quotations;

use App\Events\Quotations\QuotationCreated;
use App\Models\Quotation;
use App\Models\User;

class CreateQuotationAction
{
    /**
     * @param array<int, array{product_id: ?int, description: string, quantity: int, unit_price: float, cost_price: float, discount_percent: float}> $items
     */
    public function execute(array $data, array $items, ?User $creator): Quotation
    {
        $quotation = new Quotation(array_merge($data, ['created_by' => $creator?->id]));
        $quotation->save();

        $quotation->update(['reference' => 'QTN-'.str_pad((string) $quotation->id, 5, '0', STR_PAD_LEFT)]);

        foreach (array_values($items) as $i => $item) {
            $quotation->items()->create($item + ['sort' => $i * 10]);
        }

        $quotation->recalculate();
        $quotation->update(['requires_approval' => $quotation->fresh()->computeRequiresApproval()]);

        $quotation->logActivity("created quotation {$quotation->reference}");

        event(new QuotationCreated($quotation->fresh()));

        return $quotation->fresh();
    }
}