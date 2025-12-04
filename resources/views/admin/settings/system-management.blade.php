@extends('admin.layouts.admin')

@section('title', 'System Management')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gh-text">System Management</h1>
        <p class="mt-2 text-sm text-gh-text-muted">Manage system cache and optimization</p>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- System Information -->
        <div class="bg-gh-bg-secondary border border-gh-border rounded-lg p-6">
            <h3 class="text-lg font-medium text-gh-text mb-4">System Information</h3>
            <dl class="space-y-2">
                <div class="flex justify-between">
                    <dt class="text-sm text-gh-text-muted">PHP Version</dt>
                    <dd class="text-sm text-gh-text">{{ $systemInfo['php_version'] }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gh-text-muted">Laravel Version</dt>
                    <dd class="text-sm text-gh-text">{{ $systemInfo['laravel_version'] }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gh-text-muted">Server Software</dt>
                    <dd class="text-sm text-gh-text">{{ $systemInfo['server_software'] }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gh-text-muted">Database</dt>
                    <dd class="text-sm text-gh-text">{{ $systemInfo['db_connection'] }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gh-text-muted">Cache Driver</dt>
                    <dd class="text-sm text-gh-text">{{ $systemInfo['cache_driver'] }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gh-text-muted">Queue Driver</dt>
                    <dd class="text-sm text-gh-text">{{ $systemInfo['queue_driver'] }}</dd>
                </div>
            </dl>
        </div>
        
        <!-- Maintenance Actions -->
        <div class="bg-gh-bg-secondary border border-gh-border rounded-lg p-6">
            <h3 class="text-lg font-medium text-gh-text mb-4">Maintenance Actions</h3>
            <div class="space-y-4">
                <form method="POST" action="{{ route('admin.settings.clear-cache') }}">
                    @csrf
                    <button type="submit" class="w-full px-4 py-2 bg-gh-bg-tertiary hover:bg-gh-border text-gh-text rounded-lg text-left">
                        <div class="font-medium">Clear Cache</div>
                        <div class="text-sm text-gh-text-muted">Clear application, config, route, and view cache</div>
                    </button>
                </form>
                
                <form method="POST" action="{{ route('admin.settings.optimize') }}">
                    @csrf
                    <button type="submit" class="w-full px-4 py-2 bg-gh-bg-tertiary hover:bg-gh-border text-gh-text rounded-lg text-left">
                        <div class="font-medium">Optimize Application</div>
                        <div class="text-sm text-gh-text-muted">Optimize configuration, routes, and views for production</div>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
