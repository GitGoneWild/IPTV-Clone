<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

trait XtreamAuthenticatable
{
    /**
     * Authenticate user from request credentials.
     */
    protected function authenticateXtreamUser(Request $request): ?User
    {
        $username = $request->get('username');
        $password = $request->get('password');

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
    protected function unauthorizedXtreamResponse(string $message = 'Invalid credentials'): Response
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
