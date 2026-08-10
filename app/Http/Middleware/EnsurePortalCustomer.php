<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsurePortalCustomer
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->user()->customer_id) {
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}