<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'HomelabTV')</title>

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
                        // GitHub Copilot inspired colors
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Alpine.js for interactive components -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>

    <!-- HLS.js for stream playback -->
    <script src="https://cdn.jsdelivr.net/npm/hls.js@1.4.14/dist/hls.min.js"></script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #0d1117;
        }
        /* GitHub Copilot style scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #161b22;
        }
        ::-webkit-scrollbar-thumb {
            background: #30363d;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #484f58;
        }
        /* Glowing accent effect */
        .glow-accent {
            box-shadow: 0 0 20px rgba(88, 166, 255, 0.15);
        }
        /* Alpine.js cloak */
        [x-cloak] {
            display: none !important;
        }
    </style>

    @stack('styles')
</head>
<body class="bg-gh-bg text-gh-text min-h-screen">
    <!-- Navigation -->
    <nav class="bg-gh-bg-secondary border-b border-gh-border sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="/" class="flex items-center group">
                        <svg class="h-8 w-8 text-homelab-500 group-hover:text-homelab-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        <span class="ml-2 text-xl font-bold text-white">HomelabTV</span>
                    </a>
                </div>

                <div class="flex items-center space-x-4">
                    @auth
                        <a href="{{ route('dashboard') }}" class="text-gh-text-muted hover:text-white px-3 py-2 rounded-md text-sm font-medium transition-colors">Dashboard</a>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-gh-text-muted hover:text-white px-3 py-2 rounded-md text-sm font-medium transition-colors">Logout</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="text-gh-text-muted hover:text-white px-3 py-2 rounded-md text-sm font-medium transition-colors">Login</a>
                    @endauth
                    @if(Auth::check() && Auth::user()->is_admin)
                        <a href="/admin" class="bg-homelab-600 hover:bg-homelab-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors glow-accent">Admin Panel</a>
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="min-h-[calc(100vh-8rem)]">
        @yield('content')
    </main>

    {{-- Player Modal Component - only include if on streams page --}}
    @if(Request::is('streams'))
        @include('components.player-modal')
    @endif

    <!-- Footer -->
    <footer class="bg-gh-bg-secondary border-t border-gh-border mt-auto">
        <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center">
                <div class="text-gh-text-muted text-sm">
                    &copy; {{ date('Y') }} HomelabTV. For private homelab use only.
                </div>
                <div class="text-gh-text-muted text-sm flex items-center">
                    <span class="inline-flex items-center mr-2">
                        <svg class="h-4 w-4 mr-1 text-gh-success" fill="currentColor" viewBox="0 0 8 8">
                            <circle cx="4" cy="4" r="3" />
                        </svg>
                        System Online
                    </span>
                    <span class="text-gh-border">|</span>
                    <span class="ml-2">Powered by Laravel + Filament</span>
                </div>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
