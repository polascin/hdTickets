@extends('layouts.modern')
@section('title', 'UI/UX Improvements Showcase')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Page Header -->
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-primary mb-4">HD Tickets UI/UX Improvements</h1>
        <p class="text-lg text-secondary max-w-3xl mx-auto">
            Comprehensive enhancements for mobile-first design, theme management, and accessibility compliance
            in the HD Tickets Sports Events Entry Tickets Monitoring System.
        </p>
    </div>

    <!-- Status Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
        <div class="bg-primary p-6 rounded-lg shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-white text-lg font-semibold mb-2">Mobile Enhancement</h3>
                    <p class="text-blue-100 text-sm">Touch interactions, gestures, and responsive design</p>
                    <div class="mt-3">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            ✅ Implemented
                        </span>
                    </div>
                </div>
                <div class="text-white opacity-50">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-elevated border border-primary p-6 rounded-lg shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-primary text-lg font-semibold mb-2">Theme System</h3>
                    <p class="text-secondary text-sm">Dark/Light mode with smooth transitions</p>
                    <div class="mt-3">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            ✅ Implemented
                        </span>
                    </div>
                </div>
                <div class="text-primary opacity-50">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-elevated border border-secondary p-6 rounded-lg shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-primary text-lg font-semibold mb-2">Accessibility</h3>
                    <p class="text-secondary text-sm">WCAG AA compliance and keyboard navigation</p>
                    <div class="mt-3">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            ✅ Enhanced
                        </span>
                    </div>
                </div>
                <div class="text-secondary opacity-50">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Theme Demo Section -->
    <div class="bg-elevated rounded-lg shadow-lg p-8 mb-12" x-data="themeManager()">
        <h2 class="text-2xl font-bold mb-6">Theme System Demo</h2>
        
        <div class="grid md:grid-cols-2 gap-8">
            <div>
                <h3 class="text-lg font-semibold mb-4">Current Theme Settings</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-secondary">Selected Theme:</span>
                        <span class="font-medium" x-text="getThemeLabel()"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-secondary">Effective Theme:</span>
                        <span class="font-medium" x-text="effectiveTheme"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-secondary">System Preference:</span>
                        <span class="font-medium" x-text="window.matchMedia('(prefers-color-scheme: dark)').matches ? 'Dark' : 'Light'"></span>
                    </div>
                </div>
            </div>
            
            <div>
                <h3 class="text-lg font-semibold mb-4">Theme Controls</h3>
                <div class="space-y-3">
                    <button @click="setTheme('light')" 
                            :class="theme === 'light' ? 'bg-primary text-white' : 'bg-secondary text-primary'"
                            class="w-full py-2 px-4 rounded transition-colors">
                        Light Mode
                    </button>
                    <button @click="setTheme('dark')" 
                            :class="theme === 'dark' ? 'bg-primary text-white' : 'bg-secondary text-primary'"
                            class="w-full py-2 px-4 rounded transition-colors">
                        Dark Mode
                    </button>
                    <button @click="setTheme('auto')" 
                            :class="theme === 'auto' ? 'bg-primary text-white' : 'bg-secondary text-primary'"
                            class="w-full py-2 px-4 rounded transition-colors">
                        Auto (System)
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Features Demo -->
    <div class="bg-elevated rounded-lg shadow-lg p-8 mb-12">
        <h2 class="text-2xl font-bold mb-6">Mobile Enhancement Features</h2>
        
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Touch Feedback Demo -->
            <div class="border border-primary rounded-lg p-4" 
                 data-touch-label="Touch feedback demonstration">
                <h3 class="font-semibold mb-2">Touch Feedback</h3>
                <p class="text-sm text-secondary mb-3">Tap the button to see touch feedback in action</p>
                <button class="w-full bg-primary text-white py-2 px-4 rounded tap-target">
                    Tap Me!
                </button>
            </div>

            <!-- Swipe Gesture Demo -->
            <div class="border border-secondary rounded-lg p-4 swipe-container" 
                 data-swipeable="true">
                <h3 class="font-semibold mb-2">Swipe Gestures</h3>
                <p class="text-sm text-secondary mb-3">Swipe this card left or right on mobile</p>
                <div class="bg-gradient-to-r from-blue-500 to-purple-600 text-white p-3 rounded swipe-item">
                    Swipe me!
                </div>
            </div>

            <!-- Pull to Refresh Demo -->
            <div class="border border-warning-500 rounded-lg p-4 pull-to-refresh" 
                 data-pull-to-refresh="refreshDemo"
                 style="max-height: 150px; overflow-y: auto;">
                <h3 class="font-semibold mb-2">Pull to Refresh</h3>
                <p class="text-sm text-secondary mb-3">Pull down to refresh on mobile</p>
                <div class="space-y-2">
                    <div class="bg-tertiary p-2 rounded">Item 1</div>
                    <div class="bg-tertiary p-2 rounded">Item 2</div>
                    <div class="bg-tertiary p-2 rounded">Item 3</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Responsive Design Demo -->
    <div class="bg-elevated rounded-lg shadow-lg p-8 mb-12">
        <h2 class="text-2xl font-bold mb-6">Responsive Design</h2>
        
        <div class="mb-6">
            <h3 class="text-lg font-semibold mb-3">Responsive Grid System</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-primary p-4 rounded text-white text-center">
                    <div class="mobile-only">📱 Mobile Only</div>
                    <div class="desktop-only">🖥️ Desktop Only</div>
                    <div class="font-semibold">Responsive Item 1</div>
                </div>
                <div class="bg-sports-green p-4 rounded text-white text-center">
                    <div class="mobile-only">📱 Mobile Only</div>
                    <div class="desktop-only">🖥️ Desktop Only</div>
                    <div class="font-semibold">Responsive Item 2</div>
                </div>
                <div class="bg-sports-orange p-4 rounded text-white text-center">
                    <div class="mobile-only">📱 Mobile Only</div>
                    <div class="desktop-only">🖥️ Desktop Only</div>
                    <div class="font-semibold">Responsive Item 3</div>
                </div>
                <div class="bg-sports-purple p-4 rounded text-white text-center">
                    <div class="mobile-only">📱 Mobile Only</div>
                    <div class="desktop-only">🖥️ Desktop Only</div>
                    <div class="font-semibold">Responsive Item 4</div>
                </div>
            </div>
        </div>

        <!-- Mobile vs Desktop Indicators -->
        <div class="bg-tertiary p-4 rounded">
            <h4 class="font-semibold mb-2">Device Type Detection</h4>
            <div class="text-sm">
                <span class="mobile-only inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    📱 Mobile Device Detected
                </span>
                <span class="desktop-only inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    🖥️ Desktop Device Detected
                </span>
            </div>
        </div>
    </div>

    <!-- Form Enhancement Demo -->
    <div class="bg-elevated rounded-lg shadow-lg p-8 mb-12">
        <h2 class="text-2xl font-bold mb-6">Enhanced Form Elements</h2>
        
        <div class="grid md:grid-cols-2 gap-8">
            <!-- Traditional Forms -->
            <div>
                <h3 class="text-lg font-semibold mb-4">Traditional Forms</h3>
                <form class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-secondary mb-1">Email</label>
                        <input type="email" 
                               class="w-full px-3 py-2 border border-primary rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="your@email.com">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-secondary mb-1">Message</label>
                        <textarea class="w-full px-3 py-2 border border-primary rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                                  rows="3" 
                                  placeholder="Your message"></textarea>
                    </div>
                </form>
            </div>

            <!-- Floating Label Forms -->
            <div>
                <h3 class="text-lg font-semibold mb-4">Floating Label Forms</h3>
                <form class="space-y-6">
                    <div class="form-field-floating">
                        <input type="email" 
                               class="peer"
                               placeholder=" ">
                        <label>Email Address</label>
                    </div>
                    <div class="form-field-floating">
                        <textarea class="peer"
                                  rows="3" 
                                  placeholder=" "></textarea>
                        <label>Your Message</label>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Accessibility Features -->
    <div class="bg-elevated rounded-lg shadow-lg p-8 mb-12">
        <h2 class="text-2xl font-bold mb-6">Accessibility Features</h2>
        
        <div class="grid md:grid-cols-2 gap-8">
            <div>
                <h3 class="text-lg font-semibold mb-4">Keyboard Navigation</h3>
                <div class="space-y-2">
                    <button class="w-full text-left p-3 bg-tertiary rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Focusable Element 1
                    </button>
                    <button class="w-full text-left p-3 bg-tertiary rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Focusable Element 2
                    </button>
                    <button class="w-full text-left p-3 bg-tertiary rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Focusable Element 3
                    </button>
                </div>
                <p class="text-sm text-secondary mt-3">
                    Use Tab key to navigate between elements
                </p>
            </div>

            <div>
                <h3 class="text-lg font-semibold mb-4">Screen Reader Support</h3>
                <div class="space-y-2">
                    <button aria-label="Toggle favorite sports team" 
                            class="p-3 bg-tertiary rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <span aria-hidden="true">⭐</span>
                        <span class="sr-only">Add to favorites</span>
                        Favorite Team
                    </button>
                    <div role="alert" 
                         class="p-3 bg-green-100 border border-green-400 rounded text-green-700">
                        Success: Form submitted successfully
                    </div>
                </div>
                <p class="text-sm text-secondary mt-3">
                    Proper ARIA labels and screen reader support
                </p>
            </div>
        </div>
    </div>

    <!-- Performance Indicators -->
    <div class="bg-elevated rounded-lg shadow-lg p-8">
        <h2 class="text-2xl font-bold mb-6">Performance & Browser Support</h2>
        
        <div class="grid md:grid-cols-3 gap-6">
            <div class="text-center">
                <div class="bg-green-100 text-green-800 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-3">
                    <span class="text-2xl">⚡</span>
                </div>
                <h3 class="font-semibold mb-2">Fast Loading</h3>
                <p class="text-sm text-secondary">Optimized CSS and JS for quick load times</p>
            </div>
            
            <div class="text-center">
                <div class="bg-blue-100 text-blue-800 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-3">
                    <span class="text-2xl">📱</span>
                </div>
                <h3 class="font-semibold mb-2">Mobile First</h3>
                <p class="text-sm text-secondary">Designed and optimized for mobile devices</p>
            </div>
            
            <div class="text-center">
                <div class="bg-purple-100 text-purple-800 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-3">
                    <span class="text-2xl">♿</span>
                </div>
                <h3 class="font-semibold mb-2">Accessible</h3>
                <p class="text-sm text-secondary">WCAG AA compliant for all users</p>
            </div>
        </div>
    </div>
</div>

<script>
// Demo functions
function refreshDemo() {
    if (window.mobileTouchUtils) {
        window.mobileTouchUtils.showMobileNotification('Refresh triggered!', 'success', 2000);
    }
    console.log('Pull to refresh triggered');
}

// Add demo event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Add swipe event listeners
    document.addEventListener('swipe-complete', function(e) {
        if (window.mobileTouchUtils) {
            window.mobileTouchUtils.showMobileNotification(
                `Swiped ${e.detail.direction}!`, 
                'info', 
                2000
            );
        }
    });

    // Add long press listeners
    document.addEventListener('longpress', function(e) {
        if (e.detail.element.classList.contains('tap-target')) {
            if (window.mobileTouchUtils) {
                window.mobileTouchUtils.showMobileNotification('Long press detected!', 'warning', 2000);
            }
        }
    });
});
</script>
@endsection
