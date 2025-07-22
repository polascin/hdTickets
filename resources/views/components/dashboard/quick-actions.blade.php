@props([
    'actions' => [],
])

<div class="flex space-x-4">
    @foreach($actions as $action)
        <button type="button" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:border-blue-700 focus:ring focus:ring-blue-200 disabled:opacity-25 transition">
            {{ $action['label'] }}
        </button>
    @endforeach
</div>

