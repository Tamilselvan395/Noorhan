<?php

namespace App\Enums;

enum SecurityEvent: string
{
    case LoginSuccess          = 'login_success';
    case LoginFailed           = 'login_failed';
    case LoginBlockedLocked    = 'login_blocked_locked';
    case LoginThrottled        = 'login_throttled';
    case Logout                = 'logout';
    case AccountLocked         = 'account_locked';
    case PasswordChanged       = 'password_changed';
    case PasswordResetRequested = 'password_reset_requested';
    case PasswordResetCompleted = 'password_reset_completed';
    case ProfileUpdated        = 'profile_updated';
    case SessionRevoked        = 'session_revoked';
    case AllSessionsRevoked    = 'all_sessions_revoked';

    public function label(): string
    {
        return ucwords(str_replace('_', ' ', $this->value));
    }
}