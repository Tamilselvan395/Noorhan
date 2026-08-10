<?php

namespace Tests\Feature\Communications;

use App\Actions\Communications\SendTemplatedEmailAction;
use App\Livewire\Communications\CommunicationCenter;
use App\Livewire\Communications\ComposeEmail;
use App\Livewire\Communications\TemplateManager;
use App\Models\Customer;
use App\Models\EmailTemplate;
use App\Models\Lead;
use App\Models\User;
use App\Services\Communications\TemplateRendererService;
use Database\Seeders\EmailTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class CommunicationCenterTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->seed(EmailTemplateSeeder::class);
    }

    public function test_renderer_substitutes_nested_variables(): void
    {
        $template = EmailTemplate::where('key', 'welcome_email')->first();

        $rendered = app(TemplateRendererService::class)->render($template, [
            'customer' => ['name' => 'Karim'],
            'company' => ['name' => 'Noorhan Group'],
        ]);

        $this->assertStringContainsString('Karim', $rendered['subject']);
        $this->assertStringContainsString('Noorhan Group', $rendered['body']);
        $this->assertStringNotContainsString('{{customer.name}}', $rendered['body']);
    }

    public function test_send_action_sends_and_logs(): void
    {
        Mail::fake();
        $customer = Customer::factory()->create(['email' => 'a@b.com']);
        $template = EmailTemplate::where('key', 'welcome_email')->first();

        $sent = app(SendTemplatedEmailAction::class)->execute(
            $customer->email, $template,
            ['customer' => ['name' => $customer->name], 'company' => ['name' => 'X']],
            $customer, $customer, $this->user,
        );

        $this->assertTrue($sent);
        Mail::assertSent(\App\Mail\TemplatedEmail::class);
        $this->assertDatabaseHas('communications', [
            'communicable_id' => $customer->id, 'channel' => 'email', 'direction' => 'outbound',
        ]);
    }

    public function test_opted_out_customers_are_skipped(): void
    {
        Mail::fake();
        $customer = Customer::factory()->create(['email' => 'a@b.com', 'email_opted_out' => true]);
        $template = EmailTemplate::where('key', 'dormant_winback')->first();

        $sent = app(SendTemplatedEmailAction::class)->execute($customer->email, $template, [], $customer);

        $this->assertFalse($sent);
        Mail::assertNothingSent();
    }

    public function test_unsubscribe_sets_opt_out_with_valid_token_only(): void
    {
        $customer = Customer::factory()->create(['email' => 'a@b.com']);
        $token = sha1($customer->id.$customer->email.config('app.key'));

        $this->get(route('unsubscribe', [$customer->id, 'wrong']))->assertForbidden();

        $this->get(route('unsubscribe', [$customer->id, $token]))->assertOk();
        $this->assertTrue($customer->fresh()->email_opted_out);
    }

    public function test_compose_with_template_previews_and_sends(): void
    {
        Mail::fake();
        $customer = Customer::factory()->create(['email' => 'x@y.com']);

        Livewire::actingAs($this->user)
            ->test(ComposeEmail::class)
            ->set('entityType', 'customer')
            ->set('entityId', $customer->id)
            ->set('templateKey', 'welcome_email')
            ->assertNotSet('subject', '')           // preview filled subject
            ->call('send')
            ->assertHasNoErrors();

        Mail::assertSent(\App\Mail\TemplatedEmail::class);
    }

    public function test_template_manager_crud(): void
    {
        Livewire::actingAs($this->user)
            ->test(TemplateManager::class)
            ->call('openForm')
            ->set('name', 'Ramadan Offer')
            ->set('key', 'ramadan_offer')
            ->set('subject', 'Ramadan Kareem, {{customer.name}}')
            ->set('body', 'Special pricing inside.')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('email_templates', ['key' => 'ramadan_offer']);
    }

    public function test_communication_center_filters_by_channel(): void
    {
        $customer = Customer::factory()->create();
        $customer->communications()->create(['channel' => 'email', 'direction' => 'outbound', 'subject' => 'An email']);
        $customer->communications()->create(['channel' => 'whatsapp', 'direction' => 'inbound', 'subject' => 'A whatsapp']);

        Livewire::actingAs($this->user)
            ->test(CommunicationCenter::class)
            ->set('channel', 'email')
            ->assertSee('An email')
            ->assertDontSee('A whatsapp');
    }

    public function test_pages_render(): void
    {
        $this->actingAs($this->user)->get(route('communications.index'))->assertOk();
        $this->actingAs($this->user)->get(route('communications.templates'))->assertOk()->assertSee('welcome_email');
    }
}