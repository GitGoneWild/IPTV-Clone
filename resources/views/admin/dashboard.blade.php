@extends('admin.layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gh-text">Dashboard</h1>
        <p class="mt-2 text-sm text-gh-text-muted">Overview of your IPTV system</p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Users -->
        <div class="bg-gh-bg-secondary border border-gh-border rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gh-text-muted">Total Users</p>
                    <p class="text-3xl font-bold text-gh-text mt-2">{{ $stats['total_users'] }}</p>
                </div>
                <div class="bg-homelab-500/10 p-3 rounded-lg">
                    <svg class="w-8 h-8 text-homelab-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-4 text-sm">
                <span class="text-gh-success">{{ $stats['active_users'] }} active</span>
                <span class="text-gh-text-muted ml-2">• {{ $stats['guest_users'] }} guests</span>
            </div>
        </div>

        <!-- Total Streams -->
        <div class="bg-gh-bg-secondary border border-gh-border rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gh-text-muted">Total Streams</p>
                    <p class="text-3xl font-bold text-gh-text mt-2">{{ $stats['total_streams'] }}</p>
                </div>
                <div class="bg-gh-accent/10 p-3 rounded-lg">
                    <svg class="w-8 h-8 text-gh-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-4 text-sm">
                <span class="text-gh-success">{{ $stats['online_streams'] }} online</span>
                @if($stats['total_streams'] > 0)
                    <span class="text-gh-text-muted ml-2">
                        • {{ round(($stats['online_streams'] / $stats['total_streams']) * 100, 1) }}%
                    </span>
                @endif
            </div>
        </div>

        <!-- Categories -->
        <div class="bg-gh-bg-secondary border border-gh-border rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gh-text-muted">Categories</p>
                    <p class="text-3xl font-bold text-gh-text mt-2">{{ $stats['total_categories'] }}</p>
                </div>
                <div class="bg-gh-warning/10 p-3 rounded-lg">
                    <svg class="w-8 h-8 text-gh-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-4 text-sm text-gh-text-muted">
                Organized content
            </div>
        </div>

        <!-- Bouquets -->
        <div class="bg-gh-bg-secondary border border-gh-border rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gh-text-muted">Bouquets</p>
                    <p class="text-3xl font-bold text-gh-text mt-2">{{ $stats['total_bouquets'] }}</p>
                </div>
                <div class="bg-homelab-500/10 p-3 rounded-lg">
                    <svg class="w-8 h-8 text-homelab-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-4 text-sm text-gh-text-muted">
                Channel packages
            </div>
        </div>
    </div>

    <!-- Recent Users Table -->
    <div class="bg-gh-bg-secondary border border-gh-border rounded-lg">
        <div class="px-6 py-4 border-b border-gh-border">
            <h2 class="text-lg font-semibold text-gh-text">Recent Users</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gh-border">
                <thead class="bg-gh-bg-tertiary">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gh-text-muted uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gh-text-muted uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gh-text-muted uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gh-text-muted uppercase tracking-wider">Created</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gh-text-muted uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-gh-bg-secondary divide-y divide-gh-border">
                    @forelse($stats['recent_users'] as $user)
                        <tr class="hover:bg-gh-bg-tertiary">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gh-text">{{ $user->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gh-text-muted">{{ $user->email }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $role = $user->roles->first()?->name ?? 'guest';
                                @endphp
                                <x-role-badge :role="$role" />
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gh-text-muted">
                                {{ $user->created_at->diffForHumans() }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($user->is_active)
                                    <span class="px-2 py-1 text-xs font-medium rounded bg-gh-success/10 text-gh-success">Active</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-medium rounded bg-gh-danger/10 text-gh-danger">Inactive</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-sm text-gh-text-muted">No users found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gh-border">
            <a href="{{ route('admin.users.index') }}" class="text-sm text-homelab-500 hover:text-homelab-400">
                View all users →
            </a>
        </div>
    </div>
</div>
@endsection
