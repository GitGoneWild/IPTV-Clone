@extends('admin.layouts.admin')

@section('title', 'Servers')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gh-text">Servers</h1>
        </div>
        <a href="{{ route('admin.servers.create') }}" 
           class="inline-flex items-center px-4 py-2 bg-homelab-600 hover:bg-homelab-700 text-white font-medium rounded-lg transition-colors">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            New
        </a>
    </div>
    <div class="bg-gh-bg-secondary border border-gh-border rounded-lg p-6">
        <p class="text-gh-text-muted">Manage Servers</p>
        <!-- Add table content here -->
    </div>
</div>
@endsection
