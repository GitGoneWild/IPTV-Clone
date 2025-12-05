<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel') - HomelabTV</title>

    <!-- Inter Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
<body class="bg-gh-bg text-gh-text antialiased">
    <!-- Admin Navigation -->
    <nav class="bg-gh-bg-secondary border-b border-gh-border sticky top-0 z-50">
        <div class="mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <!-- Logo -->
                    <div class="flex-shrink-0 flex items-center">
                        <a href="{{ route('admin.dashboard') }}" class="text-xl font-bold text-homelab-500">
                            HomelabTV <span class="text-gh-text-muted text-sm">Admin</span>
                        </a>
                    </div>

                    <!-- Navigation Links -->
                    <div class="hidden sm:ml-8 sm:flex sm:space-x-4">
                        <a href="{{ route('admin.dashboard') }}" 
                           class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.dashboard') ? 'bg-gh-bg-tertiary text-homelab-400' : 'text-gh-text-muted hover:text-gh-text hover:bg-gh-bg-tertiary' }}">
                            Dashboard
                        </a>
                        
                        <!-- Dropdown for Streaming -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.streams.*', 'admin.servers.*') ? 'bg-gh-bg-tertiary text-homelab-400' : 'text-gh-text-muted hover:text-gh-text hover:bg-gh-bg-tertiary' }}">
                                Streaming
                                <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div x-show="open" @click.away="open = false" 
                                 class="absolute left-0 mt-2 w-48 bg-gh-bg-secondary border border-gh-border rounded-lg shadow-lg py-1 z-50">
                                <a href="{{ route('admin.streams.index') }}" class="block px-4 py-2 text-sm text-gh-text hover:bg-gh-bg-tertiary">Streams</a>
                                <a href="{{ route('admin.servers.index') }}" class="block px-4 py-2 text-sm text-gh-text hover:bg-gh-bg-tertiary">Servers</a>
                                <a href="{{ route('admin.load-balancers.index') }}" class="block px-4 py-2 text-sm text-gh-text hover:bg-gh-bg-tertiary">Load Balancers</a>
                            </div>
                        </div>
                        
                        <!-- Dropdown for Content -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.categories.*', 'admin.bouquets.*', 'admin.movies.*', 'admin.series.*') ? 'bg-gh-bg-tertiary text-homelab-400' : 'text-gh-text-muted hover:text-gh-text hover:bg-gh-bg-tertiary' }}">
                                Content
                                <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div x-show="open" @click.away="open = false" 
                                 class="absolute left-0 mt-2 w-48 bg-gh-bg-secondary border border-gh-border rounded-lg shadow-lg py-1 z-50">
                                <a href="{{ route('admin.categories.index') }}" class="block px-4 py-2 text-sm text-gh-text hover:bg-gh-bg-tertiary">Categories</a>
                                <a href="{{ route('admin.bouquets.index') }}" class="block px-4 py-2 text-sm text-gh-text hover:bg-gh-bg-tertiary">Bouquets</a>
                                <a href="{{ route('admin.movies.index') }}" class="block px-4 py-2 text-sm text-gh-text hover:bg-gh-bg-tertiary">Movies</a>
                                <a href="{{ route('admin.series.index') }}" class="block px-4 py-2 text-sm text-gh-text hover:bg-gh-bg-tertiary">Series</a>
                                <a href="{{ route('admin.epg-sources.index') }}" class="block px-4 py-2 text-sm text-gh-text hover:bg-gh-bg-tertiary">EPG Sources</a>
                            </div>
                        </div>
                        
                        <!-- Dropdown for Users & Access -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.users.*', 'admin.devices.*', 'admin.geo-restrictions.*') ? 'bg-gh-bg-tertiary text-homelab-400' : 'text-gh-text-muted hover:text-gh-text hover:bg-gh-bg-tertiary' }}">
                                Users
                                <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div x-show="open" @click.away="open = false" 
                                 class="absolute left-0 mt-2 w-48 bg-gh-bg-secondary border border-gh-border rounded-lg shadow-lg py-1 z-50">
                                <a href="{{ route('admin.users.index') }}" class="block px-4 py-2 text-sm text-gh-text hover:bg-gh-bg-tertiary">Users</a>
                                <a href="{{ route('admin.devices.index') }}" class="block px-4 py-2 text-sm text-gh-text hover:bg-gh-bg-tertiary">Devices</a>
                                <a href="{{ route('admin.geo-restrictions.index') }}" class="block px-4 py-2 text-sm text-gh-text hover:bg-gh-bg-tertiary">Geo Restrictions</a>
                            </div>
                        </div>
                        
                        <a href="{{ route('admin.invoices.index') }}" 
                           class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.invoices.*') ? 'bg-gh-bg-tertiary text-homelab-400' : 'text-gh-text-muted hover:text-gh-text hover:bg-gh-bg-tertiary' }}">
                            Billing
                        </a>
                        
                        <!-- Dropdown for Settings -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.settings.*') ? 'bg-gh-bg-tertiary text-homelab-400' : 'text-gh-text-muted hover:text-gh-text hover:bg-gh-bg-tertiary' }}">
                                Settings
                                <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div x-show="open" @click.away="open = false" 
                                 class="absolute left-0 mt-2 w-48 bg-gh-bg-secondary border border-gh-border rounded-lg shadow-lg py-1 z-50">
                                <a href="{{ route('admin.settings.integration-settings') }}" class="block px-4 py-2 text-sm text-gh-text hover:bg-gh-bg-tertiary">Integration Settings</a>
                                <a href="{{ route('admin.settings.system-management') }}" class="block px-4 py-2 text-sm text-gh-text hover:bg-gh-bg-tertiary">System Management</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right side navigation -->
                <div class="flex items-center space-x-4">
                    <!-- Live Clock -->
                    @include('components.live-clock')
                    
                    <!-- User Portal Link -->
                    <a href="{{ route('dashboard') }}" class="text-sm text-gh-text-muted hover:text-gh-accent">
                        View Site
                    </a>

                    <!-- User Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center text-sm focus:outline-none">
                            <span class="mr-2">{{ auth()->user()->name }}</span>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="open" @click.away="open = false" 
                             class="absolute right-0 mt-2 w-48 bg-gh-bg-secondary border border-gh-border rounded-lg shadow-lg py-1">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gh-text hover:bg-gh-bg-tertiary">
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="min-h-screen">
        <!-- Flash Messages -->
        @if(session('success'))
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 mt-4">
                <div class="bg-gh-success/10 border border-gh-success text-gh-success px-4 py-3 rounded-lg">
                    {{ session('success') }}
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 mt-4">
                <div class="bg-gh-danger/10 border border-gh-danger text-gh-danger px-4 py-3 rounded-lg">
                    {{ session('error') }}
                </div>
            </div>
        @endif

        <!-- Page Content -->
        <main class="py-6">
            @yield('content')
        </main>
    </div>

    <!-- Alpine.js for dropdowns -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.0/dist/cdn.min.js"></script>
    
    @stack('scripts')
</body>
</html>
