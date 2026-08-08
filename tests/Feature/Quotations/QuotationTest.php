<?php

namespace Tests\Feature\Quotations;

use App\Actions\Quotations\ApproveQuotationAction;
use App\Actions\Quotations\CreateQuotationAction;
use App\Actions\Quotations\NewQuotationVersionAction;
use App\Actions\Quotations\SendQuotationAction;
use App\Actions\Quotations\SubmitForApprovalAction;
use App\Enums\CommunicationChannel;
use App\Livewire\Quotations\QuotationBuilder;
use App\Models\Customer;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use RuntimeException;
use Tests\TestCase;

class QuotationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->manager = User::factory()->create();
    }

    private function makeQuotation(array $overrides = []): Quotation
    {
        return app(CreateQuotationAction::class)->execute(
            array_merge(['division' => 'automotive', 'status' => 'draft', 'tax_rate' => 5], $overrides),
            [
                ['product_id' => null, 'description' => 'Brake pads', 'quantity' => 2, 'unit_price' => 100, 'cost_price' => 60, 'discount_percent' => 0],
            ],
            $this->user,
        );
    }

    public function test_totals_math_with_discount_and_tax(): void
    {
        $q = $this->makeQuotation(['discount_type' => 'percent', 'discount_value' => 10]);

        // line 200 → discount 20 → taxable 180 → tax 9 → total 189
        $this->assertEqualsWithDelta(200.0, (float) $q->subtotal, 0.01);
        $this->assertEqualsWithDelta(20.0, (float) $q->discount_amount, 0.01);
        $this->assertEqualsWithDelta(9.0, (float) $q->tax_amount, 0.01);
        $this->assertEqualsWithDelta(189.0, (float) $q->total, 0.01);
        $this->assertEqualsWithDelta(33.33, (float) $q->margin_percent, 0.1);
        $this->assertFalse($q->requires_approval);
    }

    public function test_low_margin_requires_approval(): void
    {
        $q = app(CreateQuotationAction::class)->execute(
            ['division' => 'automotive', 'status' => 'draft', 'tax_rate' => 5],
            [['product_id' => null, 'description' => 'Cheap item', 'quantity' => 1, 'unit_price' => 100, 'cost_price' => 95, 'discount_percent' => 0]],
            $this->user,
        );

        $this->assertTrue($q->requires_approval);
    }

    public function test_approval_flow_with_separation_of_duties(): void
    {
        $q = $this->makeQuotation();
        app(SubmitForApprovalAction::class)->execute($q);
        $this->assertSame('pending_approval', $q->fresh()->status);

        // Creator cannot approve own quotation
        $this->assertFalse($this->user->can('approve', $q->fresh()));
        $this->assertTrue($this->manager->can('approve', $q->fresh()));

        app(ApproveQuotationAction::class)->execute($q->fresh(), $this->manager);
        $this->assertSame('approved', $q->fresh()->status);
        $this->assertSame($this->manager->id, $q->fresh()->approved_by);
    }

    public function test_send_requires_approval_when_flagged(): void
    {
        $q = app(CreateQuotationAction::class)->execute(
            ['division' => 'automotive', 'status' => 'draft', 'tax_rate' => 5],
            [['product_id' => null, 'description' => 'Low margin', 'quantity' => 1, 'unit_price' => 100, 'cost_price' => 99, 'discount_percent' => 0]],
            $this->user,
        );

        $this->expectException(RuntimeException::class);
        app(SendQuotationAction::class)->execute($q, CommunicationChannel::Email);
    }

    public function test_send_emails_customer_with_signed_link(): void
    {
        Mail::fake();
        $customer = Customer::factory()->create();
        $q = $this->makeQuotation(['customer_id' => $customer->id]);

        app(SendQuotationAction::class)->execute($q, CommunicationChannel::Email);

        $this->assertSame('sent', $q->fresh()->status);
        Mail::assertQueued(\App\Mail\QuotationMail::class);
    }

    public function test_public_signed_view_and_accept(): void
    {
        $customer = Customer::factory()->create();
        $q = $this->makeQuotation(['customer_id' => $customer->id]);
        app(SendQuotationAction::class)->execute($q, CommunicationChannel::Email);

        $url = \Illuminate\Support\Facades\URL::temporarySignedRoute('quotations.public', now()->addDays(30), ['quotation' => $q->id]);

        $this->get($url)->assertOk()->assertSee($q->reference);

        $this->post(route('quotations.public.accept', $q) . '?' . parse_url($url, PHP_URL_QUERY));

        $this->assertSame('accepted', $q->fresh()->status);
    }

    public function test_unsigned_public_view_is_forbidden(): void
    {
        $q = $this->makeQuotation();

        $this->get(route('quotations.public', $q))->assertForbidden();
    }

    public function test_new_version_clones_items_and_links_parent(): void
    {
        $q = $this->makeQuotation();

        $v2 = app(NewQuotationVersionAction::class)->execute($q);

        $this->assertSame(2, $v2->version);
        $this->assertSame($q->id, $v2->parent_id);
        $this->assertSame('draft', $v2->status);
        $this->assertCount(1, $v2->items);
        $this->assertStringContainsString('-V2', $v2->reference);
    }

    public function test_builder_saves_quotation(): void
    {
        Livewire::actingAs($this->user)
            ->test(QuotationBuilder::class)
            ->set('customer_id', Customer::factory()->create()->id)
            ->set('items', [[
                'product_id' => null, 'description' => 'Engine oil', 'quantity' => 4,
                'unit_price' => '25', 'cost_price' => '15', 'discount_percent' => '0',
            ]])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('quotations', ['reference' => 'QTN-00001']);
        $this->assertDatabaseHas('quotation_items', ['description' => 'Engine oil', 'quantity' => 4]);
    }
}