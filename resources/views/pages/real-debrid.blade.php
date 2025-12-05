@extends('layouts.app')

@section('title', 'Real-Debrid - StreamPilot')

@section('content')
<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-white flex items-center">
                <svg class="h-8 w-8 mr-3 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                </svg>
                Real-Debrid
            </h1>
            <p class="mt-2 text-gh-text-muted">Connect your Real-Debrid account to sync downloads</p>
        </div>

        @if(session('success'))
        <div class="mb-6 bg-gh-success/20 border border-gh-success/50 rounded-lg p-4">
            <div class="flex items-center">
                <svg class="h-5 w-5 text-gh-success mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span class="text-gh-success">{{ session('success') }}</span>
            </div>
        </div>
        @endif

        @if(session('error'))
        <div class="mb-6 bg-gh-danger/20 border border-gh-danger/50 rounded-lg p-4">
            <div class="flex items-center">
                <svg class="h-5 w-5 text-gh-danger mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                <span class="text-gh-danger">{{ session('error') }}</span>
            </div>
        </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Account Settings -->
            <div class="lg:col-span-1">
                <div class="bg-gh-bg-secondary rounded-xl p-6 border border-gh-border">
                    <h2 class="text-lg font-semibold text-white mb-4 flex items-center">
                        <svg class="h-5 w-5 mr-2 text-homelab-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Account Settings
                    </h2>

                    <form method="POST" action="{{ route('real-debrid.save') }}" class="space-y-4">
                        @csrf
                        <div>
                            <label for="api_token" class="block text-sm font-medium text-gh-text-muted mb-1">API Token</label>
                            <input
                                type="password"
                                id="api_token"
                                name="api_token"
                                value="{{ auth()->user()->real_debrid_token }}"
                                placeholder="Enter your Real-Debrid API token"
                                class="w-full px-4 py-2 bg-gh-bg border border-gh-border rounded-lg text-white placeholder-gh-text-muted focus:outline-none focus:ring-2 focus:ring-homelab-500"
                            >
                            <p class="mt-1 text-xs text-gh-text-muted">
                                Get your API token from <a href="https://real-debrid.com/apitoken" target="_blank" class="text-homelab-400 hover:text-homelab-300">real-debrid.com/apitoken</a>
                            </p>
                        </div>

                        <div class="flex space-x-2">
                            <button type="submit" class="flex-1 px-4 py-2 bg-homelab-600 hover:bg-homelab-700 text-white font-medium rounded-lg transition-colors">
                                Save Token
                            </button>
                            @if(auth()->user()->real_debrid_token)
                            <button type="submit" name="action" value="test" class="px-4 py-2 bg-gh-bg-tertiary hover:bg-gh-border text-white font-medium rounded-lg transition-colors">
                                Test
                            </button>
                            @endif
                        </div>
                    </form>

                    @if($userInfo)
                    <div class="mt-6 pt-6 border-t border-gh-border">
                        <h3 class="text-sm font-medium text-gh-text-muted mb-3">Account Info</h3>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gh-text-muted">Username</span>
                                <span class="text-white">{{ $userInfo['username'] ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gh-text-muted">Status</span>
                                <span class="px-2 py-0.5 text-xs rounded-full {{ ($userInfo['type'] ?? '') === 'premium' ? 'bg-gh-success/20 text-gh-success' : 'bg-gh-warning/20 text-gh-warning' }}">
                                    {{ ucfirst($userInfo['type'] ?? 'Free') }}
                                </span>
                            </div>
                            @if(isset($userInfo['expiration']))
                            <div class="flex justify-between">
                                <span class="text-gh-text-muted">Expires</span>
                                <span class="text-white">{{ \Carbon\Carbon::parse($userInfo['expiration'])->format('M d, Y') }}</span>
                            </div>
                            @endif
                            <div class="flex justify-between">
                                <span class="text-gh-text-muted">Points</span>
                                <span class="text-white">{{ number_format($userInfo['points'] ?? 0) }}</span>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Downloads List -->
            <div class="lg:col-span-2">
                <div class="bg-gh-bg-secondary rounded-xl p-6 border border-gh-border">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-white flex items-center">
                            <svg class="h-5 w-5 mr-2 text-gh-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Recent Downloads
                        </h2>
                        @if(auth()->user()->real_debrid_token)
                        <form method="POST" action="{{ route('real-debrid.refresh') }}" class="inline">
                            @csrf
                            <button type="submit" class="px-3 py-1.5 text-sm bg-gh-bg-tertiary hover:bg-gh-border text-white rounded-lg transition-colors flex items-center">
                                <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Refresh
                            </button>
                        </form>
                        @endif
                    </div>

                    @if(!auth()->user()->real_debrid_token)
                    <div class="text-center py-12">
                        <svg class="h-16 w-16 text-gh-text-muted mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                        </svg>
                        <p class="text-gh-text-muted text-lg">Connect your Real-Debrid account</p>
                        <p class="text-gh-text-muted text-sm mt-1">Enter your API token on the left to view your downloads</p>
                    </div>
                    @elseif(empty($downloads))
                    <div class="text-center py-12">
                        <svg class="h-16 w-16 text-gh-text-muted mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        <p class="text-gh-text-muted text-lg">No downloads found</p>
                        <p class="text-gh-text-muted text-sm mt-1">Your Real-Debrid downloads will appear here</p>
                    </div>
                    @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gh-border">
                            <thead>
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gh-text-muted uppercase tracking-wider">File</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gh-text-muted uppercase tracking-wider">Size</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gh-text-muted uppercase tracking-wider">Host</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gh-text-muted uppercase tracking-wider">Date</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gh-text-muted uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gh-border">
                                @foreach($downloads as $download)
                                <tr class="hover:bg-gh-bg/50">
                                    <td class="px-4 py-3">
                                        <div class="flex items-center">
                                            <svg class="h-5 w-5 text-gh-text-muted mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            <span class="text-white truncate max-w-xs" title="{{ $download['filename'] }}">{{ $download['filename'] }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-gh-text-muted whitespace-nowrap">
                                        {{ \App\Services\RealDebridService::formatFileSize($download['filesize']) }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-0.5 text-xs bg-gh-bg-tertiary text-gh-text-muted rounded">{{ $download['host'] }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-gh-text-muted whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($download['generated'])->diffForHumans() }}
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        @if($download['download'])
                                        <a href="{{ $download['download'] }}" target="_blank" class="text-homelab-400 hover:text-homelab-300">
                                            <svg class="h-5 w-5 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                            </svg>
                                        </a>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
