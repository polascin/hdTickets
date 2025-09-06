@extends('layouts.app')

@section('title', 'Accessibility Compliance Demo')

@section('content')
<div class="container mx-auto px-4 py-8" id="main-content">
    <div class="max-w-6xl mx-auto">
        {{-- Header --}}
        <header class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">Accessibility Compliance Demonstration</h1>
            <p class="text-xl text-gray-600 mb-6">WCAG 2.1 AA compliant features for inclusive sports ticket monitoring</p>
            
            {{-- Accessibility Status Panel --}}
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-8" role="region" aria-labelledby="a11y-status">
                <h2 id="a11y-status" class="text-lg font-semibold text-blue-900 mb-4">Current Accessibility Status</h2>
                <div x-data="accessibility" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
                    <div class="bg-white rounded p-3">
                        <strong>Keyboard User:</strong>
                        <span x-text="getStatus().keyboardUser ? 'Yes' : 'No'"></span>
                    </div>
                    <div class="bg-white rounded p-3">
                        <strong>High Contrast:</strong>
                        <span x-text="getStatus().highContrast ? 'Active' : 'Normal'"></span>
                    </div>
                    <div class="bg-white rounded p-3">
                        <strong>Reduced Motion:</strong>
                        <span x-text="getStatus().reducedMotion ? 'Enabled' : 'Disabled'"></span>
                    </div>
                    <div class="bg-white rounded p-3">
                        <strong>Focus Trap:</strong>
                        <span x-text="getStatus().focusTrapActive ? 'Active' : 'Inactive'"></span>
                    </div>
                </div>
                
                {{-- Live Demo Controls --}}
                <div class="mt-6 flex flex-wrap gap-4 justify-center">
                    <button x-data="accessibility" @click="announce('This is a test announcement', 'polite')" 
                        class="btn btn-primary">
                        Test Screen Reader Announcement
                    </button>
                    <button @click="window.a11y?.setupDebugMode()" class="btn btn-secondary">
                        Toggle Debug Mode
                    </button>
                    <button onclick="document.body.classList.toggle('high-contrast-mode')" class="btn btn-outline">
                        Toggle High Contrast
                    </button>
                </div>
            </div>
        </header>

        {{-- Navigation Shortcuts --}}
        <div class="bg-gray-50 rounded-lg p-6 mb-8" role="region" aria-labelledby="shortcuts">
            <h2 id="shortcuts" class="text-2xl font-bold mb-4">Keyboard Navigation Shortcuts</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="bg-white p-4 rounded border">
                    <h3 class="font-semibold mb-2">Skip Links</h3>
                    <p class="text-sm text-gray-600 mb-2">Use Tab key to reveal skip navigation links</p>
                    <kbd class="px-2 py-1 bg-gray-200 rounded text-xs">Tab</kbd>
                </div>
                <div class="bg-white p-4 rounded border">
                    <h3 class="font-semibold mb-2">Landmark Navigation</h3>
                    <p class="text-sm text-gray-600 mb-2">Navigate to landmarks quickly</p>
                    <div class="space-x-2">
                        <kbd class="px-2 py-1 bg-gray-200 rounded text-xs">Alt + 1</kbd>
                        <span class="text-xs text-gray-500">Main</span>
                    </div>
                    <div class="space-x-2 mt-1">
                        <kbd class="px-2 py-1 bg-gray-200 rounded text-xs">Alt + 2</kbd>
                        <span class="text-xs text-gray-500">Navigation</span>
                    </div>
                </div>
                <div class="bg-white p-4 rounded border">
                    <h3 class="font-semibold mb-2">Modal/Menu Controls</h3>
                    <p class="text-sm text-gray-600 mb-2">Close dialogs and menus</p>
                    <kbd class="px-2 py-1 bg-gray-200 rounded text-xs">Escape</kbd>
                </div>
            </div>
        </div>

        {{-- Interactive Components Demo --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            {{-- Tab Navigation Demo --}}
            <div class="bg-white rounded-lg shadow-md p-6" role="region" aria-labelledby="tabs-demo">
                <h2 id="tabs-demo" class="text-xl font-semibold mb-4">Keyboard Accessible Tabs</h2>
                <p class="text-sm text-gray-600 mb-4">Use arrow keys to navigate, Enter/Space to activate</p>
                
                <div role="tablist" aria-label="Demo tabs" class="flex border-b">
                    <button role="tab" aria-selected="true" aria-controls="tab1-panel" id="tab1" 
                        class="px-4 py-2 font-medium text-blue-600 border-b-2 border-blue-600">
                        Overview
                    </button>
                    <button role="tab" aria-selected="false" aria-controls="tab2-panel" id="tab2" 
                        class="px-4 py-2 font-medium text-gray-500 hover:text-gray-700">
                        Features
                    </button>
                    <button role="tab" aria-selected="false" aria-controls="tab3-panel" id="tab3" 
                        class="px-4 py-2 font-medium text-gray-500 hover:text-gray-700">
                        Testing
                    </button>
                </div>
                
                <div id="tab1-panel" role="tabpanel" tabindex="0" aria-labelledby="tab1" class="mt-4">
                    <h3 class="font-semibold mb-2">Accessibility Overview</h3>
                    <p class="text-sm text-gray-600">
                        This system provides comprehensive WCAG 2.1 AA compliance with features like 
                        skip links, keyboard navigation, screen reader support, and high contrast modes.
                    </p>
                </div>
                
                <div id="tab2-panel" role="tabpanel" tabindex="0" aria-labelledby="tab2" class="mt-4" hidden>
                    <h3 class="font-semibold mb-2">Key Features</h3>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li>• Focus management and visual indicators</li>
                        <li>• Screen reader announcements</li>
                        <li>• Keyboard-only navigation support</li>
                        <li>• Color contrast compliance</li>
                    </ul>
                </div>
                
                <div id="tab3-panel" role="tabpanel" tabindex="0" aria-labelledby="tab3" class="mt-4" hidden>
                    <h3 class="font-semibold mb-2">Testing Tools</h3>
                    <p class="text-sm text-gray-600">
                        Test with screen readers like NVDA, JAWS, or VoiceOver. 
                        Use browser dev tools to audit accessibility.
                    </p>
                </div>
            </div>

            {{-- Menu Navigation Demo --}}
            <div class="bg-white rounded-lg shadow-md p-6" role="region" aria-labelledby="menu-demo">
                <h2 id="menu-demo" class="text-xl font-semibold mb-4">Accessible Dropdown Menu</h2>
                <p class="text-sm text-gray-600 mb-4">Use arrow keys to navigate menu items</p>
                
                <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                    <button @click="open = !open" :aria-expanded="open" 
                        aria-haspopup="true" id="menu-button" 
                        class="inline-flex justify-center w-full rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Sports Categories
                        <svg class="-mr-1 ml-2 h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>

                    <div x-show="open" x-cloak
                        class="origin-top-right absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none"
                        role="menu" aria-orientation="vertical" aria-labelledby="menu-button">
                        <div class="py-1">
                            <a href="#" role="menuitem" class="text-gray-700 block px-4 py-2 text-sm hover:bg-gray-100">
                                Football
                            </a>
                            <a href="#" role="menuitem" class="text-gray-700 block px-4 py-2 text-sm hover:bg-gray-100">
                                Basketball
                            </a>
                            <a href="#" role="menuitem" class="text-gray-700 block px-4 py-2 text-sm hover:bg-gray-100">
                                Baseball
                            </a>
                            <a href="#" role="menuitem" class="text-gray-700 block px-4 py-2 text-sm hover:bg-gray-100">
                                Soccer
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal Demo --}}
        <div class="bg-white rounded-lg shadow-md p-6 mb-8" role="region" aria-labelledby="modal-demo">
            <h2 id="modal-demo" class="text-xl font-semibold mb-4">Accessible Modal Dialog</h2>
            <p class="text-sm text-gray-600 mb-4">Focus is trapped within modal, Escape key closes it</p>
            
            <button x-data="accessibility" @click="openModal('demo-modal')" 
                class="btn btn-primary">
                Open Demo Modal
            </button>
            
            {{-- Modal --}}
            <div id="demo-modal" class="fixed inset-0 z-50 hidden" aria-hidden="true">
                <div class="fixed inset-0 bg-black bg-opacity-50"></div>
                <div class="fixed inset-0 flex items-center justify-center p-4">
                    <div class="bg-white rounded-lg max-w-md w-full p-6" role="dialog" aria-modal="true" aria-labelledby="modal-title">
                        <div class="flex justify-between items-center mb-4">
                            <h3 id="modal-title" class="text-lg font-semibold">Accessible Modal Example</h3>
                            <button x-data="accessibility" @click="closeModal('demo-modal')" 
                                aria-label="Close modal" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                        
                        <p class="text-gray-600 mb-6">
                            This modal demonstrates proper focus trapping, ARIA attributes, 
                            and keyboard navigation. Try using Tab to navigate and Escape to close.
                        </p>
                        
                        <div class="flex gap-4">
                            <button class="btn btn-primary">Action Button</button>
                            <button x-data="accessibility" @click="closeModal('demo-modal')" 
                                class="btn btn-secondary">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Form Accessibility Demo --}}
        <div class="bg-white rounded-lg shadow-md p-6 mb-8" role="region" aria-labelledby="form-demo">
            <h2 id="form-demo" class="text-xl font-semibold mb-4">Accessible Form Elements</h2>
            
            <form class="space-y-6" novalidate>
                <fieldset class="border border-gray-300 rounded p-4">
                    <legend class="font-semibold px-2">Personal Information</legend>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div class="form-field">
                            <label for="first-name" class="form-label required-field">First Name</label>
                            <input type="text" id="first-name" name="first_name" required 
                                class="form-input" aria-describedby="first-name-help">
                            <div id="first-name-help" class="form-help-text">Enter your legal first name</div>
                        </div>
                        
                        <div class="form-field">
                            <label for="last-name" class="form-label required-field">Last Name</label>
                            <input type="text" id="last-name" name="last_name" required 
                                class="form-input">
                        </div>
                    </div>
                    
                    <div class="form-field mt-4">
                        <label for="email" class="form-label required-field">Email Address</label>
                        <input type="email" id="email" name="email" required 
                            class="form-input" aria-describedby="email-error">
                        <div id="email-error" class="error-message" role="alert" style="display: none;">
                            Please enter a valid email address
                        </div>
                    </div>
                </fieldset>
                
                <fieldset class="border border-gray-300 rounded p-4">
                    <legend class="font-semibold px-2">Preferences</legend>
                    
                    <div class="mt-4">
                        <div class="form-field">
                            <div class="form-label">Notification Preferences</div>
                            <div role="group" aria-labelledby="notification-prefs">
                                <div class="space-y-2 mt-2">
                                    <label class="flex items-center">
                                        <input type="radio" name="notifications" value="all" 
                                            class="mr-2" aria-describedby="notif-all-desc">
                                        <span>All notifications</span>
                                        <span id="notif-all-desc" class="sr-only">Receive all ticket alerts and updates</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio" name="notifications" value="important" 
                                            class="mr-2" aria-describedby="notif-important-desc">
                                        <span>Important only</span>
                                        <span id="notif-important-desc" class="sr-only">Receive only critical alerts</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio" name="notifications" value="none" 
                                            class="mr-2" aria-describedby="notif-none-desc">
                                        <span>No notifications</span>
                                        <span id="notif-none-desc" class="sr-only">Disable all notifications</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </fieldset>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        Save Preferences
                    </button>
                    <button type="reset" class="btn btn-secondary">
                        Reset Form
                    </button>
                </div>
            </form>
        </div>

        {{-- Data Table Demo --}}
        <div class="bg-white rounded-lg shadow-md p-6 mb-8" role="region" aria-labelledby="table-demo">
            <h2 id="table-demo" class="text-xl font-semibold mb-4">Accessible Data Table</h2>
            
            <table class="w-full border-collapse" role="table">
                <caption class="text-left font-semibold mb-4">
                    Recent Sports Event Tickets (Sample Data)
                </caption>
                <thead>
                    <tr>
                        <th scope="col" class="border border-gray-300 p-3 text-left bg-gray-50">Event</th>
                        <th scope="col" class="border border-gray-300 p-3 text-left bg-gray-50">Date</th>
                        <th scope="col" class="border border-gray-300 p-3 text-left bg-gray-50">Price</th>
                        <th scope="col" class="border border-gray-300 p-3 text-left bg-gray-50">Status</th>
                        <th scope="col" class="border border-gray-300 p-3 text-left bg-gray-50">
                            <span class="sr-only">Actions</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="border border-gray-300 p-3">Lakers vs Warriors</td>
                        <td class="border border-gray-300 p-3">Dec 15, 2024</td>
                        <td class="border border-gray-300 p-3">$125.00</td>
                        <td class="border border-gray-300 p-3">
                            <span class="status-success" role="status">Available</span>
                        </td>
                        <td class="border border-gray-300 p-3">
                            <button class="btn btn-sm btn-primary" aria-label="View Lakers vs Warriors ticket details">
                                View
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td class="border border-gray-300 p-3">Cowboys vs Giants</td>
                        <td class="border border-gray-300 p-3">Dec 20, 2024</td>
                        <td class="border border-gray-300 p-3">$89.50</td>
                        <td class="border border-gray-300 p-3">
                            <span class="status-warning" role="status">Limited</span>
                        </td>
                        <td class="border border-gray-300 p-3">
                            <button class="btn btn-sm btn-primary" aria-label="View Cowboys vs Giants ticket details">
                                View
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td class="border border-gray-300 p-3">Yankees vs Red Sox</td>
                        <td class="border border-gray-300 p-3">Apr 5, 2025</td>
                        <td class="border border-gray-300 p-3">$75.00</td>
                        <td class="border border-gray-300 p-3">
                            <span class="status-error" role="status">Sold Out</span>
                        </td>
                        <td class="border border-gray-300 p-3">
                            <button class="btn btn-sm btn-outline" disabled aria-label="Yankees vs Red Sox tickets sold out">
                                Sold Out
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Loading States Demo --}}
        <div class="bg-white rounded-lg shadow-md p-6 mb-8" role="region" aria-labelledby="loading-demo">
            <h2 id="loading-demo" class="text-xl font-semibold mb-4">Accessible Loading States</h2>
            
            <div class="space-y-4">
                <div>
                    <button onclick="showLoadingDemo(this)" class="btn btn-primary">
                        Trigger Loading State
                    </button>
                </div>
                
                <div id="loading-container" class="p-4 border border-gray-200 rounded">
                    <div class="flex items-center space-x-3">
                        <div class="loading-spinner" role="status" aria-label="Loading">
                            <span class="sr-only">Loading sports tickets...</span>
                        </div>
                        <span>Loading sports ticket data...</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Live Region Demo --}}
        <div class="bg-white rounded-lg shadow-md p-6 mb-8" role="region" aria-labelledby="live-region-demo">
            <h2 id="live-region-demo" class="text-xl font-semibold mb-4">Live Region Announcements</h2>
            <p class="text-sm text-gray-600 mb-4">These announcements are heard by screen readers</p>
            
            <div class="space-x-4 mb-4">
                <button x-data="accessibility" @click="announce('New ticket alert: Lakers vs Warriors tickets now available!', 'polite')" 
                    class="btn btn-primary">
                    Polite Announcement
                </button>
                <button x-data="accessibility" @click="announce('Important: Ticket sale ends in 5 minutes!', 'assertive')" 
                    class="btn btn-warning">
                    Urgent Announcement
                </button>
            </div>
            
            <div class="bg-gray-50 p-4 rounded">
                <h3 class="font-semibold mb-2">Recent Announcements:</h3>
                <div id="announcement-log" class="text-sm text-gray-600 space-y-1">
                    <div>System ready - accessibility features loaded</div>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <footer class="bg-gray-50 rounded-lg p-6 mt-12" id="footer" role="contentinfo">
            <div class="text-center">
                <h2 class="text-lg font-semibold mb-4">Accessibility Resources</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <h3 class="font-semibold mb-2">WCAG Guidelines</h3>
                        <p class="text-sm text-gray-600">
                            This system follows WCAG 2.1 AA standards for web accessibility.
                        </p>
                    </div>
                    <div>
                        <h3 class="font-semibold mb-2">Screen Readers</h3>
                        <p class="text-sm text-gray-600">
                            Compatible with NVDA, JAWS, VoiceOver, and other assistive technologies.
                        </p>
                    </div>
                    <div>
                        <h3 class="font-semibold mb-2">Feedback</h3>
                        <p class="text-sm text-gray-600">
                            Report accessibility issues to help us improve the system.
                        </p>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</div>

{{-- JavaScript for Demo Functionality --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab functionality
    const tabs = document.querySelectorAll('[role="tab"]');
    const panels = document.querySelectorAll('[role="tabpanel"]');
    
    tabs.forEach((tab, index) => {
        tab.addEventListener('click', () => activateTab(tab, index));
        tab.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowRight' || e.key === 'ArrowLeft') {
                e.preventDefault();
                const nextIndex = e.key === 'ArrowRight' ? 
                    (index + 1) % tabs.length : 
                    index === 0 ? tabs.length - 1 : index - 1;
                tabs[nextIndex].focus();
            }
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                activateTab(tab, index);
            }
        });
    });
    
    function activateTab(activeTab, activeIndex) {
        tabs.forEach((tab, index) => {
            const isActive = index === activeIndex;
            tab.setAttribute('aria-selected', isActive);
            tab.className = isActive ? 
                'px-4 py-2 font-medium text-blue-600 border-b-2 border-blue-600' :
                'px-4 py-2 font-medium text-gray-500 hover:text-gray-700';
            panels[index].hidden = !isActive;
        });
    }
    
    // Loading demo
    window.showLoadingDemo = function(button) {
        button.disabled = true;
        button.textContent = 'Loading...';
        
        if (window.a11y) {
            window.a11y.announce('Loading sports ticket data');
        }
        
        setTimeout(() => {
            button.disabled = false;
            button.textContent = 'Trigger Loading State';
            if (window.a11y) {
                window.a11y.announce('Sports ticket data loaded successfully');
            }
        }, 3000);
    };
    
    // Log announcements for demonstration
    if (window.a11y) {
        const originalAnnounce = window.a11y.announce.bind(window.a11y);
        window.a11y.announce = function(message, priority = 'polite') {
            originalAnnounce(message, priority);
            
            const log = document.getElementById('announcement-log');
            if (log) {
                const entry = document.createElement('div');
                entry.textContent = `[${priority.toUpperCase()}] ${message}`;
                entry.className = priority === 'assertive' ? 'font-semibold text-red-600' : '';
                log.appendChild(entry);
                log.scrollTop = log.scrollHeight;
            }
        };
    }
});
</script>

<style>
/* Demo-specific styles */
.btn {
    @apply px-4 py-2 rounded font-medium transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2;
}

.btn-primary {
    @apply bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500;
}

.btn-secondary {
    @apply bg-gray-600 text-white hover:bg-gray-700 focus:ring-gray-500;
}

.btn-warning {
    @apply bg-yellow-600 text-white hover:bg-yellow-700 focus:ring-yellow-500;
}

.btn-outline {
    @apply border border-gray-300 text-gray-700 hover:bg-gray-50 focus:ring-gray-500;
}

.btn-sm {
    @apply px-3 py-1 text-sm;
}

.btn:disabled {
    @apply opacity-50 cursor-not-allowed;
}

#demo-modal:not(.hidden) {
    display: flex !important;
}
</style>
@endsection
