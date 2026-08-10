<?php

namespace Tests\Feature\Divisions;

use App\Actions\Otozaar\AdvanceAppointmentAction;
use App\Actions\Otozaar\CreateAppointmentAction;
use App\Enums\AppointmentStatus;
use App\Livewire\Divisions\OtozaarPanel;
use App\Livewire\Portal\BookService;
use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use RuntimeException;
use Tests\TestCase;

class OtozaarCrmTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Product $service;
    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->service = Product::factory()->create(['division' => 'otozaar', 'sale_price' => 399, 'cost_price' => 180]);
        $this->customer = Customer::factory()->create();
    }

    private function makeAppointment(array $overrides = []): Appointment
    {
        return app(CreateAppointmentAction::class)->execute(array_merge([
            'customer_id' => $this->customer->id,
            'product_id' => $this->service->id,
            'scheduled_at' => now()->addDay()->format('Y-m-d H:i'),
            'vehicle_make' => 'Toyota', 'vehicle_model' => 'Camry', 'vehicle_year' => '2022', 'plate' => 'D-12345',
            'status' => 'booked',
        ], $overrides), $this->user);
    }

    public function test_appointment_gets_sequential_reference(): void
    {
        $apt = $this->makeAppointment();

        $this->assertSame('APT-00001', $apt->reference);
    }

    public function test_completion_creates_sales_order_at_agreed_price(): void
    {
        $apt = $this->makeAppointment(['price_estimate' => 350]);

        $advance = app(AdvanceAppointmentAction::class);
        $advance->execute($apt, AppointmentStatus::InProgress, $this->user);
        $advance->execute($apt->fresh(), AppointmentStatus::Completed, $this->user);

        $fresh = $apt->fresh();

        $this->assertSame('completed', $fresh->status);
        $this->assertNotNull($fresh->completed_at);
        $this->assertNotNull($fresh->sales_order_id);

        $order = $fresh->salesOrder;
        $this->assertSame('otozaar', $order->division);
        $this->assertEqualsWithDelta(350.0, (float) $order->items->first()->unit_price, 0.01);
    }

    public function test_invalid_transition_is_blocked(): void
    {
        $apt = $this->makeAppointment();

        $this->expectException(RuntimeException::class);

        app(AdvanceAppointmentAction::class)->execute($apt, AppointmentStatus::Completed, $this->user);
    }

    public function test_staff_console_books_and_advances(): void
    {
        Livewire::actingAs($this->user)
            ->test(OtozaarPanel::class)
            ->call('openForm')
            ->set('customer_id', $this->customer->id)
            ->set('product_id', $this->service->id)
            ->set('scheduled_at', now()->addHours(3)->format('Y-m-d\TH:i'))
            ->call('book')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('appointments', ['customer_id' => $this->customer->id, 'status' => 'booked']);

        $apt = Appointment::first();

        Livewire::actingAs($this->user)
            ->test(OtozaarPanel::class)
            ->call('advance', $apt->id, 'in_progress');

        $this->assertSame('in_progress', $apt->fresh()->status);
    }

    public function test_portal_customer_books_and_cancels_own_appointment(): void
    {
        $portalUser = User::factory()->create(['customer_id' => $this->customer->id, 'email_verified_at' => now()]);

        Livewire::actingAs($portalUser)
            ->test(BookService::class)
            ->set('product_id', $this->service->id)
            ->set('scheduled_at', now()->addDays(2)->format('Y-m-d\TH:i'))
            ->set('vehicle_make', 'Nissan')
            ->call('book')
            ->assertHasNoErrors();

        $apt = Appointment::first();
        $this->assertSame($this->customer->id, $apt->customer_id);

        $this->actingAs($portalUser)
            ->post(route('portal.appointments.cancel', $apt))
            ->assertRedirect();

        $this->assertSame('cancelled', $apt->fresh()->status);
    }

    public function test_portal_customer_cannot_cancel_others_appointment(): void
    {
        $other = Customer::factory()->create();
        $apt = $this->makeAppointment(['customer_id' => $other->id]);

        $portalUser = User::factory()->create(['customer_id' => $this->customer->id, 'email_verified_at' => now()]);

        $this->actingAs($portalUser)
            ->post(route('portal.appointments.cancel', $apt))
            ->assertNotFound();
    }

    public function test_otozaar_page_renders_console(): void
    {
        $this->actingAs($this->user)
            ->get(route('otozaar.index'))
            ->assertOk()
            ->assertSee('Service Bay Console');
    }
}