<?php

namespace App\Actions\Profile;

use App\DTOs\Profile\ProfileUpdateDTO;
use App\Enums\SecurityEvent;
use App\Models\User;
use App\Repositories\SecurityLogRepository;
use Illuminate\Support\Str;

class UpdateProfileAction
{
    public function __construct(private SecurityLogRepository $security) {}

    public function execute(User $user, ProfileUpdateDTO $dto): void
    {
        $emailChanged = strcasecmp($user->email, $dto->email) !== 0;

        $user->forceFill(['name' => $dto->name, 'email' => Str::lower($dto->email)]);

        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        $user->save();

        if ($emailChanged) {
            $user->sendEmailVerificationNotification();
        }

        $this->security->log(SecurityEvent::ProfileUpdated, $user, ['email_changed' => $emailChanged]);
        $user->logActivity('updated their profile');
    }
}