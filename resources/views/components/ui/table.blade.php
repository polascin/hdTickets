@props([
    'striped' => false,
    'bordered' => false,
    'hover' => false,
    'compact' => false,
    'responsive' => true,
    'loading' => false,
    'emptyMessage' => 'No data available'
])

@php
$tableClasses = collect([
    'hd-table',
    $striped ? 'hd-table--striped' : '',
    $bordered ? 'hd-table--bordered' : '',
    $hover ? 'hd-table--hover' : '',
    $compact ? 'hd-table--compact' : '',
    $loading ? 'hd-table--loading' : ''
])->filter()->join(' ');
@endphp

<div {{ $attributes->merge(['class' => $responsive ? 'hd-table-responsive' : '']) }}>
    @if($loading)
        <div class="hd-table-loading">
            <div class="hd-loading-skeleton hd-loading-skeleton--table">
                @for($i = 0; $i < 5; $i++)
                    <div class="hd-loading-skeleton__row">
                        @for($j = 0; $j < 4; $j++)
                            <div class="hd-loading-skeleton__cell"></div>
                        @endfor
                    </div>
                @endfor
            </div>
        </div>
    @else
        <table class="{{ $tableClasses }}">
            {{ $slot }}
        </table>
        
        @if(isset($empty) && $empty)
            <div class="hd-table-empty">
                <div class="hd-table-empty__content">
                    <svg class="hd-table-empty__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <p class="hd-table-empty__message">{{ $emptyMessage }}</p>
                </div>
            </div>
        @endif
    @endif
</div>
