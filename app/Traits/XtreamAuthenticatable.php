<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * XtreamAuthenticatable Trait
 *
 * Provides authentication functionality for Xtream Codes API-compatible endpoints.
 * This trait handles user authentication using either query parameters or route parameters,
 * making it flexible for different URL formats used by various IPTV players.
 *
 * Authentication Flow:
 * 1. Extract username and password from request (query or route parameters)
 * 2. Find user by username in database
 * 3. Validate password/token using User model's validateXtreamPassword method
 * 4. Check if user account is active and not expired
 *
 * Security Notes:
 * - Supports API tokens (recommended) and password authentication (legacy)
 * - Returns generic unauthorized response to avoid leaking user information
 * - Checks account status (active/expired) before granting access
 */
trait XtreamAuthenticatable
{
    /**
     * Authenticate user from request credentials.
     * Supports both query parameters and route parameters.
     *
     * Query parameters format: ?username=user&password=token
     * Route parameters format: /{username}/{password}
     *
     * This dual support ensures compatibility with various IPTV player implementations
     * that may use either URL format.
     *
     * @param  Request  $request  The HTTP request containing credentials
     * @return User|null Authenticated user model or null if authentication fails
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
     *
     * Returns a standardized JSON response for authentication failures.
     * The response format matches Xtream Codes API specification to ensure
     * compatibility with IPTV players expecting this format.
     *
     * @param  string  $message  Optional custom error message
     * @return JsonResponse JSON response with auth=0 and status=Disabled (HTTP 401)
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
