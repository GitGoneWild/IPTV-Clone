@extends('layouts.app')

@section('title', 'Dashboard - HomelabTV')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-white">Welcome, {{ auth()->user()->name }}!</h1>
            <p class="mt-2 text-gray-400">Manage your streams and playlists from here.</p>
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
                        <p class="text-2xl font-semibold text-green-400">Active</p>
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
                               value="{{ config('app.url') }}/get.php?username={{ auth()->user()->username }}&password={{ auth()->user()->password }}&type=m3u_plus"
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
                            <code class="ml-2 text-homelab-400">{{ auth()->user()->password }}</code>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1">EPG URL (XMLTV)</label>
                    <div class="flex">
                        <input type="text" readonly 
                               value="{{ config('app.url') }}/xmltv.php?username={{ auth()->user()->username }}&password={{ auth()->user()->password }}"
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
