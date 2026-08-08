<?php

namespace App\Livewire\SalesOrders;

use App\Actions\SalesOrders\CreateSalesOrderAction;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class OrderForm extends Component
{
    public bool $open = false;

    public ?int $customer_id = null;
    public string $division = 'automotive';
    public string $tax_rate = '5';
    public string $expected_delivery_date = '';
    public string $delivery_address = '';
    public string $notes = '';

    /** @var array<int, array{product_id: ?int, description: string, quantity: int, unit_price: string, cost_price: string, discount_percent: string}> */
    public array $items = [];

    #[On('open-order-form')]
    public function openForm(): void
    {
        Gate::authorize('create', \App\Models\SalesOrder::class);

        $this->resetValidation();
        $this->reset(['customer_id', 'delivery_address', 'notes', 'expected_delivery_date']);
        $this->items = [['product_id' => null, 'description' => '', 'quantity' => 1, 'unit_price' => '', 'cost_price' => '', 'discount_percent' => '0']];
        $this->open = true;
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

    public function updatedItems($value, $key): void
    {
        if (! str_ends_with((string) $key, '.product_id')) {
            return;
        }

        $index = (int) explode('.', (string) $key)[0];
        $product = Product::find($this->items[$index]['product_id'] ?? null);

        if ($product) {
            $this->items[$index]['description'] = $product->name;
            $this->items[$index]['unit_price'] = (string) $product->sale_price;
            $this->items[$index]['cost_price'] = (string) $product->cost_price;
        }
    }

    public function save(CreateSalesOrderAction $create)
    {
        $this->validate([
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:300'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $order = $create->execute([
            'customer_id' => $this->customer_id,
            'division' => $this->division,
            'status' => 'pending',
            'tax_rate' => (float) $this->tax_rate,
            'expected_delivery_date' => $this->expected_delivery_date ?: null,
            'delivery_address' => $this->delivery_address ?: null,
            'notes' => $this->notes ?: null,
        ], collect($this->items)->map(fn ($i) => [
            'product_id' => $i['product_id'] ?? null,
            'description' => $i['description'],
            'quantity' => (int) $i['quantity'],
            'unit_price' => (float) $i['unit_price'],
            'cost_price' => (float) ($i['cost_price'] ?? 0),
            'discount_percent' => (float) ($i['discount_percent'] ?? 0),
        ])->all(), auth()->user());

        $this->open = false;
        $this->dispatch('order-saved');
        $this->dispatch('notify', message: "Order {$order->reference} created.", type: 'success');

        return redirect()->route('sales-orders.show', $order);
    }

    public function render(): View
    {
        return view('livewire.sales-orders.order-form', [
            'customers' => Customer::active()->orderBy('name')->get(),
            'products' => Product::active()->orderBy('name')->limit(500)->get(),
        ]);
    }
}