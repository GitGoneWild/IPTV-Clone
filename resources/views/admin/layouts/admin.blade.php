<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel') - HomelabTV</title>

    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        'homelab': {
                            50: '#f5f3ff',
                            100: '#ede9fe',
                            200: '#ddd6fe',
                            300: '#c4b5fd',
                            400: '#a78bfa',
                            500: '#8b5cf6',
                            600: '#7c3aed',
                            700: '#6d28d9',
                            800: '#5b21b6',
                            900: '#4c1d95',
                        },
                        'gh': {
                            'bg': '#0d1117',
                            'bg-secondary': '#161b22',
                            'bg-tertiary': '#21262d',
                            'border': '#30363d',
                            'border-muted': '#21262d',
                            'text': '#c9d1d9',
                            'text-muted': '#8b949e',
                            'accent': '#58a6ff',
                            'accent-emphasis': '#1f6feb',
                            'success': '#3fb950',
                            'warning': '#d29922',
                            'danger': '#f85149',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Inter Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Heroicons -->
    <script src="https://cdn.jsdelivr.net/npm/heroicons@2.0.18/24/outline/index.js"></script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>

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
                        <a href="{{ route('admin.users.index') }}" 
                           class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.users.*') ? 'bg-gh-bg-tertiary text-homelab-400' : 'text-gh-text-muted hover:text-gh-text hover:bg-gh-bg-tertiary' }}">
                            Users
                        </a>
                        <a href="#" 
                           class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-md text-gh-text-muted hover:text-gh-text hover:bg-gh-bg-tertiary">
                            Streams
                        </a>
                        <a href="#" 
                           class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-md text-gh-text-muted hover:text-gh-text hover:bg-gh-bg-tertiary">
                            Content
                        </a>
                        <a href="#" 
                           class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-md text-gh-text-muted hover:text-gh-text hover:bg-gh-bg-tertiary">
                            Settings
                        </a>
                    </div>
                </div>

                <!-- Right side navigation -->
                <div class="flex items-center space-x-4">
                    <!-- User Portal Link -->
                    <a href="{{ route('dashboard') }}" class="text-sm text-gh-text-muted hover:text-gh-accent">
                        View Site
                    </a>

                    <!-- Filament Admin Link (temporary during migration) -->
                    <a href="/admin" class="text-sm text-gh-warning hover:text-white">
                        Filament Admin
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
                             class="absolute right-0 mt-2 w-48 bg-gh-bg-secondary border border-gh-border rounded-lg shadow-lg py-1" 
                             style="display: none;">
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
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    @stack('scripts')
</body>
</html>
