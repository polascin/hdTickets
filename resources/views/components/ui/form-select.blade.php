@props([
    'label' => null,
    'name' => null,
    'id' => null,
    'required' => false,
    'disabled' => false,
    'multiple' => false,
    'error' => null,
    'helpText' => null,
    'placeholder' => null,
    'selected' => null,
    'options' => [],
    'size' => 'md',
    'variant' => 'default'
])

@php
    $selectId = $id ?? ($name ? $name . '-' . uniqid() : 'select-' . uniqid());
    $labelId = $selectId . '-label';
    $errorId = $selectId . '-error';
    $helpId = $selectId . '-help';
    
    $ariaDescribedBy = collect([
        $helpText ? $helpId : null,
        $error ? $errorId : null
    ])->filter()->implode(' ');

    $sizeClasses = [
        'sm' => 'px-3 py-1.5 text-sm',
        'md' => 'px-4 py-2 text-base',
        'lg' => 'px-4 py-3 text-lg'
    ];

    $variantClasses = [
        'default' => 'border-border-primary focus:border-hd-primary-600 focus:ring-hd-primary-600',
        'error' => 'border-hd-danger-600 focus:border-hd-danger-600 focus:ring-hd-danger-600'
    ];

    $selectClasses = [
        'hdt-select__field',
        'block w-full rounded-md border transition-colors duration-150',
        'bg-surface-secondary text-text-primary',
        'focus:outline-none focus:ring-2 focus:ring-opacity-50',
        'disabled:bg-surface-tertiary disabled:text-text-quaternary disabled:cursor-not-allowed',
        'appearance-none',
        'bg-no-repeat bg-right-4 bg-center',
        $sizeClasses[$size] ?? $sizeClasses['md'],
        $error ? $variantClasses['error'] : $variantClasses['default']
    ];

    // Handle various option formats
    $normalizedOptions = [];
    foreach ($options as $key => $value) {
        if (is_array($value)) {
            $normalizedOptions[$key] = $value;
        } else {
            $normalizedOptions[$value] = ['label' => $value, 'value' => $value];
        }
    }
@endphp

<div class="hdt-form-group" 
     x-data="{
        isOpen: false,
        selectedValue: '{{ old($name, $selected) }}',
        selectedLabel: '',
        init() {
            this.updateSelectedLabel();
        },
        updateSelectedLabel() {
            const options = @js($normalizedOptions);
            const option = Object.values(options).find(opt => opt.value === this.selectedValue);
            this.selectedLabel = option ? option.label : '';
        }
     }">
    
    {{-- Label --}}
    @if($label)
        <label for="{{ $selectId }}" 
               id="{{ $labelId }}"
               class="block text-sm font-medium text-text-primary mb-2">
            {{ $label }}
            @if($required)
                <span class="text-hd-danger-600 ml-1" aria-label="required">*</span>
            @endif
        </label>
    @endif

    {{-- Select Field --}}
    <div class="relative">
        <select 
            id="{{ $selectId }}"
            name="{{ $name }}{{ $multiple ? '[]' : '' }}"
            class="{{ implode(' ', $selectClasses) }}"
            @if($multiple) multiple aria-multiselectable="true" @endif
            @if($required) required aria-required="true" @endif
            @if($disabled) disabled aria-disabled="true" @endif
            @if($error) aria-invalid="true" @endif
            @if($ariaDescribedBy) aria-describedby="{{ $ariaDescribedBy }}" @endif
            @if($label) aria-labelledby="{{ $labelId }}" @endif
            x-model="selectedValue"
            @change="updateSelectedLabel()"
            {{ $attributes->except(['class', 'id', 'name', 'multiple', 'required', 'disabled']) }}
        >
            @if($placeholder && !$multiple && !$required)
                <option value="" disabled @if(!old($name, $selected)) selected @endif>
                    {{ $placeholder }}
                </option>
            @endif

            @foreach($normalizedOptions as $optionKey => $option)
                @php
                    $value = $option['value'] ?? $optionKey;
                    $label = $option['label'] ?? $value;
                    $disabled = $option['disabled'] ?? false;
                    $isSelected = $multiple 
                        ? in_array($value, (array)old($name, $selected ?? []))
                        : $value == old($name, $selected);
                @endphp
                
                <option value="{{ $value }}"
                        @if($isSelected) selected @endif
                        @if($disabled) disabled @endif>
                    {{ $label }}
                </option>
            @endforeach
        </select>

        {{-- Custom dropdown arrow --}}
        <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
            <svg class="w-5 h-5 text-text-tertiary" 
                 fill="none" 
                 stroke="currentColor" 
                 viewBox="0 0 24 24" 
                 aria-hidden="true">
                <path stroke-linecap="round" 
                      stroke-linejoin="round" 
                      stroke-width="2" 
                      d="M19 9l-7 7-7-7"/>
            </svg>
        </div>
    </div>

    {{-- Help Text --}}
    @if($helpText)
        <div id="{{ $helpId }}" 
             class="mt-2 text-sm text-text-tertiary"
             role="note">
            {{ $helpText }}
        </div>
    @endif

    {{-- Error Message --}}
    @if($error)
        <div id="{{ $errorId }}" 
             class="mt-2 text-sm text-hd-danger-600"
             role="alert"
             aria-live="polite">
            <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ $error }}
        </div>
    @endif

    {{-- Laravel Validation Errors --}}
    @error($name)
        <div id="{{ $errorId }}" 
             class="mt-2 text-sm text-hd-danger-600"
             role="alert"
             aria-live="polite">
            <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ $message }}
        </div>
    @enderror
</div>

@pushOnce('styles')
<style>
/* Accessible form select styles */
.hdt-select__field {
    font-family: var(--hdt-font-family-sans);
    font-size: var(--hdt-font-size-base);
    line-height: var(--hdt-line-height-normal);
    min-height: 44px; /* WCAG minimum touch target size */
    padding-right: 40px; /* Space for dropdown arrow */
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 12px center;
    background-size: 16px;
}

.hdt-select__field:focus {
    box-shadow: 0 0 0 2px var(--hdt-color-focus-ring);
}

/* Multiple select styling */
.hdt-select__field[multiple] {
    min-height: 120px;
    background-image: none;
    padding-right: var(--hdt-spacing-4);
}

/* Option styling for multiple selects */
.hdt-select__field[multiple] option {
    padding: 8px 12px;
    border-radius: 4px;
    margin: 2px 0;
}

.hdt-select__field[multiple] option:checked {
    background-color: var(--hdt-color-primary-100);
    color: var(--hdt-color-primary-800);
}

/* High contrast mode support */
@media (prefers-contrast: high) {
    .hdt-select__field {
        border-width: 2px;
    }
    
    .hdt-select__field:focus {
        border-width: 3px;
    }
    
    .hdt-select__field[aria-invalid="true"] {
        border-width: 3px;
    }

    .hdt-select__field[multiple] option:checked {
        background-color: var(--hdt-color-primary-600);
        color: var(--hdt-color-surface-primary);
        font-weight: bold;
    }
}

/* Reduced motion support */
.hdt-reduced-motion .hdt-select__field {
    transition: none;
}

/* Focus visible for keyboard users */
.hdt-select__field:focus-visible {
    outline: 2px solid var(--hdt-color-focus-ring);
    outline-offset: 2px;
}

/* Error state styling */
.hdt-select__field[aria-invalid="true"] {
    border-color: var(--hdt-color-danger-600);
}

/* Dark mode arrow color adjustment */
.hdt-theme-dark .hdt-select__field {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%239ca3af' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
}

/* Disabled state */
.hdt-select__field:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.hdt-select__field:disabled + .absolute {
    opacity: 0.6;
}

/* Custom option group styling */
.hdt-select__field optgroup {
    font-weight: 600;
    color: var(--hdt-color-text-secondary);
    font-size: 0.875rem;
    padding: 8px 4px 4px 4px;
}

.hdt-select__field optgroup option {
    font-weight: normal;
    padding-left: 16px;
}

/* Screen reader improvements */
.hdt-select__field option:disabled {
    color: var(--hdt-color-text-quaternary);
    font-style: italic;
}

/* Loading state */
.hdt-select__field.loading {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24'%3e%3ccircle cx='12' cy='12' r='10' stroke='%236b7280' stroke-width='2'/%3e%3cpath fill='%236b7280' d='M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z'/%3e%3c/svg%3e");
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* Respect reduced motion for loading spinner */
.hdt-reduced-motion .hdt-select__field.loading {
    animation: none;
}
</style>
@endPushOnce