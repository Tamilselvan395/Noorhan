<?php

namespace Tests\Feature\System;

use App\Actions\Invoices\CreateInvoiceFromOrderAction;
use App\Actions\Quotations\CreateQuotationAction;
use App\Actions\SalesOrders\CreateSalesOrderAction;
use App\Jobs\Accounting\ProcessZohoSyncJob;
use App\Livewire\System\SchedulerPanel;
use App\Models\Customer;
use App\Models\Quotation;
use App\Models\SchedulerLog;
use App\Models\User;
use App\Models\ZohoSyncLog;
use App\Services\System\TaskLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Tests\TestCase;

class SchedulerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_expire_quotations_command_expires_stale_quotations(): void
    {
        $quotation = app(CreateQuotationAction::class)->execute(
            ['division' => 'automotive', 'status' => 'sent', 'tax_rate' => 5, 'valid_until' => now()->subDays(2)->toDateString()],
            [['product_id' => null, 'description' => 'X', 'quantity' => 1, 'unit_price' => 10, 'cost_price' => 5, 'discount_percent' => 0]],
            $this->user,
        );

        $fresh = app(CreateQuotationAction::class)->execute(
            ['division' => 'automotive', 'status' => 'sent', 'tax_rate' => 5, 'valid_until' => now()->addDays(10)->toDateString()],
            [['product_id' => null, 'description' => 'Y', 'quantity' => 1, 'unit_price' => 10, 'cost_price' => 5, 'discount_percent' => 0]],
            $this->user,
        );

        $this->artisan('quotations:expire')->assertSuccessful();

        $this->assertSame('expired', $quotation->fresh()->status);
        $this->assertSame('sent', $fresh->fresh()->status);
    }

    public function test_zoho_retry_requeues_failed_logs_only(): void
    {
        Queue::fake();

        $retryable = ZohoSyncLog::create(['entity_type' => 'Customer', 'operation' => 'create', 'status' => 'failed', 'attempts' => 3]);
        ZohoSyncLog::create(['entity_type' => 'Customer', 'operation' => 'create', 'status' => 'failed', 'attempts' => 5]); // exhausted
        ZohoSyncLog::create(['entity_type' => 'Customer', 'operation' => 'create', 'status' => 'success', 'attempts' => 1]);

        $this->artisan('zoho:retry-failed')->assertSuccessful();

        Queue::assertPushed(ProcessZohoSyncJob::class, 1);
    }

    public function test_task_logger_records_manual_runs(): void
    {
        TaskLogger::log('Expire Quotations', 'success', 'Expired 0 quotation(s).', 'manual');

        $this->assertDatabaseHas('scheduler_logs', [
            'task' => 'Expire Quotations',
            'status' => 'success',
            'trigger' => 'manual',
        ]);
    }

    public function test_scheduler_panel_lists_tasks_and_runs_now(): void
    {
        Livewire::actingAs($this->user)
            ->test(SchedulerPanel::class)
            ->assertSee('Notification Digest')
            ->assertSee('Expire Quotations')
            ->call('run', 'qtn_expire');

        $this->assertDatabaseHas('scheduler_logs', ['task' => 'Expire Quotations', 'trigger' => 'manual']);
    }

    public function test_scheduler_page_renders(): void
    {
        $this->actingAs($this->user)->get(route('system.scheduler'))
            ->assertOk()
            ->assertSee('Task Scheduler');
    }

    public function test_manifest_matches_console_registrations(): void
    {
        $manifest = collect(config('noorhan.scheduler.tasks'))->pluck('command');

        foreach (['notifications:digest', 'whatsapp:automations', 'whatsapp:campaigns', 'quotations:expire', 'zoho:retry-failed', 'system:prune-logs'] as $command) {
            $this->assertTrue($manifest->contains($command), "Manifest missing {$command}");
        }
    }
}