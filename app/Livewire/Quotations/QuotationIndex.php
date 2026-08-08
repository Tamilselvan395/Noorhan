<?php

namespace App\Livewire\Quotations;

use App\Models\Quotation;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class QuotationIndex extends Component
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
        $accepted = Quotation::query()->where('status', 'accepted')->count();
        $sent = Quotation::query()->where('status', 'sent')->count();

        return [
            'open' => Quotation::query()->open()->count(),
            'open_value' => (float) Quotation::query()->open()->sum('total'),
            'accepted' => $accepted,
            'win_rate' => $sent > 0 ? round(($accepted / max($sent + $accepted, 1)) * 100, 1) : 0.0,
            'pending_approval' => Quotation::query()->where('status', 'pending_approval')->count(),
        ];
    }

    public function render(): View
    {
        $quotations = Quotation::query()
            ->with(['customer', 'lead', 'creator'])
            ->when($this->search, fn ($q) => $q->where('reference', 'like', "%{$this->search}%"))
            ->when($this->status !== 'all', fn ($q) => $q->where('status', $this->status))
            ->latest()
            ->paginate(12);

        return view('livewire.quotations.quotation-index', ['quotations' => $quotations]);
    }
}