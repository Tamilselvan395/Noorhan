<?php

namespace App\Http\Middleware;

use App\Services\Auth\TwoFactorService;
use Closure;
use Illuminate\Http\Request;

class TwoFactorReady
{
    public function __construct(
        protected TwoFactorService $twoFactor,
    ) {}

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user
            && $this->twoFactor->requiresChallenge($request, $user)
            && ! $request->routeIs('two-factor.*')
            && ! $request->routeIs('logout')
        ) {
            return redirect()->route('two-factor.challenge');
        }

        return $next($request);
    }
}