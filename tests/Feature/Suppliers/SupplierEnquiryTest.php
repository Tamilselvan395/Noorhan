<?php

namespace Tests\Feature\Suppliers;

use App\Actions\Suppliers\CloseSupplierEnquiryAction;
use App\Actions\Suppliers\RecordSupplierResponseAction;
use App\Actions\Suppliers\SendSupplierEnquiryAction;
use App\Enums\CommunicationChannel;
use App\Enums\EnquiryItemStatus;
use App\Livewire\Suppliers\EnquiryForm;
use App\Livewire\Suppliers\EnquiryShow;
use App\Models\Lead;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\SupplierEnquiry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use RuntimeException;
use Tests\TestCase;

class SupplierEnquiryTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Supplier $supplier;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->supplier = Supplier::factory()->create();
    }

    private function makeEnquiry(array $items = 1): SupplierEnquiry
    {
        $rows = collect(range(1, is_int($items) ? $items : 1))->map(fn ($i) => [
            'product_id' => null, 'description' => "Item {$i}", 'quantity' => $i * 10,
        ])->all();

        return app(\App\Actions\Suppliers\CreateSupplierEnquiryAction::class)
            ->execute($this->supplier, $rows, $this->user);
    }

    public function test_enquiry_created_with_sequential_reference(): void
    {
        $lead = Lead::factory()->create();

        Livewire::actingAs($this->user)
            ->test(EnquiryForm::class)
            ->call('openForm', null, $lead->id)
            ->set('supplier_id', $this->supplier->id)
            ->set('items', [['product_id' => null, 'description' => 'Brake pads set', 'quantity' => 50]])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('supplier_enquiries', ['reference' => 'RFQ-00001', 'lead_id' => $lead->id, 'status' => 'draft']);
        $this->assertDatabaseHas('supplier_enquiry_items', ['description' => 'Brake pads set', 'quantity' => 50]);
    }

    public function test_send_requires_draft_and_items(): void
    {
        $enquiry = $this->makeEnquiry();

        app(SendSupplierEnquiryAction::class)->execute($enquiry, CommunicationChannel::Email);

        $fresh = $enquiry->fresh();
        $this->assertSame('sent', $fresh->status);
        $this->assertNotNull($fresh->sent_at);
        $this->assertSame('email', $fresh->sent_via);

        // Sending twice must fail
        $this->expectException(RuntimeException::class);
        app(SendSupplierEnquiryAction::class)->execute($fresh, CommunicationChannel::Email);
    }

    public function test_partial_then_full_response_progression(): void
    {
        $enquiry = $this->makeEnquiry(2);
        app(SendSupplierEnquiryAction::class)->execute($enquiry, CommunicationChannel::WhatsApp);

        [$first, $second] = $enquiry->items()->get()->all();

        $record = app(RecordSupplierResponseAction::class);

        $record->execute($first, EnquiryItemStatus::Quoted, 12.5, 7, now()->addDays(30)->toDateString());

        $this->assertSame('partial', $enquiry->fresh()->status);
        $this->assertNotNull($enquiry->fresh()->responded_at);

        $record->execute($second, EnquiryItemStatus::Declined);

        $this->assertSame('quoted', $enquiry->fresh()->status);
    }

    public function test_quoted_item_requires_price(): void
    {
        $enquiry = $this->makeEnquiry();
        $item = $enquiry->items()->first();

        $this->expectException(\InvalidArgumentException::class);

        app(RecordSupplierResponseAction::class)->execute($item, EnquiryItemStatus::Quoted, null);
    }

    public function test_response_recording_via_livewire(): void
    {
        $enquiry = $this->makeEnquiry();
        $item = $enquiry->items()->first();

        Livewire::actingAs($this->user)
            ->test(EnquiryShow::class, ['enquiry' => $enquiry])
            ->set("responses.{$item->id}.price", '22.00')
            ->set("responses.{$item->id}.lead", '10')
            ->set("responses.{$item->id}.status", 'quoted')
            ->call('recordResponse', $item->id);

        $this->assertDatabaseHas('supplier_enquiry_items', ['id' => $item->id, 'offered_price' => 22.00, 'status' => 'quoted']);
    }

    public function test_close_and_cancel(): void
    {
        $enquiry = $this->makeEnquiry();
        $close = app(CloseSupplierEnquiryAction::class);

        $close->execute($enquiry);
        $this->assertSame('closed', $enquiry->fresh()->status);
        $this->assertNotNull($enquiry->fresh()->closed_at);

        $this->expectException(RuntimeException::class);
        $close->execute($enquiry->fresh(), true);
    }

    public function test_response_time_is_computed(): void
    {
        $enquiry = $this->makeEnquiry();
        $enquiry->update(['sent_at' => now()->subHours(6), 'responded_at' => now()]);

        $this->assertSame(6.0, $enquiry->fresh()->responseTimeHours());
    }
}