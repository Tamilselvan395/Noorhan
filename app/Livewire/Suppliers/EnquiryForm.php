<?php

namespace App\Livewire\Suppliers;

use App\Actions\Suppliers\CreateSupplierEnquiryAction;
use App\Models\Lead;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class EnquiryForm extends Component
{
    public bool $open = false;

    public ?int $supplier_id = null;
    public ?int $lead_id = null;
    public string $notes = '';

    /** @var array<int, array{product_id: ?int, description: string, quantity: int}> */
    public array $items = [];

    #[On('open-enquiry-form')]
    public function openForm(?int $supplierId = null, ?int $leadId = null): void
    {
        Gate::authorize('create', SupplierEnquiry::class);

        $this->resetValidation();
        $this->supplier_id = $supplierId;
        $this->lead_id = $leadId;
        $this->notes = '';
        $this->items = [['product_id' => null, 'description' => '', 'quantity' => 1]];
        $this->open = true;
    }

    public function addItemRow(): void
    {
        $this->items[] = ['product_id' => null, 'description' => '', 'quantity' => 1];
    }

    public function removeItemRow(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    /** Auto-fill description when a product is picked. */
    public function updatedItems($value, $key): void
    {
        if (str_ends_with((string) $key, '.product_id')) {
            $index = (int) explode('.', (string) $key)[0];
            $productId = $this->items[$index]['product_id'] ?? null;

            if ($productId) {
                $product = Product::find($productId);
                $this->items[$index]['description'] = $product?->name ?? '';
            }
        }
    }

    public function save(CreateSupplierEnquiryAction $create): void
    {
        $this->validate([
            'supplier_id' => ['required', 'integer', 'exists:suppliers,id'],
            'lead_id' => ['nullable', 'integer', 'exists:leads,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:300'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $supplier = Supplier::findOrFail($this->supplier_id);

        $enquiry = $create->execute(
            $supplier,
            $this->items,
            auth()->user(),
            $this->lead_id,
            null,
            $this->notes ?: null,
        );

        $this->open = false;
        $this->dispatch('enquiry-saved');
        $this->dispatch('notify', message: "Enquiry {$enquiry->reference} created.", type: 'success');
    }

    public function render(): View
    {
        return view('livewire.suppliers.enquiry-form', [
            'suppliers' => Supplier::active()->orderBy('name')->get(),
            'leads' => Lead::open()->latest()->limit(100)->get(),
            'products' => Product::active()->orderBy('name')->limit(500)->get(),
        ]);
    }
}