<?php

namespace Tests\Feature\Divisions;

use App\Actions\SalesOrders\CreateSalesOrderAction;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use App\Services\Divisions\WiperexInsightsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class WiperexCrmTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    private function bladeWithSize(string $size): Product
    {
        return Product::factory()->create([
            'division' => 'wiperex',
            'attributes' => ['size' => $size, 'type' => 'Frameless'],
        ]);
    }

    private function orderItems(Customer $customer, array $items): void
    {
        app(CreateSalesOrderAction::class)->execute([
            'customer_id' => $customer->id,
            'division' => 'wiperex',
            'status' => 'confirmed',
            'tax_rate' => 5,
        ], $items, $this->user);
    }

    public function test_size_mix_groups_revenue_by_fitment(): void
    {
        $b22 = $this->bladeWithSize('22"');
        $b26 = $this->bladeWithSize('26"');
        $customer = Customer::factory()->create();

        $this->orderItems($customer, [
            ['product_id' => $b22->id, 'description' => $b22->name, 'quantity' => 2, 'unit_price' => 10, 'cost_price' => 4, 'discount_percent' => 0],
            ['product_id' => $b26->id, 'description' => $b26->name, 'quantity' => 1, 'unit_price' => 40, 'cost_price' => 15, 'discount_percent' => 0],
        ]);

        $mix = app(WiperexInsightsService::class)->sizeMix();

        $this->assertEqualsWithDelta(40.0, $mix['26"'], 0.01);
        $this->assertEqualsWithDelta(20.0, $mix['22"'], 0.01);
        // sorted desc
        $this->assertSame('26"', array_key_first($mix));
    }

    public function test_replenishment_candidates_require_repeat_consumable_orders(): void
    {
        $category = ProductCategory::firstOrCreate(['name' => 'Cleaning Liquid', 'division' => 'wiperex']);
        $liquid = Product::factory()->create(['division' => 'wiperex', 'category_id' => $category->id]);

        $repeat = Customer::factory()->create();
        $oneOff = Customer::factory()->create();

        $item = fn (Customer $c) => [['product_id' => $liquid->id, 'description' => $liquid->name, 'quantity' => 5, 'unit_price' => 5, 'cost_price' => 2, 'discount_percent' => 0]];

        $this->orderItems($repeat, $item($repeat));
        $this->orderItems($repeat, $item($repeat)); // 2 orders → eligible
        $this->orderItems($oneOff, $item($oneOff));  // 1 order → not eligible

        $candidates = collect(app(WiperexInsightsService::class)->replenishmentCandidates());

        $this->assertTrue($candidates->contains('customer_id', $repeat->id));
        $this->assertFalse($candidates->contains('customer_id', $oneOff->id));
    }

    public function test_seasonal_suggestion_shape(): void
    {
        $suggestion = app(WiperexInsightsService::class)->seasonalSuggestion();

        $this->assertArrayHasKey('season', $suggestion);
        $this->assertArrayHasKey('focus', $suggestion);
        $this->assertArrayHasKey('message', $suggestion);
    }

    public function test_draft_campaign_creation_is_idempotent(): void
    {
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Divisions\WiperexPanel::class)
            ->call('createDraftCampaign')
            ->call('createDraftCampaign');

        $this->assertDatabaseHas('marketing_campaigns', ['division' => 'wiperex', 'status' => 'planned']);
        $this->assertSame(1, \App\Models\MarketingCampaign::where('division', 'wiperex')->count());
    }

    public function test_wiperex_page_renders_workspace_and_panel(): void
    {
        $this->actingAs($this->user)
            ->get(route('wiperex.index'))
            ->assertOk()
            ->assertSee('Wiperex Intelligence')
            ->assertSee('Blade Size Mix');
    }
}