<?php

namespace Tests\Feature\Invoices;

use App\Actions\Invoices\CreateInvoiceFromOrderAction;
use App\Actions\Invoices\RecordInvoicePaymentAction;
use App\Actions\Invoices\SendInvoiceAction;
use App\Actions\SalesOrders\CreateSalesOrderAction;
use App\Enums\CommunicationChannel;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\SalesOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->customer = Customer::factory()->create(['outstanding_balance' => 0]);
    }

    private function makeOrder(): SalesOrder
    {
        return app(CreateSalesOrderAction::class)->execute([
            'customer_id' => $this->customer->id,
            'division' => 'automotive',
            'status' => 'confirmed',
            'tax_rate' => 5,
        ], [
            ['product_id' => null, 'description' => 'Brake pads', 'quantity' => 2, 'unit_price' => 100, 'cost_price' => 60, 'discount_percent' => 0],
        ], $this->user);
    }

    public function test_invoice_generation_from_order(): void
    {
        $order = $this->makeOrder();

        $invoice = app(CreateInvoiceFromOrderAction::class)->execute($order);

        $this->assertSame('INV-00001', $invoice->reference);
        $this->assertSame($order->id, $invoice->sales_order_id);
        $this->assertCount(1, $invoice->items);
        $this->assertEqualsWithDelta((float) $order->total, (float) $invoice->total, 0.01);
        $this->assertSame($invoice->id, $order->fresh()->invoice_id);
    }

    public function test_double_invoicing_returns_existing(): void
    {
        $order = $this->makeOrder();
        $action = app(CreateInvoiceFromOrderAction::class);

        $first = $action->execute($order);
        $second = $action->execute($order->fresh());

        $this->assertSame($first->id, $second->id);
        $this->assertDatabaseCount('invoices', 1);
    }

    public function test_pending_order_cannot_be_invoiced(): void
    {
        $order = $this->makeOrder();
        $order->update(['status' => 'pending']);

        $this->expectException(RuntimeException::class);

        app(CreateInvoiceFromOrderAction::class)->execute($order);
    }

    public function test_send_updates_status_and_customer_outstanding(): void
    {
        Mail::fake();
        $order = $this->makeOrder();
        $invoice = app(CreateInvoiceFromOrderAction::class)->execute($order);

        app(SendInvoiceAction::class)->execute($invoice, CommunicationChannel::Email);

        $fresh = $invoice->fresh();
        $this->assertSame('sent', $fresh->status);
        $this->assertNotNull($fresh->sent_at);
        $this->assertEqualsWithDelta((float) $fresh->total, (float) $this->customer->fresh()->outstanding_balance, 0.01);
        
        Mail::assertQueued(\App\Mail\InvoiceMail::class);
    }

    public function test_partial_and_full_payment_updates_balances(): void
    {
        $order = $this->makeOrder();
        $invoice = app(CreateInvoiceFromOrderAction::class)->execute($order);
        app(SendInvoiceAction::class)->execute($invoice, CommunicationChannel::Email);

        $record = app(RecordInvoicePaymentAction::class);

        // Partial payment
        $record->execute($invoice->fresh(), 50.00);
        $this->assertSame('partial', $invoice->fresh()->status);
        $this->assertEqualsWithDelta(50.00, (float) $invoice->fresh()->paid_amount, 0.01);

        // Full payment
        $record->execute($invoice->fresh(), (float) $invoice->fresh()->balance_due);
        $this->assertSame('paid', $invoice->fresh()->status);
        $this->assertEqualsWithDelta(0.00, (float) $invoice->fresh()->balance_due, 0.01);
        
        // Customer outstanding balance returns to 0
        $this->assertEqualsWithDelta(0.00, (float) $this->customer->fresh()->outstanding_balance, 0.01);
    }

    public function test_overpayment_is_rejected(): void
    {
        $order = $this->makeOrder();
        $invoice = app(CreateInvoiceFromOrderAction::class)->execute($order);

        $this->expectException(RuntimeException::class);

        app(RecordInvoicePaymentAction::class)->execute($invoice, (float) $invoice->total + 100);
    }
}