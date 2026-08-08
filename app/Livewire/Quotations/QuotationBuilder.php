<?php

namespace App\Livewire\Quotations;

use App\Actions\Quotations\CreateQuotationAction;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Product;
use App\Models\Quotation;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;

class QuotationBuilder extends Component
{
    public ?int $quotationId = null;

    public ?int $customer_id = null;
    public ?int $lead_id = null;
    public string $division = 'automotive';
    public string $tax_rate = '5';
    public string $discount_type = 'percent';
    public string $discount_value = '0';
    public string $valid_until = '';
    public string $notes = '';
    public string $terms = '';

    /** @var array<int, array{product_id: ?int, description: string, quantity: int, unit_price: string, cost_price: string, discount_percent: string}> */
    public array $items = [];

    public function mount(?Quotation $quotation = null): void
    {
        if ($quotation && $quotation->exists) {
            Gate::authorize('update', $quotation);

            $this->quotationId = $quotation->id;
            $this->customer_id = $quotation->customer_id;
            $this->lead_id = $quotation->lead_id;
            $this->division = $quotation->division;
            $this->tax_rate = (string) $quotation->tax_rate;
            $this->discount_type = $quotation->discount_type;
            $this->discount_value = (string) $quotation->discount_value;
            $this->valid_until = $quotation->valid_until?->format('Y-m-d') ?? '';
            $this->notes = (string) ($quotation->notes ?? '');
            $this->terms = (string) ($quotation->terms ?? '');

            $this->items = $quotation->items->map(fn ($item) => [
                'product_id' => $item->product_id,
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit_price' => (string) $item->unit_price,
                'cost_price' => (string) $item->cost_price,
                'discount_percent' => (string) $item->discount_percent,
            ])->all();
        }

        if ($this->items === []) {
            $this->addItemRow();
        }

        if ($this->valid_until === '') {
            $this->valid_until = now()->addDays((int) config('noorhan.quotation.default_valid_days', 15))->format('Y-m-d');
        }
    }

    public function addItemRow(): void
    {
        $this->items[] = ['product_id' => null, 'description' => '', 'quantity' => 1, 'unit_price' => '', 'cost_price' => '', 'discount_percent' => '0'];
    }

    public function removeItemRow(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    /** Product pick auto-fills price + cost snapshot (cost from best supplier response if available). */
    public function updatedItems($value, $key): void
    {
        if (! str_ends_with((string) $key, '.product_id')) {
            return;
        }

        $index = (int) explode('.', (string) $key)[0];
        $productId = $this->items[$index]['product_id'] ?? null;

        if (! $productId) {
            return;
        }

        $product = Product::find($productId);

        if (! $product) {
            return;
        }

        $bestCost = \App\Models\SupplierPriceList::query()
            ->current()
            ->where('product_id', $product->id)
            ->orderBy('price')
            ->value('price');

        $this->items[$index]['description'] = $product->name;
        $this->items[$index]['unit_price'] = (string) $product->sale_price;
        $this->items[$index]['cost_price'] = (string) ($bestCost ?? $product->cost_price);
        $this->items[$index]['discount_percent'] = '0';
    }

    public function totals(): array
    {
        $subtotal = collect($this->items)->sum(fn ($item) =>
            ((int) ($item['quantity'] ?? 1)) * ((float) ($item['unit_price'] ?? 0)) * (1 - ((float) ($item['discount_percent'] ?? 0)) / 100));

        $discount = $this->discount_type === 'percent'
            ? $subtotal * ((float) $this->discount_value) / 100
            : (float) $this->discount_value;

        $taxable = max($subtotal - $discount, 0);
        $tax = $taxable * ((float) $this->tax_rate) / 100;
        $cost = collect($this->items)->sum(fn ($item) => ((int) ($item['quantity'] ?? 1)) * ((float) ($item['cost_price'] ?? 0)));

        return [
            'subtotal' => round($subtotal, 2),
            'discount' => round($discount, 2),
            'tax' => round($tax, 2),
            'total' => round($taxable + $tax, 2),
            'cost' => round($cost, 2),
            'margin' => $taxable > 0 ? round((($taxable - $cost) / $taxable) * 100, 1) : 0.0,
        ];
    }

    public function save(CreateQuotationAction $create)
    {
        $this->validate([
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'lead_id' => ['nullable', 'integer', 'exists:leads,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:300'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        abort_unless($this->customer_id || $this->lead_id, 422, 'A quotation needs a customer or a lead.');

        $data = [
            'customer_id' => $this->customer_id,
            'lead_id' => $this->lead_id,
            'division' => $this->division,
            'tax_rate' => (float) $this->tax_rate,
            'discount_type' => $this->discount_type,
            'discount_value' => (float) $this->discount_value,
            'valid_until' => $this->valid_until ?: null,
            'notes' => $this->notes ?: null,
            'terms' => $this->terms ?: null,
            'status' => 'draft',
        ];

        $items = collect($this->items)->map(fn ($item) => [
            'product_id' => $item['product_id'] ?? null,
            'description' => $item['description'],
            'quantity' => (int) $item['quantity'],
            'unit_price' => (float) $item['unit_price'],
            'cost_price' => (float) ($item['cost_price'] ?? 0),
            'discount_percent' => (float) ($item['discount_percent'] ?? 0),
        ])->all();

        if ($this->quotationId) {
            $quotation = Quotation::findOrFail($this->quotationId);
            Gate::authorize('update', $quotation);

            $quotation->items()->delete();
            $quotation->update($data);
            foreach (array_values($items) as $i => $item) {
                $quotation->items()->create($item + ['sort' => $i * 10]);
            }
            $quotation->recalculate();
            $quotation->update(['requires_approval' => $quotation->fresh()->computeRequiresApproval()]);
        } else {
            $quotation = $create->execute($data, $items, auth()->user());
        }

        $this->dispatch('notify', message: "Quotation {$quotation->reference} saved.", type: 'success');

        return redirect()->route('quotations.show', $quotation);
    }

    public function render(): View
    {
        return view('livewire.quotations.quotation-builder', [
            'customers' => Customer::active()->orderBy('name')->get(),
            'leads' => Lead::open()->latest()->limit(100)->get(),
            'products' => Product::active()->orderBy('name')->limit(500)->get(),
            'totals' => $this->totals(),
        ]);
    }
}