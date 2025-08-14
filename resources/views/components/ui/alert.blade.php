@props([
    'variant' => 'info', // info, success, warning, error
    'title' => null,
    'icon' => null,
    'dismissible' => false
])

@php
$variantClass = match($variant) {
    'info' => 'hd-alert--info',
    'success' => 'hd-alert--success',
    'warning' => 'hd-alert--warning',
    'error' => 'hd-alert--error',
    default => 'hd-alert--info'
};

$iconMap = [
    'info' => '<svg class="hd-alert__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
    'success' => '<svg class="hd-alert__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
    'warning' => '<svg class="hd-alert__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>',
    'error' => '<svg class="hd-alert__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
];

$defaultIcon = $iconMap[$variant];
@endphp

<div 
    x-data="{ shown: true }"
    x-show="shown"
    x-transition
    {{ $attributes->merge(['class' => 'hd-alert ' . $variantClass, 'role' => 'alert']) }}
>
    <div class="hd-alert__content">
        <div class="hd-alert__icon-wrapper">
            {!! $icon ?? $defaultIcon !!}
        </div>
        <div class="hd-alert__text">
            @if($title)
                <h4 class="hd-alert__title">{{ $title }}</h4>
            @endif
            <div class="hd-alert__message">
                {{ $slot }}
            </div>
        </div>
        @if($dismissible)
            <button @click="shown = false" class="hd-alert__dismiss">
                <span class="hd-sr-only">Dismiss</span>
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
        @endif
    </div>
</div>
