{{--
    Multi-Select Dropdown Component
    Provides advanced multi-selection capabilities with search and tags
--}}
@props([
    'name' => 'multiselect',
    'placeholder' => 'Select options...',
    'options' => [],
    'selected' => [],
    'searchable' => true,
    'clearable' => true,
    'maxSelections' => null,
    'loading' => false,
    'disabled' => false,
    'required' => false,
    'id' => 'multiselect-' . uniqid(),
])

@php
  $selectedArray = is_array($selected) ? $selected : (is_string($selected) ? explode(',', $selected) : []);
@endphp

<div class="relative multiselect-container {{ $disabled ? 'opacity-50' : '' }}" x-data="{
    open: false,
    search: '',
    selected: {{ json_encode($selectedArray) }},
    options: {{ json_encode($options) }},
    filteredOptions: {{ json_encode($options) }},
    maxSelections: {{ $maxSelections ?? 'null' }},
    loading: {{ $loading ? 'true' : 'false' }},
    disabled: {{ $disabled ? 'true' : 'false' }},

    get selectedCount() {
        return this.selected.length;
    },

    get canAddMore() {
        return this.maxSelections === null || this.selectedCount < this.maxSelections;
    },

    get placeholder() {
        if (this.selectedCount === 0) {
            return '{{ $placeholder }}';
        }
        return this.selectedCount === 1 ? '1 item selected' : this.selectedCount + ' items selected';
    },

    init() {
        this.filterOptions();
        this.$watch('search', () => this.filterOptions());
    },

    toggle() {
        if (this.disabled) return;
        this.open = !this.open;
        if (this.open) {
            this.$nextTick(() => {
                if (this.$refs.searchInput) {
                    this.$refs.searchInput.focus();
                }
            });
        }
    },

    close() {
        this.open = false;
        this.search = '';
        this.filterOptions();
    },

    isSelected(value) {
        return this.selected.includes(value);
    },

    select(value, text) {
        if (this.disabled) return;

        if (this.isSelected(value)) {
            this.deselect(value);
        } else if (this.canAddMore) {
            this.selected.push(value);
            this.$dispatch('multiselect-added', { value, text, selected: this.selected });
        }

        this.$dispatch('multiselect-changed', { selected: this.selected });
        this.updateHiddenInput();
    },

    deselect(value) {
        const index = this.selected.indexOf(value);
        if (index > -1) {
            const removed = this.selected.splice(index, 1)[0];
            this.$dispatch('multiselect-removed', { value: removed, selected: this.selected });
            this.$dispatch('multiselect-changed', { selected: this.selected });
            this.updateHiddenInput();
        }
    },

    clear() {
        if (this.disabled) return;
        this.selected = [];
        this.$dispatch('multiselect-cleared');
        this.$dispatch('multiselect-changed', { selected: this.selected });
        this.updateHiddenInput();
    },

    updateHiddenInput() {
        const input = this.$el.querySelector('input[type=\"hidden\"]'); if
  (input) { input.value=this.selected.join(','); } }, filterOptions() { if (!this.search) {
  this.filteredOptions=this.options; return; } this.filteredOptions=this.options.filter(option=> {
  const text = typeof option === 'object' ? option.text || option.label : option;
  return text.toLowerCase().includes(this.search.toLowerCase());
  });
  },

  getOptionText(option) {
  return typeof option === 'object' ? (option.text || option.label) : option;
  },

  getOptionValue(option) {
  return typeof option === 'object' ? (option.value || option.id) : option;
  }
  }"
  @click.away="close()"
  @keydown.escape="close()"
  >
  <!-- Hidden Input for Form Submission -->
  <input type="hidden" name="{{ $name }}" :value="selected.join(',')" {{ $required ? 'required' : '' }}>

  <!-- Main Select Button -->
  <button type="button" @click="toggle()"
    class="relative w-full min-h-[44px] px-3 py-2 text-left bg-white border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 {{ $disabled ? 'cursor-not-allowed bg-gray-50' : 'cursor-pointer hover:bg-gray-50' }}"
    :class="{ 'border-red-500 ring-red-500': {{ $required ? 'true' : 'false' }} && selected.length === 0 }"
    :aria-expanded="open" aria-haspopup="listbox" :disabled="disabled">
    <div class="flex items-center justify-between">
      <!-- Selected Items Display -->
      <div class="flex-1 min-w-0">
        <div x-show="selected.length === 0" class="text-gray-500" x-text="'{{ $placeholder }}'"></div>

        <div x-show="selected.length > 0" class="flex flex-wrap gap-1">
          <!-- Tags for selected items (show first few) -->
          <template x-for="(value, index) in selected.slice(0, 3)" :key="value">
            <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-blue-100 text-blue-800">
              <span x-text="getOptionText(options.find(opt => getOptionValue(opt) === value) || value)"></span>
              <button type="button" @click.stop="deselect(value)"
                class="ml-1 inline-flex items-center p-0.5 text-blue-400 hover:text-blue-600" :disabled="disabled">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd"
                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                    clip-rule="evenodd" />
                </svg>
              </button>
            </span>
          </template>

          <!-- Show count if more items selected -->
          <span x-show="selected.length > 3"
            class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-600">
            +<span x-text="selected.length - 3"></span> more
          </span>
        </div>
      </div>

      <!-- Controls -->
      <div class="flex items-center space-x-2 ml-2">
        <!-- Clear Button -->
        <button x-show="{{ $clearable ? 'true' : 'false' }} && selected.length > 0 && !disabled" type="button"
          @click.stop="clear()" class="p-1 text-gray-400 hover:text-gray-600 focus:outline-none" aria-label="Clear all">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>

        <!-- Dropdown Arrow -->
        <svg class="w-5 h-5 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': open }"
          fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
      </div>
    </div>
  </button>

  <!-- Dropdown Panel -->
  <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-95"
    class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-64 overflow-hidden"
    style="display: none;">
    <!-- Search Input -->
    <div x-show="{{ $searchable ? 'true' : 'false' }}" class="p-3 border-b border-gray-200">
      <input x-ref="searchInput" x-model="search" type="text" placeholder="Search options..."
        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
        @click.stop>
    </div>

    <!-- Options List -->
    <div class="overflow-y-auto max-h-48">
      <!-- Loading State -->
      <div x-show="loading" class="p-4 text-center text-sm text-gray-500">
        <div class="inline-block animate-spin rounded-full h-4 w-4 border-b-2 border-blue-600 mr-2"></div>
        Loading options...
      </div>

      <!-- Options -->
      <template x-for="option in filteredOptions" :key="getOptionValue(option)">
        <button type="button" @click="select(getOptionValue(option), getOptionText(option))"
          class="w-full px-4 py-2 text-left hover:bg-gray-50 focus:bg-gray-50 focus:outline-none flex items-center justify-between"
          :class="{
              'bg-blue-50 text-blue-700': isSelected(getOptionValue(option)),
              'opacity-50 cursor-not-allowed': maxSelections && !isSelected(getOptionValue(option)) && !canAddMore
          }"
          :disabled="maxSelections && !isSelected(getOptionValue(option)) && !canAddMore">
          <span x-text="getOptionText(option)" class="flex-1"></span>
          <svg x-show="isSelected(getOptionValue(option))" class="w-4 h-4 text-blue-600 flex-shrink-0"
            fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd"
              d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
              clip-rule="evenodd" />
          </svg>
        </button>
      </template>

      <!-- No Options -->
      <div x-show="filteredOptions.length === 0 && !loading" class="p-4 text-center text-sm text-gray-500">
        <span x-show="search">No options found</span>
        <span x-show="!search">No options available</span>
      </div>
    </div>

    <!-- Selection Summary -->
    <div x-show="selected.length > 0" class="p-3 bg-gray-50 border-t border-gray-200 text-sm text-gray-600">
      <div class="flex items-center justify-between">
        <span>
          <span x-text="selected.length"></span>
          <span x-show="selected.length === 1">item</span>
          <span x-show="selected.length !== 1">items</span>
          selected
          <span x-show="maxSelections" class="text-gray-500">
            (max <span x-text="maxSelections"></span>)
          </span>
        </span>
        <button x-show="{{ $clearable ? 'true' : 'false' }}" type="button" @click="clear()"
          class="text-blue-600 hover:text-blue-700 font-medium">
          Clear all
        </button>
      </div>
    </div>
  </div>
</div>

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/dropdown-enhancements.css') }}">
@endpush
