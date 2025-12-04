<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     * Updated to use Spatie Laravel-Permission for role checking.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user()) {
            return redirect()->route('login');
        }

        // Check using Spatie's hasRole method
        foreach ($roles as $role) {
            if ($request->user()->hasRole($role)) {
                return $next($request);
            }
        }

        // Fallback to legacy role checking for backward compatibility
        $userRole = $request->user()->role;
        if (in_array($userRole, $roles)) {
            return $next($request);
        }

        abort(403, 'Unauthorized. You do not have permission to access this resource.');
    }
}
