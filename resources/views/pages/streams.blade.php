@extends('layouts.app')

@section('title', 'Watch Streams - HomelabTV')

@section('content')
<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-white">Live Streams</h1>
            <p class="mt-2 text-gh-text-muted">Click on any stream to start watching</p>
        </div>

        <!-- Filter/Search Bar -->
        <div class="mb-6 flex flex-wrap gap-4 items-center">
            <div class="flex-1 min-w-64">
                <input
                    type="text"
                    id="stream-search"
                    placeholder="Search streams..."
                    class="w-full px-4 py-2 bg-gh-bg-secondary border border-gh-border rounded-lg text-white placeholder-gh-text-muted focus:outline-none focus:ring-2 focus:ring-homelab-500 focus:border-transparent"
                >
            </div>
            <div>
                <select
                    id="category-filter"
                    class="px-4 py-2 bg-gh-bg-secondary border border-gh-border rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-homelab-500"
                >
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Streams Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4" id="streams-grid">
            @forelse($streams as $stream)
                <div
                    class="stream-card group relative bg-gh-bg-secondary rounded-lg border border-gh-border hover:border-homelab-500 transition-all duration-200 cursor-pointer overflow-hidden"
                    data-category="{{ $stream->category_id }}"
                    data-name="{{ strtolower($stream->name) }}"
                    onclick="openStream({{ json_encode([
                        'url' => $stream->getEffectiveUrl(),
                        'title' => $stream->name,
                        'type' => strtoupper($stream->stream_type),
                        'category' => $stream->category?->name ?? ''
                    ]) }})"
                >
                    <!-- Stream Thumbnail/Logo -->
                    <div class="aspect-video bg-gh-bg relative flex items-center justify-center">
                        @if($stream->logo_url)
                            <img src="{{ $stream->logo_url }}" alt="{{ $stream->name }}" class="max-h-full max-w-full object-contain">
                        @else
                            <div class="text-gh-text-muted">
                                <svg class="h-16 w-16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                            </div>
                        @endif

                        <!-- Play Overlay -->
                        <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                            <div class="bg-homelab-600 rounded-full p-4 transform group-hover:scale-110 transition-transform">
                                <svg class="h-8 w-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M8 5v14l11-7z"/>
                                </svg>
                            </div>
                        </div>

                        <!-- Status Badge -->
                        <div class="absolute top-2 right-2">
                            @if($stream->last_check_status === 'online')
                                <span class="flex items-center px-2 py-1 bg-gh-success/20 text-gh-success text-xs font-medium rounded-full">
                                    <span class="w-1.5 h-1.5 bg-gh-success rounded-full mr-1"></span>
                                    Live
                                </span>
                            @elseif($stream->last_check_status === 'offline')
                                <span class="flex items-center px-2 py-1 bg-gh-danger/20 text-gh-danger text-xs font-medium rounded-full">
                                    <span class="w-1.5 h-1.5 bg-gh-danger rounded-full mr-1"></span>
                                    Offline
                                </span>
                            @else
                                <span class="flex items-center px-2 py-1 bg-gh-warning/20 text-gh-warning text-xs font-medium rounded-full">
                                    <span class="w-1.5 h-1.5 bg-gh-warning rounded-full mr-1"></span>
                                    Unknown
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Stream Info -->
                    <div class="p-4">
                        <h3 class="font-medium text-white truncate group-hover:text-homelab-400 transition-colors">
                            {{ $stream->name }}
                        </h3>
                        <div class="mt-1 flex items-center justify-between text-sm text-gh-text-muted">
                            <span>{{ $stream->category?->name ?? 'Uncategorized' }}</span>
                            <span class="px-2 py-0.5 bg-gh-bg rounded text-xs font-mono">{{ strtoupper($stream->stream_type) }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-12">
                    <svg class="h-16 w-16 text-gh-text-muted mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                    <p class="text-gh-text-muted text-lg">No streams available</p>
                    <p class="text-gh-text-muted text-sm mt-1">Check back later or contact your administrator</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Open stream in player modal
    function openStream(data) {
        window.dispatchEvent(new CustomEvent('open-player', { detail: data }));
    }

    // Search and filter functionality
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('stream-search');
        const categoryFilter = document.getElementById('category-filter');
        const streamCards = document.querySelectorAll('.stream-card');

        function filterStreams() {
            const searchTerm = searchInput.value.toLowerCase();
            const categoryId = categoryFilter.value;

            streamCards.forEach(card => {
                const name = card.dataset.name;
                const category = card.dataset.category;

                const matchesSearch = name.includes(searchTerm);
                const matchesCategory = !categoryId || category === categoryId;

                if (matchesSearch && matchesCategory) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        searchInput.addEventListener('input', filterStreams);
        categoryFilter.addEventListener('change', filterStreams);
    });
</script>
@endpush
@endsection
