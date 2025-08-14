@props([
    'title',
    'value',
    'icon' => null,
    'color' => 'blue',
    'href' => null,
    'subtitle' => null
])

{{-- Enhanced mobile-first stat card with proper touch targets --}}
@if($href)
    <a href="{{ $href }}" class="block touch-target--large">
@endif
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm hover:shadow-lg rounded-lg border border-gray-200 dark:border-gray-700 {{ $href ? 'transition-all duration-200 hover:scale-[1.02]' : '' }} mobile-accelerate">
            <div class="p-4 sm:p-5 lg:p-6">
                <div class="flex items-center space-x-4">
                    @if($icon)
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 sm:w-12 sm:h-12 text-{{ $color }}-600 dark:text-{{ $color }}-400 flex items-center justify-center bg-{{ $color }}-50 dark:bg-{{ $color }}-900/20 rounded-lg">
                                {!! $icon !!}
                            </div>
                        </div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <dl class="space-y-1">
                            <dt class="text-sm sm:text-base font-medium text-gray-600 dark:text-gray-400 truncate">
                                {{ $title }}
                            </dt>
                            <dd class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900 dark:text-gray-100 leading-tight">
                                {{ $value }}
                            </dd>
                            @if($subtitle)
                                <dd class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1">
                                    {{ $subtitle }}
                                </dd>
                            @endif
                        </dl>
                    </div>
                    @if($href)
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                    @endif
                </div>
            </div>
        </div>
@if($href)
    </a>
@endif
