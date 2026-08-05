<?php

namespace App\Livewire\Products;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class ProductIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $division = 'all';
    public string $categoryId = 'all';
    public string $active = 'all';

    public function updated($property): void
    {
        if ($property === 'division') {
            $this->categoryId = 'all';
        }

        $this->resetPage();
    }

    public function stats(): array
    {
        return [
            'total' => Product::query()->count(),
            'active' => Product::query()->active()->count(),
            'categories' => ProductCategory::query()->count(),
            'low_margin' => Product::query()->active()->get()->filter(fn (Product $p) => $p->margin() !== null && $p->margin() < 10)->count(),
        ];
    }

    public function render(): View
    {
        $products = Product::query()
            ->with('category')
            ->search($this->search ?: null)
            ->when($this->division !== 'all', fn ($q) => $q->where('division', $this->division))
            ->when($this->categoryId !== 'all', fn ($q) => $q->where('category_id', $this->categoryId))
            ->when($this->active !== 'all', fn ($q) => $q->where('is_active', $this->active === 'active'))
            ->latest()
            ->paginate(12);

        $categories = $this->division === 'all'
            ? ProductCategory::query()->active()->orderBy('name')->get()
            : ProductCategory::query()->active()->where('division', $this->division)->orderBy('name')->get();

        return view('livewire.products.product-index', [
            'products' => $products,
            'categories' => $categories,
        ]);
    }
}