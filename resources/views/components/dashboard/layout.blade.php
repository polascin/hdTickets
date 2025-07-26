@props([
    'title' => 'Dashboard',
    'subtitle' => null,
    'headerActions' => null,
    'bannerColor' => 'blue',
    'showStats' => true
])

<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $title }}
                </h2>
                @if($subtitle)
                    <p class="text-sm text-gray-600 mt-1">{{ $subtitle }}</p>
                @endif
            </div>
            @if($headerActions)
                <div class="flex items-center space-x-4">
                    {{ $headerActions }}
                </div>
            @endif
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            {{ $slot }}
        </div>
    </div>
</x-app-layout>
