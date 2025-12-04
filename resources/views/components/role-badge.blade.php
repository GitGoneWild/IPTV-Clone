@props(['role'])

@php
    $colors = [
        'admin' => 'bg-gh-danger/10 text-gh-danger',
        'reseller' => 'bg-gh-warning/10 text-gh-warning',
        'user' => 'bg-gh-success/10 text-gh-success',
        'guest' => 'bg-gh-text-muted/10 text-gh-text-muted',
    ];
    $badgeClass = $colors[$role] ?? $colors['guest'];
@endphp

<span {{ $attributes->merge(['class' => "px-2 py-1 text-xs font-medium rounded {$badgeClass}"]) }}>
    {{ ucfirst($role) }}
</span>
