<?php

namespace App\Livewire\Suppliers;

use App\Models\Product;
use App\Models\SupplierPriceList;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;

class SupplierComparison extends Component
{
    public ?int $productId = null;

    public function results(): Collection
    {
        if (! $this->productId) {
            return collect();
        }

        return SupplierPriceList::query()
            ->with(['supplier'])
            ->current()
            ->where('product_id', $this->productId)
            ->get()
            ->map(fn (SupplierPriceList $entry) => [
                'supplier' => $entry->supplier,
                'price' => (float) $entry->price,
                'currency' => $entry->currency,
                'min_qty' => $entry->min_qty,
                'lead_time' => $entry->lead_time_days,
                'rating' => $entry->supplier->overallRating(),
            ])
            ->sortBy('price')
            ->values();
    }

    public function render(): View
    {
        return view('livewire.suppliers.supplier-comparison', [
            'products' => Product::query()->active()->orderBy('name')->get(),
        ]);
    }
}