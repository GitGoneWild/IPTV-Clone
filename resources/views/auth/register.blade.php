@extends('layouts.app')

@section('title', 'Register - StreamPilot')

@section('content')
<div class="min-h-[calc(100vh-12rem)] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <div class="flex justify-center">
                <div class="relative">
                    <div class="absolute -inset-4 bg-homelab-600/20 rounded-full blur-xl"></div>
                    <svg class="relative h-16 w-16 text-homelab-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-white">
                Create your account
            </h2>
            <p class="mt-2 text-center text-sm text-gh-text-muted">
                Join StreamPilot to access your private IPTV streams
            </p>
        </div>

        <form class="mt-8 space-y-6 bg-gh-bg-secondary rounded-lg p-6 border border-gh-border" action="{{ route('register.store') }}" method="POST">
            @csrf

            @if ($errors->any())
                <div class="bg-gh-danger/10 border border-gh-danger/50 text-gh-danger rounded-lg px-4 py-3" role="alert">
                    <ul class="list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gh-text-muted mb-1">Full name</label>
                    <input id="name" name="name" type="text" autocomplete="name" required
                           class="appearance-none relative block w-full px-3 py-2 border border-gh-border placeholder-gh-text-muted text-white bg-gh-bg rounded-md focus:outline-none focus:ring-2 focus:ring-homelab-500 focus:border-homelab-500 sm:text-sm transition-colors"
                           placeholder="John Doe"
                           value="{{ old('name') }}">
                </div>

                <div>
                    <label for="username" class="block text-sm font-medium text-gh-text-muted mb-1">Username</label>
                    <input id="username" name="username" type="text" autocomplete="username" required
                           class="appearance-none relative block w-full px-3 py-2 border border-gh-border placeholder-gh-text-muted text-white bg-gh-bg rounded-md focus:outline-none focus:ring-2 focus:ring-homelab-500 focus:border-homelab-500 sm:text-sm transition-colors"
                           placeholder="johndoe"
                           value="{{ old('username') }}">
                    <p class="mt-1 text-xs text-gh-text-muted">Only letters, numbers, dashes, and underscores allowed.</p>
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gh-text-muted mb-1">Email address</label>
                    <input id="email" name="email" type="email" autocomplete="email" required
                           class="appearance-none relative block w-full px-3 py-2 border border-gh-border placeholder-gh-text-muted text-white bg-gh-bg rounded-md focus:outline-none focus:ring-2 focus:ring-homelab-500 focus:border-homelab-500 sm:text-sm transition-colors"
                           placeholder="you@example.com"
                           value="{{ old('email') }}">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gh-text-muted mb-1">Password</label>
                    <input id="password" name="password" type="password" autocomplete="new-password" required
                           class="appearance-none relative block w-full px-3 py-2 border border-gh-border placeholder-gh-text-muted text-white bg-gh-bg rounded-md focus:outline-none focus:ring-2 focus:ring-homelab-500 focus:border-homelab-500 sm:text-sm transition-colors"
                           placeholder="••••••••">
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gh-text-muted mb-1">Confirm password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required
                           class="appearance-none relative block w-full px-3 py-2 border border-gh-border placeholder-gh-text-muted text-white bg-gh-bg rounded-md focus:outline-none focus:ring-2 focus:ring-homelab-500 focus:border-homelab-500 sm:text-sm transition-colors"
                           placeholder="••••••••">
                </div>
            </div>

            <div>
                <button type="submit"
                        class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-homelab-600 hover:bg-homelab-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-homelab-500 transition-all duration-200 glow-accent">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-homelab-400 group-hover:text-homelab-300" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M8 9a3 3 0 100-6 3 3 0 000 6zM8 11a6 6 0 016 6H2a6 6 0 016-6zM16 7a1 1 0 10-2 0v1h-1a1 1 0 100 2h1v1a1 1 0 102 0v-1h1a1 1 0 100-2h-1V7z" />
                        </svg>
                    </span>
                    Create account
                </button>
            </div>
        </form>

        <div class="text-center space-y-2">
            <p class="text-sm text-gh-text-muted">
                Already have an account?
                <a href="{{ route('login') }}" class="font-medium text-homelab-500 hover:text-homelab-400 transition-colors">
                    Sign in
                </a>
            </p>
            <a href="/" class="text-sm text-gh-text-muted hover:text-white transition-colors block">
                ← Back to home
            </a>
        </div>
    </div>
</div>
@endsection
