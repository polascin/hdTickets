@props([
    'route',
    'routePattern' => null,
    'icon' => null,
    'label',
    'responsive' => false
])

@php
    $routePattern = $routePattern ?? $route . '*';
    $isActive = request()->routeIs($routePattern);
    $component = $responsive ? 'x-responsive-nav-link' : 'x-nav-link';
@endphp

<{{ $component }} :href="route('{{ $route }}')" :active="{{ $isActive ? 'true' : 'false' }}">
    @if($icon)
        {!! $icon !!}
    @endif
    {{ $label }}
</{{ $component }}>
