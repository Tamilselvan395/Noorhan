<?php

namespace App\Livewire\Profile;

use App\Actions\Profile\ChangePasswordAction;
use App\DTOs\Profile\ChangePasswordDTO;
use App\Http\Requests\Profile\ChangePasswordRequest;
use Illuminate\View\View;
use Livewire\Component;

class UpdatePasswordForm extends Component
{
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function save(ChangePasswordAction $action): void
    {
        $data = $this->validate(ChangePasswordRequest::rules());

        $action->execute(auth()->user(), new ChangePasswordDTO(
            currentPassword: $data['current_password'],
            newPassword: $data['password'],
        ));

        $this->reset();

        $this->dispatch('notify', message: 'Password changed. Other sessions were signed out.', type: 'success');
    }

    public function render(): View
    {
        return view('livewire.profile.update-password-form');
    }
}