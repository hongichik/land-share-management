<?php

namespace Hongdev\MasterAdmin\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MasterAdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (app()->environment('local')) {
            return $next($request);
        } elseif (app()->environment('production')) {
            if (auth()->check() && Auth::user()->id != 1) {
                return $next($request);
            }
            abort(403, 'Unauthorized');
        } 

        return $next($request);
    }
}
