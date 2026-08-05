<?php

namespace App\Livewire\Products;

use App\Models\Product;
use Illuminate\View\View;
use Livewire\Component;

class ProductShow extends Component
{
    public Product $product;

    public function mount(Product $product): void
    {
        $this->product = $product;
    }

    public function render(): View
    {
        return view('livewire.products.product-show', [
            'timeline' => $this->product->activities()->with('user')->latest()->get(),
        ]);
    }
}