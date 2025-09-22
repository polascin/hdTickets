@props([
    'size' => 'md',
    'variant' => 'default',
    'showLabel' => false,
    'quickToggle' => false
])

@php
$sizeClasses = [
    'sm' => 'w-8 h-8 text-sm',
    'md' => 'w-10 h-10 text-base',
    'lg' => 'w-12 h-12 text-lg'
];

$variantClasses = [
    'default' => 'bg-surface-secondary hover:bg-surface-tertiary border-border-primary',
    'ghost' => 'bg-transparent hover:bg-surface-tertiary border-transparent',
    'outline' => 'bg-transparent hover:bg-surface-secondary border-border-secondary'
];

$buttonClasses = implode(' ', [
    'hdt-button',
    'relative inline-flex items-center justify-center',
    'border rounded-lg transition-all duration-150',
    'focus:outline-none focus:ring-2 focus:ring-hd-primary-500 focus:ring-offset-2',
    'disabled:opacity-50 disabled:cursor-not-allowed',
    $sizeClasses[$size] ?? $sizeClasses['md'],
    $variantClasses[$variant] ?? $variantClasses['default']
]);
@endphp

<div x-data="themeSwitcher()" 
     @keydown="onKeydown($event)"
     @click.away="open = false"
     class="relative">

    @if($quickToggle)
        <!-- Quick Toggle Button -->
        <button 
            type="button"
            @click="toggleQuick()"
            class="{{ $buttonClasses }}"
            :aria-label="'Switch to ' + (effectiveTheme === 'dark' ? 'light' : 'dark') + ' theme'"
            title="Toggle theme (Ctrl+Shift+T)">
            
            <!-- Sun Icon (Light Theme) -->
            <svg x-show="effectiveTheme === 'light'" 
                 x-transition:enter="transition-opacity duration-150"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="w-5 h-5 text-yellow-500" 
                 fill="none" 
                 stroke="currentColor" 
                 viewBox="0 0 24 24">
                <path stroke-linecap="round" 
                      stroke-linejoin="round" 
                      stroke-width="2" 
                      d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            
            <!-- Moon Icon (Dark Theme) -->
            <svg x-show="effectiveTheme === 'dark'" 
                 x-transition:enter="transition-opacity duration-150"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="w-5 h-5 text-blue-400" 
                 fill="none" 
                 stroke="currentColor" 
                 viewBox="0 0 24 24">
                <path stroke-linecap="round" 
                      stroke-linejoin="round" 
                      stroke-width="2" 
                      d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
            </svg>

            @if($showLabel)
                <span class="ml-2 text-sm font-medium" x-text="effectiveTheme === 'dark' ? 'Dark' : 'Light'"></span>
            @endif
        </button>
    @else
        <!-- Full Theme Switcher with Dropdown -->
        <button 
            type="button"
            @click="open = !open"
            class="{{ $buttonClasses }}"
            :aria-expanded="open"
            aria-haspopup="listbox"
            :aria-label="'Current theme: ' + $store.theme.themeLabel + '. Click to change theme.'"
            title="Theme settings">
            
            <!-- Current Theme Icon -->
            <template x-for="theme in themes" :key="theme.value">
                <div x-show="currentTheme === theme.value" class="flex items-center">
                    <!-- Sun Icon -->
                    <svg x-show="theme.icon === 'sun'" 
                         class="w-5 h-5 text-yellow-500" 
                         fill="none" 
                         stroke="currentColor" 
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round" 
                              stroke-linejoin="round" 
                              stroke-width="2" 
                              d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    
                    <!-- Moon Icon -->
                    <svg x-show="theme.icon === 'moon'" 
                         class="w-5 h-5 text-blue-400" 
                         fill="none" 
                         stroke="currentColor" 
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round" 
                              stroke-linejoin="round" 
                              stroke-width="2" 
                              d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                    
                    <!-- Computer/Auto Icon -->
                    <svg x-show="theme.icon === 'computer-desktop'" 
                         class="w-5 h-5 text-hd-gray-600 dark:text-hd-gray-400" 
                         fill="none" 
                         stroke="currentColor" 
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round" 
                              stroke-linejoin="round" 
                              stroke-width="2" 
                              d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>

                    @if($showLabel)
                        <span class="ml-2 text-sm font-medium" x-text="theme.label"></span>
                    @endif
                </div>
            </template>

            <!-- Dropdown Arrow -->
            <svg class="w-4 h-4 ml-2 text-hd-gray-500 transition-transform duration-150"
                 :class="{ 'rotate-180': open }"
                 fill="none" 
                 stroke="currentColor" 
                 viewBox="0 0 24 24">
                <path stroke-linecap="round" 
                      stroke-linejoin="round" 
                      stroke-width="2" 
                      d="m19 9-7 7-7-7"/>
            </svg>
        </button>

        <!-- Dropdown Menu -->
        <div x-show="open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             @click.away="open = false"
             class="absolute right-0 top-full mt-2 w-48 bg-surface-secondary rounded-lg shadow-lg border border-border-primary py-1 z-50"
             role="listbox"
             :aria-label="'Theme options'">
            
            <div class="px-3 py-2 text-xs font-semibold text-text-tertiary uppercase tracking-wide border-b border-border-primary">
                Theme Preference
            </div>

            <template x-for="theme in themes" :key="theme.value">
                <button 
                    type="button"
                    @click="selectTheme(theme.value)"
                    class="w-full flex items-center px-3 py-2 text-sm hover:bg-surface-tertiary transition-colors duration-150"
                    :class="{ 
                        'bg-hd-primary-50 dark:bg-hd-primary-900/20 text-hd-primary-700 dark:text-hd-primary-300': currentTheme === theme.value,
                        'text-text-primary': currentTheme !== theme.value
                    }"
                    role="option"
                    :aria-selected="currentTheme === theme.value">
                    
                    <!-- Theme Icon -->
                    <div class="w-5 h-5 mr-3 flex-shrink-0">
                        <!-- Sun Icon -->
                        <svg x-show="theme.icon === 'sun'" 
                             class="w-full h-full text-yellow-500" 
                             fill="none" 
                             stroke="currentColor" 
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" 
                                  stroke-linejoin="round" 
                                  stroke-width="2" 
                                  d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        
                        <!-- Moon Icon -->
                        <svg x-show="theme.icon === 'moon'" 
                             class="w-full h-full text-blue-400" 
                             fill="none" 
                             stroke="currentColor" 
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" 
                                  stroke-linejoin="round" 
                                  stroke-width="2" 
                                  d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                        </svg>
                        
                        <!-- Computer/Auto Icon -->
                        <svg x-show="theme.icon === 'computer-desktop'" 
                             class="w-full h-full text-hd-gray-600 dark:text-hd-gray-400" 
                             fill="none" 
                             stroke="currentColor" 
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" 
                                  stroke-linejoin="round" 
                                  stroke-width="2" 
                                  d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>

                    <!-- Theme Label and Description -->
                    <div class="flex-1 text-left">
                        <div class="font-medium" x-text="theme.label"></div>
                        <div class="text-xs text-text-tertiary mt-0.5">
                            <span x-show="theme.value === 'light'">Always use light theme</span>
                            <span x-show="theme.value === 'dark'">Always use dark theme</span>
                            <span x-show="theme.value === 'auto'">Follow system preference</span>
                        </div>
                    </div>

                    <!-- Current Selection Indicator -->
                    <svg x-show="currentTheme === theme.value" 
                         class="w-4 h-4 text-hd-primary-600 dark:text-hd-primary-400 flex-shrink-0" 
                         fill="none" 
                         stroke="currentColor" 
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round" 
                              stroke-linejoin="round" 
                              stroke-width="2" 
                              d="M5 13l4 4L19 7"/>
                    </svg>
                </button>
            </template>

            <!-- Keyboard Shortcut Hint -->
            <div class="px-3 py-2 text-xs text-text-quaternary border-t border-border-primary mt-1">
                <kbd class="inline-block px-1.5 py-0.5 text-xs font-mono bg-surface-tertiary rounded border border-border-primary">Ctrl+Shift+T</kbd>
                to quick toggle
            </div>
        </div>
    @endif
</div>

@pushOnce('styles')
<style>
/* Theme switcher specific styles */
.hdt-theme-switcher {
    /* Custom styles for theme switcher */
}

/* Smooth theme transition for the dropdown */
[x-cloak] { 
    display: none !important; 
}

/* Ensure icons transition smoothly */
.theme-icon {
    transition: opacity var(--hdt-duration-150) var(--hdt-ease-in-out);
}

/* Keyboard shortcut styling */
kbd {
    font-family: var(--hdt-font-family-mono);
    font-size: 0.75rem;
    font-weight: 600;
    line-height: 1;
}
</style>
@endPushOnce