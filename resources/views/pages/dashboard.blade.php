@extends('layouts.app')

@section('title', 'Dashboard - HomelabTV')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header with Role Badge -->
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-white">Welcome, {{ auth()->user()->name }}!</h1>
                <p class="mt-2 text-gray-400">Manage your streams and playlists from here.</p>
            </div>
            <div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                    @if($role === 'admin') bg-red-900/50 text-red-300 border border-red-500/50
                    @elseif($role === 'reseller') bg-purple-900/50 text-purple-300 border border-purple-500/50
                    @else bg-green-900/50 text-green-300 border border-green-500/50
                    @endif">
                    {{ ucfirst($role) }}
                </span>
            </div>
        </div>

        <!-- Admin Dashboard Section -->
        @if($role === 'admin')
        <div class="mb-8 bg-gradient-to-r from-red-900/20 to-transparent rounded-lg p-6 border border-red-500/30">
            <h2 class="text-lg font-semibold text-red-300 mb-4 flex items-center">
                <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                Admin Controls
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="/admin" class="bg-gray-800/50 hover:bg-gray-700/50 rounded-lg p-4 border border-gray-700 transition-colors">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 p-2 bg-red-600 rounded-lg">
                            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-white">Admin Panel</p>
                            <p class="text-xs text-gray-400">Full system control</p>
                        </div>
                    </div>
                </a>
                <div class="bg-gray-800/50 rounded-lg p-4 border border-gray-700">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 p-2 bg-blue-600 rounded-lg">
                            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-white">Total Users</p>
                            <p class="text-lg font-bold text-blue-400">{{ $totalUsers ?? 0 }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-800/50 rounded-lg p-4 border border-gray-700">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 p-2 bg-green-600 rounded-lg">
                            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-white">Active Streams</p>
                            <p class="text-lg font-bold text-green-400">{{ $activeStreams ?? 0 }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Reseller Dashboard Section -->
        @if($role === 'reseller')
        <div class="mb-8 bg-gradient-to-r from-purple-900/20 to-transparent rounded-lg p-6 border border-purple-500/30">
            <h2 class="text-lg font-semibold text-purple-300 mb-4 flex items-center">
                <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                Reseller Controls
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-gray-800/50 rounded-lg p-4 border border-gray-700">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 p-2 bg-purple-600 rounded-lg">
                            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-white">Your Clients</p>
                            <p class="text-lg font-bold text-purple-400">{{ auth()->user()->clients->count() }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-800/50 rounded-lg p-4 border border-gray-700">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 p-2 bg-yellow-600 rounded-lg">
                            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-white">Credits</p>
                            <p class="text-lg font-bold text-yellow-400">{{ auth()->user()->credits }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-800/50 rounded-lg p-4 border border-gray-700">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 p-2 bg-green-600 rounded-lg">
                            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-white">Active Clients</p>
                            <p class="text-lg font-bold text-green-400">{{ auth()->user()->clients->where('is_active', true)->count() }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Quick Actions -->
        <div class="mb-8">
            <a href="{{ route('streams') }}" class="inline-flex items-center px-6 py-3 bg-homelab-600 hover:bg-homelab-700 text-white font-medium rounded-lg transition-colors glow-accent">
                <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M8 5v14l11-7z"/>
                </svg>
                Watch Streams
            </a>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
                <div class="flex items-center">
                    <div class="flex-shrink-0 p-3 bg-homelab-600 rounded-lg">
                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-400">Available Streams</p>
                        <p class="text-2xl font-semibold text-white">{{ auth()->user()->getAvailableStreams()->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
                <div class="flex items-center">
                    <div class="flex-shrink-0 p-3 bg-green-600 rounded-lg">
                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-400">Account Status</p>
                        <p class="text-2xl font-semibold {{ auth()->user()->is_active ? 'text-green-400' : 'text-red-400' }}">
                            {{ auth()->user()->is_active ? 'Active' : 'Inactive' }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
                <div class="flex items-center">
                    <div class="flex-shrink-0 p-3 bg-blue-600 rounded-lg">
                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-400">Expires</p>
                        <p class="text-2xl font-semibold text-white">
                            {{ auth()->user()->expires_at ? auth()->user()->expires_at->format('M d, Y') : 'Never' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Playlist URLs -->
        <div class="bg-gray-800 rounded-lg p-6 border border-gray-700 mb-8">
            <h2 class="text-xl font-semibold text-white mb-4">Your Playlist URLs</h2>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1">M3U Playlist URL</label>
                    <div class="flex">
                        <input type="text" readonly
                               value="{{ config('app.url') }}/get.php?username={{ auth()->user()->username }}&password={{ auth()->user()->api_password }}&type=m3u_plus"
                               class="flex-1 bg-gray-900 border border-gray-700 rounded-l-md px-3 py-2 text-sm text-gray-300 font-mono">
                        <button onclick="copyToClipboard(this.previousElementSibling)"
                                class="bg-homelab-600 hover:bg-homelab-700 px-4 py-2 rounded-r-md text-sm font-medium">
                            Copy
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1">Xtream Codes API</label>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500">Server:</span>
                            <code class="ml-2 text-homelab-400">{{ config('app.url') }}</code>
                        </div>
                        <div>
                            <span class="text-gray-500">Username:</span>
                            <code class="ml-2 text-homelab-400">{{ auth()->user()->username }}</code>
                        </div>
                        <div>
                            <span class="text-gray-500">Password:</span>
                            <code class="ml-2 text-homelab-400">{{ auth()->user()->api_password }}</code>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1">EPG URL (XMLTV)</label>
                    <div class="flex">
                        <input type="text" readonly
                               value="{{ config('app.url') }}/xmltv.php?username={{ auth()->user()->username }}&password={{ auth()->user()->api_password }}"
                               class="flex-1 bg-gray-900 border border-gray-700 rounded-l-md px-3 py-2 text-sm text-gray-300 font-mono">
                        <button onclick="copyToClipboard(this.previousElementSibling)"
                                class="bg-homelab-600 hover:bg-homelab-700 px-4 py-2 rounded-r-md text-sm font-medium">
                            Copy
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assigned Bouquets -->
        <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
            <h2 class="text-xl font-semibold text-white mb-4">Your Bouquets</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse(auth()->user()->bouquets as $bouquet)
                    <div class="bg-gray-900 rounded-lg p-4 border border-gray-700">
                        <h3 class="font-medium text-white">{{ $bouquet->name }}</h3>
                        <p class="text-sm text-gray-400 mt-1">{{ $bouquet->streams()->count() }} streams</p>
                    </div>
                @empty
                    <p class="text-gray-400">No bouquets assigned yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
async function copyToClipboard(input) {
    const text = input.value;
    const button = input.nextElementSibling;
    const originalText = button.textContent;

    try {
        await navigator.clipboard.writeText(text);
        button.textContent = 'Copied!';
    } catch (err) {
        // Fallback for older browsers
        input.select();
        document.execCommand('copy');
        button.textContent = 'Copied!';
    }

    setTimeout(() => {
        button.textContent = originalText;
    }, 2000);
}
</script>
@endpush
@endsection
