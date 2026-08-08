<?php

namespace App\Actions\SalesOrders;

use App\Enums\QuotationStatus;
use App\Events\SalesOrders\SalesOrderCreated;
use App\Models\Quotation;
use App\Models\SalesOrder;
use RuntimeException;

class ConvertQuotationToOrderAction
{
    public function __construct(private CreateSalesOrderAction $create) {}

    public function execute(Quotation $quotation): SalesOrder
    {
        if (! in_array($quotation->status(), [QuotationStatus::Approved, QuotationStatus::Accepted], true)) {
            throw new RuntimeException('Only approved or accepted quotations can be converted to sales orders.');
        }

        if ($quotation->converted_order_id) {
            return SalesOrder::findOrFail($quotation->converted_order_id);
        }

        $order = $this->create->execute([
            'quotation_id' => $quotation->id,
            'lead_id' => $quotation->lead_id,
            'customer_id' => $quotation->customer_id,
            'division' => $quotation->division,
            'status' => 'pending',
            'currency' => $quotation->currency,
            'discount_type' => $quotation->discount_type,
            'discount_value' => (float) $quotation->discount_value,
            'tax_rate' => (float) $quotation->tax_rate,
            'notes' => "Converted from quotation {$quotation->reference}",
        ], $quotation->items->map(fn ($item) => [
            'product_id' => $item->product_id,
            'description' => $item->description,
            'quantity' => $item->quantity,
            'unit_price' => (float) $item->unit_price,
            'cost_price' => (float) $item->cost_price,
            'discount_percent' => (float) $item->discount_percent,
            'tax_rate' => (float) $item->tax_rate,
        ])->all(), $quotation->creator);

        $quotation->update([
            'status' => QuotationStatus::Converted->value,
            'converted_order_id' => $order->id,
        ]);

        $quotation->logActivity("converted to sales order {$order->reference}");

        event(new SalesOrderCreated($order, $quotation));

        return $order;
    }
}