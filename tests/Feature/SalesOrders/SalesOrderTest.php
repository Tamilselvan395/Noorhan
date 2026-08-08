<?php

namespace Tests\Feature\SalesOrders;

use App\Actions\Quotations\ApproveQuotationAction;
use App\Actions\Quotations\CreateQuotationAction;
use App\Actions\SalesOrders\AdvanceSalesOrderStatusAction;
use App\Actions\SalesOrders\ConvertQuotationToOrderAction;
use App\Enums\SalesOrderStatus;
use App\Livewire\SalesOrders\OrderForm;
use App\Models\Customer;
use App\Models\Quotation;
use App\Models\SalesOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use RuntimeException;
use Tests\TestCase;

class SalesOrderTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->manager = User::factory()->create();
    }

    private function approvedQuotation(): Quotation
    {
        $customer = Customer::factory()->create();

        $quotation = app(CreateQuotationAction::class)->execute(
            ['customer_id' => $customer->id, 'division' => 'automotive', 'status' => 'draft', 'tax_rate' => 5],
            [['product_id' => null, 'description' => 'Brake pads', 'quantity' => 2, 'unit_price' => 100, 'cost_price' => 60, 'discount_percent' => 0]],
            $this->user,
        );

        app(\App\Actions\Quotations\SubmitForApprovalAction::class)->execute($quotation);
        app(ApproveQuotationAction::class)->execute($quotation->fresh(), $this->manager);

        return $quotation->fresh();
    }

    public function test_convert_approved_quotation_creates_order(): void
    {
        $quotation = $this->approvedQuotation();

        $order = app(ConvertQuotationToOrderAction::class)->execute($quotation);

        $this->assertSame('SO-00001', $order->reference);
        $this->assertSame($quotation->id, $order->quotation_id);
        $this->assertCount(1, $order->items);
        $this->assertEqualsWithDelta((float) $quotation->total, (float) $order->total, 0.01);

        $this->assertSame('converted', $quotation->fresh()->status);
        $this->assertSame($order->id, $quotation->fresh()->converted_order_id);
    }

    public function test_conversion_is_idempotent(): void
    {
        $quotation = $this->approvedQuotation();
        $action = app(ConvertQuotationToOrderAction::class);

        $first = $action->execute($quotation);
        $second = $action->execute($quotation->fresh());

        $this->assertSame($first->id, $second->id);
        $this->assertDatabaseCount('sales_orders', 1);
    }

    public function test_draft_quotation_cannot_be_converted(): void
    {
        $customer = Customer::factory()->create();

        $quotation = app(CreateQuotationAction::class)->execute(
            ['customer_id' => $customer->id, 'division' => 'automotive', 'status' => 'draft', 'tax_rate' => 5],
            [['product_id' => null, 'description' => 'X', 'quantity' => 1, 'unit_price' => 10, 'cost_price' => 5, 'discount_percent' => 0]],
            $this->user,
        );

        $this->expectException(RuntimeException::class);

        app(ConvertQuotationToOrderAction::class)->execute($quotation);
    }

    public function test_status_machine_progression(): void
    {
        $order = SalesOrder::factory()->create();
        $advance = app(AdvanceSalesOrderStatusAction::class);

        // Invalid jump blocked
        try {
            $advance->execute($order, SalesOrderStatus::Delivered);
            $this->fail('Invalid transition allowed');
        } catch (RuntimeException) {
        }

        $advance->execute($order, SalesOrderStatus::Confirmed);
        $advance->execute($order, SalesOrderStatus::Processing);
        $advance->execute($order, SalesOrderStatus::Delivered);

        $fresh = $order->fresh();
        $this->assertSame('delivered', $fresh->status);
        $this->assertNotNull($fresh->delivered_at);

        // Terminal state
        $this->expectException(RuntimeException::class);
        $advance->execute($fresh, SalesOrderStatus::Cancelled);
    }

    public function test_manual_order_via_livewire(): void
    {
        $customer = Customer::factory()->create();

        Livewire::actingAs($this->user)
            ->test(OrderForm::class)
            ->call('openForm')
            ->set('customer_id', $customer->id)
            ->set('items', [[
                'product_id' => null, 'description' => 'Engine oil 5W-30', 'quantity' => 4,
                'unit_price' => '25', 'cost_price' => '15', 'discount_percent' => '0',
            ]])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('sales_orders', ['status' => 'pending']);
        $this->assertDatabaseHas('sales_order_items', ['description' => 'Engine oil 5W-30', 'quantity' => 4]);
    }

    public function test_delivered_order_is_frozen(): void
    {
        $order = SalesOrder::factory()->create(['status' => 'delivered']);

        $this->assertFalse($this->user->can('update', $order));
    }
}