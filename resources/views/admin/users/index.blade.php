@extends('admin.layouts.admin')

@section('title', 'Users')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gh-text">Users</h1>
            <p class="mt-2 text-sm text-gh-text-muted">Manage user accounts and permissions</p>
        </div>
        <a href="{{ route('admin.users.create') }}" 
           class="inline-flex items-center px-4 py-2 bg-homelab-600 hover:bg-homelab-700 text-white font-medium rounded-lg transition-colors">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            New User
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-gh-bg-secondary border border-gh-border rounded-lg p-4 mb-6">
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-wrap gap-4">
            <!-- Search -->
            <div class="flex-1 min-w-[200px]">
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}"
                       placeholder="Search by name, email, or username..." 
                       class="w-full px-4 py-2 bg-gh-bg border border-gh-border rounded-lg text-gh-text placeholder-gh-text-muted focus:outline-none focus:ring-2 focus:ring-homelab-500">
            </div>

            <!-- Role Filter -->
            <select name="role" 
                    class="px-4 py-2 bg-gh-bg border border-gh-border rounded-lg text-gh-text focus:outline-none focus:ring-2 focus:ring-homelab-500">
                <option value="">All Roles</option>
                @foreach($roles as $role)
                    <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>
                        {{ ucfirst($role->name) }}
                    </option>
                @endforeach
            </select>

            <!-- Active Filter -->
            <select name="is_active" 
                    class="px-4 py-2 bg-gh-bg border border-gh-border rounded-lg text-gh-text focus:outline-none focus:ring-2 focus:ring-homelab-500">
                <option value="">All Status</option>
                <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Active</option>
                <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactive</option>
            </select>

            <!-- Submit -->
            <button type="submit" 
                    class="px-6 py-2 bg-homelab-600 hover:bg-homelab-700 text-white rounded-lg transition-colors">
                Filter
            </button>

            <!-- Reset -->
            @if(request()->hasAny(['search', 'role', 'is_active']))
                <a href="{{ route('admin.users.index') }}" 
                   class="px-6 py-2 bg-gh-bg-tertiary hover:bg-gh-border text-gh-text rounded-lg transition-colors">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Users Table -->
    <div class="bg-gh-bg-secondary border border-gh-border rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gh-border">
                <thead class="bg-gh-bg-tertiary">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gh-text-muted uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gh-text-muted uppercase tracking-wider">Username</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gh-text-muted uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gh-text-muted uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gh-text-muted uppercase tracking-wider">Expires</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gh-text-muted uppercase tracking-wider">Created</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gh-text-muted uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-gh-bg-secondary divide-y divide-gh-border">
                    @forelse($users as $user)
                        <tr class="hover:bg-gh-bg-tertiary">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-gh-text">{{ $user->name }}</div>
                                    <div class="text-sm text-gh-text-muted">{{ $user->email }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gh-text">
                                {{ $user->username }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $role = $user->roles->first()?->name ?? 'guest';
                                @endphp
                                <x-role-badge :role="$role" />
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($user->is_active)
                                    <span class="px-2 py-1 text-xs font-medium rounded bg-gh-success/10 text-gh-success">Active</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-medium rounded bg-gh-danger/10 text-gh-danger">Inactive</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gh-text-muted">
                                @if($user->expires_at)
                                    {{ $user->expires_at->format('Y-m-d') }}
                                @else
                                    <span class="text-gh-text-muted">Never</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gh-text-muted">
                                {{ $user->created_at->format('Y-m-d') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('admin.users.edit', $user) }}" 
                                   class="text-homelab-500 hover:text-homelab-400 mr-3">
                                    Edit
                                </a>
                                @if($user->id !== auth()->id())
                                    <form action="{{ route('admin.users.destroy', $user) }}" 
                                          method="POST" 
                                          class="inline"
                                          onsubmit="return confirm('Are you sure you want to delete this user?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-gh-danger hover:text-red-400">
                                            Delete
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-gh-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                                <p class="mt-4 text-gh-text-muted">No users found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($users->hasPages())
            <div class="px-6 py-4 border-t border-gh-border">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
