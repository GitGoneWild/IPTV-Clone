@extends('layouts.app')

@section('title', 'Login - StreamPilot')

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
                Sign in to StreamPilot
            </h2>
            <p class="mt-2 text-center text-sm text-gh-text-muted">
                Access your private IPTV dashboard
            </p>
        </div>

        <form class="mt-8 space-y-6 bg-gh-bg-secondary rounded-lg p-6 border border-gh-border" action="{{ route('login.store') }}" method="POST">
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
                    <label for="email" class="block text-sm font-medium text-gh-text-muted mb-1">Email address</label>
                    <input id="email" name="email" type="email" autocomplete="email" required
                           class="appearance-none relative block w-full px-3 py-2 border border-gh-border placeholder-gh-text-muted text-white bg-gh-bg rounded-md focus:outline-none focus:ring-2 focus:ring-homelab-500 focus:border-homelab-500 sm:text-sm transition-colors"
                           placeholder="you@example.com"
                           value="{{ old('email') }}">
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gh-text-muted mb-1">Password</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required
                           class="appearance-none relative block w-full px-3 py-2 border border-gh-border placeholder-gh-text-muted text-white bg-gh-bg rounded-md focus:outline-none focus:ring-2 focus:ring-homelab-500 focus:border-homelab-500 sm:text-sm transition-colors"
                           placeholder="••••••••">
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember" name="remember" type="checkbox"
                           class="h-4 w-4 text-homelab-600 focus:ring-homelab-500 border-gh-border rounded bg-gh-bg">
                    <label for="remember" class="ml-2 block text-sm text-gh-text-muted">
                        Remember me
                    </label>
                </div>
            </div>

            <div>
                <button type="submit"
                        class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-homelab-600 hover:bg-homelab-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-homelab-500 transition-all duration-200 glow-accent">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-homelab-400 group-hover:text-homelab-300" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                        </svg>
                    </span>
                    Sign in
                </button>
            </div>
        </form>

        <div class="text-center space-y-2">
            <p class="text-sm text-gh-text-muted">
                Don't have an account?
                <a href="{{ route('register') }}" class="font-medium text-homelab-500 hover:text-homelab-400 transition-colors">
                    Create one
                </a>
            </p>
            <a href="/" class="text-sm text-gh-text-muted hover:text-white transition-colors block">
                ← Back to home
            </a>
        </div>
    </div>
</div>
@endsection
