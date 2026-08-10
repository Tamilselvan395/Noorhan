<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user() && ! $request->user()->is_active) {
            Auth::logout();

            return redirect()->route('login')->withErrors(['email' => 'Account deactivated. Contact your administrator.']);
        }

        return $next($request);
    }
}