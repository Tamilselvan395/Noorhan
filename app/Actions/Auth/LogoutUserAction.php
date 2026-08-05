<?php

namespace App\Actions\Auth;

use App\Enums\LoginHistoryType;
use App\Enums\SecurityEvent;
use App\Repositories\LoginHistoryRepository;
use App\Repositories\SecurityLogRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogoutUserAction
{
    public function __construct(
        private LoginHistoryRepository $history,
        private SecurityLogRepository $security,
    ) {}

    public function execute(Request $request): void
    {
        $user = $request->user();

        if ($user) {
            $this->history->record($user, LoginHistoryType::Logout, true, $request->ip(), $request->userAgent());
            $this->security->log(SecurityEvent::Logout, $user);
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}