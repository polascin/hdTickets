{{--
    Enhanced Universal Dropdown Component
    Provides consistent dropdown functionality across the application
    with accessibility, keyboard navigation, and responsive design
--}}
@props([
    'align' => 'right', // left, center, right
    'width' => 'w-48',
    'maxHeight' => 'max-h-96',
    'contentClasses' => 'py-1 bg-white border border-gray-200',
    'trigger' => null,
    'loading' => false,
    'error' => false,
    'errorMessage' => '',
    'id' => 'dropdown-' . uniqid(),
    'closeOnClick' => true,
    'searchable' => false,
    'placeholder' => 'Select an option...',
])

@php
  $alignmentClasses = match ($align) {
      'left' => 'left-0',
      'center' => 'left-1/2 transform -translate-x-1/2',
      'right' => 'right-0',
      default => 'right-0',
  };

  $dropdownClasses = implode(' ', [
      'absolute z-50 mt-1 rounded-lg shadow-lg',
      $alignmentClasses,
      $width,
      $maxHeight,
      'overflow-auto',
  ]);
@endphp

<div class="relative dropdown-container {{ $error ? 'dropdown-error' : '' }} {{ $loading ? 'dropdown-loading' : '' }}"
  x-data="{
      open: false,
      search: '',
      selectedValue: '',
      selectedText: '{{ $placeholder }}',
      searchable: {{ $searchable ? 'true' : 'false' }},
      closeOnClick: {{ $closeOnClick ? 'true' : 'false' }},
  
      toggle() {
          this.open = !this.open;
          if (this.open && this.searchable) {
              this.$nextTick(() => {
                  this.$refs.searchInput?.focus();
              });
          }
      },
  
      close() {
          this.open = false;
          this.search = '';
      },
  
      select(value, text) {
          this.selectedValue = value;
          this.selectedText = text;
          this.$dispatch('dropdown-selected', { value, text });
          if (this.closeOnClick) {
              this.close();
          }
      },
  
      handleKeydown(event) {
          if (event.key === 'Escape') {
              this.close();
              return;
          }
  
          if (event.key === 'Enter' && !this.open) {
              this.toggle();
              return;
          }
  
          if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
              event.preventDefault();
              if (!this.open) {
                  this.open = true;
                  return;
              }
  
              const items = this.$el.querySelectorAll('[role=\"menuitem\"]'); const current=document.activeElement; const
  index=Array.from(items).indexOf(current); let nextIndex; if (event.key === 'ArrowDown') { nextIndex=index <
  items.length - 1 ? index + 1 : 0; } else { nextIndex=index> 0 ? index - 1 : items.length - 1;
  }

  items[nextIndex]?.focus();
  }
  }
  }"
  @click.away="close()"
  @keydown="handleKeydown"
  x-init="$watch('open', value => {
      if (value) {
          $dispatch('dropdown-opened', { id: '{{ $id }}' });
      } else {
          $dispatch('dropdown-closed', { id: '{{ $id }}' });
      }
  });"
  role="combobox"
  :aria-expanded="open"
  aria-haspopup="listbox"
  :aria-owns="'{{ $id }}-menu'"
  >
  <!-- Trigger -->
  <div @click="toggle()" class="dropdown-trigger cursor-pointer">
    @if ($trigger)
      {{ $trigger }}
    @else
      <button type="button"
        class="inline-flex items-center justify-between w-full px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors"
        :class="{ 'border-red-500 ring-red-500': {{ $error ? 'true' : 'false' }} }" :aria-label="selectedText">
        <span x-text="selectedText" class="truncate"></span>
        <svg class="w-5 h-5 ml-2 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': open }"
          fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
      </button>
    @endif
  </div>

  <!-- Dropdown Menu -->
  <div x-show="open" x-cloak x-transition:enter="dropdown-fade-enter-active"
    x-transition:enter-start="dropdown-fade-enter" x-transition:enter-end="dropdown-fade-enter-to"
    x-transition:leave="dropdown-fade-leave-active" x-transition:leave-start="dropdown-fade-leave"
    x-transition:leave-end="dropdown-fade-leave-to" class="{{ $dropdownClasses }}" id="{{ $id }}-menu"
    role="listbox" aria-orientation="vertical" style="display: none;">
    <div class="{{ $contentClasses }} rounded-lg">
      <!-- Search Input (if searchable) -->
      <div x-show="searchable" class="p-3 border-b border-gray-200">
        <input x-ref="searchInput" x-model="search" type="text" placeholder="Search..."
          class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          @click.stop>
      </div>

      <!-- Loading State -->
      @if ($loading)
        <div class="p-4 text-center">
          <div class="inline-block animate-spin rounded-full h-4 w-4 border-b-2 border-blue-600"></div>
          <span class="ml-2 text-sm text-gray-600">Loading...</span>
        </div>
      @else
        <!-- Content Slot -->
        {{ $slot }}
      @endif

      <!-- No Results (for searchable dropdowns) -->
      <div x-show="searchable && search && $el.querySelectorAll('[role=\"menuitem\"]:not(.hidden)').length === 0"
        class="p-3 text-sm text-gray-500 text-center">
        No results found
      </div>
    </div>
  </div>

  <!-- Error Message -->
  @if ($error && $errorMessage)
    <div class="dropdown-error-message">
      {{ $errorMessage }}
    </div>
  @endif
</div>

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/dropdown-enhancements.css') }}">
@endpush

@push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Global dropdown management
      document.addEventListener('dropdown-opened', function(event) {
        // Close other open dropdowns
        const allDropdowns = document.querySelectorAll('[x-data*="open:"]');
        allDropdowns.forEach(dropdown => {
          if (dropdown.__x && dropdown.__x.$data.open && dropdown !== event.target) {
            dropdown.__x.$data.open = false;
          }
        });
      });

      // Keyboard navigation improvements
      document.addEventListener('keydown', function(event) {
        if (event.key === 'Tab') {
          // Close dropdowns when tabbing away
          const activeDropdown = document.querySelector('[role="combobox"][aria-expanded="true"]');
          if (activeDropdown && !activeDropdown.contains(event.target)) {
            activeDropdown.__x?.$data.close();
          }
        }
      });
    });
  </script>
@endpush
