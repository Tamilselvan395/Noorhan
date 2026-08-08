<?php

namespace App\Livewire\Invoices;

use App\Models\Invoice;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class InvoiceIndex extends Component
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
        $outstanding = Invoice::query()->outstanding()->sum('balance_due');
        $overdue = Invoice::query()->outstanding()->where('due_date', '<', now())->sum('balance_due');

        return [
            'total_invoiced' => (float) Invoice::query()->sum('total'),
            'outstanding' => (float) $outstanding,
            'overdue' => (float) $overdue,
            'paid_month' => (float) Invoice::query()->where('status', 'paid')
                ->whereMonth('updated_at', now()->month)->sum('paid_amount'),
        ];
    }

    public function render(): View
    {
        $invoices = Invoice::query()
            ->with(['customer', 'salesOrder'])
            ->when($this->search, fn ($q) => $q->where('reference', 'like', "%{$this->search}%"))
            ->when($this->status !== 'all', fn ($q) => $q->where('status', $this->status))
            ->latest()
            ->paginate(12);

        return view('livewire.invoices.invoice-index', ['invoices' => $invoices]);
    }
}