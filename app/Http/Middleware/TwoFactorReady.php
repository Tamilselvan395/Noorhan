<?php

namespace App\Http\Middleware;

use App\Services\Auth\TwoFactorService;
use Closure;
use Illuminate\Http\Request;

class TwoFactorReady
{
    public function __construct(private TwoFactorService $twoFactor) {}

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && $this->twoFactor->requiresChallenge($request, $user)) {
            // Challenge screen ships with the 2FA activation module.
            return redirect()->route('two-factor.challenge');
        }

        return $next($request);
    }
}