<?php

namespace App\Livewire\Companies;

use App\Models\Company;
use App\Models\Customer;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class CompanyIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $type = 'all';
    public string $status = 'all';

    public function updated($property): void
    {
        $this->resetPage();
    }

    public function stats(): array
    {
        return [
            'total' => Company::query()->count(),
            'active' => Company::query()->active()->count(),
            'partners' => Company::query()->whereIn('type', ['distributor', 'dealer'])->count(),
            'contacts' => Customer::query()->whereNotNull('company_id')->count(),
        ];
    }

    public function render(): View
    {
        $companies = Company::query()
            ->with('owner')
            ->withCount('contacts')
            ->search($this->search ?: null)
            ->when($this->type !== 'all', fn ($q) => $q->where('type', $this->type))
            ->when($this->status !== 'all', fn ($q) => $q->where('status', $this->status))
            ->latest()
            ->paginate(12);

        return view('livewire.companies.company-index', ['companies' => $companies]);
    }
}