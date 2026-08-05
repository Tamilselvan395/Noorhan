<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Cookie\CookieJar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class TwoFactorService
{
    public const COOKIE = 'noorhan_2fa_device';

    public function isEnabled(): bool
    {
        return (bool) config('noorhan.auth.two_factor.enabled', false);
    }

    public function isEnrolled(User $user): bool
    {
        return $user->hasTwoFactorEnabled();
    }

    public function requiresChallenge(Request $request, User $user): bool
    {
        return $this->isEnabled()
            && $this->isEnrolled($user)
            && ! session('noorhan.2fa.verified', false)
            && ! $this->isDeviceTrusted($request, $user);
    }

    public function isDeviceTrusted(Request $request, User $user): bool
    {
        $cookie = $request->cookie(self::COOKIE);

        if (! $cookie) {
            return false;
        }

        try {
            [$userId, $expiresAt] = json_decode(Crypt::decryptString($cookie), true);

            return (int) $userId === $user->id && now()->timestamp < (int) $expiresAt;
        } catch (\Throwable) {
            return false;
        }
    }

    public function trustDeviceCookie(User $user): \Symfony\Component\HttpFoundation\Cookie
    {
        $days = (int) config('noorhan.auth.two_factor.remember_device_days', 7);
        $payload = json_encode([$user->id, now()->addDays($days)->timestamp]);

        return cookie(self::COOKIE, Crypt::encryptString($payload), $days * 24 * 60, null, null, true, true);
    }
}