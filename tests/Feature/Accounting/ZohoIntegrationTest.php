<?php

namespace Tests\Feature\Accounting;

use App\Actions\Invoices\CreateInvoiceFromOrderAction;
use App\Actions\Invoices\SendInvoiceAction;
use App\Actions\SalesOrders\CreateSalesOrderAction;
use App\Enums\CommunicationChannel;
use App\Jobs\Accounting\ProcessZohoSyncJob;
use App\Jobs\Accounting\ProcessZohoWebhookJob;
use App\Models\Customer;
use App\Models\User;
use App\Models\ZohoConnection;
use App\Models\ZohoSyncLog;
use App\Models\ZohoWebhookEvent;
use App\Services\Accounting\SyncCustomerToZoho;
use App\Services\Accounting\SyncInvoiceToZoho;
use App\Services\Accounting\ZohoSyncDispatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ZohoIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();

        config(['zoho.enabled' => true]);
        config(['zoho.organization_id' => 'ORG123']);

        ZohoConnection::create([
            'organization_id' => 'ORG123',
            'client_id' => 'client',
            'client_secret_cipher' => Crypt::encryptString('secret'),
            'refresh_token_cipher' => Crypt::encryptString('refresh'),
            'access_token_cipher' => Crypt::encryptString('valid-token'),
            'token_expires_at' => now()->addHour(),
            'settings' => ['sync_customers' => true, 'sync_invoices' => true],
        ]);
    }

    public function test_customer_sync_creates_contact_and_stores_zoho_id(): void
    {
        Http::fake([
            'www.zohoapis.com/*' => Http::response(['contact' => ['contact_id' => 'ZC-1']], 200),
        ]);

        $customer = Customer::factory()->create();

        $zohoId = app(SyncCustomerToZoho::class)->execute($customer);

        $this->assertSame('ZC-1', $zohoId);
        $this->assertSame('ZC-1', $customer->fresh()->zoho_id);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/contacts')
                && str_contains($request['JSONString'], $customer->name);
        });
    }

    public function test_expired_token_is_refreshed_automatically(): void
    {
        $connection = ZohoConnection::first();
        $connection->update(['token_expires_at' => now()->subMinute()]);

        Http::fake([
            'accounts.zoho.com/oauth/v2/token' => Http::response([
                'access_token' => 'new-token', 'expires_in' => 3600,
            ]),
            'www.zohoapis.com/*' => Http::response(['contact' => ['contact_id' => 'ZC-2']], 200),
        ]);

        $customer = Customer::factory()->create();

        app(SyncCustomerToZoho::class)->execute($customer);

        Http::assertSentCount(2); // refresh + contact create
        $this->assertNotNull($connection->fresh()->token_expires_at);
        $this->assertTrue($connection->fresh()->token_expires_at->isFuture());
    }

    public function test_dispatcher_queues_sync_job_and_respects_toggles(): void
    {
        Queue::fake();

        $customer = Customer::factory()->create();

        app(ZohoSyncDispatcher::class)->queue($customer);

        Queue::assertPushed(ProcessZohoSyncJob::class);
        $this->assertDatabaseHas('zoho_sync_logs', ['entity_type' => 'Customer', 'status' => 'pending']);
    }

    public function test_dispatcher_is_silent_when_disabled(): void
    {
        config(['zoho.enabled' => false]);
        Queue::fake();

        app(ZohoSyncDispatcher::class)->queue(Customer::factory()->create());

        Queue::assertNothingPushed();
        $this->assertDatabaseCount('zoho_sync_logs', 0);
    }

    public function test_failed_sync_is_logged_and_retriable(): void
    {
        Http::fake(['www.zohoapis.com/*' => Http::response(['message' => 'rate limit'], 429)]);

        $customer = Customer::factory()->create();
        $log = app(ZohoSyncDispatcher::class)->queue($customer);

        $job = new ProcessZohoSyncJob($log);

        try {
            $job->handle();
        } catch (\Throwable) {
            $job->failed(new \App\Exceptions\ZohoApiException('rate limit'));
        }

        $this->assertSame('failed', $log->fresh()->status);
        $this->assertSame(1, $log->fresh()->attempts);
        $this->assertNotNull($log->fresh()->error);
    }

    public function test_invoice_sync_requires_customer_zoho_id(): void
    {
        $order = app(CreateSalesOrderAction::class)->execute([
            'customer_id' => Customer::factory()->create()->id,
            'division' => 'automotive', 'status' => 'confirmed', 'tax_rate' => 5,
        ], [['product_id' => null, 'description' => 'X', 'quantity' => 1, 'unit_price' => 10, 'cost_price' => 5, 'discount_percent' => 0]], $this->user);

        $invoice = app(CreateInvoiceFromOrderAction::class)->execute($order);

        $this->expectException(\RuntimeException::class);

        app(SyncInvoiceToZoho::class)->execute($invoice);
    }

    public function test_webhook_paid_invoice_applies_local_payment(): void
    {
        // Customer synced, invoice sent with zoho id
        $customer = Customer::factory()->create(['zoho_id' => 'ZC-9']);

        $order = app(CreateSalesOrderAction::class)->execute([
            'customer_id' => $customer->id, 'division' => 'automotive',
            'status' => 'confirmed', 'tax_rate' => 5,
        ], [['product_id' => null, 'description' => 'X', 'quantity' => 1, 'unit_price' => 100, 'cost_price' => 50, 'discount_percent' => 0]], $this->user);

        $invoice = app(CreateInvoiceFromOrderAction::class)->execute($order);
        $invoice->update(['zoho_id' => 'ZINV-77', 'status' => 'sent']);

        config(['zoho.webhook_secret' => 'whsec']);

        // Bad secret rejected
        $this->postJson('/api/zoho/webhook?secret=wrong', ['event' => 'invoice.status'])->assertForbidden();

        // Valid webhook
        $this->postJson('/api/zoho/webhook?secret=whsec', [
            'event' => 'invoice.status',
            'data' => ['invoice_id' => 'ZINV-77', 'status' => 'paid'],
        ])->assertAccepted();

        $event = ZohoWebhookEvent::latest('id')->first();
        (new ProcessZohoWebhookJob($event))->handle();

        $this->assertSame('paid', $invoice->fresh()->status);
        $this->assertEqualsWithDelta(0.0, (float) $invoice->fresh()->balance_due, 0.01);
    }
}