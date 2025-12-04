@extends('admin.layouts.admin')

@section('title', 'Devices')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gh-text">Devices</h1>
        <p class="mt-2 text-sm text-gh-text-muted">View and manage user devices. Devices are automatically registered when users connect.</p>
    </div>
    <div class="bg-gh-bg-secondary border border-gh-border rounded-lg p-6">
        <p class="text-gh-text-muted">Manage Devices</p>
        <!-- Add table content here -->
    </div>
</div>
@endsection
