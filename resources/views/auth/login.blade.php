@extends('layouts.app')

@section('title', 'Login - HomelabTV')

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <div class="flex justify-center">
                <svg class="h-16 w-16 text-homelab-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
            </div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-white">
                Sign in to HomelabTV
            </h2>
        </div>
        
        <form class="mt-8 space-y-6" action="{{ route('login.store') }}" method="POST">
            @csrf
            
            @if ($errors->any())
                <div class="bg-red-900/50 border border-red-500 text-red-300 px-4 py-3 rounded relative" role="alert">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            <div class="rounded-md shadow-sm -space-y-px">
                <div>
                    <label for="email" class="sr-only">Email address</label>
                    <input id="email" name="email" type="email" autocomplete="email" required 
                           class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-700 placeholder-gray-500 text-white bg-gray-800 rounded-t-md focus:outline-none focus:ring-homelab-500 focus:border-homelab-500 focus:z-10 sm:text-sm" 
                           placeholder="Email address"
                           value="{{ old('email') }}">
                </div>
                <div>
                    <label for="password" class="sr-only">Password</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required 
                           class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-700 placeholder-gray-500 text-white bg-gray-800 rounded-b-md focus:outline-none focus:ring-homelab-500 focus:border-homelab-500 focus:z-10 sm:text-sm" 
                           placeholder="Password">
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember" name="remember" type="checkbox" 
                           class="h-4 w-4 text-homelab-600 focus:ring-homelab-500 border-gray-700 rounded bg-gray-800">
                    <label for="remember" class="ml-2 block text-sm text-gray-400">
                        Remember me
                    </label>
                </div>
            </div>

            <div>
                <button type="submit" 
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-homelab-600 hover:bg-homelab-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-homelab-500">
                    Sign in
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
