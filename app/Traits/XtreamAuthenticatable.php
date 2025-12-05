<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

trait XtreamAuthenticatable
{
    /**
     * Authenticate user from request credentials.
     * Supports both query parameters and route parameters.
     */
    protected function authenticateXtreamUser(Request $request): ?User
    {
        // Check query parameters first, then fall back to route parameters
        $username = $request->get('username') ?? $request->route('username');
        $password = $request->get('password') ?? $request->route('password');

        if (! $username || ! $password) {
            return null;
        }

        $user = User::where('username', $username)->first();

        if (! $user || ! $user->validateXtreamPassword($password)) {
            return null;
        }

        if (! $user->canAccessStreams()) {
            return null;
        }

        return $user;
    }

    /**
     * Generate unauthorized response for Xtream API.
     */
    protected function unauthorizedXtreamResponse(string $message = 'Invalid credentials'): JsonResponse
    {
        return response()->json([
            'user_info' => [
                'auth' => 0,
                'status' => 'Disabled',
                'message' => $message,
            ],
        ], 401);
    }
}
