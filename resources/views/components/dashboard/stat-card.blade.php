@props([
    'title',
    'value',
    'icon' => null,
    'color' => 'blue',
    'href' => null,
    'subtitle' => null
])

@if($href)
    <a href="{{ $href }}" class="block">
@endif
        <div class="bg-white overflow-hidden shadow rounded-lg {{ $href ? 'hover:shadow-md transition-shadow duration-200' : '' }}">
            <div class="p-5">
                <div class="flex items-center">
                    @if($icon)
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 text-{{ $color }}-600">
                                {!! $icon !!}
                            </div>
                        </div>
                    @endif
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">{{ $title }}</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $value }}</dd>
                            @if($subtitle)
                                <dd class="text-xs text-gray-500 mt-1">{{ $subtitle }}</dd>
                            @endif
                        </dl>
                    </div>
                </div>
            </div>
        </div>
@if($href)
    </a>
@endif
