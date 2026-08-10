<?php

namespace App\Actions\Auth;

use App\DTOs\Auth\LoginDTO;
use App\DTOs\Auth\LoginResult;
use App\Enums\LoginHistoryType;
use App\Enums\SecurityEvent;
use App\Events\Auth\AccountLocked;
use App\Events\Auth\UserLoggedIn;
use App\Repositories\LoginHistoryRepository;
use App\Repositories\SecurityLogRepository;
use App\Repositories\UserRepository;
use App\Services\Auth\LoginThrottleService;
use App\Services\Auth\TwoFactorService;
use App\Services\Sessions\SessionManagementService;
use Illuminate\Support\Facades\Auth;

class AuthenticateUserAction
{
    public function __construct(
        private UserRepository $users,
        private LoginHistoryRepository $history,
        private SecurityLogRepository $security,
        private LoginThrottleService $throttle,
        private SessionManagementService $sessions,
        private TwoFactorService $twoFactor,
    ) {}

    public function execute(LoginDTO $dto): LoginResult
    {
        $ip = request()->ip();
        $userAgent = request()->userAgent();
        $user = $this->users->findByEmail($dto->email);

        // 1 — Hard account lock
        if ($user && $user->isLocked()) {
            $this->history->record($user, LoginHistoryType::FailedLogin, false, $ip, $userAgent);
            $this->security->log(SecurityEvent::LoginBlockedLocked, $user);

            return LoginResult::locked(
                "Account is locked. Try again {$user->locked_until->diffForHumans()} or contact your administrator."
            );
        }

        if ($user && ! $user->is_active) {
            $this->security->log(SecurityEvent::LoginBlockedLocked, $user, ['reason' => 'inactive_account']);

            return LoginResult::locked('Account deactivated. Contact your administrator.');
        }

        // 2 — Request rate limiting
        if ($this->throttle->isThrottled($dto->email, $ip)) {
            $seconds = $this->throttle->availableIn($dto->email, $ip);
            $this->security->log(SecurityEvent::LoginThrottled, $user, ['email' => $dto->email]);

            return LoginResult::throttled("Too many login attempts. Please try again in {$seconds} seconds.");
        }

        // 3 — Credential attempt
        if (Auth::attempt(['email' => strtolower($dto->email), 'password' => $dto->password], $dto->remember)) {
            session()->regenerate();

            /** @var \App\Models\User $user */
            $user = Auth::user();

            $newDevice = $this->history->isNewDevice($user, $ip, $userAgent);

            $user->forceFill([
                'failed_login_attempts' => 0,
                'locked_until'          => null,
                'last_login_at'         => now(),
                'last_login_ip'         => $ip,
            ])->save();

            $this->throttle->clear($dto->email, $ip);
            $this->history->record($user, LoginHistoryType::Login, true, $ip, $userAgent);
            $this->security->log(SecurityEvent::LoginSuccess, $user);

            event(new UserLoggedIn($user, $newDevice, $ip));

            // 2FA-ready hook: redirect to challenge when enabled & enrolled.
            if ($this->twoFactor->requiresChallenge(request(), $user)) {
                session()->put('noorhan.2fa.pending', $user->id);
            }

            return LoginResult::success();
        }

        // 4 — Failure handling + progressive lockout
        $this->throttle->hit($dto->email, $ip);

        if ($user) {
            $user->increment('failed_login_attempts');
            $this->history->record($user, LoginHistoryType::FailedLogin, false, $ip, $userAgent);
            $this->security->log(SecurityEvent::LoginFailed, $user);

            if ($user->failed_login_attempts >= (int) config('noorhan.auth.max_failed_attempts', 5)) {
                $user->forceFill([
                    'locked_until' => now()->addMinutes((int) config('noorhan.auth.lockout_minutes', 15)),
                ])->save();

                $this->sessions->revokeAll($user);
                $this->security->log(SecurityEvent::AccountLocked, $user);

                event(new AccountLocked($user, $user->locked_until));

                return LoginResult::locked('Account locked due to multiple failed attempts.');
            }
        }

        return LoginResult::failed('These credentials do not match our records.');
    }
}