<?php

namespace App\Livewire\Products;

use App\Actions\Products\CreateProductAction;
use App\Actions\Products\UpdateProductAction;
use App\DTOs\Products\ProductDTO;
use App\Http\Requests\Products\StoreProductRequest;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class ProductForm extends Component
{
    public bool $open = false;
    public ?int $productId = null;

    public string $sku = '';
    public string $name = '';
    public string $division = 'automotive';
    public ?int $category_id = null;
    public string $brand = '';
    public string $unit = 'pcs';
    public string $cost_price = '';
    public string $sale_price = '';
    public string $tax_rate = '5';
    public string $description = '';
    public bool $is_active = true;

    /** @var array<int, array{key: string, value: string}> */
    public array $attributes = [];

    #[On('open-product-form')]
    public function openForm(?int $productId = null): void
    {
        $this->resetValidation();
        $this->productId = $productId;

        if ($productId) {
            $product = Product::findOrFail($productId);
            Gate::authorize('update', $product);

            foreach (['sku','name','division','category_id','brand','unit','tax_rate','description','is_active'] as $field) {
                $this->{$field} = $product->{$field} ?? '';
            }
            $this->cost_price = (string) $product->cost_price;
            $this->sale_price = (string) $product->sale_price;
            $this->attributes = collect($product->attributes ?? [])
                ->map(fn ($value, $key) => ['key' => (string) $key, 'value' => (string) $value])
                ->values()->all();
        } else {
            Gate::authorize('create', Product::class);
            $this->reset(['sku','name','brand','description','category_id']);
            $this->cost_price = '';
            $this->sale_price = '';
            $this->tax_rate = '5';
            $this->is_active = true;
            $this->attributes = [['key' => '', 'value' => '']];
        }

        if ($this->attributes === []) {
            $this->attributes = [['key' => '', 'value' => '']];
        }

        $this->open = true;
    }

    public function generateSku(): void
    {
        $prefix = match ($this->division) {
            'swiftec' => 'SF',
            'wiperex' => 'WX',
            'otozaar' => 'OZ',
            default => 'SW',
        };

        $this->sku = $prefix.'-'.strtoupper(Str::random(6));
    }

    public function addAttributeRow(): void
    {
        $this->attributes[] = ['key' => '', 'value' => ''];
    }

    public function removeAttributeRow(int $index): void
    {
        unset($this->attributes[$index]);
        $this->attributes = array_values($this->attributes);
    }

    public function save(CreateProductAction $create, UpdateProductAction $update): void
    {
        $data = $this->validate(StoreProductRequest::rules($this->productId));

        $attributes = collect($this->attributes)
            ->filter(fn ($row) => trim($row['key']) !== '')
            ->mapWithKeys(fn ($row) => [trim($row['key']) => trim($row['value'])])
            ->all();

        $dto = new ProductDTO(
            sku: $data['sku'],
            name: $data['name'],
            division: $data['division'],
            unit: $data['unit'],
            category_id: $data['category_id'] ?? null,
            brand: $data['brand'] ?? null,
            description: $data['description'] ?? null,
            cost_price: (float) $data['cost_price'],
            sale_price: (float) $data['sale_price'],
            tax_rate: (float) $data['tax_rate'],
            attributes: $attributes ?: null,
            is_active: $this->is_active,
        );

        $this->productId
            ? $update->execute(Product::findOrFail($this->productId), $dto)
            : $create->execute($dto);

        $this->open = false;
        $this->dispatch('product-saved');
        $this->dispatch('notify', message: 'Product saved.', type: 'success');
    }

    public function render(): View
    {
        return view('livewire.products.product-form', [
            'categories' => ProductCategory::query()->active()->where('division', $this->division)->orderBy('name')->get(),
        ]);
    }
}