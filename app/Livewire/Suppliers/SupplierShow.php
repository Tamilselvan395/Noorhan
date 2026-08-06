<?php

namespace App\Livewire\Suppliers;

use App\Actions\Suppliers\RateSupplierAction;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;

class SupplierShow extends Component
{
    public Supplier $supplier;

    public string $tab = 'overview';

    // Contact form
    public string $contact_name = '';
    public string $contact_position = '';
    public string $contact_email = '';
    public string $contact_phone = '';
    public bool $contact_primary = false;

    // Price list form
    public ?int $price_product_id = null;
    public string $price = '';
    public string $price_currency = 'USD';
    public int $min_qty = 1;
    public ?int $lead_time_days = null;
    public string $valid_until = '';

    // Rating form
    public int $r_quality = 5;
    public int $r_price = 3;
    public int $r_delivery = 3;
    public int $r_service = 3;
    public string $r_comment = '';

    public function mount(Supplier $supplier): void
    {
        $this->supplier = $supplier;
    }

    public function switchTab(string $tab): void
    {
        $this->tab = $tab;
    }

    public function addContact(): void
    {
        Gate::authorize('update', $this->supplier);

        $this->validate([
            'contact_name' => ['required', 'string', 'max:120'],
            'contact_email' => ['nullable', 'email'],
        ]);

        if ($this->contact_primary) {
            $this->supplier->contacts()->update(['is_primary' => false]);
        }

        $this->supplier->contacts()->create([
            'name' => $this->contact_name,
            'position' => $this->contact_position ?: null,
            'email' => $this->contact_email ?: null,
            'phone' => $this->contact_phone ?: null,
            'is_primary' => $this->contact_primary,
        ]);

        $this->supplier->logActivity("added contact {$this->contact_name}");
        $this->reset(['contact_name', 'contact_position', 'contact_email', 'contact_phone']);
        $this->contact_primary = false;

        $this->dispatch('notify', message: 'Contact added.', type: 'success');
    }

    public function addPrice(): void
    {
        Gate::authorize('update', $this->supplier);

        $this->validate([
            'price_product_id' => ['required', 'integer', 'exists:products,id'],
            'price' => ['required', 'numeric', 'min:0'],
            'price_currency' => ['required', 'string', 'max:10'],
        ]);

        $this->supplier->priceLists()->updateOrCreate(
            ['product_id' => $this->price_product_id, 'currency' => $this->price_currency],
            [
                'price' => $this->price,
                'min_qty' => $this->min_qty,
                'lead_time_days' => $this->lead_time_days,
                'valid_until' => $this->valid_until ?: null,
            ],
        );

        $this->supplier->logActivity('updated a price list entry');
        $this->reset(['price', 'valid_until']);
        $this->price_product_id = null;

        $this->dispatch('notify', message: 'Price list updated.', type: 'success');
    }

    public function submitRating(RateSupplierAction $rate): void
    {
        $this->validate(['r_comment' => ['nullable', 'string', 'max:500']]);

        $rate->execute($this->supplier, auth()->user(), $this->r_quality, $this->r_price, $this->r_delivery, $this->r_service, $this->r_comment ?: null);

        $this->dispatch('notify', message: 'Rating submitted.', type: 'success');
    }

    public function render(): View
    {
        return view('livewire.suppliers.supplier-show', [
            'contacts' => $this->supplier->contacts()->orderByDesc('is_primary')->get(),
            'prices' => $this->supplier->priceLists()->with('product')->current()->get(),
            'ratings' => $this->supplier->ratings()->with('user')->latest()->get(),
            'products' => Product::query()->active()->orderBy('name')->limit(500)->get(),
            'timeline' => $this->supplier->activities()->with('user')->latest()->get(),
        ]);
    }
}