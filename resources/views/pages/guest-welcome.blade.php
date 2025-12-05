@extends('layouts.app')

@section('title', 'Welcome - StreamPilot')

@section('content')
<div class="min-h-[calc(100vh-12rem)] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl w-full">
        <!-- Welcome Card -->
        <div class="bg-gh-bg-secondary rounded-lg border border-gh-border p-8 space-y-6">
            <!-- Header -->
            <div class="text-center">
                <div class="flex justify-center mb-4">
                    <div class="relative">
                        <div class="absolute -inset-4 bg-homelab-600/20 rounded-full blur-xl"></div>
                        <svg class="relative h-20 w-20 text-homelab-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
                <h1 class="text-3xl font-bold text-white mb-2">
                    Welcome to StreamPilot, {{ $user->name }}! 👋
                </h1>
                <p class="text-gh-text-muted text-lg">
                    Your account has been successfully created
                </p>
            </div>

            <!-- Status Badge -->
            <div class="flex justify-center">
                <div class="inline-flex items-center px-4 py-2 rounded-full bg-yellow-500/10 border border-yellow-500/50">
                    <svg class="w-5 h-5 text-yellow-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-yellow-500 font-medium">Pending Package Assignment</span>
                </div>
            </div>

            <!-- Information Box -->
            <div class="bg-gh-bg rounded-lg p-6 space-y-4">
                <div class="flex items-start space-x-3">
                    <svg class="w-6 h-6 text-homelab-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="flex-1">
                        <h3 class="text-white font-semibold mb-2">What's Next?</h3>
                        <p class="text-gh-text-muted mb-3">
                            Your account is currently in <strong class="text-white">Guest</strong> status. To access IPTV streams and content, an administrator needs to assign you a package.
                        </p>
                        <ul class="space-y-2 text-gh-text-muted text-sm">
                            <li class="flex items-start">
                                <svg class="w-4 h-4 text-homelab-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <span>Once a package is assigned, you'll automatically be upgraded to <strong class="text-white">User</strong> status</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-4 h-4 text-homelab-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <span>You'll gain access to streams, playlists, and EPG data</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-4 h-4 text-homelab-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <span>You'll receive access to your M3U and Xtream API credentials</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="bg-homelab-600/10 rounded-lg p-6 border border-homelab-600/30">
                <div class="flex items-start space-x-3">
                    <svg class="w-6 h-6 text-homelab-400 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <div class="flex-1">
                        <h3 class="text-white font-semibold mb-2">Need Help?</h3>
                        <p class="text-gh-text-muted text-sm">
                            If you haven't heard from an administrator within 24 hours, please contact support or reach out to your system administrator to request package assignment.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Account Details -->
            <div class="border-t border-gh-border pt-6">
                <h3 class="text-white font-semibold mb-4">Your Account Details</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gh-text-muted">Username:</span>
                        <span class="text-white ml-2 font-mono">{{ $user->username }}</span>
                    </div>
                    <div>
                        <span class="text-gh-text-muted">Email:</span>
                        <span class="text-white ml-2">{{ $user->email }}</span>
                    </div>
                    <div>
                        <span class="text-gh-text-muted">Account Status:</span>
                        <span class="text-yellow-500 ml-2 font-medium">Guest (Pending)</span>
                    </div>
                    <div>
                        <span class="text-gh-text-muted">Registered:</span>
                        <span class="text-white ml-2">{{ $user->created_at->diffForHumans() }}</span>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-center pt-4">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-sm text-gh-text-muted hover:text-white transition-colors">
                        Sign out
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
