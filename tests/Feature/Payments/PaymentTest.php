<?php

namespace Tests\Feature\Payments;

use App\Actions\Invoices\CreateInvoiceFromOrderAction;
use App\Actions\Invoices\SendInvoiceAction;
use App\Actions\Payments\CreatePaymentAction;
use App\Actions\Payments\VoidPaymentAction;
use App\Actions\SalesOrders\CreateSalesOrderAction;
use App\DTOs\Payments\PaymentDTO;
use App\Enums\CommunicationChannel;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->customer = Customer::factory()->create(['outstanding_balance' => 0, 'credit_balance' => 0]);
    }

    private function makeOutstandingInvoice(float $total = 210.00) // 200 + 5% tax
    {
        $order = app(CreateSalesOrderAction::class)->execute([
            'customer_id' => $this->customer->id,
            'division' => 'automotive',
            'status' => 'confirmed',
            'tax_rate' => 5,
        ], [
            ['product_id' => null, 'description' => 'Item', 'quantity' => 2, 'unit_price' => 100, 'cost_price' => 50, 'discount_percent' => 0],
        ], $this->user);

        $invoice = app(CreateInvoiceFromOrderAction::class)->execute($order);
        app(SendInvoiceAction::class)->execute($invoice, CommunicationChannel::Email);

        return $invoice->fresh();
    }

    public function test_exact_payment_clears_invoice_and_outstanding(): void
    {
        $invoice = $this->makeOutstandingInvoice();
        $this->assertEqualsWithDelta(210.00, (float) $this->customer->fresh()->outstanding_balance, 0.01);

        $dto = new PaymentDTO(
            customer_id: $this->customer->id,
            amount: 210.00,
            allocations: [$invoice->id => 210.00]
        );

        $payment = app(CreatePaymentAction::class)->execute($dto, $this->user);

        $this->assertSame('PAY-00001', $payment->reference);
        $this->assertSame('paid', $invoice->fresh()->status);
        $this->assertEqualsWithDelta(0.00, (float) $invoice->fresh()->balance_due, 0.01);
        $this->assertEqualsWithDelta(0.00, (float) $this->customer->fresh()->outstanding_balance, 0.01);
        $this->assertEqualsWithDelta(0.00, (float) $this->customer->fresh()->credit_balance, 0.01);
    }

    public function test_partial_payment_updates_status_and_balances(): void
    {
        $invoice = $this->makeOutstandingInvoice();

        $dto = new PaymentDTO(
            customer_id: $this->customer->id,
            amount: 100.00,
            allocations: [$invoice->id => 100.00]
        );

        app(CreatePaymentAction::class)->execute($dto, $this->user);

        $this->assertSame('partial', $invoice->fresh()->status);
        $this->assertEqualsWithDelta(110.00, (float) $invoice->fresh()->balance_due, 0.01);
        $this->assertEqualsWithDelta(110.00, (float) $this->customer->fresh()->outstanding_balance, 0.01);
    }

    public function test_overpayment_creates_customer_credit(): void
    {
        $invoice = $this->makeOutstandingInvoice(); // 210

        $dto = new PaymentDTO(
            customer_id: $this->customer->id,
            amount: 300.00, // Overpaid by 90
            allocations: [$invoice->id => 210.00]
        );

        app(CreatePaymentAction::class)->execute($dto, $this->user);

        $this->assertSame('paid', $invoice->fresh()->status);
        $this->assertEqualsWithDelta(0.00, (float) $this->customer->fresh()->outstanding_balance, 0.01);
        $this->assertEqualsWithDelta(90.00, (float) $this->customer->fresh()->credit_balance, 0.01);
    }

    public function test_unallocated_payment_goes_entirely_to_credit(): void
    {
        $dto = new PaymentDTO(
            customer_id: $this->customer->id,
            amount: 500.00,
            allocations: [] // No invoices selected
        );

        app(CreatePaymentAction::class)->execute($dto, $this->user);

        $this->assertEqualsWithDelta(500.00, (float) $this->customer->fresh()->credit_balance, 0.01);
    }

    public function test_allocation_cannot_exceed_payment_amount(): void
    {
        $invoice = $this->makeOutstandingInvoice();

        $dto = new PaymentDTO(
            customer_id: $this->customer->id,
            amount: 100.00,
            allocations: [$invoice->id => 150.00] // Exceeds total received
        );

        $this->expectException(RuntimeException::class);
        app(CreatePaymentAction::class)->execute($dto, $this->user);
    }

    public function test_voiding_payment_reverses_all_balances(): void
    {
        $invoice = $this->makeOutstandingInvoice();
        
        $dto = new PaymentDTO(
            customer_id: $this->customer->id,
            amount: 210.00,
            allocations: [$invoice->id => 210.00]
        );

        $payment = app(CreatePaymentAction::class)->execute($dto, $this->user);
        
        // Void it
        app(VoidPaymentAction::class)->execute($payment);

        $this->assertSame('voided', $payment->fresh()->status);
        $this->assertSame('sent', $invoice->fresh()->status); // Reverts from 'paid' back to 'sent'
        $this->assertEqualsWithDelta(210.00, (float) $invoice->fresh()->balance_due, 0.01);
        $this->assertEqualsWithDelta(210.00, (float) $this->customer->fresh()->outstanding_balance, 0.01);
    }
}