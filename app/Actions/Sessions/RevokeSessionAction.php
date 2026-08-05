<?php

namespace App\Actions\Sessions;

use App\Enums\SecurityEvent;
use App\Models\User;
use App\Repositories\SecurityLogRepository;
use App\Services\Sessions\SessionManagementService;
use Illuminate\Support\Facades\Gate;

class RevokeSessionAction
{
    public function __construct(
        private SessionManagementService $sessions,
        private SecurityLogRepository $security,
    ) {}

    public function execute(User $user, string $sessionId): void
    {
        $session = $this->sessions->findForUser($user, $sessionId);

        abort_if($session === null, 404);

        Gate::forUser($user)->authorize('revoke', $session);

        $session->delete();

        $this->security->log(SecurityEvent::SessionRevoked, $user, ['session_id' => $sessionId]);
    }
}