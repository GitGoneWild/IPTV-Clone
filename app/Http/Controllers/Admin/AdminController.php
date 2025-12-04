<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

/**
 * Base controller for all admin panel controllers.
 * Provides common functionality and authorization checks.
 */
class AdminController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        // All admin routes require authentication and admin role
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * Return success JSON response.
     */
    protected function successResponse(string $message, mixed $data = null, int $status = 200): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    /**
     * Return error JSON response.
     */
    protected function errorResponse(string $message, mixed $errors = null, int $status = 400): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }
}
