{{-- Keyboard Shortcuts Help Modal --}}
{{-- Comprehensive keyboard shortcuts documentation and help --}}

<div x-data="keyboardShortcutsHelp()" x-init="init()" class="keyboard-shortcuts-help">
    {{-- Help Modal --}}
    <div 
        x-show="showHelp"
        x-transition:enter="transition-all duration-300 ease-out"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-all duration-200 ease-in"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4"
        role="dialog"
        aria-labelledby="shortcuts-help-title"
        aria-describedby="shortcuts-help-desc"
        @keydown.escape="closeHelp()"
        @click.self="closeHelp()"
    >
        <div 
            class="bg-white rounded-xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden"
            x-transition:enter="transition-all duration-300 ease-out"
            x-transition:enter-start="opacity-0 scale-95 transform translate-y-4"
            x-transition:enter-end="opacity-100 scale-100 transform translate-y-0"
            x-transition:leave="transition-all duration-200 ease-in"
            x-transition:leave-start="opacity-100 scale-100 transform translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 transform translate-y-4"
        >
            {{-- Header --}}
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 id="shortcuts-help-title" class="text-2xl font-bold mb-2">
                            ⌨️ Keyboard Shortcuts
                        </h2>
                        <p id="shortcuts-help-desc" class="text-blue-100">
                            Learn keyboard shortcuts to navigate HD Tickets more efficiently
                        </p>
                    </div>
                    <button
                        @click="closeHelp()"
                        class="text-blue-100 hover:text-white p-2 rounded-lg hover:bg-blue-500 transition-colors focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50"
                        aria-label="Close keyboard shortcuts help"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            
            {{-- Content --}}
            <div class="flex h-[calc(90vh-140px)]">
                {{-- Categories Sidebar --}}
                <div class="w-64 bg-gray-50 border-r border-gray-200 overflow-y-auto">
                    <div class="p-4">
                        <h3 class="text-sm font-semibold text-gray-700 mb-3 uppercase tracking-wide">Categories</h3>
                        <nav class="space-y-1">
                            <template x-for="category in categories" :key="category.id">
                                <button
                                    @click="selectCategory(category.id)"
                                    :class="{
                                        'bg-blue-100 text-blue-700 border-blue-300': selectedCategory === category.id,
                                        'text-gray-700 hover:bg-gray-100': selectedCategory !== category.id
                                    }"
                                    class="w-full text-left px-3 py-2 rounded-lg border border-transparent transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500"
                                >
                                    <div class="flex items-center">
                                        <span class="mr-3" x-text="category.icon"></span>
                                        <span class="text-sm font-medium" x-text="category.name"></span>
                                        <span 
                                            x-show="category.shortcuts.length > 0"
                                            class="ml-auto text-xs bg-gray-200 text-gray-600 px-2 py-1 rounded-full"
                                            x-text="category.shortcuts.length"
                                        ></span>
                                    </div>
                                </button>
                            </template>
                        </nav>
                    </div>
                </div>
                
                {{-- Shortcuts Content --}}
                <div class="flex-1 overflow-y-auto">
                    <div class="p-6">
                        <template x-for="category in categories" :key="category.id">
                            <div x-show="selectedCategory === category.id">
                                <div class="flex items-center mb-6">
                                    <span class="text-2xl mr-3" x-text="category.icon"></span>
                                    <div>
                                        <h3 class="text-xl font-bold text-gray-900" x-text="category.name"></h3>
                                        <p class="text-gray-600" x-text="category.description"></p>
                                    </div>
                                </div>
                                
                                <div class="space-y-4">
                                    <template x-for="shortcut in category.shortcuts" :key="shortcut.keys">
                                        <div class="flex items-center justify-between py-3 px-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                            <div class="flex-1">
                                                <div class="font-medium text-gray-900" x-text="shortcut.description"></div>
                                                <div 
                                                    x-show="shortcut.note"
                                                    class="text-sm text-gray-600 mt-1"
                                                    x-text="shortcut.note"
                                                ></div>
                                            </div>
                                            <div class="flex items-center space-x-1 ml-4">
                                                <template x-for="key in shortcut.keys.split(' + ')" :key="key">
                                                    <kbd class="px-2 py-1 bg-white border border-gray-300 rounded text-sm font-mono text-gray-700 shadow-sm" x-text="key"></kbd>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                    
                                    <div x-show="category.shortcuts.length === 0" class="text-center py-8 text-gray-500">
                                        <p>No shortcuts available for this category.</p>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
            
            {{-- Footer --}}
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-600">
                        <span class="font-medium">Pro Tip:</span> 
                        Press <kbd class="px-2 py-1 bg-white border border-gray-300 rounded text-xs font-mono">Ctrl + /</kbd> 
                        anytime to open this help
                    </div>
                    <div class="flex items-center space-x-3">
                        <button
                            @click="printShortcuts()"
                            class="text-sm text-gray-600 hover:text-gray-900 flex items-center space-x-1"
                            title="Print shortcuts reference"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                            </svg>
                            <span>Print</span>
                        </button>
                        
                        <button
                            @click="closeHelp()"
                            class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                        >
                            Got it!
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Floating Help Button --}}
    <button
        x-show="!showHelp && showFloatingButton"
        @click="openHelp()"
        class="fixed bottom-20 right-6 bg-gray-700 text-white p-3 rounded-full shadow-lg hover:bg-gray-800 focus:outline-none focus:ring-4 focus:ring-gray-500 focus:ring-opacity-50 z-30 transition-all duration-200"
        aria-label="Show keyboard shortcuts help"
        data-tooltip="Keyboard Shortcuts (Ctrl + /)"
    >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16l2.879-2.879m0 0a3 3 0 104.243-4.242 3 3 0 00-4.243 4.242zM21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
    </button>
</div>

<style>
    /* Print styles for shortcuts reference */
    @media print {
        .keyboard-shortcuts-help {
            position: static !important;
            background: white !important;
        }
        
        .keyboard-shortcuts-help .bg-gradient-to-r {
            background: #2563eb !important;
            color: white !important;
        }
        
        .keyboard-shortcuts-help kbd {
            border: 1px solid #ccc !important;
            padding: 2px 4px !important;
            border-radius: 2px !important;
            font-size: 10px !important;
        }
        
        .keyboard-shortcuts-help .fixed {
            position: static !important;
        }
        
        .keyboard-shortcuts-help button:not(.print-visible) {
            display: none !important;
        }
    }
    
    /* Enhanced kbd styling */
    .keyboard-shortcuts-help kbd {
        font-family: 'SF Mono', Monaco, 'Cascadia Code', 'Roboto Mono', Consolas, 'Courier New', monospace;
        font-size: 0.75rem;
        font-weight: 600;
        min-width: 1.5rem;
        text-align: center;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    }
    
    /* Accessibility focus styles */
    .keyboard-shortcuts-help button:focus-visible {
        outline: 2px solid #2563eb;
        outline-offset: 2px;
    }
    
    /* Category selection animation */
    .keyboard-shortcuts-help .category-item {
        transform: translateX(-4px);
        transition: transform 0.2s ease;
    }
    
    .keyboard-shortcuts-help .category-item.selected {
        transform: translateX(0);
    }
</style>

<script>
function keyboardShortcutsHelp() {
    return {
        showHelp: false,
        showFloatingButton: true,
        selectedCategory: 'general',
        
        categories: [
            {
                id: 'general',
                name: 'General Navigation',
                icon: '🌐',
                description: 'Basic navigation and global shortcuts',
                shortcuts: [
                    {
                        keys: 'Ctrl + /',
                        description: 'Show this keyboard shortcuts help',
                        note: 'Works from anywhere in the application'
                    },
                    {
                        keys: 'Alt + A',
                        description: 'Open accessibility settings',
                        note: 'Configure accessibility preferences'
                    },
                    {
                        keys: 'Alt + S',
                        description: 'Skip to main content',
                        note: 'Useful for screen reader users'
                    },
                    {
                        keys: 'Alt + N',
                        description: 'Skip to main navigation',
                        note: 'Jump directly to the navigation menu'
                    },
                    {
                        keys: 'Escape',
                        description: 'Close modal, dropdown, or overlay',
                        note: 'Universal close shortcut'
                    },
                    {
                        keys: 'Tab',
                        description: 'Navigate forward through interactive elements',
                        note: 'Standard focus navigation'
                    },
                    {
                        keys: 'Shift + Tab',
                        description: 'Navigate backward through interactive elements',
                        note: 'Reverse focus navigation'
                    }
                ]
            },
            {
                id: 'search',
                name: 'Search & Discovery',
                icon: '🔍',
                description: 'Shortcuts for finding and filtering tickets',
                shortcuts: [
                    {
                        keys: 'Ctrl + K',
                        description: 'Open global search',
                        note: 'Quick access to search functionality'
                    },
                    {
                        keys: '/',
                        description: 'Focus search input',
                        note: 'When search is visible on page'
                    },
                    {
                        keys: 'F',
                        description: 'Open filters panel',
                        note: 'On ticket discovery pages'
                    },
                    {
                        keys: 'Ctrl + F',
                        description: 'Find in page',
                        note: 'Browser find functionality'
                    },
                    {
                        keys: 'Enter',
                        description: 'Submit search or apply filters',
                        note: 'When focused on search input'
                    },
                    {
                        keys: 'Escape',
                        description: 'Clear search or close filters',
                        note: 'Reset search state'
                    }
                ]
            },
            {
                id: 'navigation',
                name: 'Page Navigation',
                icon: '🧭',
                description: 'Moving between pages and sections',
                shortcuts: [
                    {
                        keys: 'G + H',
                        description: 'Go to homepage',
                        note: 'Quick navigation shortcut'
                    },
                    {
                        keys: 'G + D',
                        description: 'Go to dashboard',
                        note: 'Return to your dashboard'
                    },
                    {
                        keys: 'G + T',
                        description: 'Go to ticket discovery',
                        note: 'Browse available tickets'
                    },
                    {
                        keys: 'G + A',
                        description: 'Go to alerts',
                        note: 'Manage your price alerts'
                    },
                    {
                        keys: 'G + O',
                        description: 'Go to orders',
                        note: 'View your purchase history'
                    },
                    {
                        keys: 'G + P',
                        description: 'Go to profile',
                        note: 'Access account settings'
                    }
                ]
            },
            {
                id: 'tickets',
                name: 'Ticket Management',
                icon: '🎫',
                description: 'Working with tickets and alerts',
                shortcuts: [
                    {
                        keys: 'C',
                        description: 'Create new alert',
                        note: 'On ticket detail pages'
                    },
                    {
                        keys: 'F',
                        description: 'Add to favorites',
                        note: 'Mark ticket as favorite'
                    },
                    {
                        keys: 'S',
                        description: 'Share ticket',
                        note: 'Open sharing options'
                    },
                    {
                        keys: 'B',
                        description: 'Buy now',
                        note: 'Start purchase process'
                    },
                    {
                        keys: 'Enter',
                        description: 'View ticket details',
                        note: 'When ticket card is focused'
                    },
                    {
                        keys: 'Space',
                        description: 'Select/deselect ticket for comparison',
                        note: 'Toggle comparison checkbox'
                    }
                ]
            },
            {
                id: 'dashboard',
                name: 'Dashboard Controls',
                icon: '📊',
                description: 'Dashboard and widget management',
                shortcuts: [
                    {
                        keys: 'Ctrl + R',
                        description: 'Refresh dashboard',
                        note: 'Update all widgets with latest data'
                    },
                    {
                        keys: 'C',
                        description: 'Customize dashboard',
                        note: 'Open dashboard customization panel'
                    },
                    {
                        keys: 'W',
                        description: 'Widget mode toggle',
                        note: 'Enter/exit widget arrangement mode'
                    },
                    {
                        keys: 'E',
                        description: 'Export dashboard data',
                        note: 'Download dashboard data as CSV'
                    },
                    {
                        keys: '1-9',
                        description: 'Focus widget by number',
                        note: 'Jump to specific widget'
                    }
                ]
            },
            {
                id: 'forms',
                name: 'Forms & Input',
                icon: '📝',
                description: 'Form navigation and submission',
                shortcuts: [
                    {
                        keys: 'Ctrl + Enter',
                        description: 'Submit form',
                        note: 'Quick form submission'
                    },
                    {
                        keys: 'Ctrl + S',
                        description: 'Save draft',
                        note: 'Save form progress without submitting'
                    },
                    {
                        keys: 'Tab',
                        description: 'Next form field',
                        note: 'Move to next input'
                    },
                    {
                        keys: 'Shift + Tab',
                        description: 'Previous form field',
                        note: 'Move to previous input'
                    },
                    {
                        keys: 'Escape',
                        description: 'Cancel form changes',
                        note: 'Discard unsaved changes'
                    },
                    {
                        keys: 'Ctrl + Z',
                        description: 'Undo last change',
                        note: 'In supported form fields'
                    }
                ]
            },
            {
                id: 'accessibility',
                name: 'Accessibility',
                icon: '♿',
                description: 'Accessibility and screen reader shortcuts',
                shortcuts: [
                    {
                        keys: 'Alt + A',
                        description: 'Open accessibility settings',
                        note: 'Configure visual and interaction preferences'
                    },
                    {
                        keys: 'Alt + H',
                        description: 'Toggle high contrast mode',
                        note: 'Improve visual contrast'
                    },
                    {
                        keys: 'Alt + R',
                        description: 'Toggle reduced motion',
                        note: 'Minimize animations and transitions'
                    },
                    {
                        keys: 'Alt + F',
                        description: 'Increase font size',
                        note: 'Make text larger'
                    },
                    {
                        keys: 'Alt + Shift + F',
                        description: 'Decrease font size',
                        note: 'Make text smaller'
                    },
                    {
                        keys: 'H',
                        description: 'Next heading',
                        note: 'Screen reader navigation (when supported)'
                    },
                    {
                        keys: 'Shift + H',
                        description: 'Previous heading',
                        note: 'Screen reader navigation (when supported)'
                    }
                ]
            },
            {
                id: 'admin',
                name: 'Admin Functions',
                icon: '⚙️',
                description: 'Administrative and power user shortcuts',
                shortcuts: [
                    {
                        keys: 'Ctrl + Shift + D',
                        description: 'Open developer tools',
                        note: 'Debug and inspect elements'
                    },
                    {
                        keys: 'Ctrl + Shift + R',
                        description: 'Force refresh',
                        note: 'Bypass cache and reload'
                    },
                    {
                        keys: 'Ctrl + Shift + I',
                        description: 'Import data',
                        note: 'Open data import dialog'
                    },
                    {
                        keys: 'Ctrl + E',
                        description: 'Export data',
                        note: 'Download data in various formats'
                    },
                    {
                        keys: 'Ctrl + U',
                        description: 'User management',
                        note: 'Quick access to user admin'
                    },
                    {
                        keys: 'Ctrl + L',
                        description: 'View logs',
                        note: 'Open system logs'
                    }
                ]
            }
        ],
        
        init() {
            this.setupGlobalShortcuts();
            this.loadUserPreferences();
            
            // Listen for show shortcuts event
            window.addEventListener('show-shortcuts', () => {
                this.openHelp();
            });
            
            console.log('[Shortcuts] Keyboard shortcuts help initialized');
        },
        
        setupGlobalShortcuts() {
            document.addEventListener('keydown', (e) => {
                // Ctrl + / or Cmd + / to show help
                if ((e.ctrlKey || e.metaKey) && e.key === '/') {
                    e.preventDefault();
                    this.openHelp();
                    return;
                }
                
                // Don't handle shortcuts if user is typing in an input
                if (this.isTypingInInput(e.target)) {
                    return;
                }
                
                // Handle other global shortcuts
                this.handleGlobalShortcut(e);
            });
        },
        
        handleGlobalShortcut(e) {
            const key = e.key.toLowerCase();
            const hasModifier = e.ctrlKey || e.metaKey || e.altKey || e.shiftKey;
            
            // Navigation shortcuts (G + key combinations)
            if (key === 'g' && !hasModifier) {
                this.handleNavigationShortcut(e);
                return;
            }
            
            // Single key shortcuts (when no modifiers)
            if (!hasModifier) {
                this.handleSingleKeyShortcut(e, key);
            }
            
            // Modified key shortcuts
            if (hasModifier) {
                this.handleModifiedKeyShortcut(e);
            }
        },
        
        handleNavigationShortcut(e) {
            e.preventDefault();
            
            // Set up listener for next key press
            const navigationListener = (nextEvent) => {
                nextEvent.preventDefault();
                document.removeEventListener('keydown', navigationListener);
                
                const nextKey = nextEvent.key.toLowerCase();
                const routes = {
                    'h': '/',
                    'd': '/dashboard',
                    't': '/discover',
                    'a': '/alerts',
                    'o': '/orders',
                    'p': '/profile'
                };
                
                if (routes[nextKey]) {
                    window.location.href = routes[nextKey];
                }
            };
            
            document.addEventListener('keydown', navigationListener);
            
            // Remove listener after timeout
            setTimeout(() => {
                document.removeEventListener('keydown', navigationListener);
            }, 2000);
        },
        
        handleSingleKeyShortcut(e, key) {
            const currentPage = window.location.pathname;
            
            // Page-specific shortcuts
            if (currentPage.includes('/discover') || currentPage.includes('/tickets')) {
                switch (key) {
                    case 'f':
                        e.preventDefault();
                        this.triggerFilters();
                        break;
                    case '/':
                        e.preventDefault();
                        this.focusSearch();
                        break;
                }
            }
            
            if (currentPage.includes('/dashboard')) {
                switch (key) {
                    case 'c':
                        e.preventDefault();
                        this.triggerDashboardCustomization();
                        break;
                    case 'w':
                        e.preventDefault();
                        this.toggleWidgetMode();
                        break;
                }
            }
        },
        
        handleModifiedKeyShortcut(e) {
            // Ctrl/Cmd shortcuts
            if (e.ctrlKey || e.metaKey) {
                switch (e.key.toLowerCase()) {
                    case 'k':
                        e.preventDefault();
                        this.openGlobalSearch();
                        break;
                    case 'r':
                        if (window.location.pathname.includes('/dashboard')) {
                            e.preventDefault();
                            this.refreshDashboard();
                        }
                        break;
                }
            }
            
            // Alt shortcuts
            if (e.altKey) {
                switch (e.key.toLowerCase()) {
                    case 'a':
                        e.preventDefault();
                        this.openAccessibilitySettings();
                        break;
                    case 's':
                        e.preventDefault();
                        this.skipToMainContent();
                        break;
                    case 'n':
                        e.preventDefault();
                        this.skipToNavigation();
                        break;
                }
            }
        },
        
        isTypingInInput(element) {
            const inputTypes = ['INPUT', 'TEXTAREA', 'SELECT'];
            const editableElements = element.isContentEditable;
            
            return inputTypes.includes(element.tagName) || editableElements;
        },
        
        // Action methods
        triggerFilters() {
            const filtersButton = document.querySelector('[data-action="open-filters"], .filters-toggle');
            if (filtersButton) {
                filtersButton.click();
            }
        },
        
        focusSearch() {
            const searchInput = document.querySelector('input[type="search"], #search, [data-search]');
            if (searchInput) {
                searchInput.focus();
                searchInput.select();
            }
        },
        
        triggerDashboardCustomization() {
            const customizeButton = document.querySelector('[data-action="customize-dashboard"]');
            if (customizeButton) {
                customizeButton.click();
            } else {
                // Dispatch custom event
                window.dispatchEvent(new CustomEvent('dashboard-customize'));
            }
        },
        
        toggleWidgetMode() {
            window.dispatchEvent(new CustomEvent('widget-mode-toggle'));
        },
        
        openGlobalSearch() {
            const searchOverlay = document.querySelector('[data-search-overlay]');
            if (searchOverlay) {
                searchOverlay.classList.remove('hidden');
                const searchInput = searchOverlay.querySelector('input');
                if (searchInput) {
                    searchInput.focus();
                }
            }
        },
        
        refreshDashboard() {
            window.dispatchEvent(new CustomEvent('dashboard-refresh'));
        },
        
        openAccessibilitySettings() {
            window.dispatchEvent(new CustomEvent('accessibility-settings-open'));
        },
        
        skipToMainContent() {
            const mainContent = document.getElementById('main-content') || document.querySelector('main');
            if (mainContent) {
                mainContent.focus();
                mainContent.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        },
        
        skipToNavigation() {
            const navigation = document.getElementById('main-navigation') || document.querySelector('nav');
            if (navigation) {
                const firstLink = navigation.querySelector('a, button');
                if (firstLink) {
                    firstLink.focus();
                }
            }
        },
        
        // Modal methods
        openHelp() {
            this.showHelp = true;
            this.showFloatingButton = false;
            
            // Focus first category
            setTimeout(() => {
                const firstCategory = document.querySelector('.keyboard-shortcuts-help nav button');
                if (firstCategory) {
                    firstCategory.focus();
                }
            }, 100);
        },
        
        closeHelp() {
            this.showHelp = false;
            this.showFloatingButton = true;
        },
        
        selectCategory(categoryId) {
            this.selectedCategory = categoryId;
        },
        
        printShortcuts() {
            // Create print-friendly version
            const printWindow = window.open('', '_blank');
            const printContent = this.generatePrintContent();
            
            printWindow.document.write(printContent);
            printWindow.document.close();
            printWindow.print();
        },
        
        generatePrintContent() {
            let html = `
                <!DOCTYPE html>
                <html>
                <head>
                    <title>HD Tickets - Keyboard Shortcuts Reference</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; }
                        .header { text-align: center; margin-bottom: 30px; }
                        .category { margin-bottom: 30px; }
                        .category-title { font-size: 18px; font-weight: bold; margin-bottom: 15px; padding-bottom: 5px; border-bottom: 2px solid #ccc; }
                        .shortcut { display: flex; justify-content: space-between; margin-bottom: 8px; padding: 5px 0; }
                        .shortcut:nth-child(even) { background-color: #f5f5f5; }
                        .keys { font-family: monospace; font-weight: bold; }
                        kbd { border: 1px solid #ccc; padding: 2px 4px; border-radius: 2px; font-size: 11px; }
                        .note { font-style: italic; color: #666; font-size: 12px; }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <h1>🎫 HD Tickets - Keyboard Shortcuts</h1>
                        <p>Comprehensive keyboard shortcuts reference</p>
                    </div>
            `;
            
            this.categories.forEach(category => {
                html += `
                    <div class="category">
                        <div class="category-title">${category.icon} ${category.name}</div>
                `;
                
                category.shortcuts.forEach(shortcut => {
                    const keys = shortcut.keys.split(' + ').map(key => `<kbd>${key}</kbd>`).join(' + ');
                    html += `
                        <div class="shortcut">
                            <div>
                                <strong>${shortcut.description}</strong>
                                ${shortcut.note ? `<div class="note">${shortcut.note}</div>` : ''}
                            </div>
                            <div class="keys">${keys}</div>
                        </div>
                    `;
                });
                
                html += '</div>';
            });
            
            html += `
                </body>
                </html>
            `;
            
            return html;
        },
        
        loadUserPreferences() {
            const preferences = localStorage.getItem('hd_tickets_shortcuts_preferences');
            if (preferences) {
                const parsed = JSON.parse(preferences);
                this.showFloatingButton = parsed.showFloatingButton !== false;
            }
        },
        
        saveUserPreferences() {
            const preferences = {
                showFloatingButton: this.showFloatingButton
            };
            localStorage.setItem('hd_tickets_shortcuts_preferences', JSON.stringify(preferences));
        }
    };
}
</script>