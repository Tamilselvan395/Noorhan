<?php

namespace App\Livewire\Settings;

use App\Models\Setting;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;
use Livewire\Component;

class SettingsHub extends Component
{
    public string $section = 'general';

    public array $general = [];
    public array $defaults = [];
    public string $userTheme = 'light';

    public string $maintenanceMessage = '';

    public function mount(): void
    {
        $this->load();
    }

    private function load(): void
    {
        $this->general = [
            'company_name' => (string) Setting::get('general.company_name', config('app.name')),
            'company_email' => (string) Setting::get('general.company_email', ''),
            'company_phone' => (string) Setting::get('general.company_phone', ''),
            'address' => (string) Setting::get('general.address', ''),
            'country' => (string) Setting::get('general.country', 'UAE'),
            'timezone' => (string) Setting::get('general.timezone', 'Asia/Dubai'),
            'date_format' => (string) Setting::get('general.date_format', 'M d, Y'),
        ];

        $this->defaults = [
            'currency' => (string) Setting::get('defaults.currency', 'USD'),
            'tax_rate' => (string) Setting::get('defaults.tax_rate', 5),
            'quotation_valid_days' => (string) Setting::get('defaults.quotation_valid_days', config('noorhan.quotation.default_valid_days', 15)),
            'invoice_due_days' => (string) Setting::get('defaults.invoice_due_days', 15),
            'payment_terms' => (string) Setting::get('defaults.payment_terms', 'Payment due within 15 days of invoice date.'),
        ];

        $this->userTheme = auth()->user()->theme ?? 'light';
    }

    public function switchSection(string $section): void
    {
        $this->section = $section;
        $this->maintenanceMessage = '';
    }

    public function saveGeneral(): void
    {
        $this->validate([
            'general.company_name' => ['required', 'string', 'max:160'],
            'general.company_email' => ['nullable', 'email'],
            'general.timezone' => ['required', 'string'],
        ]);

        foreach ($this->general as $key => $value) {
            Setting::set('general.'.$key, $value);
        }

        config(['app.name' => $this->general['company_name']]);

        $this->dispatch('notify', message: 'General settings saved.', type: 'success');
    }

    public function saveDefaults(): void
    {
        $this->validate([
            'defaults.currency' => ['required', 'string', 'max:10'],
            'defaults.tax_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'defaults.quotation_valid_days' => ['required', 'integer', 'min:1', 'max:365'],
            'defaults.invoice_due_days' => ['required', 'integer', 'min:1', 'max:365'],
        ]);

        foreach ($this->defaults as $key => $value) {
            Setting::set('defaults.'.$key, $value);
        }

        // Reflect immediately in the running request
        config(['noorhan.quotation.default_valid_days' => (int) $this->defaults['quotation_valid_days']]);

        $this->dispatch('notify', message: 'Commercial defaults saved.', type: 'success');
    }

    public function saveAppearance(): void
    {
        $this->validate(['userTheme' => ['required', 'in:light,dark']]);

        auth()->user()->update(['theme' => $this->userTheme]);

        Setting::set('appearance.default_theme', $this->userTheme);

        $this->dispatch('notify', message: 'Appearance saved. Applies on next page load.', type: 'success');
    }

    public function runPrune(): void
    {
        Artisan::call('system:prune-logs');
        $this->maintenanceMessage = trim(Artisan::output());
    }

    public function clearCache(): void
    {
        Artisan::call('cache:clear');
        $this->maintenanceMessage = 'Application cache cleared.';
    }

    public function render(): View
    {
        return view('livewire.settings.settings-hub');
    }
}