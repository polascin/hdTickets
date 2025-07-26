@props([
    'message',
    'status' => null,
    'bannerColor' => 'blue'
])

<div class="bg-{{ $bannerColor }}-100 p-4 rounded-lg">
    <h3 class="text-{{ $bannerColor }}-800 font-medium">
        {{ $message }}
    </h3>
    @if($status)
        <p class="text-sm text-{{ $bannerColor }}-700 mt-1">
            {{ $status }}
        </p>
    @endif
</div>

