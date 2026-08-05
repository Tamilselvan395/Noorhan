<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Enums\ThemeMode;

class SetTheme
{
    public function handle(Request $request, Closure $next)
    {
        $theme = $request->cookie('theme', config('noorhan.theme.default'));
        view()->share('currentTheme', $theme);
        return $next($request);
    }
}