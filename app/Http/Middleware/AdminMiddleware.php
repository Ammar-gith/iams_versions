<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    /** App\Models\User */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            /** @var \App\Models\User */
            $user = Auth::user();
            if ($user && $user->hasRole(['Super Admin', 'Admin'])) {
                return $next($request);
            }
            abort(403, 'User does not have the right roles.');
        }
        abort(401, 'Unauthorized action.');
    }
}
