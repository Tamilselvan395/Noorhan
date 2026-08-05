<?php

namespace App\Livewire\Security;

use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class LoginHistoryTable extends Component
{
    use WithPagination;

    public function render(): View
    {
        return view('livewire.security.login-history-table', [
            'histories' => auth()->user()->loginHistories()->latest()->paginate(8),
        ]);
    }
}