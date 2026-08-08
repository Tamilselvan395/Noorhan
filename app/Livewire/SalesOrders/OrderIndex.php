<?php

namespace App\Livewire\SalesOrders;

use App\Models\SalesOrder;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class OrderIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $status = 'all';

    public function updated($property): void
    {
        $this->resetPage();
    }

    public function stats(): array
    {
        return [
            'open' => SalesOrder::query()->open()->count(),
            'open_value' => (float) SalesOrder::query()->open()->sum('total'),
            'delivered_month' => SalesOrder::query()->where('status', 'delivered')
                ->whereMonth('delivered_at', now()->month)->whereYear('delivered_at', now()->year)->count(),
            'pending' => SalesOrder::query()->where('status', 'pending')->count(),
        ];
    }

    public function render(): View
    {
        $orders = SalesOrder::query()
            ->with(['customer', 'quotation', 'creator'])
            ->when($this->search, fn ($q) => $q->where('reference', 'like', "%{$this->search}%"))
            ->when($this->status !== 'all', fn ($q) => $q->where('status', $this->status))
            ->latest()
            ->paginate(12);

        return view('livewire.sales-orders.order-index', ['orders' => $orders]);
    }
}