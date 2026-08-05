<?php

namespace App\Services\Sessions;

use App\Helpers\AgentHelper;
use App\Models\User;
use App\Models\UserSession;
use Illuminate\Support\Collection;

class SessionManagementService
{
    public function activeFor(User $user): Collection
    {
        return $user->sessions()
            ->orderByDesc('last_activity')
            ->get()
            ->map(fn (UserSession $session) => $session
                ->setAttribute('agent', AgentHelper::parse($session->user_agent))
                ->setAttribute('is_current', $session->id === session()->getId()));
    }

    public function findForUser(User $user, string $id): ?UserSession
    {
        return $user->sessions()->whereKey($id)->first();
    }

    public function revokeOthers(User $user): int
    {
        return $user->sessions()->where('id', '!=', session()->getId())->delete();
    }

    public function revokeAll(User $user): int
    {
        return $user->sessions()->delete();
    }
}