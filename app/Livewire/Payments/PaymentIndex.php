<?php

namespace App\Livewire\Payments;

use App\Models\Payment;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class PaymentIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $method = 'all';

    public function updated($property): void
    {
        $this->resetPage();
    }

    public function stats(): array
    {
        return [
            'total_collected' => (float) Payment::where('status', 'completed')->sum('amount'),
            'this_month' => (float) Payment::where('status', 'completed')->whereMonth('payment_date', now()->month)->sum('amount'),
            'unallocated_credits' => (float) Payment::where('status', 'completed')->get()->sum(fn($p) => $p->unallocatedAmount()),
            'total_transactions' => Payment::where('status', 'completed')->count(),
        ];
    }

    public function render(): View
    {
        $payments = Payment::query()
            ->with('customer')
            ->when($this->search, fn ($q) => $q->where('reference', 'like', "%{$this->search}%")->orWhereHas('customer', fn($q) => $q->where('name', 'like', "%{$this->search}%")))
            ->when($this->method !== 'all', fn ($q) => $q->where('method', $this->method))
            ->latest('payment_date')
            ->paginate(12);

        return view('livewire.payments.payment-index', ['payments' => $payments]);
    }
}