<?php

namespace Tests\Feature\Leads;

use App\Actions\Leads\AssignLeadAction;
use App\Actions\Leads\MoveLeadStageAction;
use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Models\User;
use App\Services\Leads\LeadPipelineService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadPipelineTest extends TestCase
{
    use RefreshDatabase;

    private MoveLeadStageAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = app(MoveLeadStageAction::class);
    }

    public function test_valid_transition_succeeds(): void
    {
        $lead = Lead::factory()->create();

        $this->action->execute($lead, LeadStatus::Qualified);

        $this->assertSame('qualified', $lead->fresh()->status);
    }

    public function test_invalid_transition_is_blocked(): void
    {
        $lead = Lead::factory()->create(); // status: new

        $this->expectException(DomainException::class);

        $this->action->execute($lead, LeadStatus::Won);
    }

    public function test_lost_stores_reason_and_closes(): void
    {
        $lead = Lead::factory()->create();

        $this->action->execute($lead, LeadStatus::Lost, 'Went with competitor');

        $fresh = $lead->fresh();
        $this->assertSame('lost', $fresh->status);
        $this->assertSame('Went with competitor', $fresh->lost_reason);
        $this->assertNotNull($fresh->closed_at);
    }

    public function test_lost_lead_can_be_reopened(): void
    {
        $lead = Lead::factory()->create(['status' => 'lost', 'closed_at' => now()]);

        $this->action->execute($lead, LeadStatus::New);

        $this->assertSame('new', $lead->fresh()->status);
        $this->assertNull($lead->fresh()->closed_at);
    }

    public function test_assignment_sends_notification(): void
    {
        $assignee = User::factory()->create();
        $manager = User::factory()->create();
        $lead = Lead::factory()->create();

        app(AssignLeadAction::class)->execute($lead, $assignee, $manager);

        $this->assertDatabaseHas('notifications', ['notifiable_id' => $assignee->id]);
        $this->assertSame($assignee->id, $lead->fresh()->assigned_to);
    }

    public function test_pipeline_stats_are_computed(): void
    {
        Lead::factory()->count(2)->create(['estimated_value' => 1000]);
        Lead::factory()->create(['status' => 'won', 'closed_at' => now()]);

        $stats = app(LeadPipelineService::class)->stats();

        $this->assertSame(2, $stats['open_count']);
        $this->assertEqualsWithDelta(2000.0, $stats['pipeline_value'], 0.01);
        $this->assertSame(1, $stats['won_this_month']);
        $this->assertSame(100.0, $stats['conversion_rate']);
    }
}