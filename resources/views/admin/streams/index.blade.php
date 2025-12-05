@extends('admin.layouts.admin')

@section('title', 'Streams')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gh-text">Streams</h1>
            <p class="mt-2 text-sm text-gh-text-muted">Manage live TV streams</p>
        </div>
        <a href="{{ route('admin.streams.create') }}" 
           class="inline-flex items-center px-4 py-2 bg-homelab-600 hover:bg-homelab-700 text-white font-medium rounded-lg transition-colors">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            New Stream
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-gh-bg-secondary border border-gh-border rounded-lg p-4 mb-6">
        <form method="GET" action="{{ route('admin.streams.index') }}" class="flex flex-wrap gap-4">
            <!-- Search -->
            <div class="flex-1 min-w-[200px]">
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}"
                       placeholder="Search streams..." 
                       class="w-full px-4 py-2 bg-gh-bg border border-gh-border rounded-lg text-gh-text placeholder-gh-text-muted focus:outline-none focus:ring-2 focus:ring-homelab-500">
            </div>

            <!-- Category Filter -->
            <select name="category_id" 
                    class="px-4 py-2 bg-gh-bg border border-gh-border rounded-lg text-gh-text focus:outline-none focus:ring-2 focus:ring-homelab-500">
                <option value="">All Categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>

            <!-- Status Filter -->
            <select name="status" 
                    class="px-4 py-2 bg-gh-bg border border-gh-border rounded-lg text-gh-text focus:outline-none focus:ring-2 focus:ring-homelab-500">
                <option value="">All Status</option>
                <option value="online" {{ request('status') === 'online' ? 'selected' : '' }}>Online</option>
                <option value="offline" {{ request('status') === 'offline' ? 'selected' : '' }}>Offline</option>
            </select>

            <!-- Active Filter -->
            <select name="is_active" 
                    class="px-4 py-2 bg-gh-bg border border-gh-border rounded-lg text-gh-text focus:outline-none focus:ring-2 focus:ring-homelab-500">
                <option value="">All</option>
                <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Active</option>
                <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactive</option>
            </select>

            <!-- Submit -->
            <button type="submit" 
                    class="px-6 py-2 bg-homelab-600 hover:bg-homelab-700 text-white rounded-lg transition-colors">
                Filter
            </button>

            <!-- Reset -->
            @if(request()->hasAny(['search', 'category_id', 'status', 'is_active']))
                <a href="{{ route('admin.streams.index') }}" 
                   class="px-6 py-2 bg-gh-bg-tertiary hover:bg-gh-border text-gh-text rounded-lg transition-colors">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Streams Table -->
    <div class="bg-gh-bg-secondary border border-gh-border rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gh-border">
                <thead class="bg-gh-bg-tertiary">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gh-text-muted uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gh-text-muted uppercase tracking-wider">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gh-text-muted uppercase tracking-wider">Server</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gh-text-muted uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gh-text-muted uppercase tracking-wider">Active</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gh-text-muted uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-gh-bg-secondary divide-y divide-gh-border">
                    @forelse($streams as $stream)
                        <tr class="hover:bg-gh-bg-tertiary">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gh-text">{{ $stream->name }}</div>
                                <div class="text-sm text-gh-text-muted truncate max-w-xs">{{ $stream->stream_url }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gh-text">
                                {{ $stream->category->name ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gh-text">
                                {{ $stream->server->name ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($stream->last_check_status === 'online')
                                    <span class="px-2 py-1 text-xs font-medium rounded bg-gh-success/10 text-gh-success">Online</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-medium rounded bg-gh-danger/10 text-gh-danger">Offline</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($stream->is_active)
                                    <span class="px-2 py-1 text-xs font-medium rounded bg-gh-success/10 text-gh-success">Yes</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-medium rounded bg-gh-danger/10 text-gh-danger">No</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button onclick="previewStream(@json($stream->stream_url), @json($stream->name), @json($stream->stream_type))" 
                                        class="text-homelab-500 hover:text-homelab-400 mr-3"
                                        title="Preview Stream">
                                    <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </button>
                                <button onclick="checkStream({{ $stream->id }})" 
                                        class="text-gh-accent hover:text-homelab-400 mr-3"
                                        title="Check Stream">
                                    <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </button>
                                <a href="{{ route('admin.streams.edit', $stream) }}" 
                                   class="text-homelab-500 hover:text-homelab-400 mr-3">
                                    Edit
                                </a>
                                <form action="{{ route('admin.streams.destroy', $stream) }}" 
                                      method="POST" 
                                      class="inline"
                                      onsubmit="return confirm('Are you sure you want to delete this stream?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-gh-danger hover:text-red-400">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <p class="mt-4 text-gh-text-muted">No streams found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($streams->hasPages())
            <div class="px-6 py-4 border-t border-gh-border">
                {{ $streams->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Check Stream Modal -->
<div id="checkStreamModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-gh-bg-secondary rounded-lg p-6 max-w-md w-full mx-4 border border-gh-border">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-white">Stream Health Check</h3>
            <button onclick="closeCheckModal()" class="text-gh-text-muted hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <div id="checkStreamContent" class="py-4">
            <!-- Content will be loaded here -->
            <div class="flex items-center justify-center">
                <svg class="animate-spin h-8 w-8 text-homelab-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="ml-3 text-gh-text-muted">Checking stream...</span>
            </div>
        </div>
        
        <div class="mt-4 flex justify-end">
            <button onclick="closeCheckModal()" 
                    class="px-4 py-2 bg-gh-bg-tertiary hover:bg-gh-border text-gh-text rounded-lg transition-colors">
                Close
            </button>
        </div>
    </div>
</div>

<!-- Stream Preview Modal -->
<div id="streamPreviewModal" class="fixed inset-0 bg-black bg-opacity-75 hidden flex items-center justify-center z-50" role="dialog" aria-modal="true" aria-labelledby="streamPreviewTitle">
    <div class="bg-gh-bg-secondary rounded-lg p-6 max-w-4xl w-full mx-4 border border-gh-border">
        <div class="flex items-center justify-between mb-4">
            <h3 id="streamPreviewTitle" class="text-lg font-semibold text-white">Stream Preview</h3>
            <button onclick="closePreviewModal()" class="text-gh-text-muted hover:text-white" aria-label="Close modal">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <div id="streamPreviewContent" class="bg-black rounded-lg overflow-hidden">
            <!-- Video player will be loaded here -->
            <video id="streamPreviewPlayer" class="w-full h-auto" controls autoplay>
                Your browser does not support the video tag.
            </video>
        </div>
        
        <div id="streamErrorMessage" class="hidden mt-4 p-4 bg-gh-danger/10 border border-gh-danger text-gh-danger rounded-lg">
            <p class="text-sm"></p>
        </div>
        
        <div class="mt-4 flex justify-between items-center">
            <p id="streamPreviewUrl" class="text-sm text-gh-text-muted truncate max-w-xl"></p>
            <button onclick="closePreviewModal()" 
                    class="px-4 py-2 bg-gh-bg-tertiary hover:bg-gh-border text-gh-text rounded-lg transition-colors">
                Close
            </button>
        </div>
    </div>
</div>

<script>
let previousFocusedElement = null;

function showError(message) {
    const errorDiv = document.getElementById('streamErrorMessage');
    errorDiv.querySelector('p').textContent = message;
    errorDiv.classList.remove('hidden');
}

function hideError() {
    const errorDiv = document.getElementById('streamErrorMessage');
    errorDiv.classList.add('hidden');
}

function previewStream(streamUrl, streamName, streamType) {
    // Store the currently focused element to return focus later
    previousFocusedElement = document.activeElement;
    
    // Show modal
    const modal = document.getElementById('streamPreviewModal');
    modal.classList.remove('hidden');
    
    // Hide any previous error messages
    hideError();
    
    // Set title and URL
    document.getElementById('streamPreviewTitle').textContent = streamName;
    document.getElementById('streamPreviewUrl').textContent = streamUrl;
    
    // Get video player
    const player = document.getElementById('streamPreviewPlayer');
    
    // Focus the close button for keyboard accessibility
    setTimeout(() => {
        modal.querySelector('button[aria-label="Close modal"]').focus();
    }, 100);
    
    // Load HLS library if needed for HLS streams
    if (streamType === 'hls' && streamUrl.includes('.m3u8')) {
        if (!window.Hls) {
            // Check if script is already being loaded
            if (!document.querySelector('script[src*="hls.js"]')) {
                // Load HLS.js library with pinned version
                const script = document.createElement('script');
                script.src = 'https://cdn.jsdelivr.net/npm/hls.js@1.5.0/dist/hls.min.js';
                script.onload = () => loadHlsStream(player, streamUrl);
                script.onerror = () => {
                    showError('Failed to load HLS player library. Please refresh and try again.');
                };
                document.head.appendChild(script);
            }
        } else {
            loadHlsStream(player, streamUrl);
        }
    } else {
        // For other stream types, use native video player
        player.src = streamUrl;
        player.load();
        player.play().catch(err => {
            console.error('Error playing stream:', err);
            showError('Unable to play this stream. It may not be supported by your browser.');
        });
    }
}

function loadHlsStream(player, streamUrl) {
    if (Hls.isSupported()) {
        const hls = new Hls({
            enableWorker: true,
            lowLatencyMode: true,
        });
        hls.loadSource(streamUrl);
        hls.attachMedia(player);
        hls.on(Hls.Events.MANIFEST_PARSED, () => {
            player.play().catch(err => {
                console.error('Error playing HLS stream:', err);
                showError('Error starting stream playback: ' + err.message);
            });
        });
        hls.on(Hls.Events.ERROR, (event, data) => {
            if (data.fatal) {
                console.error('Fatal HLS error:', data);
                showError('Error loading HLS stream: ' + data.details);
            }
        });
        
        // Store hls instance for cleanup
        player._hls = hls;
    } else if (player.canPlayType('application/vnd.apple.mpegurl')) {
        // Native HLS support (Safari)
        player.src = streamUrl;
        player.load();
        player.play().catch(err => {
            console.error('Error playing native HLS:', err);
            showError('Error starting stream playback: ' + err.message);
        });
    } else {
        showError('HLS streams are not supported in your browser.');
    }
}

function closePreviewModal() {
    const modal = document.getElementById('streamPreviewModal');
    const player = document.getElementById('streamPreviewPlayer');
    
    // Stop playback
    player.pause();
    player.src = '';
    
    // Clean up HLS instance if exists
    if (player._hls) {
        player._hls.destroy();
        player._hls = null;
    }
    
    // Hide modal
    modal.classList.add('hidden');
    
    // Return focus to the element that opened the modal
    if (previousFocusedElement) {
        previousFocusedElement.focus();
        previousFocusedElement = null;
    }
}

// Keyboard support for modal
document.addEventListener('keydown', function(e) {
    const modal = document.getElementById('streamPreviewModal');
    if (!modal.classList.contains('hidden')) {
        if (e.key === 'Escape') {
            closePreviewModal();
        }
    }
});

function checkStream(streamId) {
    // Show modal
    document.getElementById('checkStreamModal').classList.remove('hidden');
    
    // Reset content
    document.getElementById('checkStreamContent').innerHTML = `
        <div class="flex items-center justify-center">
            <svg class="animate-spin h-8 w-8 text-homelab-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="ml-3 text-gh-text-muted">Checking stream...</span>
        </div>
    `;
    
    // Make AJAX request
    fetch(`/admin/streams/${streamId}/check`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        let statusBgClass = 'bg-gh-success/20';
        let statusTextClass = 'text-gh-success';
        let statusIcon = `<svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`;
        
        if (data.status === 'offline') {
            statusBgClass = 'bg-gh-danger/20';
            statusTextClass = 'text-gh-danger';
            statusIcon = `<svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>`;
        } else if (data.status === 'unknown' || data.status === 'error' || data.status === 'valid_url') {
            statusBgClass = 'bg-gh-warning/20';
            statusTextClass = 'text-gh-warning';
            statusIcon = `<svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>`;
        }
        
        let html = `
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full ${statusBgClass} mb-4">
                    <div class="${statusTextClass}">
                        ${statusIcon}
                    </div>
                </div>
                <h4 class="text-xl font-semibold text-white mb-2">${data.status.charAt(0).toUpperCase() + data.status.slice(1).replace('_', ' ')}</h4>
                <p class="text-gh-text-muted mb-4">${data.message}</p>
                <div class="bg-gh-bg rounded-lg p-3 text-left space-y-1 text-sm">
                    ${data.http_code ? `<div class="flex justify-between"><span class="text-gh-text-muted">HTTP Code:</span><span class="text-white">${data.http_code}</span></div>` : ''}
                    <div class="flex justify-between"><span class="text-gh-text-muted">Checked at:</span><span class="text-white">${new Date(data.checked_at).toLocaleString()}</span></div>
                </div>
            </div>
        `;
        
        document.getElementById('checkStreamContent').innerHTML = html;
    })
    .catch(error => {
        document.getElementById('checkStreamContent').innerHTML = `
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-gh-danger/20 mb-4">
                    <svg class="w-12 h-12 text-gh-danger" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h4 class="text-xl font-semibold text-white mb-2">Error</h4>
                <p class="text-gh-text-muted">Failed to check stream: ${error.message}</p>
            </div>
        `;
    });
}

function closeCheckModal() {
    document.getElementById('checkStreamModal').classList.add('hidden');
}

// Close modals when clicking outside
document.getElementById('checkStreamModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeCheckModal();
    }
});

document.getElementById('streamPreviewModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closePreviewModal();
    }
});
</script>
@endsection
