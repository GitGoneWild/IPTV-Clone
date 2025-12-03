@extends('layouts.app')

@section('title', 'HomelabTV - Private IPTV Management')

@section('content')
<!-- Hero Section with GitHub Copilot inspired design -->
<div class="relative overflow-hidden">
    <!-- Background gradient -->
    <div class="absolute inset-0 bg-gradient-to-b from-homelab-900/20 to-transparent pointer-events-none"></div>

    <div class="max-w-7xl mx-auto">
        <div class="relative z-10 pb-8 sm:pb-16 md:pb-20 lg:pb-28 xl:pb-32">
            <main class="mt-10 mx-auto max-w-7xl px-4 sm:mt-12 sm:px-6 md:mt-16 lg:mt-20 lg:px-8 xl:mt-28">
                <div class="lg:grid lg:grid-cols-12 lg:gap-8">
                    <!-- Hero Content -->
                    <div class="lg:col-span-7 text-center lg:text-left">
                        <!-- Animated badge -->
                        <div class="inline-flex items-center px-4 py-1.5 rounded-full text-sm font-medium bg-gh-bg-tertiary border border-gh-border mb-6">
                            <span class="flex h-2 w-2 relative mr-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-gh-success opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-gh-success"></span>
                            </span>
                            <span class="text-gh-text-muted">Self-hosted IPTV for your homelab</span>
                        </div>

                        <h1 class="text-4xl tracking-tight font-extrabold text-white sm:text-5xl md:text-6xl">
                            <span class="block">Your Private</span>
                            <span class="block bg-gradient-to-r from-homelab-400 to-homelab-600 bg-clip-text text-transparent">IPTV Management</span>
                        </h1>
                        <p class="mt-3 text-base text-gh-text-muted sm:mt-5 sm:text-lg md:mt-5 md:text-xl lg:max-w-xl">
                            HomelabTV is a self-hosted IPTV management panel designed for homelab enthusiasts.
                            Manage your legal streams, CCTV cameras, and private channels all in one place.
                        </p>
                        <div class="mt-5 sm:mt-8 flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                            @auth
                                <a href="{{ route('dashboard') }}" class="flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-homelab-600 hover:bg-homelab-700 md:py-4 md:text-lg md:px-10 transition-all duration-200 glow-accent">
                                    Go to Dashboard
                                    <svg class="ml-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                    </svg>
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-homelab-600 hover:bg-homelab-700 md:py-4 md:text-lg md:px-10 transition-all duration-200 glow-accent">
                                    Get Started
                                    <svg class="ml-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                    </svg>
                                </a>
                                <a href="#features" class="flex items-center justify-center px-8 py-3 border border-gh-border text-base font-medium rounded-md text-gh-text hover:bg-gh-bg-tertiary md:py-4 md:text-lg md:px-10 transition-colors">
                                    Learn More
                                </a>
                            @endauth
                        </div>
                    </div>

                    <!-- Live Server Status Widget -->
                    <div class="lg:col-span-5 mt-10 lg:mt-0">
                        <div id="server-status-widget" class="bg-gh-bg-secondary rounded-xl p-6 border border-gh-border shadow-xl">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-white flex items-center">
                                    <svg class="h-5 w-5 mr-2 text-gh-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2" />
                                    </svg>
                                    Server Status
                                </h3>
                                <span id="status-indicator" class="flex items-center text-sm">
                                    <span class="flex h-2.5 w-2.5 relative mr-2">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75" id="status-ping"></span>
                                        <span class="relative inline-flex rounded-full h-2.5 w-2.5" id="status-dot"></span>
                                    </span>
                                    <span id="status-text" class="font-medium">Loading...</span>
                                </span>
                            </div>

                            <div class="space-y-4">
                                <!-- Uptime -->
                                <div class="flex items-center justify-between p-3 bg-gh-bg rounded-lg border border-gh-border-muted">
                                    <div class="flex items-center">
                                        <svg class="h-5 w-5 text-gh-success mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span class="text-gh-text-muted">Uptime</span>
                                    </div>
                                    <span id="uptime-value" class="font-semibold text-white">--</span>
                                </div>

                                <!-- Streams -->
                                <div class="flex items-center justify-between p-3 bg-gh-bg rounded-lg border border-gh-border-muted">
                                    <div class="flex items-center">
                                        <svg class="h-5 w-5 text-gh-accent mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                        </svg>
                                        <span class="text-gh-text-muted">Active Streams</span>
                                    </div>
                                    <span class="font-semibold text-white">
                                        <span id="online-streams">--</span>
                                        <span class="text-gh-text-muted">/</span>
                                        <span id="total-streams">--</span>
                                    </span>
                                </div>

                                <!-- Last Updated -->
                                <div class="text-center pt-2 border-t border-gh-border-muted">
                                    <span class="text-xs text-gh-text-muted">
                                        Last updated: <span id="last-updated">--</span>
                                    </span>
                                </div>
                            </div>

                            @guest
                            {{-- Quick Login Form - Uses same route as main login page.
                                 Rate limiting is handled by Laravel's built-in throttling
                                 via the ThrottleRequests middleware. --}}
                            <div class="mt-6 pt-4 border-t border-gh-border">
                                <h4 class="text-sm font-medium text-white mb-3">Quick Access</h4>
                                <form method="POST" action="{{ route('login.store') }}" class="space-y-3">
                                    @csrf
                                    <input type="email" name="email" placeholder="Email" required
                                        class="w-full px-3 py-2 bg-gh-bg border border-gh-border rounded-md text-white placeholder-gh-text-muted focus:outline-none focus:ring-2 focus:ring-homelab-500 focus:border-transparent text-sm">
                                    <input type="password" name="password" placeholder="Password" required
                                        class="w-full px-3 py-2 bg-gh-bg border border-gh-border rounded-md text-white placeholder-gh-text-muted focus:outline-none focus:ring-2 focus:ring-homelab-500 focus:border-transparent text-sm">
                                    <button type="submit" class="w-full px-4 py-2 bg-homelab-600 hover:bg-homelab-700 text-white font-medium rounded-md transition-colors text-sm">
                                        Sign In
                                    </button>
                                </form>
                            </div>
                            @endguest
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</div>

<!-- Stats Section -->
<div class="border-y border-gh-border bg-gh-bg-secondary py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
            <div class="text-center">
                <p class="text-3xl font-bold text-white">10-50</p>
                <p class="text-sm text-gh-text-muted">Channels Supported</p>
            </div>
            <div class="text-center">
                <p class="text-3xl font-bold text-white">∞</p>
                <p class="text-sm text-gh-text-muted">Users</p>
            </div>
            <div class="text-center">
                <p class="text-3xl font-bold text-gh-success">100%</p>
                <p class="text-sm text-gh-text-muted">Xtream Compatible</p>
            </div>
            <div class="text-center">
                <p class="text-3xl font-bold text-white">24/7</p>
                <p class="text-sm text-gh-text-muted">Stream Monitoring</p>
            </div>
        </div>
    </div>
</div>

<!-- Features Section -->
<div id="features" class="py-16 bg-gh-bg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-base text-gh-accent font-semibold tracking-wide uppercase">Features</h2>
            <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-white sm:text-4xl">
                Everything you need for your homelab
            </p>
            <p class="mt-4 max-w-2xl text-xl text-gh-text-muted mx-auto">
                Built with best practices, optimized for performance, and designed for simplicity.
            </p>
        </div>

        <div class="mt-16">
            <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
                <!-- Feature 1 -->
                <div class="relative bg-gh-bg-secondary rounded-lg p-6 border border-gh-border hover:border-gh-accent/50 transition-colors group">
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 h-24 w-24 bg-homelab-600/10 rounded-full blur-2xl group-hover:bg-homelab-600/20 transition-colors"></div>
                    <div class="relative">
                        <div class="flex items-center justify-center h-12 w-12 rounded-lg bg-homelab-600/20 border border-homelab-600/30 text-homelab-400">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h3 class="mt-4 text-lg font-medium text-white">Multi-Protocol Support</h3>
                        <p class="mt-2 text-gh-text-muted">
                            Support for HLS, MPEG-TS, RTMP, and HTTP streams. Convert RTMP to HLS on the fly.
                        </p>
                    </div>
                </div>

                <!-- Feature 2 -->
                <div class="relative bg-gh-bg-secondary rounded-lg p-6 border border-gh-border hover:border-gh-accent/50 transition-colors group">
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 h-24 w-24 bg-gh-success/10 rounded-full blur-2xl group-hover:bg-gh-success/20 transition-colors"></div>
                    <div class="relative">
                        <div class="flex items-center justify-center h-12 w-12 rounded-lg bg-gh-success/20 border border-gh-success/30 text-gh-success">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <h3 class="mt-4 text-lg font-medium text-white">Xtream Codes Compatible</h3>
                        <p class="mt-2 text-gh-text-muted">
                            100% compatible with Xtream Codes API. Works with any IPTV player out of the box.
                        </p>
                    </div>
                </div>

                <!-- Feature 3 -->
                <div class="relative bg-gh-bg-secondary rounded-lg p-6 border border-gh-border hover:border-gh-accent/50 transition-colors group">
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 h-24 w-24 bg-gh-accent/10 rounded-full blur-2xl group-hover:bg-gh-accent/20 transition-colors"></div>
                    <div class="relative">
                        <div class="flex items-center justify-center h-12 w-12 rounded-lg bg-gh-accent/20 border border-gh-accent/30 text-gh-accent">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                        <h3 class="mt-4 text-lg font-medium text-white">Role-Based Access</h3>
                        <p class="mt-2 text-gh-text-muted">
                            Admin, Reseller, and Viewer roles with custom dashboards and permissions for each.
                        </p>
                    </div>
                </div>

                <!-- Feature 4 -->
                <div class="relative bg-gh-bg-secondary rounded-lg p-6 border border-gh-border hover:border-gh-accent/50 transition-colors group">
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 h-24 w-24 bg-gh-warning/10 rounded-full blur-2xl group-hover:bg-gh-warning/20 transition-colors"></div>
                    <div class="relative">
                        <div class="flex items-center justify-center h-12 w-12 rounded-lg bg-gh-warning/20 border border-gh-warning/30 text-gh-warning">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h3 class="mt-4 text-lg font-medium text-white">EPG Support</h3>
                        <p class="mt-2 text-gh-text-muted">
                            Import XMLTV EPG data from URL or file. Easy channel linking with automatic updates.
                        </p>
                    </div>
                </div>

                <!-- Feature 5 -->
                <div class="relative bg-gh-bg-secondary rounded-lg p-6 border border-gh-border hover:border-gh-accent/50 transition-colors group">
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 h-24 w-24 bg-homelab-600/10 rounded-full blur-2xl group-hover:bg-homelab-600/20 transition-colors"></div>
                    <div class="relative">
                        <div class="flex items-center justify-center h-12 w-12 rounded-lg bg-homelab-600/20 border border-homelab-600/30 text-homelab-400">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                            </svg>
                        </div>
                        <h3 class="mt-4 text-lg font-medium text-white">Server Load Balancing</h3>
                        <p class="mt-2 text-gh-text-muted">
                            Distribute streams across multiple servers with weighted load balancing.
                        </p>
                    </div>
                </div>

                <!-- Feature 6 -->
                <div class="relative bg-gh-bg-secondary rounded-lg p-6 border border-gh-border hover:border-gh-accent/50 transition-colors group">
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 h-24 w-24 bg-gh-success/10 rounded-full blur-2xl group-hover:bg-gh-success/20 transition-colors"></div>
                    <div class="relative">
                        <div class="flex items-center justify-center h-12 w-12 rounded-lg bg-gh-success/20 border border-gh-success/30 text-gh-success">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <h3 class="mt-4 text-lg font-medium text-white">Stream Monitoring</h3>
                        <p class="mt-2 text-gh-text-muted">
                            Automatic health checks with real-time status. API usage tracking and analytics.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- API Info Section -->
<div class="py-16 bg-gh-bg-secondary border-t border-gh-border">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-base text-gh-accent font-semibold tracking-wide uppercase">API Endpoints</h2>
            <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-white sm:text-4xl">
                Xtream Codes Compatible
            </p>
        </div>

        <div class="bg-gh-bg rounded-lg p-6 border border-gh-border">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-3">
                    <div class="flex items-center space-x-3 p-3 bg-gh-bg-tertiary rounded-lg border border-gh-border-muted">
                        <span class="px-2 py-1 text-xs font-mono font-semibold rounded bg-gh-success/20 text-gh-success">GET</span>
                        <code class="text-sm text-gh-accent">/player_api.php</code>
                        <span class="text-xs text-gh-text-muted ml-auto">Main API</span>
                    </div>
                    <div class="flex items-center space-x-3 p-3 bg-gh-bg-tertiary rounded-lg border border-gh-border-muted">
                        <span class="px-2 py-1 text-xs font-mono font-semibold rounded bg-gh-success/20 text-gh-success">GET</span>
                        <code class="text-sm text-gh-accent">/get.php</code>
                        <span class="text-xs text-gh-text-muted ml-auto">M3U Playlist</span>
                    </div>
                    <div class="flex items-center space-x-3 p-3 bg-gh-bg-tertiary rounded-lg border border-gh-border-muted">
                        <span class="px-2 py-1 text-xs font-mono font-semibold rounded bg-gh-success/20 text-gh-success">GET</span>
                        <code class="text-sm text-gh-accent">/panel_api.php</code>
                        <span class="text-xs text-gh-text-muted ml-auto">Panel Data</span>
                    </div>
                </div>
                <div class="space-y-3">
                    <div class="flex items-center space-x-3 p-3 bg-gh-bg-tertiary rounded-lg border border-gh-border-muted">
                        <span class="px-2 py-1 text-xs font-mono font-semibold rounded bg-gh-accent/20 text-gh-accent">GET</span>
                        <code class="text-sm text-gh-accent">/xmltv.php</code>
                        <span class="text-xs text-gh-text-muted ml-auto">EPG Data</span>
                    </div>
                    <div class="flex items-center space-x-3 p-3 bg-gh-bg-tertiary rounded-lg border border-gh-border-muted">
                        <span class="px-2 py-1 text-xs font-mono font-semibold rounded bg-gh-accent/20 text-gh-accent">GET</span>
                        <code class="text-sm text-gh-accent">/enigma2.php</code>
                        <span class="text-xs text-gh-text-muted ml-auto">Enigma2</span>
                    </div>
                    <div class="flex items-center space-x-3 p-3 bg-gh-bg-tertiary rounded-lg border border-gh-border-muted">
                        <span class="px-2 py-1 text-xs font-mono font-semibold rounded bg-homelab-600/20 text-homelab-400">GET</span>
                        <code class="text-sm text-gh-accent">/live/{user}/{pass}/{id}</code>
                        <span class="text-xs text-gh-text-muted ml-auto">Stream</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- CTA -->
        <div class="mt-12 text-center">
            <p class="text-gh-text-muted mb-4">Ready to get started with your private IPTV setup?</p>
            @guest
            <a href="{{ route('login') }}" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-homelab-600 hover:bg-homelab-700 transition-colors glow-accent">
                Login to Get Started
                <svg class="ml-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </a>
            @endguest
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    /**
     * Live Server Status Widget
     * Fetches and displays real-time server status from the API
     */
    (function() {
        const statusColors = {
            operational: { bg: 'bg-gh-success', text: 'text-gh-success', label: 'Operational' },
            degraded: { bg: 'bg-gh-warning', text: 'text-gh-warning', label: 'Degraded' },
            offline: { bg: 'bg-gh-danger', text: 'text-gh-danger', label: 'Offline' }
        };

        function updateStatus(data) {
            const statusConfig = statusColors[data.status] || statusColors.offline;

            // Update status indicator
            const statusPing = document.getElementById('status-ping');
            const statusDot = document.getElementById('status-dot');
            const statusText = document.getElementById('status-text');

            statusPing.className = 'animate-ping absolute inline-flex h-full w-full rounded-full opacity-75 ' + statusConfig.bg;
            statusDot.className = 'relative inline-flex rounded-full h-2.5 w-2.5 ' + statusConfig.bg;
            statusText.className = 'font-medium ' + statusConfig.text;
            statusText.textContent = statusConfig.label;

            // Update stats
            document.getElementById('uptime-value').textContent = data.uptime;
            document.getElementById('online-streams').textContent = data.online_streams;
            document.getElementById('total-streams').textContent = data.streams;

            // Update last updated time
            const lastUpdated = new Date(data.last_updated);
            document.getElementById('last-updated').textContent = lastUpdated.toLocaleTimeString();
        }

        function fetchStatus() {
            fetch('/api/server-status')
                .then(response => response.json())
                .then(data => updateStatus(data))
                .catch(error => {
                    console.error('Failed to fetch server status:', error);
                    updateStatus({ status: 'offline', uptime: 'N/A', streams: 0, online_streams: 0, last_updated: new Date().toISOString() });
                });
        }

        // Initial load with server-rendered data (if available)
        @if(isset($serverStatus))
        updateStatus(@json($serverStatus));
        @else
        fetchStatus();
        @endif

        // Refresh status every 30 seconds
        setInterval(fetchStatus, 30000);
    })();
</script>
@endpush
