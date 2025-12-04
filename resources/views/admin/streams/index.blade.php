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
@endsection
