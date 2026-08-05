<?php

namespace App\Livewire\Profile;

use App\Actions\Profile\UpdateProfileAction;
use App\DTOs\Profile\ProfileUpdateDTO;
use App\Http\Requests\Profile\UpdateProfileRequest;
use Illuminate\View\View;
use Livewire\Component;

class UpdateProfileForm extends Component
{
    public string $name = '';
    public string $email = '';

    public function mount(): void
    {
        $this->name = auth()->user()->name;
        $this->email = auth()->user()->email;
    }

    public function save(UpdateProfileAction $action): void
    {
        $data = $this->validate(UpdateProfileRequest::rules(auth()->user()));

        $action->execute(auth()->user(), ProfileUpdateDTO::fromArray($data));

        $this->dispatch('notify', message: 'Profile updated successfully.', type: 'success');
    }

    public function render(): View
    {
        return view('livewire.profile.update-profile-form');
    }
}