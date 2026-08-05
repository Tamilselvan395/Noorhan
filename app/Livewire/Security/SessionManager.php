<?php

namespace App\Livewire\Security;

use App\Actions\Sessions\RevokeSessionAction;
use App\Enums\SecurityEvent;
use App\Repositories\SecurityLogRepository;
use App\Services\Sessions\SessionManagementService;
use Illuminate\View\View;
use Livewire\Component;

class SessionManager extends Component
{
    public function __construct(
        public SessionManagementService $sessionService,
        private SecurityLogRepository $security,
    ) {}

    public function revoke(string $id, RevokeSessionAction $action): void
    {
        $action->execute(auth()->user(), $id);

        $this->dispatch('notify', message: 'Session revoked.', type: 'success');
    }

    public function revokeOthers(): void
    {
        $this->sessionService->revokeOthers(auth()->user());
        $this->security->log(SecurityEvent::AllSessionsRevoked, auth()->user());

        $this->dispatch('notify', message: 'All other sessions were signed out.', type: 'success');
    }

    public function render(): View
    {
        return view('livewire.security.session-manager', [
            'sessions' => $this->sessionService->activeFor(auth()->user()),
        ]);
    }
}