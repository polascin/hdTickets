{{-- Accessibility Manager Component --}}
{{-- Comprehensive accessibility features including keyboard navigation, ARIA labels, screen reader support --}}

<div x-data="accessibilityManager()" x-init="init()" class="accessibility-manager">
    {{-- Skip Navigation Links --}}
    <div class="skip-navigation sr-only-focusable">
        <a 
            href="#main-content" 
            class="skip-link bg-blue-600 text-white px-4 py-2 rounded-md font-medium focus:not-sr-only"
            @click="announceToScreenReader('Skipped to main content')"
        >
            Skip to main content
        </a>
        <a 
            href="#main-navigation" 
            class="skip-link bg-blue-600 text-white px-4 py-2 rounded-md font-medium focus:not-sr-only ml-2"
            @click="announceToScreenReader('Skipped to main navigation')"
        >
            Skip to navigation
        </a>
        <a 
            href="#search" 
            class="skip-link bg-blue-600 text-white px-4 py-2 rounded-md font-medium focus:not-sr-only ml-2"
            @click="announceToScreenReader('Skipped to search')"
        >
            Skip to search
        </a>
    </div>

    {{-- Accessibility Settings Panel --}}
    <div 
        x-show="showSettings"
        x-transition:enter="transition-all duration-300 ease-out"
        x-transition:enter-start="opacity-0 transform scale-95"
        x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="transition-all duration-200 ease-in"
        x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95"
        class="fixed top-4 right-4 bg-white rounded-lg shadow-2xl border border-gray-200 z-50 w-80"
        role="dialog"
        aria-labelledby="accessibility-settings-title"
        aria-describedby="accessibility-settings-desc"
        @keydown.escape="closeSettings()"
    >
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 id="accessibility-settings-title" class="text-lg font-semibold text-gray-900">
                    Accessibility Settings
                </h2>
                <button
                    @click="closeSettings()"
                    class="text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 rounded-md p-1"
                    aria-label="Close accessibility settings"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <p id="accessibility-settings-desc" class="text-sm text-gray-600 mb-6">
                Customize your accessibility preferences for a better browsing experience.
            </p>
            
            <div class="space-y-4">
                {{-- High Contrast Toggle --}}
                <div class="flex items-center justify-between">
                    <label for="high-contrast" class="text-sm font-medium text-gray-700">
                        High Contrast Mode
                    </label>
                    <button
                        id="high-contrast"
                        @click="toggleHighContrast()"
                        :class="{ 'bg-blue-600': highContrast, 'bg-gray-200': !highContrast }"
                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                        role="switch"
                        :aria-checked="highContrast"
                        aria-describedby="high-contrast-desc"
                    >
                        <span 
                            :class="{ 'translate-x-6': highContrast, 'translate-x-1': !highContrast }"
                            class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                        ></span>
                    </button>
                </div>
                <p id="high-contrast-desc" class="text-xs text-gray-500">
                    Increases color contrast for better visibility
                </p>
                
                {{-- Reduced Motion Toggle --}}
                <div class="flex items-center justify-between">
                    <label for="reduced-motion" class="text-sm font-medium text-gray-700">
                        Reduce Motion
                    </label>
                    <button
                        id="reduced-motion"
                        @click="toggleReducedMotion()"
                        :class="{ 'bg-blue-600': reducedMotion, 'bg-gray-200': !reducedMotion }"
                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                        role="switch"
                        :aria-checked="reducedMotion"
                        aria-describedby="reduced-motion-desc"
                    >
                        <span 
                            :class="{ 'translate-x-6': reducedMotion, 'translate-x-1': !reducedMotion }"
                            class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                        ></span>
                    </button>
                </div>
                <p id="reduced-motion-desc" class="text-xs text-gray-500">
                    Reduces animations and transitions
                </p>
                
                {{-- Font Size Adjustment --}}
                <div>
                    <label for="font-size" class="text-sm font-medium text-gray-700 block mb-2">
                        Font Size: <span x-text="fontSize + '%'"></span>
                    </label>
                    <input
                        id="font-size"
                        type="range"
                        min="75"
                        max="150"
                        step="25"
                        x-model="fontSize"
                        @input="adjustFontSize()"
                        class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500"
                        aria-describedby="font-size-desc"
                    >
                    <div class="flex justify-between text-xs text-gray-500 mt-1">
                        <span>75%</span>
                        <span>100%</span>
                        <span>125%</span>
                        <span>150%</span>
                    </div>
                </div>
                <p id="font-size-desc" class="text-xs text-gray-500">
                    Adjust text size for better readability
                </p>
                
                {{-- Screen Reader Announcements Toggle --}}
                <div class="flex items-center justify-between">
                    <label for="screen-reader-announcements" class="text-sm font-medium text-gray-700">
                        Screen Reader Announcements
                    </label>
                    <button
                        id="screen-reader-announcements"
                        @click="toggleScreenReaderAnnouncements()"
                        :class="{ 'bg-blue-600': screenReaderAnnouncements, 'bg-gray-200': !screenReaderAnnouncements }"
                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                        role="switch"
                        :aria-checked="screenReaderAnnouncements"
                        aria-describedby="screen-reader-desc"
                    >
                        <span 
                            :class="{ 'translate-x-6': screenReaderAnnouncements, 'translate-x-1': !screenReaderAnnouncements }"
                            class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                        ></span>
                    </button>
                </div>
                <p id="screen-reader-desc" class="text-xs text-gray-500">
                    Enable live announcements for screen readers
                </p>
            </div>
            
            <div class="mt-6 pt-4 border-t border-gray-200">
                <button
                    @click="resetAccessibilitySettings()"
                    class="w-full bg-gray-100 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-200 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    Reset to Defaults
                </button>
            </div>
        </div>
    </div>

    {{-- Accessibility Settings Toggle Button --}}
    <button
        @click="toggleSettings()"
        class="fixed bottom-6 left-6 bg-blue-600 text-white p-3 rounded-full shadow-lg hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500 focus:ring-opacity-50 z-40"
        aria-label="Open accessibility settings"
        data-tooltip="Accessibility Settings"
    >
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
        </svg>
    </button>

    {{-- Live Region for Screen Reader Announcements --}}
    <div
        id="live-region"
        aria-live="polite"
        aria-atomic="true"
        class="sr-only"
        x-text="liveMessage"
    ></div>

    {{-- Focus Trap Helper --}}
    <div x-show="focusTrapActive" x-ref="focusTrap" @keydown.tab="handleFocusTrap($event)"></div>
</div>

<style>
    /* Screen reader only classes */
    .sr-only {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border: 0;
    }
    
    .sr-only-focusable:focus,
    .sr-only-focusable:active {
        position: static;
        width: auto;
        height: auto;
        padding: inherit;
        margin: inherit;
        overflow: visible;
        clip: auto;
        white-space: normal;
    }
    
    .focus\:not-sr-only:focus {
        position: static;
        width: auto;
        height: auto;
        padding: inherit;
        margin: inherit;
        overflow: visible;
        clip: auto;
        white-space: normal;
    }
    
    /* Skip links */
    .skip-navigation {
        position: fixed;
        top: -100px;
        left: 1rem;
        z-index: 9999;
        transition: top 0.3s ease;
    }
    
    .skip-navigation:focus-within {
        top: 1rem;
    }
    
    .skip-link {
        text-decoration: none;
        transition: all 0.2s ease;
    }
    
    .skip-link:focus {
        outline: 2px solid currentColor;
        outline-offset: 2px;
    }
    
    /* High contrast mode */
    .high-contrast {
        filter: contrast(150%) saturate(200%);
    }
    
    .high-contrast * {
        text-shadow: none !important;
        box-shadow: none !important;
    }
    
    .high-contrast a {
        text-decoration: underline;
    }
    
    .high-contrast button,
    .high-contrast input,
    .high-contrast select,
    .high-contrast textarea {
        border: 2px solid currentColor !important;
    }
    
    /* Reduced motion */
    @media (prefers-reduced-motion: reduce) {
        .reduce-motion,
        .reduce-motion *,
        .reduce-motion *::before,
        .reduce-motion *::after {
            animation-duration: 0.01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: 0.01ms !important;
            scroll-behavior: auto !important;
        }
    }
    
    .reduced-motion,
    .reduced-motion *,
    .reduced-motion *::before,
    .reduced-motion *::after {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
        scroll-behavior: auto !important;
    }
    
    /* Focus indicators */
    .focus-ring:focus {
        outline: 2px solid #2563eb;
        outline-offset: 2px;
        border-radius: 0.375rem;
    }
    
    .focus-ring-inset:focus {
        outline: 2px solid #2563eb;
        outline-offset: -2px;
    }
    
    /* Enhanced focus for interactive elements */
    button:focus-visible,
    a:focus-visible,
    input:focus-visible,
    select:focus-visible,
    textarea:focus-visible {
        outline: 2px solid #2563eb;
        outline-offset: 2px;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
    }
    
    /* Font size adjustments */
    .font-size-75 { font-size: 0.75em; }
    .font-size-100 { font-size: 1em; }
    .font-size-125 { font-size: 1.25em; }
    .font-size-150 { font-size: 1.5em; }
    
    /* Keyboard navigation indicators */
    .keyboard-navigation {
        position: relative;
    }
    
    .keyboard-navigation::after {
        content: '';
        position: absolute;
        top: -2px;
        left: -2px;
        right: -2px;
        bottom: -2px;
        border: 2px solid #2563eb;
        border-radius: 0.5rem;
        opacity: 0;
        transition: opacity 0.2s ease;
        pointer-events: none;
    }
    
    .keyboard-navigation:focus::after {
        opacity: 1;
    }
    
    /* Color adjustments for better contrast */
    .high-contrast .text-gray-500 {
        color: #374151 !important;
    }
    
    .high-contrast .text-gray-400 {
        color: #4b5563 !important;
    }
    
    .high-contrast .bg-gray-50 {
        background-color: #f9fafb !important;
        border: 1px solid #d1d5db;
    }
    
    .high-contrast .bg-gray-100 {
        background-color: #f3f4f6 !important;
        border: 1px solid #9ca3af;
    }
</style>

<script>
function accessibilityManager() {
    return {
        // Settings state
        showSettings: false,
        highContrast: false,
        reducedMotion: false,
        fontSize: 100,
        screenReaderAnnouncements: true,
        
        // Focus management
        focusTrapActive: false,
        focusableElements: [],
        currentFocusIndex: 0,
        
        // Screen reader
        liveMessage: '',
        
        // Keyboard navigation
        keyboardNavigation: false,
        lastFocusedElement: null,
        
        init() {
            this.loadAccessibilitySettings();
            this.setupKeyboardNavigation();
            this.setupFocusManagement();
            this.enhanceFormAccessibility();
            this.addAriaLabels();
            this.setupLiveRegions();
            
            // Apply saved settings
            this.applyAccessibilitySettings();
            
            // Listen for system preference changes
            this.watchSystemPreferences();
            
            console.log('[A11y] Accessibility manager initialized');
        },
        
        loadAccessibilitySettings() {
            const saved = localStorage.getItem('hd_tickets_accessibility');
            if (saved) {
                const settings = JSON.parse(saved);
                this.highContrast = settings.highContrast || false;
                this.reducedMotion = settings.reducedMotion || false;
                this.fontSize = settings.fontSize || 100;
                this.screenReaderAnnouncements = settings.screenReaderAnnouncements !== false;
            }
        },
        
        saveAccessibilitySettings() {
            const settings = {
                highContrast: this.highContrast,
                reducedMotion: this.reducedMotion,
                fontSize: this.fontSize,
                screenReaderAnnouncements: this.screenReaderAnnouncements
            };
            localStorage.setItem('hd_tickets_accessibility', JSON.stringify(settings));
        },
        
        applyAccessibilitySettings() {
            // Apply high contrast
            if (this.highContrast) {
                document.documentElement.classList.add('high-contrast');
            }
            
            // Apply reduced motion
            if (this.reducedMotion) {
                document.documentElement.classList.add('reduced-motion');
            }
            
            // Apply font size
            document.documentElement.style.setProperty('--base-font-size', `${this.fontSize}%`);
            document.documentElement.style.fontSize = `${this.fontSize}%`;
        },
        
        setupKeyboardNavigation() {
            // Track keyboard usage
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Tab') {
                    this.keyboardNavigation = true;
                    document.body.classList.add('keyboard-navigation');
                }
            });
            
            document.addEventListener('mousedown', () => {
                this.keyboardNavigation = false;
                document.body.classList.remove('keyboard-navigation');
            });
            
            // Global keyboard shortcuts
            document.addEventListener('keydown', (e) => {
                // Alt + A: Open accessibility settings
                if (e.altKey && e.key === 'a') {
                    e.preventDefault();
                    this.toggleSettings();
                }
                
                // Alt + S: Skip to main content
                if (e.altKey && e.key === 's') {
                    e.preventDefault();
                    const mainContent = document.getElementById('main-content');
                    if (mainContent) {
                        mainContent.focus();
                        this.announceToScreenReader('Skipped to main content');
                    }
                }
                
                // Alt + N: Skip to navigation
                if (e.altKey && e.key === 'n') {
                    e.preventDefault();
                    const navigation = document.getElementById('main-navigation');
                    if (navigation) {
                        navigation.focus();
                        this.announceToScreenReader('Skipped to main navigation');
                    }
                }
                
                // Escape: Close overlays and return focus
                if (e.key === 'Escape') {
                    this.handleEscapeKey();
                }
            });
        },
        
        setupFocusManagement() {
            // Enhance focus visibility
            this.addFocusRings();
            
            // Monitor focus changes
            document.addEventListener('focusin', (e) => {
                this.lastFocusedElement = e.target;
                this.announceFocusChange(e.target);
            });
            
            // Trap focus in modals
            document.addEventListener('keydown', (e) => {
                if (this.focusTrapActive && e.key === 'Tab') {
                    this.handleFocusTrap(e);
                }
            });
        },
        
        addFocusRings() {
            // Add focus rings to interactive elements that don't have them
            const elements = document.querySelectorAll('button, a, input, select, textarea, [tabindex]:not([tabindex="-1"])');
            elements.forEach(el => {
                if (!el.classList.contains('focus-ring')) {
                    el.classList.add('focus-ring');
                }
            });
        },
        
        enhanceFormAccessibility() {
            // Add proper labels and descriptions to form fields
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                const inputs = form.querySelectorAll('input, select, textarea');
                inputs.forEach(input => {
                    this.enhanceFormField(input);
                });
            });
        },
        
        enhanceFormField(field) {
            const fieldContainer = field.closest('.form-field, .form-group, .input-group');
            if (!fieldContainer) return;
            
            // Add aria-describedby for error messages
            const errorMessage = fieldContainer.querySelector('.error-message, .field-error, .invalid-feedback');
            if (errorMessage && !errorMessage.id) {
                const errorId = `error-${field.name || field.id || Math.random().toString(36).substr(2, 9)}`;
                errorMessage.id = errorId;
                field.setAttribute('aria-describedby', errorId);
            }
            
            // Add aria-invalid for validation states
            if (fieldContainer.classList.contains('error') || fieldContainer.classList.contains('invalid')) {
                field.setAttribute('aria-invalid', 'true');
            }
            
            // Add aria-required for required fields
            if (field.required || field.classList.contains('required')) {
                field.setAttribute('aria-required', 'true');
            }
            
            // Add role and aria-label for custom inputs
            if (field.type === 'range') {
                field.setAttribute('role', 'slider');
                if (!field.getAttribute('aria-label') && !field.getAttribute('aria-labelledby')) {
                    const label = fieldContainer.querySelector('label');
                    if (label) {
                        field.setAttribute('aria-labelledby', label.id || this.generateId('label'));
                    }
                }
            }
        },
        
        addAriaLabels() {
            // Add missing ARIA labels to common UI elements
            
            // Navigation elements
            const navElements = document.querySelectorAll('nav:not([aria-label]):not([aria-labelledby])');
            navElements.forEach((nav, index) => {
                nav.setAttribute('aria-label', `Navigation ${index + 1}`);
            });
            
            // Button without accessible names
            const buttons = document.querySelectorAll('button:not([aria-label]):not([aria-labelledby])');
            buttons.forEach(button => {
                if (!button.textContent.trim() && !button.querySelector('span:not(.sr-only)')) {
                    // Button with only icons
                    const icon = button.querySelector('svg, i, .icon');
                    if (icon) {
                        button.setAttribute('aria-label', this.guessButtonPurpose(button));
                    }
                }
            });
            
            // Images without alt text
            const images = document.querySelectorAll('img:not([alt])');
            images.forEach(img => {
                img.setAttribute('alt', ''); // Decorative image
            });
            
            // Form controls without labels
            const unlabeledInputs = document.querySelectorAll('input:not([aria-label]):not([aria-labelledby]):not([id])');
            unlabeledInputs.forEach(input => {
                const placeholder = input.getAttribute('placeholder');
                if (placeholder) {
                    input.setAttribute('aria-label', placeholder);
                }
            });
        },
        
        setupLiveRegions() {
            // Create additional live regions if needed
            if (!document.getElementById('live-region-assertive')) {
                const assertiveRegion = document.createElement('div');
                assertiveRegion.id = 'live-region-assertive';
                assertiveRegion.setAttribute('aria-live', 'assertive');
                assertiveRegion.setAttribute('aria-atomic', 'true');
                assertiveRegion.className = 'sr-only';
                document.body.appendChild(assertiveRegion);
            }
            
            // Listen for toast notifications and announce them
            window.addEventListener('showtoast', (e) => {
                const { message, type } = e.detail;
                const priority = type === 'error' ? 'assertive' : 'polite';
                this.announceToScreenReader(message, priority);
            });
        },
        
        watchSystemPreferences() {
            // Watch for prefers-reduced-motion changes
            const motionQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
            motionQuery.addEventListener('change', (e) => {
                if (e.matches && !this.reducedMotion) {
                    this.reducedMotion = true;
                    this.applyReducedMotion();
                    this.announceToScreenReader('Reduced motion enabled due to system preference');
                }
            });
            
            // Watch for prefers-contrast changes
            const contrastQuery = window.matchMedia('(prefers-contrast: high)');
            contrastQuery.addEventListener('change', (e) => {
                if (e.matches && !this.highContrast) {
                    this.highContrast = true;
                    this.applyHighContrast();
                    this.announceToScreenReader('High contrast mode enabled due to system preference');
                }
            });
        },
        
        // Settings methods
        toggleSettings() {
            this.showSettings = !this.showSettings;
            
            if (this.showSettings) {
                this.focusTrapActive = true;
                this.announceToScreenReader('Accessibility settings opened');
                
                // Focus the first interactive element
                setTimeout(() => {
                    const firstButton = document.querySelector('#accessibility-settings button');
                    if (firstButton) {
                        firstButton.focus();
                    }
                }, 100);
            } else {
                this.focusTrapActive = false;
                this.announceToScreenReader('Accessibility settings closed');
            }
        },
        
        closeSettings() {
            this.showSettings = false;
            this.focusTrapActive = false;
            this.announceToScreenReader('Accessibility settings closed');
        },
        
        toggleHighContrast() {
            this.highContrast = !this.highContrast;
            this.applyHighContrast();
            this.saveAccessibilitySettings();
            
            const message = this.highContrast ? 'High contrast mode enabled' : 'High contrast mode disabled';
            this.announceToScreenReader(message);
        },
        
        applyHighContrast() {
            if (this.highContrast) {
                document.documentElement.classList.add('high-contrast');
            } else {
                document.documentElement.classList.remove('high-contrast');
            }
        },
        
        toggleReducedMotion() {
            this.reducedMotion = !this.reducedMotion;
            this.applyReducedMotion();
            this.saveAccessibilitySettings();
            
            const message = this.reducedMotion ? 'Reduced motion enabled' : 'Reduced motion disabled';
            this.announceToScreenReader(message);
        },
        
        applyReducedMotion() {
            if (this.reducedMotion) {
                document.documentElement.classList.add('reduced-motion');
            } else {
                document.documentElement.classList.remove('reduced-motion');
            }
        },
        
        adjustFontSize() {
            document.documentElement.style.fontSize = `${this.fontSize}%`;
            document.documentElement.style.setProperty('--base-font-size', `${this.fontSize}%`);
            this.saveAccessibilitySettings();
            
            this.announceToScreenReader(`Font size set to ${this.fontSize}%`);
        },
        
        toggleScreenReaderAnnouncements() {
            this.screenReaderAnnouncements = !this.screenReaderAnnouncements;
            this.saveAccessibilitySettings();
            
            const message = this.screenReaderAnnouncements ? 
                'Screen reader announcements enabled' : 
                'Screen reader announcements disabled';
            this.announceToScreenReader(message);
        },
        
        resetAccessibilitySettings() {
            this.highContrast = false;
            this.reducedMotion = false;
            this.fontSize = 100;
            this.screenReaderAnnouncements = true;
            
            this.applyAccessibilitySettings();
            this.saveAccessibilitySettings();
            
            // Remove all applied classes
            document.documentElement.classList.remove('high-contrast', 'reduced-motion');
            document.documentElement.style.fontSize = '';
            document.documentElement.style.removeProperty('--base-font-size');
            
            this.announceToScreenReader('Accessibility settings reset to defaults');
        },
        
        // Screen reader announcements
        announceToScreenReader(message, priority = 'polite') {
            if (!this.screenReaderAnnouncements) return;
            
            const regionId = priority === 'assertive' ? 'live-region-assertive' : 'live-region';
            const region = document.getElementById(regionId);
            
            if (region) {
                // Clear the region first
                region.textContent = '';
                
                // Add the message after a brief delay
                setTimeout(() => {
                    region.textContent = message;
                }, 100);
                
                // Clear the message after it's been announced
                setTimeout(() => {
                    region.textContent = '';
                }, 5000);
            } else {
                // Fallback to the component's live region
                this.liveMessage = '';
                setTimeout(() => {
                    this.liveMessage = message;
                }, 100);
                setTimeout(() => {
                    this.liveMessage = '';
                }, 5000);
            }
        },
        
        announceFocusChange(element) {
            if (!this.screenReaderAnnouncements || !this.keyboardNavigation) return;
            
            // Announce focus changes for important UI elements
            const announcements = {
                'button': 'Button',
                'link': 'Link',
                'heading': element.tagName.toLowerCase(),
                'input': this.getInputAnnouncement(element),
                'select': 'Select',
                'textarea': 'Text area'
            };
            
            const role = element.getAttribute('role') || element.tagName.toLowerCase();
            const announcement = announcements[role];
            
            if (announcement && element.textContent.trim()) {
                this.announceToScreenReader(`${announcement}: ${element.textContent.trim()}`);
            }
        },
        
        getInputAnnouncement(input) {
            const type = input.type || 'text';
            const announcements = {
                'text': 'Text input',
                'email': 'Email input',
                'password': 'Password input',
                'search': 'Search input',
                'tel': 'Phone input',
                'url': 'URL input',
                'number': 'Number input',
                'range': 'Slider',
                'checkbox': input.checked ? 'Checked checkbox' : 'Unchecked checkbox',
                'radio': input.checked ? 'Selected radio button' : 'Radio button'
            };
            
            return announcements[type] || 'Input';
        },
        
        // Focus management
        handleFocusTrap(event) {
            if (!this.showSettings) return;
            
            const focusableElements = this.getFocusableElements();
            const firstElement = focusableElements[0];
            const lastElement = focusableElements[focusableElements.length - 1];
            
            if (event.shiftKey) {
                if (document.activeElement === firstElement) {
                    event.preventDefault();
                    lastElement.focus();
                }
            } else {
                if (document.activeElement === lastElement) {
                    event.preventDefault();
                    firstElement.focus();
                }
            }
        },
        
        getFocusableElements() {
            const selector = 'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])';
            const settingsPanel = document.querySelector('[role="dialog"]');
            
            if (settingsPanel) {
                return Array.from(settingsPanel.querySelectorAll(selector))
                    .filter(el => !el.disabled && !el.hidden && el.offsetWidth > 0 && el.offsetHeight > 0);
            }
            
            return [];
        },
        
        handleEscapeKey() {
            if (this.showSettings) {
                this.closeSettings();
                return;
            }
            
            // Close any open overlays
            const overlays = document.querySelectorAll('[role="dialog"], .modal, .dropdown[aria-expanded="true"]');
            overlays.forEach(overlay => {
                if (overlay.style.display !== 'none' && !overlay.hidden) {
                    const closeButton = overlay.querySelector('[aria-label*="close"], .close, .modal-close');
                    if (closeButton) {
                        closeButton.click();
                    }
                }
            });
        },
        
        // Utility methods
        generateId(prefix = 'element') {
            return `${prefix}-${Math.random().toString(36).substr(2, 9)}`;
        },
        
        guessButtonPurpose(button) {
            // Try to guess button purpose from context
            const classes = Array.from(button.classList);
            const parent = button.parentElement;
            
            if (classes.includes('close') || classes.includes('dismiss')) return 'Close';
            if (classes.includes('menu') || classes.includes('hamburger')) return 'Menu';
            if (classes.includes('search')) return 'Search';
            if (classes.includes('submit')) return 'Submit';
            if (classes.includes('cancel')) return 'Cancel';
            if (classes.includes('edit')) return 'Edit';
            if (classes.includes('delete')) return 'Delete';
            if (classes.includes('save')) return 'Save';
            
            // Check parent context
            if (parent?.classList.contains('modal')) return 'Close dialog';
            if (parent?.classList.contains('notification')) return 'Close notification';
            
            return 'Button';
        },
        
        // Public API methods
        activateFocusTrap() {
            this.focusTrapActive = true;
        },
        
        deactivateFocusTrap() {
            this.focusTrapActive = false;
        },
        
        announce(message, priority = 'polite') {
            this.announceToScreenReader(message, priority);
        }
    };
}

// Initialize accessibility enhancements when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Auto-enhance common patterns
    
    // Add landmark roles
    const main = document.querySelector('main');
    if (main && !main.getAttribute('role')) {
        main.setAttribute('role', 'main');
        main.id = main.id || 'main-content';
    }
    
    // Add navigation landmarks
    const navs = document.querySelectorAll('nav');
    navs.forEach((nav, index) => {
        if (!nav.getAttribute('aria-label') && !nav.getAttribute('aria-labelledby')) {
            if (index === 0) {
                nav.setAttribute('aria-label', 'Main navigation');
                nav.id = nav.id || 'main-navigation';
            } else {
                nav.setAttribute('aria-label', `Navigation ${index + 1}`);
            }
        }
    });
    
    // Enhance tables
    const tables = document.querySelectorAll('table');
    tables.forEach(table => {
        if (!table.getAttribute('role')) {
            table.setAttribute('role', 'table');
        }
        
        // Add scope to header cells
        const headerCells = table.querySelectorAll('th');
        headerCells.forEach(th => {
            if (!th.getAttribute('scope')) {
                th.setAttribute('scope', 'col');
            }
        });
    });
    
    console.log('[A11y] DOM enhancements applied');
});
</script>