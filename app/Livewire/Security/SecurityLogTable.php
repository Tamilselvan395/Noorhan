<?php

namespace App\Livewire\Security;

use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class SecurityLogTable extends Component
{
    use WithPagination;

    public function render(): View
    {
        return view('livewire.security.security-log-table', [
            'logs' => auth()->user()->securityLogs()->latest()->paginate(8),
        ]);
    }
}
