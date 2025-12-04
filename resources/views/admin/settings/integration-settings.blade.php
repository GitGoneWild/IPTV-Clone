@extends('admin.layouts.admin')

@section('title', 'Integration Settings')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gh-text">Integration Settings</h1>
        <p class="mt-2 text-sm text-gh-text-muted">Configure external service integrations</p>
    </div>
    
    <div class="bg-gh-bg-secondary border border-gh-border rounded-lg p-6">
        <form method="POST" action="{{ route('admin.settings.update-integration-settings') }}">
            @csrf
            
            <div class="space-y-6">
                <div class="border-b border-gh-border pb-6">
                    <h3 class="text-lg font-medium text-gh-text mb-4">Real-Debrid Integration</h3>
                    <label class="flex items-center">
                        <input type="checkbox" name="real_debrid_enabled" value="1" {{ old('real_debrid_enabled', $settings['real_debrid_enabled']) ? 'checked' : '' }} class="rounded border-gh-border text-homelab-600 focus:ring-homelab-500">
                        <span class="ml-2 text-sm text-gh-text">Enable Real-Debrid integration</span>
                    </label>
                </div>
                
                <div class="border-b border-gh-border pb-6">
                    <h3 class="text-lg font-medium text-gh-text mb-4">TMDB Integration</h3>
                    <label class="flex items-center">
                        <input type="checkbox" name="tmdb_enabled" value="1" {{ old('tmdb_enabled', $settings['tmdb_enabled']) ? 'checked' : '' }} class="rounded border-gh-border text-homelab-600 focus:ring-homelab-500">
                        <span class="ml-2 text-sm text-gh-text">Enable TMDB metadata integration</span>
                    </label>
                </div>
                
                <div>
                    <h3 class="text-lg font-medium text-gh-text mb-4">EPG Integration</h3>
                    <label class="flex items-center">
                        <input type="checkbox" name="epg_enabled" value="1" {{ old('epg_enabled', $settings['epg_enabled']) ? 'checked' : '' }} class="rounded border-gh-border text-homelab-600 focus:ring-homelab-500">
                        <span class="ml-2 text-sm text-gh-text">Enable EPG data import</span>
                    </label>
                </div>
            </div>
            
            <div class="mt-6 flex justify-end">
                <button type="submit" class="px-4 py-2 bg-homelab-600 hover:bg-homelab-700 text-white rounded-lg">Save Settings</button>
            </div>
        </form>
    </div>
</div>
@endsection
