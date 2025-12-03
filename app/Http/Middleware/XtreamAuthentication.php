<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class XtreamAuthentication
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $username = $request->get('username');
        $password = $request->get('password');

        if (!$username || !$password) {
            return $this->unauthorizedResponse();
        }

        $user = User::where('username', $username)->first();

        if (!$user) {
            return $this->unauthorizedResponse();
        }

        // Validate password (plain text for Xtream compatibility)
        if ($user->password !== $password && !password_verify($password, $user->password)) {
            return $this->unauthorizedResponse();
        }

        if (!$user->is_active) {
            return response()->json([
                'user_info' => [
                    'auth' => 0,
                    'status' => 'Disabled',
                    'message' => 'Account is disabled',
                ],
            ], 403);
        }

        if ($user->isExpired()) {
            return response()->json([
                'user_info' => [
                    'auth' => 0,
                    'status' => 'Expired',
                    'message' => 'Account has expired',
                ],
            ], 403);
        }

        // Store user in request for later use
        $request->merge(['xtream_user' => $user]);

        // Update last login
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        return $next($request);
    }

    /**
     * Return unauthorized response.
     */
    protected function unauthorizedResponse(): Response
    {
        return response()->json([
            'user_info' => [
                'auth' => 0,
                'status' => 'Disabled',
                'message' => 'Invalid credentials',
            ],
        ], 401);
    }
}
