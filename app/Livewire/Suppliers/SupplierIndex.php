<?php

namespace App\Livewire\Suppliers;

use App\Models\Supplier;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class SupplierIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $division = 'all';
    public string $status = 'all';

    public function updated($property): void
    {
        $this->resetPage();
    }

    public function stats(): array
    {
        return [
            'total' => Supplier::query()->count(),
            'active' => Supplier::query()->active()->count(),
            'rated' => Supplier::query()->has('ratings')->count(),
            'countries' => Supplier::query()->distinct()->count('country'),
        ];
    }

    public function render(): View
    {
        $suppliers = Supplier::query()
            ->withCount('ratings')
            ->with('owner')
            ->search($this->search ?: null)
            ->when($this->division !== 'all', fn ($q) => $q->where('division', $this->division))
            ->when($this->status !== 'all', fn ($q) => $q->where('status', $this->status))
            ->latest()
            ->paginate(12);

        return view('livewire.suppliers.supplier-index', ['suppliers' => $suppliers]);
    }
}