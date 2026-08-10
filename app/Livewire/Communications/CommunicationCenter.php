<?php

namespace App\Livewire\Communications;

use App\Models\Communication;
use App\Models\Customer;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class CommunicationCenter extends Component
{
    use WithPagination;

    public string $channel = 'all';
    public string $direction = 'all';
    public string $search = '';

    public function updated($property): void
    {
        $this->resetPage();
    }

    public function entityLink(Communication $communication): ?string
    {
        $map = [
            'App\Models\Customer' => 'customers.show',
            'App\Models\Company' => 'companies.show',
            'App\Models\Lead' => 'leads.show',
        ];

        $route = $map[$communication->communicable_type] ?? null;

        return $route && $communication->communicable ? route($route, $communication->communicable) : null;
    }

    public function render(): View
    {
        $communications = Communication::query()
            ->whereIn('communicable_type', ['App\Models\Customer', 'App\Models\Company', 'App\Models\Lead'])
            ->with(['user', 'communicable'])
            ->when($this->channel !== 'all', fn ($q) => $q->where('channel', $this->channel))
            ->when($this->direction !== 'all', fn ($q) => $q->where('direction', $this->direction))
            ->when($this->search, fn ($q) => $q->where(fn ($q) => $q
                ->where('subject', 'like', "%{$this->search}%")
                ->orWhere('body', 'like', "%{$this->search}%")))
            ->latest('occurred_at')
            ->paginate(20);

        return view('livewire.communications.communication-center', ['communications' => $communications]);
    }
}