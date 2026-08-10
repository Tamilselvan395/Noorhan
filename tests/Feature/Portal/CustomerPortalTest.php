<?php

namespace Tests\Feature\Portal;

use App\Actions\Invoices\CreateInvoiceFromOrderAction;
use App\Actions\Quotations\CreateQuotationAction;
use App\Actions\SalesOrders\CreateSalesOrderAction;
use App\Models\Customer;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerPortalTest extends TestCase
{
    use RefreshDatabase;

    private Customer $customer;
    private User $portalUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = Customer::factory()->create();
        $this->portalUser = User::factory()->create([
            'customer_id' => $this->customer->id,
            'email_verified_at' => now(),
        ]);
        $this->portalUser->syncRoles('Customer');
    }

    private function makeOrder()
    {
        return app(CreateSalesOrderAction::class)->execute([
            'customer_id' => $this->customer->id, 'division' => 'automotive',
            'status' => 'confirmed', 'tax_rate' => 5,
        ], [['product_id' => null, 'description' => 'Brake pads', 'quantity' => 1, 'unit_price' => 100, 'cost_price' => 50, 'discount_percent' => 0]], $this->portalUser);
    }

    public function test_portal_dashboard_shows_account_summary(): void
    {
        $this->actingAs($this->portalUser)
            ->get(route('portal.dashboard'))
            ->assertOk()
            ->assertSee('Welcome, '.$this->customer->name)
            ->assertSee('Outstanding Balance');
    }

    public function test_customer_sees_only_own_documents(): void
    {
        $otherCustomer = Customer::factory()->create();
        $otherOrder = app(CreateSalesOrderAction::class)->execute([
            'customer_id' => $otherCustomer->id, 'division' => 'automotive',
            'status' => 'confirmed', 'tax_rate' => 5,
        ], [['product_id' => null, 'description' => 'X', 'quantity' => 1, 'unit_price' => 10, 'cost_price' => 5, 'discount_percent' => 0]], $this->portalUser);

        $myOrder = $this->makeOrder();

        $this->actingAs($this->portalUser)->get(route('portal.orderShow', $myOrder->id))->assertOk();
        $this->actingAs($this->portalUser)->get(route('portal.orderShow', $otherOrder->id))->assertNotFound();
    }

    public function test_customer_can_accept_quotation(): void
    {
        $quotation = app(CreateQuotationAction::class)->execute([
            'customer_id' => $this->customer->id, 'division' => 'automotive',
            'status' => 'sent', 'tax_rate' => 5,
        ], [['product_id' => null, 'description' => 'X', 'quantity' => 1, 'unit_price' => 100, 'cost_price' => 50, 'discount_percent' => 0]], $this->portalUser);

        $this->actingAs($this->portalUser)
            ->post(route('portal.quotations.accept', $quotation->id))
            ->assertRedirect();

        $this->assertSame('accepted', $quotation->fresh()->status);
    }

    public function test_customer_can_decline_quotation(): void
    {
        $quotation = app(CreateQuotationAction::class)->execute([
            'customer_id' => $this->customer->id, 'division' => 'automotive',
            'status' => 'sent', 'tax_rate' => 5,
        ], [['product_id' => null, 'description' => 'X', 'quantity' => 1, 'unit_price' => 100, 'cost_price' => 50, 'discount_percent' => 0]], $this->portalUser);

        $this->actingAs($this->portalUser)->post(route('portal.quotations.decline', $quotation->id));

        $this->assertSame('rejected', $quotation->fresh()->status);
    }

    public function test_invoice_show_displays_balance(): void
    {
        $order = $this->makeOrder();
        $invoice = app(CreateInvoiceFromOrderAction::class)->execute($order);
        $invoice->update(['status' => 'sent']);

        $this->actingAs($this->portalUser)
            ->get(route('portal.invoices.show', $invoice->id))
            ->assertOk()
            ->assertSee($invoice->reference);
    }

    public function test_profile_update_persists_to_customer(): void
    {
        $this->actingAs($this->portalUser)
            ->post(route('portal.dashboard')); // warm session

        \Livewire\Livewire::actingAs($this->portalUser)
            ->test(\App\Livewire\Portal\ProfileForm::class)
            ->set('phone', '+971509998888')
            ->set('city', 'Dubai')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('+971509998888', $this->customer->fresh()->phone);
        $this->assertSame('Dubai', $this->customer->fresh()->city);
    }

    public function test_staff_without_customer_link_is_redirected(): void
    {
        $staff = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($staff)->get(route('portal.dashboard'))->assertRedirect(route('dashboard'));
    }

    public function test_login_redirects_customer_to_portal(): void
    {
        $this->portalUser->update(['password' => bcrypt('Password@123')]);

        $this->post('/login', ['email' => $this->portalUser->email, 'password' => 'Password@123'])
            ->assertRedirect(route('portal.dashboard'));
    }
}