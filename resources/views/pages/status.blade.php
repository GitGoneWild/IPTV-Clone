@extends('layouts.app')

@section('title', 'System Status - StreamPilot')

@section('content')
<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-white flex items-center">
                <svg class="h-8 w-8 mr-3 text-homelab-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                System Status
            </h1>
            <p class="mt-2 text-gh-text-muted">Monitor the health of channels and services</p>
        </div>

        <!-- Overall Status Card -->
        <div class="mb-8 bg-gh-bg-secondary rounded-xl p-6 border border-gh-border">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    @if($overallStatus === 'operational')
                        <div class="flex-shrink-0 p-3 bg-gh-success/20 rounded-full mr-4">
                            <svg class="h-8 w-8 text-gh-success" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-gh-success">All Systems Operational</h2>
                            <p class="text-gh-text-muted">All channels are functioning normally</p>
                        </div>
                    @elseif($overallStatus === 'degraded')
                        <div class="flex-shrink-0 p-3 bg-gh-warning/20 rounded-full mr-4">
                            <svg class="h-8 w-8 text-gh-warning" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-gh-warning">Partial System Outage</h2>
                            <p class="text-gh-text-muted">Some channels are experiencing issues</p>
                        </div>
                    @else
                        <div class="flex-shrink-0 p-3 bg-gh-danger/20 rounded-full mr-4">
                            <svg class="h-8 w-8 text-gh-danger" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-gh-danger">Major System Outage</h2>
                            <p class="text-gh-text-muted">Multiple channels are currently offline</p>
                        </div>
                    @endif
                </div>
                <div class="text-right">
                    <p class="text-sm text-gh-text-muted">Last updated</p>
                    <p class="text-white font-medium">{{ now()->format('M d, Y H:i') }}</p>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-gh-bg-secondary rounded-lg p-4 border border-gh-border">
                <div class="flex items-center">
                    <div class="flex-shrink-0 p-2 bg-homelab-600/20 rounded-lg">
                        <svg class="h-5 w-5 text-homelab-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-gh-text-muted">Total Channels</p>
                        <p class="text-xl font-bold text-white">{{ $totalStreams }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-gh-bg-secondary rounded-lg p-4 border border-gh-border">
                <div class="flex items-center">
                    <div class="flex-shrink-0 p-2 bg-gh-success/20 rounded-lg">
                        <svg class="h-5 w-5 text-gh-success" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-gh-text-muted">Online</p>
                        <p class="text-xl font-bold text-gh-success">{{ $onlineStreams }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-gh-bg-secondary rounded-lg p-4 border border-gh-border">
                <div class="flex items-center">
                    <div class="flex-shrink-0 p-2 bg-gh-danger/20 rounded-lg">
                        <svg class="h-5 w-5 text-gh-danger" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-gh-text-muted">Offline</p>
                        <p class="text-xl font-bold text-gh-danger">{{ $offlineStreams }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-gh-bg-secondary rounded-lg p-4 border border-gh-border">
                <div class="flex items-center">
                    <div class="flex-shrink-0 p-2 bg-gh-accent/20 rounded-lg">
                        <svg class="h-5 w-5 text-gh-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-gh-text-muted">Uptime</p>
                        <p class="text-xl font-bold text-white">{{ $uptimePercent }}%</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Category Status -->
        @if($categoryStats->isNotEmpty())
        <div class="mb-8">
            <h3 class="text-lg font-semibold text-white mb-4">Status by Category</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($categoryStats as $category)
                <div class="bg-gh-bg-secondary rounded-lg p-4 border border-gh-border">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="font-medium text-white">{{ $category->name ?? 'Uncategorized' }}</h4>
                        @if($category->online_count === $category->total_count)
                            <span class="px-2 py-1 text-xs font-medium bg-gh-success/20 text-gh-success rounded-full">All Online</span>
                        @elseif($category->online_count > 0)
                            <span class="px-2 py-1 text-xs font-medium bg-gh-warning/20 text-gh-warning rounded-full">Partial</span>
                        @else
                            <span class="px-2 py-1 text-xs font-medium bg-gh-danger/20 text-gh-danger rounded-full">Offline</span>
                        @endif
                    </div>
                    <div class="flex items-center">
                        <div class="flex-1 bg-gh-bg rounded-full h-2 mr-3">
                            @php
                                $percentage = $category->total_count > 0 ? ($category->online_count / $category->total_count) * 100 : 0;
                            @endphp
                            <div class="h-2 rounded-full {{ $percentage === 100 ? 'bg-gh-success' : ($percentage > 0 ? 'bg-gh-warning' : 'bg-gh-danger') }}" style="width: {{ $percentage }}%"></div>
                        </div>
                        <span class="text-sm text-gh-text-muted">{{ $category->online_count }}/{{ $category->total_count }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Offline Channels List -->
        @if($offlineChannels->isNotEmpty())
        <div class="mb-8">
            <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                <svg class="h-5 w-5 mr-2 text-gh-danger" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Channels with Issues
            </h3>
            <div class="bg-gh-bg-secondary rounded-lg border border-gh-border overflow-hidden">
                <table class="min-w-full divide-y divide-gh-border">
                    <thead class="bg-gh-bg">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gh-text-muted uppercase tracking-wider">Channel</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gh-text-muted uppercase tracking-wider">Category</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gh-text-muted uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gh-text-muted uppercase tracking-wider">Last Checked</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gh-border">
                        @foreach($offlineChannels as $channel)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    @if($channel->logo_url)
                                        <img src="{{ $channel->logo_url }}" alt="" class="h-8 w-8 rounded object-contain mr-3 bg-gh-bg">
                                    @else
                                        <div class="h-8 w-8 rounded bg-gh-bg flex items-center justify-center mr-3">
                                            <svg class="h-4 w-4 text-gh-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                    @endif
                                    <span class="text-white font-medium">{{ $channel->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gh-text-muted">
                                {{ $channel->category?->name ?? 'Uncategorized' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gh-danger/20 text-gh-danger">
                                    <svg class="h-3 w-3 mr-1" fill="currentColor" viewBox="0 0 8 8">
                                        <circle cx="4" cy="4" r="3" />
                                    </svg>
                                    Offline
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gh-text-muted">
                                {{ $channel->last_check_at ? $channel->last_check_at->diffForHumans() : 'Never' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- Recent Events Timeline -->
        <div class="mb-8">
            <h3 class="text-lg font-semibold text-white mb-4 flex items-center">
                <svg class="h-5 w-5 mr-2 text-gh-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                System Information
            </h3>
            <div class="bg-gh-bg-secondary rounded-lg p-6 border border-gh-border">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="text-sm font-medium text-gh-text-muted mb-2">Stream Health Check</h4>
                        <p class="text-white">Streams are checked every {{ config('streampilot.stream_check_interval', 60) }} seconds</p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gh-text-muted mb-2">EPG Updates</h4>
                        <p class="text-white">Program guide updated every {{ config('streampilot.epg_update_interval', 3600) / 60 }} minutes</p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gh-text-muted mb-2">API Status</h4>
                        <p class="text-gh-success flex items-center">
                            <svg class="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 8 8">
                                <circle cx="4" cy="4" r="3" />
                            </svg>
                            Xtream Codes API - Online
                        </p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gh-text-muted mb-2">Supported Formats</h4>
                        <p class="text-white">HLS, MPEG-TS, RTMP, HTTP Direct</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Help Section -->
        <div class="bg-gh-bg-secondary rounded-lg p-6 border border-gh-border">
            <h3 class="text-lg font-semibold text-white mb-3 flex items-center">
                <svg class="h-5 w-5 mr-2 text-gh-warning" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Having Issues?
            </h3>
            <div class="text-gh-text-muted space-y-2">
                <p>If you're experiencing problems with a specific channel:</p>
                <ul class="list-disc list-inside space-y-1 ml-4">
                    <li>Try refreshing the stream in your IPTV player</li>
                    <li>Clear your player's cache and restart</li>
                    <li>Check if the channel is listed as offline above</li>
                    <li>Wait a few minutes - our system automatically retries failed streams</li>
                </ul>
                @auth
                    @if(auth()->user()->is_admin)
                    <p class="mt-4 pt-4 border-t border-gh-border">
                        <a href="/admin" class="text-homelab-400 hover:text-homelab-300 font-medium">
                            Go to Admin Panel →
                        </a> to manage streams and run health checks.
                    </p>
                    @endif
                @endauth
            </div>
        </div>
    </div>
</div>
@endsection
