@extends('admin.layouts.admin')

@section('title', 'Create User')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center mb-4">
            <a href="{{ route('admin.users.index') }}" class="text-gh-text-muted hover:text-gh-text mr-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gh-text">Create User</h1>
                <p class="mt-2 text-sm text-gh-text-muted">Add a new user to the system</p>
            </div>
        </div>
    </div>

    <!-- Form -->
    <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- User Information -->
        <div class="bg-gh-bg-secondary border border-gh-border rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gh-text mb-4">User Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gh-text mb-2">Name *</label>
                    <input type="text" 
                           name="name" 
                           id="name" 
                           value="{{ old('name') }}"
                           required
                           class="w-full px-4 py-2 bg-gh-bg border border-gh-border rounded-lg text-gh-text focus:outline-none focus:ring-2 focus:ring-homelab-500 @error('name') border-gh-danger @enderror">
                    @error('name')
                        <p class="mt-1 text-sm text-gh-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gh-text mb-2">Email *</label>
                    <input type="email" 
                           name="email" 
                           id="email" 
                           value="{{ old('email') }}"
                           required
                           class="w-full px-4 py-2 bg-gh-bg border border-gh-border rounded-lg text-gh-text focus:outline-none focus:ring-2 focus:ring-homelab-500 @error('email') border-gh-danger @enderror">
                    @error('email')
                        <p class="mt-1 text-sm text-gh-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="username" class="block text-sm font-medium text-gh-text mb-2">Username *</label>
                    <input type="text" 
                           name="username" 
                           id="username" 
                           value="{{ old('username') }}"
                           required
                           class="w-full px-4 py-2 bg-gh-bg border border-gh-border rounded-lg text-gh-text focus:outline-none focus:ring-2 focus:ring-homelab-500 @error('username') border-gh-danger @enderror">
                    @error('username')
                        <p class="mt-1 text-sm text-gh-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gh-text mb-2">Password *</label>
                    <input type="password" 
                           name="password" 
                           id="password" 
                           required
                           class="w-full px-4 py-2 bg-gh-bg border border-gh-border rounded-lg text-gh-text focus:outline-none focus:ring-2 focus:ring-homelab-500 @error('password') border-gh-danger @enderror">
                    @error('password')
                        <p class="mt-1 text-sm text-gh-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gh-text mb-2">Confirm Password *</label>
                    <input type="password" 
                           name="password_confirmation" 
                           id="password_confirmation" 
                           required
                           class="w-full px-4 py-2 bg-gh-bg border border-gh-border rounded-lg text-gh-text focus:outline-none focus:ring-2 focus:ring-homelab-500">
                </div>
            </div>
        </div>

        <!-- Role & Permissions -->
        <div class="bg-gh-bg-secondary border border-gh-border rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gh-text mb-4">Role & Permissions</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="role" class="block text-sm font-medium text-gh-text mb-2">Primary Role *</label>
                    <select name="role" 
                            id="role" 
                            required
                            class="w-full px-4 py-2 bg-gh-bg border border-gh-border rounded-lg text-gh-text focus:outline-none focus:ring-2 focus:ring-homelab-500 @error('role') border-gh-danger @enderror">
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}" {{ old('role', 'guest') == $role->name ? 'selected' : '' }}>
                                {{ ucfirst($role->name) }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-sm text-gh-text-muted">Guest users are automatically upgraded to User when a package is assigned</p>
                    @error('role')
                        <p class="mt-1 text-sm text-gh-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="reseller_id" class="block text-sm font-medium text-gh-text mb-2">Parent Reseller</label>
                    <select name="reseller_id" 
                            id="reseller_id" 
                            class="w-full px-4 py-2 bg-gh-bg border border-gh-border rounded-lg text-gh-text focus:outline-none focus:ring-2 focus:ring-homelab-500">
                        <option value="">None</option>
                        @foreach($resellers as $reseller)
                            <option value="{{ $reseller->id }}" {{ old('reseller_id') == $reseller->id ? 'selected' : '' }}>
                                {{ $reseller->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" 
                           name="is_active" 
                           id="is_active" 
                           value="1"
                           {{ old('is_active', true) ? 'checked' : '' }}
                           class="w-4 h-4 text-homelab-600 bg-gh-bg border-gh-border rounded focus:ring-homelab-500">
                    <label for="is_active" class="ml-2 text-sm text-gh-text">Active</label>
                </div>
            </div>
        </div>

        <!-- Subscription & Limits -->
        <div class="bg-gh-bg-secondary border border-gh-border rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gh-text mb-4">Subscription & Limits</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="expires_at" class="block text-sm font-medium text-gh-text mb-2">Expiry Date</label>
                    <input type="datetime-local" 
                           name="expires_at" 
                           id="expires_at" 
                           value="{{ old('expires_at') }}"
                           class="w-full px-4 py-2 bg-gh-bg border border-gh-border rounded-lg text-gh-text focus:outline-none focus:ring-2 focus:ring-homelab-500">
                </div>

                <div>
                    <label for="max_connections" class="block text-sm font-medium text-gh-text mb-2">Max Connections</label>
                    <input type="number" 
                           name="max_connections" 
                           id="max_connections" 
                           value="{{ old('max_connections', 1) }}"
                           min="1"
                           class="w-full px-4 py-2 bg-gh-bg border border-gh-border rounded-lg text-gh-text focus:outline-none focus:ring-2 focus:ring-homelab-500">
                </div>

                <div>
                    <label for="credits" class="block text-sm font-medium text-gh-text mb-2">Credits (for resellers)</label>
                    <input type="number" 
                           name="credits" 
                           id="credits" 
                           value="{{ old('credits', 0) }}"
                           min="0"
                           class="w-full px-4 py-2 bg-gh-bg border border-gh-border rounded-lg text-gh-text focus:outline-none focus:ring-2 focus:ring-homelab-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gh-text mb-2">Allowed Output Formats</label>
                    <div class="space-y-2">
                        @foreach($outputFormats as $key => $label)
                            <div class="flex items-center">
                                <input type="checkbox" 
                                       name="allowed_outputs[]" 
                                       id="output_{{ $key }}" 
                                       value="{{ $key }}"
                                       {{ in_array($key, old('allowed_outputs', [])) ? 'checked' : '' }}
                                       class="w-4 h-4 text-homelab-600 bg-gh-bg border-gh-border rounded focus:ring-homelab-500">
                                <label for="output_{{ $key }}" class="ml-2 text-sm text-gh-text">{{ $label }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-end space-x-4">
            <a href="{{ route('admin.users.index') }}" 
               class="px-6 py-2 bg-gh-bg-tertiary hover:bg-gh-border text-gh-text rounded-lg transition-colors">
                Cancel
            </a>
            <button type="submit" 
                    class="px-6 py-2 bg-homelab-600 hover:bg-homelab-700 text-white rounded-lg transition-colors">
                Create User
            </button>
        </div>
    </form>
</div>
@endsection
