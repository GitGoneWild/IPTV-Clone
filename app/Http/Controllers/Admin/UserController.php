<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

/**
 * Admin User Management Controller
 * Handles CRUD operations for users in the admin panel.
 */
class UserController extends AdminController
{
    /**
     * Get the available output formats configuration.
     */
    private function getOutputFormats(): array
    {
        return config('homelabtv.output_formats', [
            'm3u' => 'M3U Playlist',
            'xtream' => 'Xtream Codes',
            'enigma2' => 'Enigma2',
        ]);
    }

    /**
     * Display a listing of users.
     */
    public function index(Request $request): View
    {
        $query = User::query()->with('roles', 'reseller', 'bouquets');

        // Search functionality
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($role = $request->get('role')) {
            $query->role($role);
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $users = $query->latest()->paginate(15)->withQueryString();
        $roles = Role::all();

        return view('admin.users.index', compact('users', 'roles'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): View
    {
        $roles = Role::all();
        $resellers = User::where('is_reseller', true)->get();
        $outputFormats = $this->getOutputFormats();

        return view('admin.users.create', compact('roles', 'resellers', 'outputFormats'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'username' => ['required', 'string', 'max:255', 'unique:users', 'alpha_dash'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', Rule::in(['guest', 'user', 'reseller', 'admin'])],
            'is_active' => ['boolean'],
            'reseller_id' => ['nullable', 'exists:users,id'],
            'expires_at' => ['nullable', 'date'],
            'max_connections' => ['nullable', 'integer', 'min:1'],
            'credits' => ['nullable', 'integer', 'min:0'],
            'allowed_outputs' => ['nullable', 'array'],
        ]);

        // Create user
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'username' => $validated['username'],
            'password' => Hash::make($validated['password']),
            'is_active' => $validated['is_active'] ?? true,
            'reseller_id' => $validated['reseller_id'] ?? null,
            'expires_at' => $validated['expires_at'] ?? null,
            'max_connections' => $validated['max_connections'] ?? 1,
            'credits' => $validated['credits'] ?? 0,
            'allowed_outputs' => $validated['allowed_outputs'] ?? [],
            'is_admin' => $validated['role'] === 'admin',
            'is_reseller' => in_array($validated['role'], ['admin', 'reseller']),
            'api_token' => bin2hex(random_bytes(32)),
        ]);

        // Assign role
        $user->assignRole($validated['role']);

        activity()
            ->performedOn($user)
            ->causedBy(auth()->user())
            ->withProperties(['role' => $validated['role']])
            ->log('User created via admin panel');

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user): View
    {
        $roles = Role::all();
        $resellers = User::where('is_reseller', true)->where('id', '!=', $user->id)->get();
        $outputFormats = $this->getOutputFormats();
        $currentRole = $user->roles->first()?->name ?? 'guest';

        return view('admin.users.edit', compact('user', 'roles', 'resellers', 'outputFormats', 'currentRole'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->id), 'alpha_dash'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', Rule::in(['guest', 'user', 'reseller', 'admin'])],
            'is_active' => ['boolean'],
            'reseller_id' => ['nullable', 'exists:users,id'],
            'expires_at' => ['nullable', 'date'],
            'max_connections' => ['nullable', 'integer', 'min:1'],
            'credits' => ['nullable', 'integer', 'min:0'],
            'allowed_outputs' => ['nullable', 'array'],
        ]);

        // Update user
        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'username' => $validated['username'],
            'is_active' => $validated['is_active'] ?? true,
            'reseller_id' => $validated['reseller_id'] ?? null,
            'expires_at' => $validated['expires_at'] ?? null,
            'max_connections' => $validated['max_connections'] ?? 1,
            'credits' => $validated['credits'] ?? 0,
            'allowed_outputs' => $validated['allowed_outputs'] ?? [],
            'is_admin' => $validated['role'] === 'admin',
            'is_reseller' => in_array($validated['role'], ['admin', 'reseller']),
        ]);

        // Update password if provided
        if (!empty($validated['password'])) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        // Sync role
        $user->syncRoles([$validated['role']]);

        // Check for role upgrade from guest to user
        if ($validated['role'] === 'guest' && $user->hasPackageAssigned()) {
            $user->upgradeFromGuestToUser();
        }

        activity()
            ->performedOn($user)
            ->causedBy(auth()->user())
            ->withProperties(['role' => $validated['role']])
            ->log('User updated via admin panel');

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        // Prevent self-deletion
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $userName = $user->name;
        $user->delete();

        activity()
            ->causedBy(auth()->user())
            ->withProperties(['deleted_user' => $userName])
            ->log('User deleted via admin panel');

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }
}
