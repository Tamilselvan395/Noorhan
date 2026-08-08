<?php

namespace App\Livewire\Suppliers;

use App\Models\SupplierEnquiry;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class EnquiryIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $status = 'all';
    public string $supplierId = 'all';

    public function updated($property): void
    {
        $this->resetPage();
    }

    public function stats(): array
    {
        $responded = SupplierEnquiry::query()->whereNotNull('responded_at')->whereNotNull('sent_at')->get();

        return [
            'total' => SupplierEnquiry::query()->count(),
            'open' => SupplierEnquiry::query()->open()->count(),
            'quoted' => SupplierEnquiry::query()->where('status', 'quoted')->count(),
            'avg_response_h' => $responded->isEmpty()
                ? null
                : round($responded->avg(fn (SupplierEnquiry $e) => $e->responseTimeHours()), 1),
        ];
    }

    public function render(): View
    {
        $enquiries = SupplierEnquiry::query()
            ->with(['supplier', 'lead', 'creator'])
            ->withCount('items')
            ->when($this->search, fn ($q) => $q->where(fn ($q) => $q->where('reference', 'like', "%{$this->search}%")))
            ->when($this->status !== 'all', fn ($q) => $q->where('status', $this->status))
            ->when($this->supplierId !== 'all', fn ($q) => $q->where('supplier_id', $this->supplierId))
            ->latest()
            ->paginate(12);

        return view('livewire.suppliers.enquiry-index', [
            'enquiries' => $enquiries,
            'suppliers' => \App\Models\Supplier::orderBy('name')->get(),
        ]);
    }
}