<?php

namespace Tests\Feature\Divisions;

use App\Actions\SalesOrders\CreateSalesOrderAction;
use App\Enums\Division;
use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use App\Services\Divisions\DivisionMetricsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SwiftecCrmTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private DivisionMetricsService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->service = app(DivisionMetricsService::class);
    }

    private function orderIn(string $division, Customer $customer, Product $product, float $price): void
    {
        app(CreateSalesOrderAction::class)->execute([
            'customer_id' => $customer->id,
            'division' => $division,
            'status' => 'confirmed',
            'tax_rate' => 5,
        ], [[
            'product_id' => $product->id, 'description' => $product->name, 'quantity' => 2,
            'unit_price' => $price, 'cost_price' => (float) $product->cost_price, 'discount_percent' => 0,
        ]], $this->user);
    }

    public function test_metrics_are_strictly_division_scoped(): void
    {
        $swiftecProduct = Product::factory()->create(['division' => 'swiftec']);
        $wiperexProduct = Product::factory()->create(['division' => 'wiperex']);
        $customer = Customer::factory()->create();

        $this->orderIn('swiftec', $customer, $swiftecProduct, 100);
        $this->orderIn('wiperex', $customer, $wiperexProduct, 500);

        $metrics = $this->service->metrics(Division::Swiftec);

        // 2 × 100 = 200 + 5% tax = 210; the 500 wiperex order must NOT leak in
        $this->assertEqualsWithDelta(210.0, $metrics['revenue'], 0.01);
        $this->assertSame(1, $metrics['orders']);
    }

    public function test_category_breakdown_groups_by_product_line(): void
    {
        $oil = Product::factory()->create(['division' => 'swiftec']);
        // attach to "Engine Oil" category created by seeder
        $category = \App\Models\ProductCategory::firstOrCreate(['name' => 'Engine Oil', 'division' => 'swiftec']);
        $oil->update(['category_id' => $category->id]);

        $this->orderIn('swiftec', Customer::factory()->create(), $oil, 50);

        $breakdown = $this->service->categoryBreakdown(Division::Swiftec);

        $this->assertArrayHasKey('Engine Oil', $breakdown);
        $this->assertEqualsWithDelta(100.0, $breakdown['Engine Oil'], 0.01);
    }

    public function test_partner_dormancy_flagging(): void
    {
        $active = Customer::factory()->create(['division' => 'swiftec', 'type' => 'distributor']);
        $dormant = Customer::factory()->create(['division' => 'swiftec', 'type' => 'dealer']);
        $product = Product::factory()->create(['division' => 'swiftec']);

        $this->orderIn('swiftec', $active, $product, 100);

        $partners = collect($this->service->partners(Division::Swiftec));

        $this->assertFalse($partners->firstWhere('id', $active->id)['dormant']);
        $this->assertTrue($partners->firstWhere('id', $dormant->id)['dormant']);
    }

    public function test_reorder_plan_uses_demand_forecast(): void
    {
        $product = Product::factory()->create(['division' => 'swiftec']);

        // sales in each of the past 3 months
        foreach ([1, 2, 3] as $monthsAgo) {
            $order = app(CreateSalesOrderAction::class)->execute([
                'customer_id' => Customer::factory()->create()->id,
                'division' => 'swiftec', 'status' => 'delivered', 'tax_rate' => 5,
            ], [[
                'product_id' => $product->id, 'description' => $product->name, 'quantity' => 10,
                'unit_price' => 20, 'cost_price' => 10, 'discount_percent' => 0,
            ]], $this->user);

            $order->update(['created_at' => now()->subMonths($monthsAgo)]);
            $order->items()->update(['created_at' => now()->subMonths($monthsAgo)]);
        }

        $plan = collect($this->service->reorderPlan(Division::Swiftec));
        $row = $plan->firstWhere('id', $product->id);

        $this->assertNotNull($row);
        $this->assertEqualsWithDelta(10.0, $row['avg_monthly'], 0.1);
        $this->assertSame(12, $row['suggested_order']); // 10 × 1.2 buffer
    }

    public function test_swiftec_workspace_page_renders(): void
    {
        $this->actingAs($this->user)
            ->get(route('swiftec.index'))
            ->assertOk()
            ->assertSee('Swiftec Lubricants')
            ->assertSee('AI Reorder / Production Plan');
    }
}