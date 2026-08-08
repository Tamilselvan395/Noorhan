<?php

namespace App\Concerns;

/**
 * Shared commercial engine for Quotations & Sales Orders.
 * Requires: items() relation with quantity/unit_price/cost_price/discount_percent/line_total,
 * and columns: discount_type, discount_value, tax_rate, subtotal, discount_amount,
 * tax_amount, total, total_cost, margin_percent.
 */
trait HasCommercialTotals
{
    public function recalculate(): void
    {
        $subtotal = 0;
        $totalCost = 0;

        foreach ($this->items as $item) {
            $item->line_total = round($item->quantity * $item->unit_price * (1 - ((float) $item->discount_percent) / 100), 2);
            $item->save();

            $subtotal += (float) $item->line_total;
            $totalCost += $item->quantity * (float) $item->cost_price;
        }

        $discountAmount = $this->discount_type === 'percent'
            ? $subtotal * ((float) $this->discount_value) / 100
            : (float) $this->discount_value;

        $taxable = max($subtotal - $discountAmount, 0);
        $taxAmount = $taxable * ((float) $this->tax_rate) / 100;

        $this->update([
            'subtotal' => round($subtotal, 2),
            'discount_amount' => round($discountAmount, 2),
            'tax_amount' => round($taxAmount, 2),
            'total' => round($taxable + $taxAmount, 2),
            'total_cost' => round($totalCost, 2),
            'margin_percent' => $taxable > 0 ? round((($taxable - $totalCost) / $taxable) * 100, 2) : 0,
        ]);
    }
}