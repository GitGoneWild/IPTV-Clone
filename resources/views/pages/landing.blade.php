@extends('layouts.app')

@section('title', 'HomelabTV - Private IPTV Management')

@section('content')
<!-- Hero Section -->
<div class="relative overflow-hidden">
    <div class="max-w-7xl mx-auto">
        <div class="relative z-10 pb-8 sm:pb-16 md:pb-20 lg:pb-28 xl:pb-32">
            <main class="mt-10 mx-auto max-w-7xl px-4 sm:mt-12 sm:px-6 md:mt-16 lg:mt-20 lg:px-8 xl:mt-28">
                <div class="text-center">
                    <h1 class="text-4xl tracking-tight font-extrabold text-white sm:text-5xl md:text-6xl">
                        <span class="block">Your Private</span>
                        <span class="block text-homelab-500">IPTV Management</span>
                    </h1>
                    <p class="mt-3 text-base text-gray-400 sm:mt-5 sm:text-lg sm:max-w-xl sm:mx-auto md:mt-5 md:text-xl">
                        HomelabTV is a self-hosted IPTV management panel designed for homelab enthusiasts. 
                        Manage your legal streams, CCTV cameras, and private channels all in one place.
                    </p>
                    <div class="mt-5 sm:mt-8 sm:flex sm:justify-center">
                        @auth
                            <div class="rounded-md shadow">
                                <a href="{{ route('dashboard') }}" class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-homelab-600 hover:bg-homelab-700 md:py-4 md:text-lg md:px-10">
                                    Go to Dashboard
                                </a>
                            </div>
                        @else
                            <div class="rounded-md shadow">
                                <a href="{{ route('login') }}" class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-homelab-600 hover:bg-homelab-700 md:py-4 md:text-lg md:px-10">
                                    Login
                                </a>
                            </div>
                        @endauth
                    </div>
                </div>
            </main>
        </div>
    </div>
</div>

<!-- Features Section -->
<div class="py-12 bg-gray-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-base text-homelab-500 font-semibold tracking-wide uppercase">Features</h2>
            <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-white sm:text-4xl">
                Everything you need for your homelab
            </p>
        </div>

        <div class="mt-10">
            <div class="grid grid-cols-1 gap-10 sm:grid-cols-2 lg:grid-cols-3">
                <!-- Feature 1 -->
                <div class="bg-gray-900 rounded-lg p-6 border border-gray-700">
                    <div class="flex items-center justify-center h-12 w-12 rounded-md bg-homelab-500 text-white">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="mt-4 text-lg font-medium text-white">Multi-Protocol Support</h3>
                    <p class="mt-2 text-gray-400">
                        Support for HLS, MPEG-TS, RTMP, and HTTP streams. Convert RTMP to HLS on the fly.
                    </p>
                </div>

                <!-- Feature 2 -->
                <div class="bg-gray-900 rounded-lg p-6 border border-gray-700">
                    <div class="flex items-center justify-center h-12 w-12 rounded-md bg-homelab-500 text-white">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <h3 class="mt-4 text-lg font-medium text-white">Xtream Codes Compatible</h3>
                    <p class="mt-2 text-gray-400">
                        100% compatible with Xtream Codes API. Works with any IPTV player out of the box.
                    </p>
                </div>

                <!-- Feature 3 -->
                <div class="bg-gray-900 rounded-lg p-6 border border-gray-700">
                    <div class="flex items-center justify-center h-12 w-12 rounded-md bg-homelab-500 text-white">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <h3 class="mt-4 text-lg font-medium text-white">User Management</h3>
                    <p class="mt-2 text-gray-400">
                        Create users with expiry dates, connection limits, and assigned channel bouquets.
                    </p>
                </div>

                <!-- Feature 4 -->
                <div class="bg-gray-900 rounded-lg p-6 border border-gray-700">
                    <div class="flex items-center justify-center h-12 w-12 rounded-md bg-homelab-500 text-white">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="mt-4 text-lg font-medium text-white">EPG Support</h3>
                    <p class="mt-2 text-gray-400">
                        Import XMLTV EPG data from URL or file upload. Automatic updates via cron.
                    </p>
                </div>

                <!-- Feature 5 -->
                <div class="bg-gray-900 rounded-lg p-6 border border-gray-700">
                    <div class="flex items-center justify-center h-12 w-12 rounded-md bg-homelab-500 text-white">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                        </svg>
                    </div>
                    <h3 class="mt-4 text-lg font-medium text-white">Server Load Balancing</h3>
                    <p class="mt-2 text-gray-400">
                        Distribute streams across multiple servers with weighted load balancing.
                    </p>
                </div>

                <!-- Feature 6 -->
                <div class="bg-gray-900 rounded-lg p-6 border border-gray-700">
                    <div class="flex items-center justify-center h-12 w-12 rounded-md bg-homelab-500 text-white">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <h3 class="mt-4 text-lg font-medium text-white">Stream Monitoring</h3>
                    <p class="mt-2 text-gray-400">
                        Automatic health checks for all streams with real-time status monitoring.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- API Info Section -->
<div class="py-12 bg-gray-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
            <h2 class="text-base text-homelab-500 font-semibold tracking-wide uppercase">API Endpoints</h2>
            <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-white sm:text-4xl">
                Xtream Codes Compatible
            </p>
        </div>
        
        <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="font-mono text-sm text-gray-300">
                    <p class="mb-2"><span class="text-homelab-400">/player_api.php</span> - Main API endpoint</p>
                    <p class="mb-2"><span class="text-homelab-400">/get.php</span> - M3U playlist generation</p>
                    <p class="mb-2"><span class="text-homelab-400">/panel_api.php</span> - Panel data</p>
                </div>
                <div class="font-mono text-sm text-gray-300">
                    <p class="mb-2"><span class="text-homelab-400">/xmltv.php</span> - EPG data (XMLTV)</p>
                    <p class="mb-2"><span class="text-homelab-400">/enigma2.php</span> - Enigma2 bouquet</p>
                    <p class="mb-2"><span class="text-homelab-400">/live/{user}/{pass}/{id}</span> - Direct stream</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
