<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureHotspotUserVerified
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->isVerified()) {
            if (! $request->routeIs('verification.*') && ! $request->routeIs('logout')) {
                return redirect()->route('verification.notice');
            }
        }

        return $next($request);
    }
}
