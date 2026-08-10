<?php

namespace Tests\Feature\Ai;

use App\Actions\SalesOrders\CreateSalesOrderAction;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Product;
use App\Models\User;
use App\Services\Ai\ChurnPredictionService;
use App\Services\Ai\CustomerHealthService;
use App\Services\Ai\DailyBriefingService;
use App\Services\Ai\LeadScoringService;
use App\Services\Ai\NaturalLanguageSearchService;
use App\Services\Ai\RecommendationService;
use App\Services\Ai\SalesForecastService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiEngineTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_lead_scoring_rewards_complete_high_value_leads(): void
    {
        $strong = Lead::factory()->create([
            'estimated_value' => 15000, 'source' => 'exhibition', 'email' => 'a@b.com',
            'phone' => '123', 'company_name' => 'Co', 'vehicle_brand_category' => 'japanese',
            'customer_type' => 'garage', 'needs_triage' => false,
        ]);

        $weak = Lead::factory()->create([
            'estimated_value' => 0, 'source' => 'manual', 'email' => null,
            'phone' => null, 'company_name' => null, 'vehicle_brand_category' => 'unknown',
            'needs_triage' => true,
        ]);

        $service = app(LeadScoringService::class);

        $strongScore = $service->score($strong);
        $weakScore = $service->score($weak);

        $this->assertGreaterThan($weakScore, $strongScore);
        $this->assertGreaterThanOrEqual(80, $strongScore);
        $this->assertLessThanOrEqual(100, $strongScore);
    }

    public function test_customer_health_and_churn_inverse(): void
    {
        $active = Customer::factory()->create(['last_activity_at' => now()->subDays(5)]);
        $dormant = Customer::factory()->create(['last_activity_at' => now()->subDays(300)]);

        $health = app(CustomerHealthService::class);
        $churn = app(ChurnPredictionService::class);

        $this->assertGreaterThan($health->score($dormant), $health->score($active));

        $risk = $churn->predict($dormant);
        $this->assertSame('high', $risk['level']);
        $this->assertNotEmpty($risk['reasons']);
    }

    public function test_recommendations_use_co_occurrence(): void
    {
        $customer = Customer::factory()->create();
        [$brake, $oil, $wiper] = Product::factory()->count(3)->create();

        // Order 1: customer buys brake. Order 2 (other customer): brake + oil together.
        $this->orderWith($customer, [$brake]);
        $other = Customer::factory()->create();
        $this->orderWith($other, [$brake, $oil]);

        $recs = app(RecommendationService::class)->forCustomer($customer)->pluck('product.id');

        $this->assertTrue($recs->contains($oil->id), 'Co-occurred product must be recommended');
        $this->assertFalse($recs->contains($brake->id), 'Already-bought product excluded');
    }

    private function orderWith(Customer $customer, array $products): void
    {
        app(CreateSalesOrderAction::class)->execute(
            ['customer_id' => $customer->id, 'division' => 'automotive', 'status' => 'confirmed', 'tax_rate' => 5],
            collect($products)->map(fn ($p) => [
                'product_id' => $p->id, 'description' => $p->name, 'quantity' => 2,
                'unit_price' => (float) $p->sale_price, 'cost_price' => (float) $p->cost_price, 'discount_percent' => 0,
            ])->all(),
            $this->user,
        );
    }

    public function test_sales_forecast_returns_weighted_average(): void
    {
        $forecast = app(SalesForecastService::class)->nextMonth();

        $this->assertArrayHasKey('forecast', $forecast);
        $this->assertCount(6, $forecast['history']);
        $this->assertContains($forecast['trend'], ['up', 'down']);
    }

    public function test_natural_language_search_parses_overdue_invoices(): void
    {
        $results = app(NaturalLanguageSearchService::class)->search('show me overdue invoices');

        $this->assertContains('overdue invoices', $results['parsed']);
        $this->assertIsArray($results['invoices']);
    }

    public function test_daily_briefing_persists_and_reuses(): void
    {
        $brief = app(DailyBriefingService::class)->today();

        $this->assertDatabaseHas('ai_briefings', ['briefing_date' => today()->toDateString()]);
        $this->assertArrayHasKey('top_leads', $brief);
        $this->assertArrayHasKey('sales_forecast', $brief);
    }

    public function test_compute_scores_command_persists_ai_scores(): void
    {
        Lead::factory()->create();
        Customer::factory()->create();

        $this->artisan('ai:compute-scores')->assertSuccessful();

        $this->assertDatabaseHas('ai_scores', ['score_type' => 'lead_score']);
        $this->assertDatabaseHas('ai_scores', ['score_type' => 'health_score']);
        $this->assertDatabaseHas('ai_scores', ['score_type' => 'churn_risk']);
    }

    public function test_ai_dashboard_renders(): void
    {
        $this->actingAs($this->user)->get(route('ai.index'))->assertOk()->assertSee('AI Engine');
    }
}