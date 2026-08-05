<?php

namespace Tests\Feature\Customers;

use App\Actions\Customers\ConvertLeadToCustomerAction;
use App\Livewire\Customers\CustomerForm;
use App\Livewire\Customers\CustomerIndex;
use App\Livewire\Customers\CustomerShow;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class CustomerManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_customer_can_be_created(): void
    {
        Livewire::actingAs($this->user)
            ->test(CustomerForm::class)
            ->call('openForm')
            ->set('name', 'Karim Garage')
            ->set('type', 'garage')
            ->set('division', 'automotive')
            ->set('status', 'active')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('customers', ['name' => 'Karim Garage', 'type' => 'garage']);
    }

    public function test_lead_conversion_creates_linked_customer(): void
    {
        $lead = Lead::factory()->create(['customer_type' => 'workshop', 'assigned_to' => $this->user->id]);

        $customer = app(ConvertLeadToCustomerAction::class)->execute($lead);

        $this->assertSame($lead->fresh()->customer_id, $customer->id);
        $this->assertSame($lead->id, $customer->fresh()->lead_id);
        $this->assertSame('workshop', $customer->type);
        $this->assertSame($this->user->id, $customer->owner_id);
    }

    public function test_conversion_is_idempotent(): void
    {
        $lead = Lead::factory()->create();
        $action = app(ConvertLeadToCustomerAction::class);

        $first = $action->execute($lead);
        $second = $action->execute($lead);

        $this->assertSame($first->id, $second->id);
        $this->assertDatabaseCount('customers', 1);
    }

    public function test_communication_is_logged_and_updates_activity(): void
    {
        $customer = Customer::factory()->create();

        Livewire::actingAs($this->user)
            ->test(CustomerShow::class, ['customer' => $customer])
            ->set('channel', 'whatsapp')
            ->set('direction', 'outbound')
            ->set('body', 'Sent quotation follow-up.')
            ->call('addCommunication');

        $this->assertDatabaseHas('communications', [
            'communicable_id' => $customer->id,
            'channel' => 'whatsapp',
        ]);
        $this->assertNotNull($customer->fresh()->last_activity_at);
    }

    public function test_document_upload_stores_file(): void
    {
        Storage::fake('public');
        $customer = Customer::factory()->create();

        Livewire::actingAs($this->user)
            ->test(CustomerShow::class, ['customer' => $customer])
            ->set('file', UploadedFile::fake()->create('trade-license.pdf', 120, 'application/pdf'))
            ->call('uploadDocument');

        $this->assertDatabaseHas('documents', ['documentable_id' => $customer->id]);
    }

    public function test_index_search_filters(): void
    {
        Customer::factory()->create(['name' => 'Findable Customer']);
        Customer::factory()->create(['name' => 'Hidden Customer']);

        Livewire::actingAs($this->user)
            ->test(CustomerIndex::class)
            ->set('search', 'Findable')
            ->assertSee('Findable Customer')
            ->assertDontSee('Hidden Customer');
    }

    public function test_owned_customer_cannot_be_updated_by_others(): void
    {
        $owner = User::factory()->create();
        $customer = Customer::factory()->create(['owner_id' => $owner->id]);

        $this->expectException(\Illuminate\Auth\Access\AuthorizationException::class);

        Livewire::actingAs($this->user)
            ->test(CustomerForm::class)
            ->call('openForm', $customer->id);
    }
}