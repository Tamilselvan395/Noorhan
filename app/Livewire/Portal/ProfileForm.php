<?php

namespace App\Livewire\Portal;

use Illuminate\View\View;
use Livewire\Component;

class ProfileForm extends Component
{
    public string $name = '';
    public string $phone = '';
    public string $whatsapp = '';
    public string $address = '';
    public string $city = '';
    public string $country = '';

    public function mount(): void
    {
        $customer = auth()->user()->customer;

        foreach (['name', 'phone', 'whatsapp', 'address', 'city', 'country'] as $field) {
            $this->{$field} = (string) ($customer->{$field} ?? '');
        }
    }

    public function save(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:30'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
        ]);

        $customer = auth()->user()->customer;

        $customer->update([
            'name' => $this->name,
            'phone' => $this->phone ?: null,
            'whatsapp' => $this->whatsapp ?: null,
            'address' => $this->address ?: null,
            'city' => $this->city ?: null,
            'country' => $this->country ?: null,
        ]);

        $customer->logActivity('updated their profile via the customer portal');

        // Keep the linked user's display name in sync
        auth()->user()->update(['name' => $this->name]);

        $this->dispatch('notify', message: 'Profile updated.', type: 'success');
    }

    public function render(): View
    {
        return view('livewire.portal.profile-form');
    }
}