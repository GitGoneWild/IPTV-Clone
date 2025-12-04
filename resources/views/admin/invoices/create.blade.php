@extends('admin.layouts.admin')

@section('title', 'Create Invoice')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gh-text">Create Invoice</h1>
    </div>
    <div class="bg-gh-bg-secondary border border-gh-border rounded-lg p-6">
        <form method="POST" action="{{ isset($invoices) ? route('admin.invoices.update', $invoices) : route('admin.invoices.store') }}">
            @csrf
            @if(isset($invoices))
                @method('PUT')
            @endif
            
            <div class="space-y-4">
                <!-- Add form fields here -->
                <div>
                    <label class="block text-sm font-medium text-gh-text mb-2">Name</label>
                    <input type="text" name="name" class="w-full px-4 py-2 bg-gh-bg border border-gh-border rounded-lg text-gh-text" required>
                </div>
            </div>
            
            <div class="mt-6 flex justify-end space-x-3">
                <a href="{{ route('admin.invoices.index') }}" class="px-4 py-2 bg-gh-bg-tertiary text-gh-text rounded-lg hover:bg-gh-border">Cancel</a>
                <button type="submit" class="px-4 py-2 bg-homelab-600 hover:bg-homelab-700 text-white rounded-lg">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection
