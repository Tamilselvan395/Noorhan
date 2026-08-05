<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class CustomerIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $type = 'all';
    public string $status = 'all';
    public string $division = 'all';

    public function updated($property): void
    {
        $this->resetPage();
    }

    public function stats(): array
    {
        return [
            'total' => Customer::query()->count(),
            'active' => Customer::query()->active()->count(),
            'new_this_month' => Customer::query()->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
            'blacklisted' => Customer::query()->where('status', 'blacklisted')->count(),
        ];
    }

    public function render(): View
    {
        $customers = Customer::query()
            ->with('owner')
            ->search($this->search ?: null)
            ->when($this->type !== 'all', fn ($q) => $q->where('type', $this->type))
            ->when($this->status !== 'all', fn ($q) => $q->where('status', $this->status))
            ->when($this->division !== 'all', fn ($q) => $q->where('division', $this->division))
            ->latest()
            ->paginate(12);

        return view('livewire.customers.customer-index', ['customers' => $customers]);
    }
}