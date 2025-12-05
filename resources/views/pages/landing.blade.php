@extends('layouts.app')

@section('title', 'StreamPilot - Private IPTV Management')

@section('content')
<!-- Hero Section - Compact and Modern -->
<div class="relative min-h-[calc(100vh-8rem)] flex items-center">
    <!-- Background gradient -->
    <div class="absolute inset-0 bg-gradient-to-br from-homelab-900/30 via-transparent to-gh-bg pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
        <div class="grid lg:grid-cols-2 gap-12 items-center py-8">
            <!-- Left: Hero Content -->
            <div class="text-center lg:text-left">
                <!-- Badge -->
                <div class="inline-flex items-center px-4 py-1.5 rounded-full text-sm font-medium bg-gh-bg-tertiary border border-gh-border mb-6">
                    <span class="flex h-2 w-2 relative mr-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-gh-success opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-gh-success"></span>
                    </span>
                    <span class="text-gh-text-muted">Self-hosted IPTV for your homelab</span>
                </div>

                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-white">
                    Your Private
                    <span class="block bg-gradient-to-r from-homelab-400 to-homelab-600 bg-clip-text text-transparent">IPTV Panel</span>
                </h1>

                <p class="mt-4 text-lg text-gh-text-muted max-w-xl mx-auto lg:mx-0">
                    Manage legal streams, CCTV cameras, and private channels. 100% Xtream Codes compatible.
                </p>

                <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                    @auth
                        <a href="{{ route('dashboard') }}" class="flex items-center justify-center px-8 py-3 text-base font-medium rounded-lg text-white bg-homelab-600 hover:bg-homelab-700 transition-all glow-accent">
                            Go to Dashboard
                            <svg class="ml-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="flex items-center justify-center px-8 py-3 text-base font-medium rounded-lg text-white bg-homelab-600 hover:bg-homelab-700 transition-all glow-accent">
                            Get Started
                            <svg class="ml-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                    @endauth
                </div>

                <!-- Quick Stats -->
                <div class="mt-10 grid grid-cols-3 gap-4 max-w-md mx-auto lg:mx-0">
                    <div class="text-center lg:text-left">
                        <p class="text-2xl font-bold text-white">HLS/TS</p>
                        <p class="text-xs text-gh-text-muted">Multi-Protocol</p>
                    </div>
                    <div class="text-center lg:text-left">
                        <p class="text-2xl font-bold text-gh-success">100%</p>
                        <p class="text-xs text-gh-text-muted">Xtream API</p>
                    </div>
                    <div class="text-center lg:text-left">
                        <p class="text-2xl font-bold text-white">24/7</p>
                        <p class="text-xs text-gh-text-muted">Monitoring</p>
                    </div>
                </div>
            </div>

            <!-- Right: Server Status + Quick Login -->
            <div class="space-y-6">
                <!-- Server Status Widget -->
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

                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex items-center p-3 bg-gh-bg rounded-lg border border-gh-border-muted">
                            <svg class="h-5 w-5 text-gh-success mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div>
                                <span class="text-xs text-gh-text-muted block">Uptime</span>
                                <span id="uptime-value" class="font-semibold text-white">--</span>
                            </div>
                        </div>
                        <div class="flex items-center p-3 bg-gh-bg rounded-lg border border-gh-border-muted">
                            <svg class="h-5 w-5 text-gh-accent mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                            <div>
                                <span class="text-xs text-gh-text-muted block">Streams</span>
                                <span class="font-semibold text-white">
                                    <span id="online-streams">--</span>/<span id="total-streams">--</span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 text-center">
                        <span class="text-xs text-gh-text-muted">Updated: <span id="last-updated">--</span></span>
                    </div>
                </div>

                @guest
                <!-- Quick Login Form -->
                <div class="bg-gh-bg-secondary rounded-xl p-6 border border-gh-border">
                    <h4 class="text-lg font-semibold text-white mb-4">Quick Access</h4>
                    <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
                        @csrf
                        <input type="email" name="email" placeholder="Email" required
                            class="w-full px-4 py-3 bg-gh-bg border border-gh-border rounded-lg text-white placeholder-gh-text-muted focus:outline-none focus:ring-2 focus:ring-homelab-500">
                        <input type="password" name="password" placeholder="Password" required
                            class="w-full px-4 py-3 bg-gh-bg border border-gh-border rounded-lg text-white placeholder-gh-text-muted focus:outline-none focus:ring-2 focus:ring-homelab-500">
                        <button type="submit" class="w-full px-4 py-3 bg-homelab-600 hover:bg-homelab-700 text-white font-medium rounded-lg transition-colors">
                            Sign In
                        </button>
                    </form>
                </div>
                @endguest
            </div>
        </div>

        <!-- Feature Cards - Compact Grid -->
        <div class="py-12 border-t border-gh-border mt-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-gh-bg-secondary rounded-lg p-4 border border-gh-border hover:border-homelab-500/50 transition-colors">
                    <div class="h-10 w-10 rounded-lg bg-homelab-600/20 border border-homelab-600/30 text-homelab-400 flex items-center justify-center mb-3">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="font-medium text-white text-sm">Multi-Protocol</h3>
                    <p class="text-xs text-gh-text-muted mt-1">HLS, MPEG-TS, RTMP</p>
                </div>

                <div class="bg-gh-bg-secondary rounded-lg p-4 border border-gh-border hover:border-gh-success/50 transition-colors">
                    <div class="h-10 w-10 rounded-lg bg-gh-success/20 border border-gh-success/30 text-gh-success flex items-center justify-center mb-3">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <h3 class="font-medium text-white text-sm">Xtream Compatible</h3>
                    <p class="text-xs text-gh-text-muted mt-1">Full API support</p>
                </div>

                <div class="bg-gh-bg-secondary rounded-lg p-4 border border-gh-border hover:border-gh-accent/50 transition-colors">
                    <div class="h-10 w-10 rounded-lg bg-gh-accent/20 border border-gh-accent/30 text-gh-accent flex items-center justify-center mb-3">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <h3 class="font-medium text-white text-sm">Role-Based Access</h3>
                    <p class="text-xs text-gh-text-muted mt-1">Admin, Reseller, User</p>
                </div>

                <div class="bg-gh-bg-secondary rounded-lg p-4 border border-gh-border hover:border-gh-warning/50 transition-colors">
                    <div class="h-10 w-10 rounded-lg bg-gh-warning/20 border border-gh-warning/30 text-gh-warning flex items-center justify-center mb-3">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="font-medium text-white text-sm">EPG Support</h3>
                    <p class="text-xs text-gh-text-muted mt-1">XMLTV import</p>
                </div>
            </div>
        </div>

        <!-- API Endpoints - Compact -->
        <div class="py-8">
            <div class="bg-gh-bg-secondary rounded-xl p-6 border border-gh-border">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-white">API Endpoints</h3>
                    <span class="text-xs text-gh-text-muted">Xtream Codes Compatible</span>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                    <div class="flex items-center space-x-2 p-2 bg-gh-bg rounded border border-gh-border-muted">
                        <span class="px-1.5 py-0.5 text-xs font-mono rounded bg-gh-success/20 text-gh-success">GET</span>
                        <code class="text-xs text-gh-accent truncate">/player_api.php</code>
                    </div>
                    <div class="flex items-center space-x-2 p-2 bg-gh-bg rounded border border-gh-border-muted">
                        <span class="px-1.5 py-0.5 text-xs font-mono rounded bg-gh-success/20 text-gh-success">GET</span>
                        <code class="text-xs text-gh-accent truncate">/get.php</code>
                    </div>
                    <div class="flex items-center space-x-2 p-2 bg-gh-bg rounded border border-gh-border-muted">
                        <span class="px-1.5 py-0.5 text-xs font-mono rounded bg-gh-success/20 text-gh-success">GET</span>
                        <code class="text-xs text-gh-accent truncate">/xmltv.php</code>
                    </div>
                    <div class="flex items-center space-x-2 p-2 bg-gh-bg rounded border border-gh-border-muted">
                        <span class="px-1.5 py-0.5 text-xs font-mono rounded bg-gh-accent/20 text-gh-accent">GET</span>
                        <code class="text-xs text-gh-accent truncate">/panel_api.php</code>
                    </div>
                    <div class="flex items-center space-x-2 p-2 bg-gh-bg rounded border border-gh-border-muted">
                        <span class="px-1.5 py-0.5 text-xs font-mono rounded bg-gh-accent/20 text-gh-accent">GET</span>
                        <code class="text-xs text-gh-accent truncate">/enigma2.php</code>
                    </div>
                    <div class="flex items-center space-x-2 p-2 bg-gh-bg rounded border border-gh-border-muted">
                        <span class="px-1.5 py-0.5 text-xs font-mono rounded bg-homelab-600/20 text-homelab-400">GET</span>
                        <code class="text-xs text-gh-accent truncate">/live/{u}/{p}/{id}</code>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    /**
     * Live Server Status Widget
     */
    (function() {
        const statusColors = {
            operational: { bg: 'bg-gh-success', text: 'text-gh-success', label: 'Operational' },
            degraded: { bg: 'bg-gh-warning', text: 'text-gh-warning', label: 'Degraded' },
            offline: { bg: 'bg-gh-danger', text: 'text-gh-danger', label: 'Offline' }
        };

        function updateStatus(data) {
            const statusConfig = statusColors[data.status] || statusColors.offline;

            document.getElementById('status-ping').className = 'animate-ping absolute inline-flex h-full w-full rounded-full opacity-75 ' + statusConfig.bg;
            document.getElementById('status-dot').className = 'relative inline-flex rounded-full h-2.5 w-2.5 ' + statusConfig.bg;
            document.getElementById('status-text').className = 'font-medium ' + statusConfig.text;
            document.getElementById('status-text').textContent = statusConfig.label;

            document.getElementById('uptime-value').textContent = data.uptime;
            document.getElementById('online-streams').textContent = data.online_streams;
            document.getElementById('total-streams').textContent = data.streams;
            document.getElementById('last-updated').textContent = new Date(data.last_updated).toLocaleTimeString();
        }

        function fetchStatus() {
            fetch('/api/server-status')
                .then(response => response.json())
                .then(data => updateStatus(data))
                .catch(() => updateStatus({ status: 'offline', uptime: 'N/A', streams: 0, online_streams: 0, last_updated: new Date().toISOString() }));
        }

        @if(isset($serverStatus))
        updateStatus(@json($serverStatus));
        @else
        fetchStatus();
        @endif

        setInterval(fetchStatus, 30000);
    })();
</script>
@endpush
