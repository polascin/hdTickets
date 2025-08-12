
@props([
    'title' => null,
    'subtitle' => null,
    'bordered' => true
]);

@php
$classes = collect([
    'hd-card__header',
    $bordered ? 'hd-card__header--bordered' : ''
])->filter()->join(' ');
@endphp;

<div {{ $attributes->merge(['class' => $classes]) }}>
    <div class="hd-card__header-content">
        @if($title)
            <div class="hd-card__header-text">
                <h3 class="hd-card__title">{{ $title }}</h3>
                @if($subtitle)
                    <p class="hd-card__subtitle">{{ $subtitle }}</p>
                @endif
            </div>
        @endif
        
        @isset($slot)
            @if(!$title)
                {{ $slot }}
            @else
                <div class="hd-card__header-actions">
                    {{ $slot }}
                </div>
            @endif
        @endisset
    </div>
</div>
