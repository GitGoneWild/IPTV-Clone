<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    /**
     * Show the registration form.
     */
    public function showRegistrationForm(): View
    {
        return view('auth.register');
    }

    /**
     * Handle a registration request.
     */
    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'username' => ['required', 'string', 'max:255', 'unique:users', 'alpha_dash'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        // Create user with default settings
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'username' => $validated['username'],
            'password' => Hash::make($validated['password']),
            'is_active' => true,
            'is_admin' => false,
            'is_reseller' => false,
            'max_connections' => config('homelabtv.max_connections_per_user', 1),
            'allowed_outputs' => ['m3u', 'xtream', 'enigma2'],
        ]);

        // Assign default 'guest' role
        $user->assignRole('guest');

        // Generate API token for the user
        $user->generateApiToken();

        // Log the registration activity
        activity()
            ->causedBy($user)
            ->withProperties(['ip' => $request->ip()])
            ->log('User registered with guest role');

        // Log the user in
        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'Welcome! Your account has been created. Please wait for an administrator to assign you a package to access streams.');
    }
}
