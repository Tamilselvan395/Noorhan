<?php

namespace Tests\Feature\Marketing;

use App\Livewire\Marketing\CampaignShow;
use App\Livewire\Marketing\MarketingOverview;
use App\Models\Lead;
use App\Models\MarketingCampaign;
use App\Models\User;
use App\Models\WhatsAppCampaign;
use App\Services\Marketing\MarketingMetricsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MarketingTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_campaign_can_be_created_with_utm_slug_fallback(): void
    {
        Livewire::actingAs($this->user)
            ->test(MarketingOverview::class)
            ->call('openForm')
            ->set('name', 'Summer Brake Promo')
            ->set('channel', 'ads')
            ->set('utm_campaign', '')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('marketing_campaigns', [
            'name' => 'Summer Brake Promo',
            'utm_campaign' => 'summer-brake-promo',
        ]);
    }

    public function test_attribution_metrics_count_utm_leads(): void
    {
        $campaign = MarketingCampaign::factory()->create(['utm_campaign' => 'summer-promo', 'spent' => 100]);

        Lead::factory()->count(2)->create(['utm_campaign' => 'summer-promo', 'estimated_value' => 500]);
        Lead::factory()->create(['utm_campaign' => 'other', 'estimated_value' => 900]);

        $perf = app(MarketingMetricsService::class)->campaignPerformance($campaign);

        $this->assertSame(2, $perf['leads']);
        $this->assertEqualsWithDelta(1000.0, $perf['pipeline_value'], 0.01);
        $this->assertEqualsWithDelta(50.0, $perf['cost_per_lead'], 0.01);
    }

    public function test_whatsapp_delivery_rollup_in_performance(): void
    {
        $campaign = MarketingCampaign::factory()->create();

        WhatsAppCampaign::create([
            'marketing_campaign_id' => $campaign->id,
            'name' => 'Blast 1', 'message_type' => 'text', 'body' => 'Hi',
            'status' => 'sent', 'sent_count' => 40, 'failed_count' => 2,
        ]);

        $perf = app(MarketingMetricsService::class)->campaignPerformance($campaign);

        $this->assertSame(40, $perf['wa_sent']);
        $this->assertSame(2, $perf['wa_failed']);
    }

    public function test_leads_by_source_aggregation(): void
    {
        Lead::factory()->count(3)->create(['source' => 'facebook_ads']);
        Lead::factory()->create(['source' => 'walk_in']);

        $sources = app(MarketingMetricsService::class)->leadsBySource();

        $this->assertSame('Facebook Ads', $sources[0]['name']);
        $this->assertSame(3, $sources[0]['value']);
    }

    public function test_marketing_pages_render(): void
    {
        $campaign = MarketingCampaign::factory()->create();

        $this->actingAs($this->user)->get(route('marketing.index'))->assertOk();
        $this->actingAs($this->user)->get(route('marketing.show', $campaign))->assertOk();
    }
}