@extends('layouts.app')

@section('title', 'Dashboard - StreamPilot')

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
        <div class="bg-gray-800 rounded-lg p-6 border border-gray-700 mb-8" x-data="{ showCredentials: true }">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-semibold text-white flex items-center">
                    <svg class="h-5 w-5 mr-2 text-homelab-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                    </svg>
                    Your Playlist URLs
                </h2>
                <button
                    @click="showCredentials = !showCredentials"
                    class="flex items-center px-3 py-1.5 text-sm font-medium rounded-lg transition-colors"
                    :class="showCredentials ? 'bg-gray-700 text-gray-300 hover:bg-gray-600' : 'bg-homelab-600 text-white'"
                    aria-label="Toggle credential visibility"
                >
                    <svg x-show="showCredentials" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                    </svg>
                    <svg x-show="!showCredentials" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" x-cloak>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <span x-text="showCredentials ? 'Hide Credentials' : 'Show Credentials'"></span>
                </button>
            </div>

            <div class="space-y-4">
                <!-- M3U Playlist URL Card -->
                <div class="bg-gray-900/50 rounded-lg p-4 border border-gray-700">
                    <div class="flex items-center mb-2">
                        <svg class="h-5 w-5 mr-2 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                        </svg>
                        <label class="text-sm font-medium text-gray-300">M3U Playlist URL</label>
                        <span class="ml-2 px-2 py-0.5 text-xs bg-green-900/50 text-green-400 rounded-full">Recommended</span>
                    </div>
                    <div class="flex">
                        <input
                            :type="showCredentials ? 'text' : 'password'"
                            readonly
                            value="{{ config('app.url') }}/get.php?username={{ auth()->user()->username }}&password={{ auth()->user()->api_password }}&type=m3u_plus"
                            class="flex-1 bg-gray-900 border border-gray-700 rounded-l-lg px-3 py-2 text-sm text-gray-300 font-mono focus:outline-none focus:ring-2 focus:ring-homelab-500 focus:border-transparent"
                            aria-label="M3U Playlist URL"
                        >
                        <button
                            onclick="copyToClipboard(this.previousElementSibling)"
                            class="bg-homelab-600 hover:bg-homelab-700 px-4 py-2 rounded-r-lg text-sm font-medium transition-colors flex items-center"
                            aria-label="Copy M3U Playlist URL"
                        >
                            <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                            Copy
                        </button>
                    </div>
                    <p class="mt-2 text-xs text-gray-500">Use this URL with most IPTV players (VLC, Kodi, TiviMate, etc.)</p>
                </div>

                <!-- Xtream Codes API Card -->
                <div class="bg-gray-900/50 rounded-lg p-4 border border-gray-700">
                    <div class="flex items-center mb-3">
                        <svg class="h-5 w-5 mr-2 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                        </svg>
                        <label class="text-sm font-medium text-gray-300">Xtream Codes API</label>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div class="bg-gray-800 rounded-lg p-3 border border-gray-700">
                            <span class="block text-xs text-gray-500 mb-1">Server URL</span>
                            <div class="flex items-center justify-between">
                                <code class="text-sm text-homelab-400 truncate" x-text="showCredentials ? '{{ config('app.url') }}' : '••••••••••••'">{{ config('app.url') }}</code>
                                <button onclick="navigator.clipboard.writeText('{{ config('app.url') }}')" class="text-gray-400 hover:text-white p-1" aria-label="Copy server URL">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="bg-gray-800 rounded-lg p-3 border border-gray-700">
                            <span class="block text-xs text-gray-500 mb-1">Username</span>
                            <div class="flex items-center justify-between">
                                <code class="text-sm text-homelab-400 truncate" x-text="showCredentials ? '{{ auth()->user()->username }}' : '••••••••'">{{ auth()->user()->username }}</code>
                                <button onclick="navigator.clipboard.writeText('{{ auth()->user()->username }}')" class="text-gray-400 hover:text-white p-1" aria-label="Copy username">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="bg-gray-800 rounded-lg p-3 border border-gray-700">
                            <span class="block text-xs text-gray-500 mb-1">Password</span>
                            <div class="flex items-center justify-between">
                                <code class="text-sm text-homelab-400 truncate" x-text="showCredentials ? '{{ auth()->user()->api_password }}' : '••••••••'">{{ auth()->user()->api_password }}</code>
                                <button onclick="navigator.clipboard.writeText('{{ auth()->user()->api_password }}')" class="text-gray-400 hover:text-white p-1" aria-label="Copy password">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    <p class="mt-2 text-xs text-gray-500">For IPTV Smarters, Xciptv, and other Xtream-compatible apps</p>
                </div>

                <!-- EPG URL Card -->
                <div class="bg-gray-900/50 rounded-lg p-4 border border-gray-700">
                    <div class="flex items-center mb-2">
                        <svg class="h-5 w-5 mr-2 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <label class="text-sm font-medium text-gray-300">EPG URL (XMLTV)</label>
                    </div>
                    <div class="flex">
                        <input
                            :type="showCredentials ? 'text' : 'password'"
                            readonly
                            value="{{ config('app.url') }}/xmltv.php?username={{ auth()->user()->username }}&password={{ auth()->user()->api_password }}"
                            class="flex-1 bg-gray-900 border border-gray-700 rounded-l-lg px-3 py-2 text-sm text-gray-300 font-mono focus:outline-none focus:ring-2 focus:ring-homelab-500 focus:border-transparent"
                            aria-label="EPG URL"
                        >
                        <button
                            onclick="copyToClipboard(this.previousElementSibling)"
                            class="bg-homelab-600 hover:bg-homelab-700 px-4 py-2 rounded-r-lg text-sm font-medium transition-colors flex items-center"
                            aria-label="Copy EPG URL"
                        >
                            <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                            Copy
                        </button>
                    </div>
                    <p class="mt-2 text-xs text-gray-500">Electronic Program Guide for channel listings and schedules</p>
                </div>
            </div>

            <!-- Security Notice -->
            <div class="mt-4 flex items-start p-3 bg-yellow-900/20 rounded-lg border border-yellow-700/50">
                <svg class="h-5 w-5 text-yellow-500 mr-2 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div class="text-xs text-yellow-200">
                    <p class="font-semibold mb-1">Security Notice:</p>
                    <p>Your credentials are visible by default for easy copying. Keep them private and never share these URLs publicly. Use the "Hide Credentials" button above to mask them when needed.</p>
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
