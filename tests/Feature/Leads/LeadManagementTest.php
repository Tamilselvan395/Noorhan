<?php

namespace Tests\Feature\Leads;

use App\Models\Lead;
use App\Models\User;
use App\Livewire\Leads\LeadForm;
use App\Livewire\Leads\LeadIndex;
use App\Livewire\Leads\LeadKanban;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LeadManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_lead_can_be_created(): void
    {
        Livewire::actingAs($this->user)
            ->test(LeadForm::class)
            ->call('openForm')
            ->set('name', 'Ahmed Khan')
            ->set('company_name', 'Auto Zone Garage')
            ->set('division', 'automotive')
            ->set('source', 'walk_in')
            ->set('vehicle_brand_category', 'japanese')
            ->set('priority', 'high')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('leads', ['name' => 'Ahmed Khan', 'status' => 'new', 'needs_triage' => false]);
    }

    public function test_unknown_brand_routes_to_triage(): void
    {
        Livewire::actingAs($this->user)
            ->test(LeadForm::class)
            ->call('openForm')
            ->set('name', 'Unknown Enquiry')
            ->set('division', 'automotive')
            ->set('source', 'website')
            ->call('save');

        $this->assertDatabaseHas('leads', ['name' => 'Unknown Enquiry', 'needs_triage' => true]);
    }

    public function test_name_is_required(): void
    {
        Livewire::actingAs($this->user)
            ->test(LeadForm::class)
            ->call('openForm')
            ->call('save')
            ->assertHasErrors(['name']);
    }

    public function test_index_filters_by_search(): void
    {
        Lead::factory()->create(['name' => 'Findable Person']);
        Lead::factory()->create(['name' => 'Other Person']);

        Livewire::actingAs($this->user)
            ->test(LeadIndex::class)
            ->set('search', 'Findable')
            ->assertSee('Findable Person')
            ->assertDontSee('Other Person');
    }

    public function test_kanban_move_respects_policy(): void
    {
        $other = User::factory()->create();
        $lead = Lead::factory()->create(['assigned_to' => $other->id, 'created_by' => $other->id]);

        $this->expectException(\Illuminate\Auth\Access\AuthorizationException::class);

        Livewire::actingAs($this->user)
            ->test(LeadKanban::class)
            ->call('move', $lead->id, 'contacted');
    }

    public function test_kanban_move_updates_stage(): void
    {
        $lead = Lead::factory()->create(['assigned_to' => $this->user->id]);

        Livewire::actingAs($this->user)
            ->test(LeadKanban::class)
            ->call('move', $lead->id, 'contacted');

        $this->assertSame('contacted', $lead->fresh()->status);
    }
}