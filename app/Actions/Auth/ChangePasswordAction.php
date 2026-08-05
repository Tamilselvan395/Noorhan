<?php

namespace App\Actions\Profile;

use App\DTOs\Profile\ChangePasswordDTO;
use App\Enums\SecurityEvent;
use App\Events\Auth\PasswordChanged;
use App\Models\User;
use App\Repositories\SecurityLogRepository;
use App\Services\Sessions\SessionManagementService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ChangePasswordAction
{
    public function __construct(
        private SecurityLogRepository $security,
        private SessionManagementService $sessions,
    ) {}

    public function execute(User $user, ChangePasswordDTO $dto): void
    {
        if (! Hash::check($dto->currentPassword, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => __('The current password is incorrect.'),
            ]);
        }

        if (Hash::check($dto->newPassword, $user->password)) {
            throw ValidationException::withMessages([
                'password' => __('The new password must be different from the current one.'),
            ]);
        }

        $user->forceFill([
            'password'            => $dto->newPassword, // hashed cast
            'password_changed_at' => now(),
        ])->save();

        // Enterprise hardening: invalidate every other device.
        $this->sessions->revokeOthers($user);

        $this->security->log(SecurityEvent::PasswordChanged, $user);
        $user->logActivity('changed their password');

        event(new PasswordChanged($user));
    }
}