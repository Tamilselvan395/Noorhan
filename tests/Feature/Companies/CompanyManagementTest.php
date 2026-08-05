<?php

namespace Tests\Feature\Companies;

use App\Actions\Companies\AttachContactAction;
use App\Livewire\Companies\CompanyForm;
use App\Livewire\Companies\CompanyIndex;
use App\Livewire\Companies\CompanyShow;
use App\Livewire\Customers\CustomerForm;
use App\Models\Company;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CompanyManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_company_can_be_created(): void
    {
        Livewire::actingAs($this->user)
            ->test(CompanyForm::class)
            ->call('openForm')
            ->set('name', 'Noorhan Distribution FZE')
            ->set('type', 'distributor')
            ->set('division', 'swiftec')
            ->set('status', 'active')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('companies', ['name' => 'Noorhan Distribution FZE', 'type' => 'distributor']);
    }

    public function test_contact_can_be_attached_to_company(): void
    {
        $company = Company::factory()->create();
        $customer = Customer::factory()->create(['company_id' => null]);

        app(AttachContactAction::class)->execute($company, $customer);

        $this->assertSame($company->id, $customer->fresh()->company_id);
    }

    public function test_attach_via_livewire(): void
    {
        $company = Company::factory()->create();
        $customer = Customer::factory()->create(['company_id' => null]);

        Livewire::actingAs($this->user)
            ->test(CompanyShow::class, ['company' => $company])
            ->set('attachContactId', $customer->id)
            ->call('attachContact');

        $this->assertSame($company->id, $customer->fresh()->company_id);
    }

    public function test_customer_form_can_preselect_company(): void
    {
        $company = Company::factory()->create();

        Livewire::actingAs($this->user)
            ->test(CustomerForm::class)
            ->call('openForm', null, $company->id)
            ->assertSet('company_id', $company->id)
            ->set('name', 'Contact Person')
            ->set('type', 'retail')
            ->set('division', 'automotive')
            ->set('status', 'active')
            ->call('save');

        $this->assertDatabaseHas('customers', ['name' => 'Contact Person', 'company_id' => $company->id]);
    }

    public function test_company_search_filters(): void
    {
        Company::factory()->create(['name' => 'Findable Co']);
        Company::factory()->create(['name' => 'Hidden Co']);

        Livewire::actingAs($this->user)
            ->test(CompanyIndex::class)
            ->set('search', 'Findable')
            ->assertSee('Findable Co')
            ->assertDontSee('Hidden Co');
    }

    public function test_owned_company_protected_from_others(): void
    {
        $owner = User::factory()->create();
        $company = Company::factory()->create(['owner_id' => $owner->id]);

        $this->expectException(\Illuminate\Auth\Access\AuthorizationException::class);

        Livewire::actingAs($this->user)
            ->test(CompanyForm::class)
            ->call('openForm', $company->id);
    }

    public function test_company_communication_logged(): void
    {
        $company = Company::factory()->create();

        Livewire::actingAs($this->user)
            ->test(CompanyShow::class, ['company' => $company])
            ->set('body', 'Discussed annual distributor agreement.')
            ->call('addCommunication');

        $this->assertDatabaseHas('communications', [
            'communicable_type' => Company::class,
            'communicable_id' => $company->id,
        ]);
    }
}