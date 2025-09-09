{{-- PWA Install Badge Component --}}
<div 
    x-data="installBadge()" 
    x-show="showBadge" 
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 transform translate-y-2"
    x-transition:enter-end="opacity-100 transform translate-y-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 transform translate-y-0"
    x-transition:leave-end="opacity-0 transform translate-y-2"
    class="fixed bottom-4 left-4 right-4 md:left-auto md:right-4 md:w-80 z-40 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg shadow-xl"
    :class="{ 'mb-16 md:mb-4': $store.ui?.mobileNavVisible }"
>
    {{-- Main Install Badge Content --}}
    <div class="p-4">
        <div class="flex items-start space-x-3">
            {{-- App Icon --}}
            <div class="flex-shrink-0">
                <img src="/images/icons/icon-72x72.png" alt="HD Tickets" class="w-12 h-12 rounded-lg shadow-md">
            </div>
            
            {{-- Content --}}
            <div class="flex-1 min-w-0">
                <h3 class="font-semibold text-sm leading-5">Get HD Tickets App</h3>
                <p class="text-xs text-indigo-100 mt-1 leading-4" x-text="getMessage()"></p>
                
                {{-- Features List --}}
                <div class="flex items-center space-x-4 mt-2 text-xs text-indigo-200">
                    <div class="flex items-center">
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span>Offline</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 2L3 7v11a1 1 0 001 1h5v-6h2v6h5a1 1 0 001-1V7l-7-5z"></path>
                        </svg>
                        <span>Home screen</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"></path>
                        </svg>
                        <span>Alerts</span>
                    </div>
                </div>
            </div>
            
            {{-- Close Button --}}
            <button 
                @click="dismiss(true)" 
                class="flex-shrink-0 p-1 text-indigo-200 hover:text-white transition-colors"
                aria-label="Dismiss install prompt"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        {{-- Action Buttons --}}
        <div class="flex space-x-2 mt-3">
            <button 
                @click="install()" 
                class="flex-1 bg-white text-indigo-600 px-4 py-2 rounded-md text-sm font-medium hover:bg-indigo-50 transition-colors focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-indigo-600"
            >
                <span class="flex items-center justify-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Install App
                </span>
            </button>
            <button 
                @click="dismiss(false)" 
                class="px-4 py-2 text-indigo-100 text-sm font-medium hover:text-white transition-colors focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-indigo-600 rounded-md"
            >
                Later
            </button>
        </div>
    </div>
    
    {{-- Progress Indicator (shown during install) --}}
    <div x-show="installing" class="px-4 pb-4">
        <div class="bg-white/20 rounded-full h-1 overflow-hidden">
            <div class="bg-white h-full rounded-full animate-pulse" style="width: 60%"></div>
        </div>
        <p class="text-xs text-indigo-100 mt-1 text-center">Installing...</p>
    </div>
</div>

{{-- iOS Safari Install Instructions Modal --}}
<div 
    x-show="showIOSInstructions" 
    x-transition.opacity
    @click.away="showIOSInstructions = false"
    class="fixed inset-0 z-50 flex items-end justify-center bg-black/50 p-4"
>
    <div class="bg-white rounded-t-2xl w-full max-w-md p-6" @click.stop>
        <div class="text-center mb-4">
            <img src="/images/icons/icon-96x96.png" alt="HD Tickets" class="w-16 h-16 rounded-2xl shadow-lg mx-auto mb-3">
            <h3 class="text-lg font-semibold text-gray-900">Install HD Tickets</h3>
            <p class="text-sm text-gray-600 mt-1">Add to your iPhone home screen for the best experience</p>
        </div>
        
        <div class="space-y-4 text-sm">
            <div class="flex items-start space-x-3 p-3 bg-gray-50 rounded-lg">
                <div class="flex-shrink-0 w-6 h-6 bg-blue-500 text-white rounded-full flex items-center justify-center text-xs font-bold">1</div>
                <div>
                    <p class="font-medium text-gray-900">Tap the Share button</p>
                    <p class="text-gray-600 text-xs mt-1">Look for <svg class="inline w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M15 8a3 3 0 10-2.977-2.63l-4.94 2.47a3 3 0 100 4.319l4.94 2.47a3 3 0 10.895-1.789l-4.94-2.47a3.027 3.027 0 000-.74l4.94-2.47C13.456 7.68 14.19 8 15 8z"></path></svg> in Safari toolbar</p>
                </div>
            </div>
            
            <div class="flex items-start space-x-3 p-3 bg-gray-50 rounded-lg">
                <div class="flex-shrink-0 w-6 h-6 bg-blue-500 text-white rounded-full flex items-center justify-center text-xs font-bold">2</div>
                <div>
                    <p class="font-medium text-gray-900">Select "Add to Home Screen"</p>
                    <p class="text-gray-600 text-xs mt-1">Scroll down and tap the <svg class="inline w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path></svg> icon</p>
                </div>
            </div>
            
            <div class="flex items-start space-x-3 p-3 bg-gray-50 rounded-lg">
                <div class="flex-shrink-0 w-6 h-6 bg-blue-500 text-white rounded-full flex items-center justify-center text-xs font-bold">3</div>
                <div>
                    <p class="font-medium text-gray-900">Tap "Add"</p>
                    <p class="text-gray-600 text-xs mt-1">Confirm to add HD Tickets to your home screen</p>
                </div>
            </div>
        </div>
        
        <button @click="showIOSInstructions = false" class="w-full mt-6 bg-indigo-600 text-white py-3 rounded-lg font-medium hover:bg-indigo-700 transition-colors">
            Got it!
        </button>
    </div>
</div>

<script>
function installBadge() {
    return {
        showBadge: false,
        installing: false,
        showIOSInstructions: false,
        dismissed: false,
        installReason: 'general',
        
        init() {
            // Check if we should show the badge
            setTimeout(() => this.evaluateShowBadge(), 2000);
            
            // Listen for install prompt availability
            document.addEventListener('install:prompt-available', () => {
                if (!this.dismissed) {
                    this.showBadge = true;
                }
            });
            
            // Listen for high-value actions that should trigger badge
            document.addEventListener('ticket:purchased', () => this.handleHighValueAction('purchase'));
            document.addEventListener('alert:created', () => this.handleHighValueAction('alert'));
            document.addEventListener('watchlist:added', () => this.handleHighValueAction('watchlist'));
            
            // Hide badge if app gets installed
            document.addEventListener('lifecycle:installed', () => {
                this.showBadge = false;
            });
        },
        
        evaluateShowBadge() {
            // Don't show if already installed
            if (this.isAppInstalled()) {
                return;
            }
            
            // Don't show if recently dismissed
            if (this.isRecentlyDismissed()) {
                return;
            }
            
            // Check engagement criteria
            const engagement = this.getUserEngagement();
            
            if (engagement.visits >= 2 || engagement.highValueActions > 0) {
                this.installReason = engagement.highValueActions > 0 ? 'engagement' : 'visits';
                this.showBadge = true;
            }
        },
        
        handleHighValueAction(action) {
            if (!this.dismissed && !this.isAppInstalled()) {
                this.installReason = action;
                this.showBadge = true;
            }
        },
        
        isAppInstalled() {
            return localStorage.getItem('hd_tickets_installed') === 'true' ||
                   (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches) ||
                   window.navigator.standalone === true;
        },
        
        isRecentlyDismissed() {
            const lastDismissed = localStorage.getItem('hd_tickets_badge_dismissed');
            if (!lastDismissed) return false;
            
            const dismissedTime = parseInt(lastDismissed);
            const cooldownPeriod = 3 * 24 * 60 * 60 * 1000; // 3 days
            
            return (Date.now() - dismissedTime) < cooldownPeriod;
        },
        
        getUserEngagement() {
            try {
                const stats = JSON.parse(localStorage.getItem('hd_tickets_install_stats') || '{}');
                return {
                    visits: stats.visits || 0,
                    highValueActions: (stats.highValueActions?.purchase || 0) + 
                                    (stats.highValueActions?.alert || 0) + 
                                    (stats.highValueActions?.watchlist || 0)
                };
            } catch (error) {
                return { visits: 0, highValueActions: 0 };
            }
        },
        
        getMessage() {
            const messages = {
                purchase: 'Get faster checkout and instant purchase notifications!',
                alert: 'Never miss price drops with instant notifications!',
                watchlist: 'Quick access to your watchlist and real-time updates!',
                engagement: 'Get the full HD Tickets experience on your home screen!',
                visits: 'Install for faster access and offline browsing!',
                general: 'Add to home screen for instant access and notifications!'
            };
            
            return messages[this.installReason] || messages.general;
        },
        
        async install() {
            // Check if this is iOS Safari
            if (this.isIOSSafari()) {
                this.showIOSInstructions = true;
                return;
            }
            
            this.installing = true;
            
            try {
                // Trigger install via the install prompt manager
                document.dispatchEvent(new CustomEvent('install:request'));
                
                // Hide badge after attempting install
                setTimeout(() => {
                    this.showBadge = false;
                    this.installing = false;
                }, 2000);
                
            } catch (error) {
                console.error('Install failed:', error);
                this.installing = false;
                
                // Show instructions as fallback
                this.showIOSInstructions = true;
            }
        },
        
        dismiss(permanent = false) {
            this.showBadge = false;
            this.dismissed = true;
            
            // Store dismissal timestamp
            localStorage.setItem('hd_tickets_badge_dismissed', Date.now().toString());
            
            if (permanent) {
                localStorage.setItem('hd_tickets_badge_permanent_dismiss', 'true');
            }
            
            // Trigger dismissal event
            document.dispatchEvent(new CustomEvent('install:dismiss'));
        },
        
        isIOSSafari() {
            const ua = window.navigator.userAgent;
            const iOS = /iPad|iPhone|iPod/.test(ua) && !window.MSStream;
            const safari = /Safari/.test(ua) && !/Chrome/.test(ua) && !/CriOS/.test(ua);
            return iOS && safari;
        }
    }
}
</script>

<style>
/* Custom animations for install badge */
@keyframes slideUpIn {
    from {
        opacity: 0;
        transform: translateY(100%);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.install-badge-enter {
    animation: slideUpIn 0.3s ease-out;
}

/* Hide badge on very small screens to avoid cluttering */
@media (max-height: 500px) {
    .install-badge {
        display: none !important;
    }
}
</style>
