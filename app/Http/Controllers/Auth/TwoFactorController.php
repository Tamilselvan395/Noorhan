<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\TotpService;
use App\Services\Auth\TwoFactorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TwoFactorController extends Controller
{
    public function __construct(
        private TotpService $totp,
        private TwoFactorService $twoFactor,
    ) {}

    /* ------------------------------------------------------------------
    | Enrollment (settings screen)
    |------------------------------------------------------------------ */

    public function settings(Request $request): View
    {
        return view('auth.two-factor-settings', [
            'enabled' => $request->user()->hasTwoFactorEnabled(),
            'pendingSecret' => session('2fa.secret'),
            'recoveryCodes' => session('2fa.recovery_codes', []),
        ]);
    }

    /** Step 1 — generate a secret and show provisioning QR. */
    public function enable(Request $request)
    {
        $secret = $this->totp->generateSecret();

        $request->user()->update([
            'two_factor_secret' => Crypt::encryptString($secret),
            'two_factor_confirmed_at' => null,
        ]);

        return redirect()->route('two-factor.settings')->with('2fa.secret', $secret);
    }

    /** Step 2 — verify a code from the authenticator app to activate. */
    public function confirm(Request $request)
    {
        $request->validate(['code' => 'required|string']);

        $user = $request->user();

        $secret = $user->two_factor_secret ? Crypt::decryptString($user->two_factor_secret) : null;

        abort_unless($secret !== null, 400);

        if (! $this->totp->verify($secret, $request->code)) {
            return back()->withErrors(['code' => 'Invalid code. Open your authenticator app and try again.']);
        }

        $codes = collect(range(1, 8))
            ->map(fn () => strtoupper(Str::random(4).'-'.Str::random(4)))
            ->all();

        $user->update([
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => Crypt::encryptString(json_encode($codes)),
        ]);

        $user->logActivity('enabled two-factor authentication');

        return redirect()->route('two-factor.settings')->with('2fa.recovery_codes', $codes);
    }

    public function disable(Request $request)
    {
        $request->user()->update([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ]);

        $request->user()->logActivity('disabled two-factor authentication');

        return redirect()->route('two-factor.settings')->with('status', 'Two-factor authentication disabled.');
    }

    /* ------------------------------------------------------------------
    | Login-time challenge
    |------------------------------------------------------------------ */

    public function challenge(Request $request): View|\Illuminate\Http\RedirectResponse
    {
        // Already verified or not enrolled → nothing to challenge.
        if (! $this->twoFactor->requiresChallenge($request, $request->user())) {
            return redirect()->route('dashboard');
        }

        return view('auth.two-factor-challenge');
    }

    public function verify(Request $request)
    {
        $user = $request->user();

        $secret = $user->two_factor_secret ? Crypt::decryptString($user->two_factor_secret) : null;

        $ok = $secret !== null && $this->totp->verify($secret, (string) $request->code);

        // Recovery code fallback (single-use)
        if (! $ok && $request->filled('recovery')) {
            $ok = $this->consumeRecoveryCode($user, strtoupper(trim((string) $request->recovery)));
        }

        if (! $ok) {
            return back()->withErrors(['code' => 'Invalid two-factor credentials.']);
        }

        session()->put('noorhan.2fa.verified', true);

        $user->logActivity('completed a two-factor challenge');

        $redirect = redirect()->intended(route('dashboard'));

        return $request->boolean('remember')
            ? $redirect->withCookie($this->twoFactor->trustDeviceCookie($user))
            : $redirect;
    }

    private function consumeRecoveryCode($user, string $code): bool
    {
        if (! $user->two_factor_recovery_codes) {
            return false;
        }

        $codes = collect(json_decode(Crypt::decryptString($user->two_factor_recovery_codes), true) ?? []);

        if (! $codes->contains($code)) {
            return false;
        }

        $user->update([
            'two_factor_recovery_codes' => Crypt::encryptString(
                json_encode($codes->reject(fn ($c) => $c === $code)->values()->all())
            ),
        ]);

        return true;
    }
}