<?php

namespace App\Actions\Invoices;

use App\Enums\SalesOrderStatus;
use App\Events\Invoices\InvoiceCreated;
use App\Models\Invoice;
use App\Models\SalesOrder;
use RuntimeException;

class CreateInvoiceFromOrderAction
{
    public function execute(SalesOrder $order): Invoice
    {
        $billable = in_array($order->status(), [
            SalesOrderStatus::Confirmed, SalesOrderStatus::Processing, SalesOrderStatus::Delivered,
        ], true);

        if (! $billable) {
            throw new RuntimeException('Order must be confirmed, processing, or delivered to be invoiced.');
        }

        if ($order->invoice_id) {
            return Invoice::findOrFail($order->invoice_id);
        }

        $invoice = new Invoice([
            'sales_order_id' => $order->id,
            'customer_id' => $order->customer_id,
            'division' => $order->division,
            'status' => 'draft',
            'currency' => $order->currency,
            'discount_type' => $order->discount_type,
            'discount_value' => (float) $order->discount_value,
            'tax_rate' => (float) $order->tax_rate,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(15)->toDateString(), // Standard Net 15
            'notes' => "Invoiced from Sales Order {$order->reference}",
            'terms' => 'Payment due within 15 days of invoice date.',
            'created_by' => $order->created_by,
        ]);

        $invoice->save();
        $invoice->update(['reference' => 'INV-'.str_pad((string) $invoice->id, 5, '0', STR_PAD_LEFT)]);

        foreach ($order->items as $i => $item) {
            $invoice->items()->create([
                'product_id' => $item->product_id,
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'cost_price' => (float) $item->cost_price,
                'discount_percent' => (float) $item->discount_percent,
                'tax_rate' => (float) $item->tax_rate,
                'sort' => $i * 10,
            ]);
        }

        $invoice->recalculate();
        $invoice->updateBalances();

        $order->update(['invoice_id' => $invoice->id]);

        $invoice->logActivity("generated invoice {$invoice->reference} from order {$order->reference}");

        event(new InvoiceCreated($invoice));

        return $invoice->fresh();
    }
}