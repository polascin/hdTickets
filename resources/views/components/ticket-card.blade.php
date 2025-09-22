{{-- Minimal Ticket Card component --}}
@props([
    'title' => null,
    'price' => null,
    'currency' => 'USD',
    'venue' => null,
    'date' => null,
    'status' => null,
    'href' => null,
])

@php
    $classes = 'ticket-card block rounded-lg border border-gray-200 bg-white shadow-sm p-4 hover:shadow-md transition-shadow';
@endphp

<a {{ $href ? "href=$href" : '' }} {{ $attributes->merge(['class' => $classes]) }}>
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <div class="text-gray-900 font-semibold truncate">{{ $title ?? $slot ?? 'Ticket' }}</div>
            <div class="text-sm text-gray-500 truncate">{{ $venue ?? '—' }}</div>
            @if($date)
                <div class="text-xs text-gray-400">{{ $date }}</div>
            @endif
        </div>
        <div class="text-right">
            @if(!is_null($price))
                <div class="text-lg font-bold text-emerald-600">
                    {{ is_numeric($price) ? (new \NumberFormatter('en_US', \NumberFormatter::CURRENCY))->formatCurrency($price, $currency) : $price }}
                </div>
            @endif
            @if($status)
                <div class="text-xs mt-1 inline-flex items-center rounded px-2 py-0.5 border"
                     @class([
                        'border-green-200 text-green-700 bg-green-50' => $status === 'online',
                        'border-yellow-200 text-yellow-700 bg-yellow-50' => $status === 'warning',
                        'border-red-200 text-red-700 bg-red-50' => $status === 'offline',
                        'border-gray-200 text-gray-700 bg-gray-50' => !in_array($status, ['online','warning','offline']),
                     ])>
                    {{ ucfirst($status) }}
                </div>
            @endif
        </div>
    </div>
</a>
