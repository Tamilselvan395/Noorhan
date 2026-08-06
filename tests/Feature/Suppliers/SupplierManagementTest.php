<?php

namespace Tests\Feature\Suppliers;

use App\Livewire\Suppliers\SupplierComparison;
use App\Livewire\Suppliers\SupplierForm;
use App\Livewire\Suppliers\SupplierShow;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SupplierManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_supplier_can_be_created(): void
    {
        Livewire::actingAs($this->user)
            ->test(SupplierForm::class)
            ->call('openForm')
            ->set('name', 'Shanghai Parts Co.')
            ->set('division', 'automotive')
            ->set('status', 'active')
            ->set('currency', 'CNY')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('suppliers', ['name' => 'Shanghai Parts Co.', 'currency' => 'CNY']);
    }

    public function test_contact_can_be_added_with_primary_flag(): void
    {
        $supplier = Supplier::factory()->create();

        Livewire::actingAs($this->user)
            ->test(SupplierShow::class, ['supplier' => $supplier])
            ->set('contact_name', 'Li Wei')
            ->set('contact_primary', true)
            ->call('addContact');

        $this->assertDatabaseHas('supplier_contacts', ['supplier_id' => $supplier->id, 'name' => 'Li Wei', 'is_primary' => true]);
    }

    public function test_price_list_entry_upserts_per_currency(): void
    {
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create();

        $component = Livewire::actingAs($this->user)
            ->test(SupplierShow::class, ['supplier' => $supplier])
            ->set('price_product_id', $product->id)
            ->set('price', '10.00')
            ->set('price_currency', 'USD')
            ->call('addPrice')
            ->set('price', '11.00')
            ->call('addPrice');

        $this->assertDatabaseCount('supplier_price_lists', 1);
        $this->assertDatabaseHas('supplier_price_lists', ['supplier_id' => $supplier->id, 'price' => 11.00]);
    }

    public function test_expired_prices_are_excluded_from_current(): void
    {
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create();

        $supplier->priceLists()->create(['product_id' => $product->id, 'price' => 5, 'valid_until' => now()->subDay()]);

        Livewire::actingAs($this->user)
            ->test(SupplierShow::class, ['supplier' => $supplier])
            ->assertDontSee('MOQ'); // no current price rows rendered
    }

    public function test_rating_averages_are_computed(): void
    {
        $supplier = Supplier::factory()->create();

        Livewire::actingAs($this->user)
            ->test(SupplierShow::class, ['supplier' => $supplier])
            ->set('r_quality', 5)->set('r_price', 4)->set('r_delivery', 3)->set('r_service', 4)
            ->call('submitRating');

        $this->assertSame(4.0, $supplier->fresh()->overallRating());
        $this->assertSame(5.0, $supplier->fresh()->ratingBreakdown()['quality']);
    }

    public function test_comparison_ranks_by_lowest_price(): void
    {
        $product = Product::factory()->create();
        $cheap = Supplier::factory()->create(['name' => 'Cheap Supplier']);
        $expensive = Supplier::factory()->create(['name' => 'Expensive Supplier']);

        $cheap->priceLists()->create(['product_id' => $product->id, 'price' => 10]);
        $expensive->priceLists()->create(['product_id' => $product->id, 'price' => 20]);

        Livewire::actingAs($this->user)
            ->test(SupplierComparison::class)
            ->set('productId', $product->id)
            ->assertSee('Cheap Supplier')
            ->assertSee('BEST PRICE');

        $results = Livewire::actingAs($this->user)->test(SupplierComparison::class)->set('productId', $product->id)->instance()->results();

        $this->assertSame('Cheap Supplier', $results->first()['supplier']->name);
    }

    public function test_index_search(): void
    {
        Supplier::factory()->create(['name' => 'Findable Supplier']);
        Supplier::factory()->create(['name' => 'Hidden Supplier']);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Suppliers\SupplierIndex::class)
            ->set('search', 'Findable')
            ->assertSee('Findable Supplier')
            ->assertDontSee('Hidden Supplier');
    }
}