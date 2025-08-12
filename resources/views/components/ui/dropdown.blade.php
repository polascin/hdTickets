@props([
    'align' => 'right', // left, right, center
    'width' => 'w-48',
    'contentClasses' => 'hd-dropdown__content',
    'trigger' => null
])

@php
$alignmentClasses = match ($align) {
    'left' => 'hd-dropdown--left',
    'right' => 'hd-dropdown--right', 
    'center' => 'hd-dropdown--center',
    default => 'hd-dropdown--right'
};
@endphp

<div class="hd-dropdown {{ $alignmentClasses }}" x-data="{ open: false }" @click.away="open = false" @close.stop="open = false">
    <div @click="open = ! open" class="hd-dropdown__trigger">
        @if ($trigger)
            {{ $trigger }}
        @else
            {{ $trigger ?? $slot }}
        @endif
    </div>

    <div 
        x-show="open"
        x-transition:enter="hd-dropdown--enter"
        x-transition:enter-start="hd-dropdown--enter-start"
        x-transition:enter-end="hd-dropdown--enter-end"
        x-transition:leave="hd-dropdown--leave"
        x-transition:leave-start="hd-dropdown--leave-start"
        x-transition:leave-end="hd-dropdown--leave-end"
        class="hd-dropdown__menu {{ $width }}"
        style="display: none;"
        @click="open = false">
        <div class="{{ $contentClasses }}">
            @if (!$trigger)
                {{ $slot }}
            @else
                @isset($content)
                    {{ $content }}
                @endisset
            @endif
        </div>
    </div>
</div>
