@props([
    'variant' => 'default', // default, primary, success, warning, error, info
    'size' => 'md', // xs, sm, md, lg
    'dot' => false,
    'pill' => false,
    'removable' => false,
    'pulse' => false,
    'outline' => false,
    'href' => null,
    'status' => null, // online, offline, pending, active, inactive
    'ariaLabel' => null
])

@php
// Handle status-based variants
if ($status) {
    $variant = match($status) {
        'online', 'active' => 'success',
        'offline', 'inactive' => 'error', 
        'pending' => 'warning',
        default => $variant
    };
    $pulse = $status === 'online' || $status === 'pending';
}

$variantClass = match($variant) {
    'default' => 'hd-badge--default',
    'primary' => 'hd-badge--primary',
    'success' => 'hd-badge--success',
    'warning' => 'hd-badge--warning',
    'error' => 'hd-badge--error',
    'info' => 'hd-badge--info',
    default => 'hd-badge--default'
};

$sizeClass = match($size) {
    'xs' => 'hd-badge--xs',
    'sm' => 'hd-badge--sm',
    'md' => 'hd-badge--md',
    'lg' => 'hd-badge--lg',
    default => 'hd-badge--md'
};

$element = $href ? 'a' : 'span';
$badgeId = 'badge-' . uniqid();

$classes = collect([
    'hd-badge',
    $variantClass,
    $sizeClass,
    $dot ? 'hd-badge--dot' : '',
    $pill ? 'hd-badge--pill' : '',
    $pulse ? 'hd-badge--pulse' : '',
    $outline ? 'hd-badge--outline' : '',
    $removable ? 'hd-badge--removable' : '',
    $href ? 'hd-badge--clickable' : '',
    $status ? 'hd-badge--status-' . $status : ''
])->filter()->join(' ');
@endphp

<{{ $element }}
    @if($href) href="{{ $href }}" @endif
    id="{{ $badgeId }}"
    class="{{ $classes }}"
    @if($ariaLabel) aria-label="{{ $ariaLabel }}" @endif
    @if($status) role="status" aria-live="polite" @endif
    {{ $attributes->except(['class', 'id', 'href', 'aria-label']) }}
    x-data="{
        removable: {{ $removable ? 'true' : 'false' }},
        removed: false,
        handleRemove() {
            this.removed = true;
            this.$dispatch('badge-removed', { id: '{{ $badgeId }}', status: '{{ $status }}' });
        }
    }"
    x-show="!removed"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-95">

    {{-- Dot Indicator --}}
    @if($dot)
        <span class="hd-badge__dot {{ $pulse ? 'hd-badge__dot--pulse' : '' }}" 
              aria-hidden="true"></span>
    @endif

    {{-- Badge Content --}}
    <span class="hd-badge__text">
        {{ $slot }}
    </span>

    {{-- Remove Button --}}
    @if($removable)
        <button type="button" 
                class="hd-badge__remove"
                @click="handleRemove()"
                aria-label="Remove {{ $ariaLabel ?: 'badge' }}"
                tabindex="0">
            <svg class="hd-badge__remove-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    @endif

</{{ $element }}>
