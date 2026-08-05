<?php

namespace Tests\Feature\Capture;

use App\Jobs\Capture\ProcessLeadCaptureJob;
use App\Models\LeadCaptureEvent;
use App\Models\User;
use App\Services\Capture\LeadCaptureService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class LeadCaptureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['noorhan.capture.async_processing' => false]);
        config(['noorhan.capture.meta_verify_token' => 'meta-token']);
        config(['noorhan.capture.whatsapp_verify_token' => 'wa-token']);
        config(['noorhan.capture.google_ads_shared_secret' => 'g-secret']);
    }

    public function test_web_form_captures_lead_with_utm(): void
    {
        $this->post(route('capture.web.store'), [
            'name' => 'Web Visitor',
            'phone' => '+971500000000',
            'division' => 'automotive',
            'vehicle_brand_category' => 'japanese',
            'utm_source' => 'google',
            'utm_campaign' => 'brake-pads',
            'landing_url' => 'https://noorhan.com/capture/lead?utm_source=google',
        ])->assertRedirect(route('capture.web.success'));

        $this->assertDatabaseHas('leads', ['name' => 'Web Visitor', 'source' => 'website', 'needs_triage' => false]);
        $this->assertDatabaseHas('leads', ['utm_campaign' => 'brake-pads']);
    }

    public function test_honeypot_silently_discards_bots(): void
    {
        $this->post(route('capture.web.store'), [
            'name' => 'Bot', 'division' => 'automotive', 'website' => 'http://spam.xyz',
        ]);

        $this->assertDatabaseCount('leads', 0);
        $this->assertDatabaseCount('lead_capture_events', 0);
    }

    public function test_facebook_handshake_returns_challenge(): void
    {
        $this->get('/api/capture/facebook/webhook?hub_mode=subscribe&hub_verify_token=meta-token&hub_challenge=42')
            ->assertOk();

        $this->get('/api/capture/facebook/webhook?hub_mode=subscribe&hub_verify_token=wrong&hub_challenge=42')
            ->assertForbidden();
    }

    public function test_facebook_leadgen_payload_creates_lead(): void
    {
        $this->postJson('/api/capture/facebook/webhook', [
            'object' => 'leadgen',
            'entry' => [[
                'changes' => [[
                    'value' => [
                        'form_id' => '123',
                        'field_data' => [
                            ['name' => 'full_name', 'values' => ['Meta Lead']],
                            ['name' => 'email', 'values' => ['meta@lead.com']],
                            ['name' => 'phone_number', 'values' => ['+971501111222']],
                        ],
                    ],
                ]],
            ]],
        ])->assertAccepted();

        $this->assertDatabaseHas('leads', ['name' => 'Meta Lead', 'source' => 'facebook_ads', 'email' => 'meta@lead.com']);
    }

    public function test_whatsapp_message_creates_triage_lead(): void
    {
        $this->postJson('/api/capture/whatsapp/webhook', [
            'entry' => [[
                'changes' => [[
                    'value' => [
                        'messages' => [[
                            'from' => '971501234567',
                            'type' => 'text',
                            'text' => ['body' => 'Do you have brake pads for Toyota Corolla 2020?'],
                        ]],
                        'contacts' => [['profile' => ['name' => 'Ahmed']]],
                    ],
                ]],
            ]],
        ])->assertAccepted();

        $this->assertDatabaseHas('leads', [
            'name' => 'Ahmed',
            'source' => 'whatsapp',
            'needs_triage' => true,
            'phone' => '+971501234567',
        ]);
    }

    public function test_whatsapp_status_callbacks_are_ignored(): void
    {
        $this->postJson('/api/capture/whatsapp/webhook', [
            'entry' => [['changes' => [['value' => ['statuses' => [['status' => 'delivered']]]]]]],
        ])->assertAccepted();

        $this->assertDatabaseCount('leads', 0);
    }

    public function test_google_webhook_requires_shared_secret(): void
    {
        $this->postJson('/api/capture/google/webhook', ['name' => 'G Lead'])->assertForbidden();

        $this->withHeader('X-Shared-Secret', 'g-secret')
            ->postJson('/api/capture/google/webhook', ['name' => 'G Lead', 'email' => 'g@ads.com'])
            ->assertAccepted();

        $this->assertDatabaseHas('leads', ['name' => 'G Lead', 'source' => 'google_ads']);
    }

    public function test_generic_api_requires_sanctum(): void
    {
        $this->postJson('/api/leads', ['name' => 'X'])->assertUnauthorized();

        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/leads', ['name' => 'API Lead', 'source' => 'manual'])
            ->assertCreated();

        $this->assertDatabaseHas('leads', ['name' => 'API Lead']);
    }

    public function test_business_card_upload_creates_triage_lead(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(\App\Livewire\Capture\BusinessCardUpload::class)
            ->call('openModal')
            ->set('file', \Illuminate\Http\UploadedFile::fake()->image('card.jpg'))
            ->set('note', 'Met at Automechanika Dubai')
            ->call('save');

        $this->assertDatabaseHas('leads', ['source' => 'business_card', 'needs_triage' => true]);
    }

    public function test_failed_processing_marks_event_failed(): void
    {
        $event = LeadCaptureEvent::create([
            'source' => 'website',
            'payload' => [], // missing name → normalizer throws
            'status' => 'received',
        ]);

        try {
            (new ProcessLeadCaptureJob($event))->handle(app(LeadCaptureService::class));
        } catch (\Throwable) {
            // expected rethrow
        }

        $this->assertSame('failed', $event->fresh()->status);
        $this->assertNotNull($event->fresh()->error);
    }

    public function test_async_mode_dispatches_job(): void
    {
        Queue::fake();
        config(['noorhan.capture.async_processing' => true]);

        app(LeadCaptureService::class)->ingest(\App\Enums\LeadSource::Website, ['name' => 'Async Lead']);

        Queue::assertPushed(ProcessLeadCaptureJob::class);
    }
}