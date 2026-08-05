<?php

namespace Tests\Feature\Products;

use App\Livewire\Products\ProductForm;
use App\Livewire\Products\ProductIndex;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Database\Seeders\ProductCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProductManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->seed(ProductCategorySeeder::class);
    }

    public function test_category_seeder_creates_division_catalog(): void
    {
        $this->assertDatabaseHas('product_categories', ['name' => 'Engine Oil', 'division' => 'swiftec']);
        $this->assertDatabaseHas('product_categories', ['name' => 'Wiper Blades', 'division' => 'wiperex']);
        $this->assertDatabaseHas('product_categories', ['name' => 'Brake System', 'division' => 'automotive']);
    }

    public function test_product_can_be_created_with_attributes(): void
    {
        $category = ProductCategory::where('division', 'swiftec')->first();

        Livewire::actingAs($this->user)
            ->test(ProductForm::class)
            ->call('openForm')
            ->set('sku', 'SF-TEST-001')
            ->set('name', 'Swiftec Test Oil')
            ->set('division', 'swiftec')
            ->set('category_id', $category->id)
            ->set('cost_price', '10')
            ->set('sale_price', '20')
            ->set('attributes', [['key' => 'viscosity', 'value' => '5W-40']])
            ->call('save')
            ->assertHasNoErrors();

        $product = Product::where('sku', 'SF-TEST-001')->first();

        $this->assertNotNull($product);
        $this->assertSame(['viscosity' => '5W-40'], $product->attributes);
    }

    public function test_sku_must_be_unique(): void
    {
        Product::factory()->create(['sku' => 'DUP-001']);

        Livewire::actingAs($this->user)
            ->test(ProductForm::class)
            ->call('openForm')
            ->set('sku', 'DUP-001')
            ->set('name', 'Duplicate')
            ->set('cost_price', '1')
            ->set('sale_price', '2')
            ->call('save')
            ->assertHasErrors(['sku']);
    }

    public function test_margin_and_tax_math(): void
    {
        $product = Product::factory()->create(['cost_price' => 100, 'sale_price' => 130, 'tax_rate' => 5]);

        $this->assertSame(30.0, $product->margin());
        $this->assertSame(136.5, $product->priceWithTax());
    }

    public function test_index_filters_by_division_and_search(): void
    {
        Product::factory()->create(['name' => 'Swiftec Oil', 'division' => 'swiftec']);
        Product::factory()->create(['name' => 'Wiper Blade', 'division' => 'wiperex']);

        Livewire::actingAs($this->user)
            ->test(ProductIndex::class)
            ->set('division', 'swiftec')
            ->assertSee('Swiftec Oil')
            ->assertDontSee('Wiper Blade')
            ->set('search', 'Wiper')
            ->assertDontSee('Swiftec Oil');
    }

    public function test_category_options_follow_division(): void
    {
        Livewire::actingAs($this->user)
            ->test(ProductForm::class)
            ->call('openForm')
            ->set('division', 'wiperex')
            ->assertSee('Wiper Blades')
            ->assertDontSee('Engine Oil');
    }

    public function test_sku_generator_respects_division_prefix(): void
    {
        Livewire::actingAs($this->user)
            ->test(ProductForm::class)
            ->call('openForm')
            ->set('division', 'swiftec')
            ->call('generateSku')
            ->assertSet('sku', fn ($sku) => str_starts_with($sku, 'SF-'));
    }
}