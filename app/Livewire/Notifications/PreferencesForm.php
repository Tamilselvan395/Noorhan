<?php

namespace App\Livewire\Notifications;

use App\Concerns\HasNotificationPreferences;
use Illuminate\View\View;
use Livewire\Component;

class PreferencesForm extends Component
{
    /** @var array<string, array<string, bool>> */
    public array $prefs = [];

    public const CATEGORIES = [
        'leads' => 'Leads & Routing',
        'sales' => 'Quotations & Orders',
        'finance' => 'Invoices & Payments',
        'system' => 'Security & System',
        'marketing' => 'Marketing & Campaigns',
    ];

    public function mount(): void
    {
        $this->prefs = auth()->user()->notificationPreferences();
    }

    public function save(): void
    {
        auth()->user()->updatePreferences($this->prefs);

        $this->dispatch('notify', message: 'Notification preferences saved.', type: 'success');
    }

    public function render(): View
    {
        return view('livewire.notifications.preferences-form', ['categories' => self::CATEGORIES]);
    }
}