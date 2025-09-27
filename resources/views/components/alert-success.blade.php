{{-- Success Alert Component --}}
@props(['message', 'dismissible' => true, 'icon' => true])

<div x-data="{ show: true }" x-show="show" x-transition:enter="transition ease-out duration-300"
  x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100"
  x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform scale-100"
  x-transition:leave-end="opacity-0 transform scale-95" class="rounded-lg bg-green-50 border border-green-200 p-4">
  <div class="flex">
    @if ($icon)
      <div class="flex-shrink-0">
        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
          <path fill-rule="evenodd"
            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.236 4.53L7.53 10.53a.75.75 0 00-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
            clip-rule="evenodd" />
        </svg>
      </div>
    @endif
    <div class="ml-3 flex-1">
      <p class="text-sm font-medium text-green-800">{{ $message }}</p>
    </div>
    @if ($dismissible)
      <div class="ml-auto pl-3">
        <div class="-mx-1.5 -my-1.5">
          <button @click="show = false" type="button"
            class="inline-flex rounded-md bg-green-50 p-1.5 text-green-500 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-green-600 focus:ring-offset-2 focus:ring-offset-green-50">
            <span class="sr-only">Dismiss</span>
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
              <path
                d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
            </svg>
          </button>
        </div>
      </div>
    @endif
  </div>
</div>
