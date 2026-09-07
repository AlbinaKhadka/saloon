<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AddTokenFromCookie
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->hasHeader('Authorization') && $request->hasCookie('access_token')) {
            $request->headers->set('Authorization', 'Bearer ' . $request->cookie('access_token'));
        }

        return $next($request);
    }
}
