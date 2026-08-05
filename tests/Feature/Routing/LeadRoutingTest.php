<?php

namespace Tests\Feature\Routing;

use App\Actions\Leads\CreateLeadAction;
use App\Actions\Routing\RouteLeadAction;
use App\DTOs\Leads\LeadDTO;
use App\Enums\LeadSource;
use App\Enums\RoutingOutcome;
use App\Livewire\Leads\TriageQueue;
use App\Livewire\Routing\RulesManager;
use App\Models\Lead;
use App\Models\LeadRoutingRule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LeadRoutingTest extends TestCase
{
    use RefreshDatabase;

    public function test_japanese_brand_routes_to_configured_rep(): void
    {
        $rep = User::factory()->create();

        LeadRoutingRule::create([
            'division' => 'automotive', 'condition_type' => 'vehicle_brand',
            'condition_value' => 'japanese', 'user_id' => $rep->id, 'priority' => 100,
        ]);

        $lead = Lead::factory()->create(['vehicle_brand_category' => 'japanese', 'assigned_to' => null]);

        app(RouteLeadAction::class)->execute($lead, applyAi: false);

        $this->assertSame($rep->id, $lead->fresh()->assigned_to);
        $this->assertDatabaseHas('lead_routing_logs', ['lead_id' => $lead->id, 'outcome' => RoutingOutcome::RuleMatch->value]);
    }

    public function test_manufacturing_distributor_routes_to_distributor_manager(): void
    {
        $manager = User::factory()->create();

        LeadRoutingRule::create([
            'division' => 'swiftec', 'condition_type' => 'customer_type',
            'condition_value' => 'distributor', 'user_id' => $manager->id, 'priority' => 100,
        ]);

        $lead = Lead::factory()->create([
            'division' => 'swiftec', 'customer_type' => 'distributor',
            'vehicle_brand_category' => null, 'assigned_to' => null,
        ]);

        app(RouteLeadAction::class)->execute($lead, applyAi: false);

        $this->assertSame($manager->id, $lead->fresh()->assigned_to);
    }

    public function test_unmatched_lead_stays_in_triage(): void
    {
        $lead = Lead::factory()->create(['vehicle_brand_category' => 'unknown', 'needs_triage' => true]);

        app(RouteLeadAction::class)->execute($lead, applyAi: false);

        $this->assertNull($lead->fresh()->assigned_to);
        $this->assertTrue($lead->fresh()->needs_triage);
        $this->assertDatabaseHas('lead_routing_logs', ['lead_id' => $lead->id, 'outcome' => RoutingOutcome::Triage->value]);
    }

    public function test_keyword_classifier_extracts_brand_and_intent(): void
    {
        $lead = Lead::factory()->make([
            'subject' => 'Engine oil enquiry',
            'requirements' => 'We need engine oil and grease for our Toyota fleet. We are a distributor.',
        ]);

        $result = app(\App\Contracts\LeadClassifierInterface::class)->classify($lead);

        $this->assertSame('japanese', $result->vehicle_brand_category);
        $this->assertSame('swiftec', $result->division);
        $this->assertSame('distributor', $result->customer_type);
        $this->assertSame(0.9, $result->confidence);
    }

    public function test_ai_apply_and_route_from_triage(): void
    {
        $rep = User::factory()->create();

        LeadRoutingRule::create([
            'division' => 'automotive', 'condition_type' => 'vehicle_brand',
            'condition_value' => 'korean', 'user_id' => $rep->id, 'priority' => 100,
        ]);

        $lead = Lead::factory()->create([
            'vehicle_brand_category' => null,
            'needs_triage' => true,
            'requirements' => 'Looking for Hyundai brake pads please.',
        ]);

        Livewire::actingAs($lead->creator)
            ->test(TriageQueue::class)
            ->call('applyAndRoute', $lead->id);

        $fresh = $lead->fresh();
        $this->assertSame('korean', $fresh->vehicle_brand_category);
        $this->assertSame($rep->id, $fresh->assigned_to);
        $this->assertFalse($fresh->needs_triage);
    }

    public function test_auto_routing_on_lead_creation(): void
    {
        config(['noorhan.routing.auto_route_on_create' => true]);

        $rep = User::factory()->create();
        $creator = User::factory()->create();

        LeadRoutingRule::create([
            'division' => 'automotive', 'condition_type' => 'vehicle_brand',
            'condition_value' => 'european', 'user_id' => $rep->id, 'priority' => 100,
        ]);

        $lead = app(CreateLeadAction::class)->execute(new LeadDTO(
            name: 'Auto Route Me',
            source: LeadSource::Website->value,
            vehicle_brand_category: 'european',
        ), $creator);

        $this->assertSame($rep->id, $lead->fresh()->assigned_to);
    }

    public function test_rules_crud_via_livewire(): void
    {
        $user = User::factory()->create();
        $rep = User::factory()->create();

        Livewire::actingAs($user)
            ->test(RulesManager::class)
            ->call('openForm')
            ->set('division', 'automotive')
            ->set('condition_type', 'vehicle_brand')
            ->set('condition_value', 'american')
            ->set('user_id', $rep->id)
            ->set('priority', 50)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('lead_routing_rules', ['condition_value' => 'american', 'priority' => 50]);
    }

    public function test_priority_ordering_wins(): void
    {
        $repA = User::factory()->create();
        $repB = User::factory()->create();

        LeadRoutingRule::create(['division' => 'automotive', 'condition_type' => 'vehicle_brand', 'condition_value' => 'japanese', 'user_id' => $repA->id, 'priority' => 10]);
        LeadRoutingRule::create(['division' => 'automotive', 'condition_type' => 'vehicle_brand', 'condition_value' => 'japanese', 'user_id' => $repB->id, 'priority' => 200]);

        $lead = Lead::factory()->create(['vehicle_brand_category' => 'japanese']);

        app(RouteLeadAction::class)->execute($lead, applyAi: false);

        $this->assertSame($repB->id, $lead->fresh()->assigned_to);
    }
}