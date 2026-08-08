<?php

namespace Tests\Feature\WhatsApp;

use App\Actions\Invoices\CreateInvoiceFromOrderAction;
use App\Actions\SalesOrders\CreateSalesOrderAction;
use App\Events\Customers\CustomerCreated;
use App\Events\WhatsApp\WhatsAppMessageReceived;
use App\Jobs\WhatsApp\ProcessCampaignJob;
use App\Jobs\WhatsApp\SendCampaignMessageJob;
use App\Models\Customer;
use App\Models\User;
use App\Models\WhatsAppCampaign;
use App\Services\WhatsApp\WhatsAppMessenger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatsAppCrmTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['whatsapp.enabled' => true]);
        config(['whatsapp.access_token' => 'token']);
        config(['whatsapp.phone_number_id' => '12345']);

        Http::fake(['graph.facebook.com/*' => Http::response(['messages' => [['id' => 'wamid.1']]], 200)]);
    }

    public function test_text_message_is_sent_and_logged(): void
    {
        $customer = Customer::factory()->create(['whatsapp' => '+971 50 123 4567']);

        app(WhatsAppMessenger::class)->sendText($customer, 'Hello from Noorhan!', 'test');

        Http::assertSent(fn ($request) => $request['to'] === '971501234567' && $request['type'] === 'text');

        $this->assertDatabaseHas('communications', [
            'communicable_type' => Customer::class,
            'communicable_id' => $customer->id,
            'channel' => 'whatsapp',
            'direction' => 'outbound',
        ]);

        $this->assertNotNull($customer->fresh()->whatsapp_last_messaged_at);
    }

    public function test_opted_out_customers_are_skipped(): void
    {
        $customer = Customer::factory()->create(['whatsapp' => '+971501234567', 'whatsapp_opted_out' => true]);

        $result = app(WhatsAppMessenger::class)->sendText($customer, 'Should not send');

        $this->assertNull($result);
        Http::assertNothingSent();
    }

    public function test_welcome_automation_fires_on_customer_creation(): void
    {
        $customer = Customer::factory()->make(['whatsapp' => '+971501234567']);
        $customer->save();

        event(new CustomerCreated($customer));

        Http::assertSent(fn ($request) => $request['type'] === 'template');
    }

    public function test_stop_keyword_opts_customer_out(): void
    {
        $customer = Customer::factory()->create(['whatsapp' => '+971501234567']);

        event(new WhatsAppMessageReceived('971501234567', 'Please STOP messaging me'));

        $this->assertTrue($customer->fresh()->whatsapp_opted_out);
        $this->assertDatabaseHas('communications', ['direction' => 'inbound', 'channel' => 'whatsapp']);
    }

    public function test_campaign_broadcast_delivers_to_audience(): void
    {
        $user = User::factory()->create();
        Customer::factory()->count(2)->create(['whatsapp' => '+971501111111']);
        Customer::factory()->create(['whatsapp' => null]); // excluded (no number)

        $campaign = WhatsAppCampaign::create([
            'name' => 'Ramadan Promo', 'audience_type' => 'all', 'message_type' => 'text',
            'body' => 'Eid offer inside!', 'status' => 'scheduled', 'created_by' => $user->id,
        ]);

        (new ProcessCampaignJob($campaign))->handle();

        $this->assertSame(2, $campaign->recipients()->count());

        foreach ($campaign->recipients as $recipient) {
            (new SendCampaignMessageJob($recipient))->handle(app(WhatsAppMessenger::class));
        }

        $this->assertSame(2, (int) $campaign->fresh()->sent_count);
        $this->assertDatabaseHas('whatsapp_campaign_recipients', ['status' => 'sent']);
    }

    public function test_payment_reminder_command_sends_and_dedupes(): void
    {
        $user = User::factory()->create();
        $customer = Customer::factory()->create(['whatsapp' => '+971501234567']);

        $order = app(CreateSalesOrderAction::class)->execute([
            'customer_id' => $customer->id, 'division' => 'automotive', 'status' => 'confirmed', 'tax_rate' => 5,
        ], [['product_id' => null, 'description' => 'X', 'quantity' => 1, 'unit_price' => 100, 'cost_price' => 50, 'discount_percent' => 0]], $user);

        $invoice = app(CreateInvoiceFromOrderAction::class)->execute($order);
        $invoice->update(['status' => 'sent', 'due_date' => now()->subDay()]);

        $this->artisan('whatsapp:automations')->assertSuccessful();

        Http::assertSent(fn ($r) => $r['type'] === 'template');

        // Second run same day: deduped (no additional sends)
        Http::fake(['graph.facebook.com/*' => Http::response(['messages' => [['id' => 'wamid.2']]], 200)]);
        $this->artisan('whatsapp:automations')->assertSuccessful();
        Http::assertNothingSent();
    }
}