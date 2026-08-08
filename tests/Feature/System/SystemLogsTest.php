<?php

namespace Tests\Feature\System;

use App\Livewire\System\AuditExplorer;
use App\Livewire\System\ActivityFeed;
use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\User;
use App\Helpers\AuditDiffHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SystemLogsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_activity_feed_filters_by_user_and_search(): void
    {
        $other = User::factory()->create();

        Customer::factory()->create(); // generates activity via creator? none — log manually:
        $customer = Customer::factory()->create();
        $customer->logActivity('updated the customer record');

        $otherCustomer = Customer::factory()->create();
        // simulate other user's activity
        auth()->setUser($other);
        $otherCustomer->logActivity('deleted something else');

        Livewire::actingAs($this->user)
            ->test(ActivityFeed::class)
            ->set('search', 'updated the customer')
            ->assertSee('updated the customer record')
            ->assertDontSee('deleted something else');
    }

    public function test_audit_diff_shows_old_and_new_values(): void
    {
        $customer = Customer::factory()->create(['name' => 'Before Name']);
        $customer->update(['name' => 'After Name']);

        $log = AuditLog::query()->where('event', 'updated')->latest('id')->first();

        $changes = AuditDiffHelper::changes($log);

        $this->assertArrayHasKey('name', $changes);
        $this->assertSame('Before Name', $changes['name']['old']);
        $this->assertSame('After Name', $changes['name']['new']);
    }

    public function test_audit_explorer_renders_and_expands(): void
    {
        $customer = Customer::factory()->create();
        $customer->update(['city' => 'Dubai']);

        Livewire::actingAs($this->user)
            ->test(AuditExplorer::class)
            ->set('event', 'updated')
            ->assertSee('Customer')
            ->call('toggle', AuditLog::latest('id')->first()->id)
            ->assertSee('city');
    }

    public function test_sensitive_fields_never_enter_audit(): void
    {
        $user = User::factory()->create();
        $user->update(['password' => bcrypt('NewPass@123'), 'name' => 'Renamed']);

        $log = AuditLog::query()->where('auditable_type', User::class)->where('event', 'updated')->latest('id')->first();

        $this->assertArrayNotHasKey('password', $log->new_values ?? []);
        $this->assertArrayHasKey('name', $log->new_values ?? []);
    }

    public function test_csv_exports_stream(): void
    {
        Customer::factory()->create()->update(['city' => 'Sharjah']);

        $this->actingAs($this->user)->get(route('system.activity.export'))->assertOk();

        $audit = $this->actingAs($this->user)->get(route('system.audit.export'));
        $audit->assertOk();
        $this->assertStringContainsString('text/csv', $audit->headers->get('content-type'));
    }

    public function test_prune_command_respects_retention(): void
    {
        AuditLog::create([
            'auditable_type' => Customer::class, 'auditable_id' => 1, 'event' => 'updated',
            'old_values' => [], 'new_values' => [], 'created_at' => now()->subDays(400),
        ]);
        AuditLog::create([
            'auditable_type' => Customer::class, 'auditable_id' => 2, 'event' => 'updated',
            'old_values' => [], 'new_values' => [],
        ]);

        $this->artisan('system:prune-logs')->assertSuccessful();

        $this->assertSame(1, AuditLog::count()); // only the recent one survives
    }

    public function test_pages_render(): void
    {
        $this->actingAs($this->user)->get(route('system.activity'))->assertOk();
        $this->actingAs($this->user)->get(route('system.audit'))->assertOk();
    }
}