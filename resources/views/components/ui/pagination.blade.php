@props([
    'paginator',
    'size' => 'md', // sm, md, lg
    'showInfo' => true,
    'showFirst' => true,
    'showLast' => true
])

@php
$sizeClass = match($size) {
    'sm' => 'hd-pagination--sm',
    'md' => 'hd-pagination--md',
    'lg' => 'hd-pagination--lg',
    default => 'hd-pagination--md'
};
@endphp

@if ($paginator->hasPages())
<nav {{ $attributes->merge(['class' => 'hd-pagination-wrapper']) }}>
    @if($showInfo)
    <div class="hd-pagination-info">
        <p class="hd-pagination-info__text">
            Showing {{ $paginator->firstItem() ?? 0 }} to {{ $paginator->lastItem() ?? 0 }} of {{ $paginator->total() }} results
        </p>
    </div>
    @endif
    
    <div class="hd-pagination {{ $sizeClass }}">
        {{-- First Page Link --}}
        @if($showFirst && $paginator->currentPage() > 3)
            <a href="{{ $paginator->url(1) }}" class="hd-pagination__item" aria-label="Go to first page">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
                </svg>
            </a>
        @endif

        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span class="hd-pagination__item hd-pagination__item--disabled" aria-disabled="true" aria-label="Previous page">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="hd-pagination__item" rel="prev" aria-label="Previous page">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($paginator->getUrlRange(max(1, $paginator->currentPage() - 2), min($paginator->lastPage(), $paginator->currentPage() + 2)) as $page => $url)
            @if ($page == $paginator->currentPage())
                <span class="hd-pagination__item hd-pagination__item--active" aria-current="page">{{ $page }}</span>
            @else
                <a href="{{ $url }}" class="hd-pagination__item" aria-label="Go to page {{ $page }}">{{ $page }}</a>
            @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="hd-pagination__item" rel="next" aria-label="Next page">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        @else
            <span class="hd-pagination__item hd-pagination__item--disabled" aria-disabled="true" aria-label="Next page">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </span>
        @endif

        {{-- Last Page Link --}}
        @if($showLast && $paginator->currentPage() < $paginator->lastPage() - 2)
            <a href="{{ $paginator->url($paginator->lastPage()) }}" class="hd-pagination__item" aria-label="Go to last page">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path>
                </svg>
            </a>
        @endif
    </div>
</nav>
@endif
