@extends('admin.layouts.admin')

@section('title', 'Create Stream')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gh-text">Create Stream</h1>
    </div>
    <div class="bg-gh-bg-secondary border border-gh-border rounded-lg p-6">
        <form method="POST" action="{{ route('admin.streams.store') }}">
            @csrf
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gh-text mb-2">Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="w-full px-4 py-2 bg-gh-bg border border-gh-border rounded-lg text-gh-text" required>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gh-text mb-2">Stream URL *</label>
                    <input type="url" name="stream_url" value="{{ old('stream_url') }}" class="w-full px-4 py-2 bg-gh-bg border border-gh-border rounded-lg text-gh-text" required>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gh-text mb-2">Category *</label>
                    <select name="category_id" class="w-full px-4 py-2 bg-gh-bg border border-gh-border rounded-lg text-gh-text" required>
                        <option value="">Select Category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gh-text mb-2">Server</label>
                    <select name="server_id" class="w-full px-4 py-2 bg-gh-bg border border-gh-border rounded-lg text-gh-text">
                        <option value="">Select Server (Optional)</option>
                        @foreach($servers as $server)
                            <option value="{{ $server->id }}" {{ old('server_id') == $server->id ? 'selected' : '' }}>
                                {{ $server->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gh-text mb-2">Stream Icon URL</label>
                    <input type="url" name="stream_icon" value="{{ old('stream_icon') }}" class="w-full px-4 py-2 bg-gh-bg border border-gh-border rounded-lg text-gh-text">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gh-text mb-2">EPG Channel ID</label>
                    <input type="text" name="epg_channel_id" value="{{ old('epg_channel_id') }}" class="w-full px-4 py-2 bg-gh-bg border border-gh-border rounded-lg text-gh-text">
                </div>
                
                <div>
                    <label class="flex items-center">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="rounded border-gh-border text-homelab-600 focus:ring-homelab-500">
                        <span class="ml-2 text-sm text-gh-text">Active</span>
                    </label>
                </div>
            </div>
            
            <div class="mt-6 flex justify-end space-x-3">
                <a href="{{ route('admin.streams.index') }}" class="px-4 py-2 bg-gh-bg-tertiary text-gh-text rounded-lg hover:bg-gh-border">Cancel</a>
                <button type="submit" class="px-4 py-2 bg-homelab-600 hover:bg-homelab-700 text-white rounded-lg">Create Stream</button>
            </div>
        </form>
    </div>
</div>
@endsection
