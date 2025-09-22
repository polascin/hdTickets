<!-- Toast Notifications Container -->
<div x-data="toastManager()" 
     x-init="init()"
     class="fixed inset-0 flex items-end justify-center px-4 py-6 pointer-events-none sm:items-start sm:justify-end z-50">
    
    <!-- Toast Stack -->
    <div class="w-full flex flex-col items-center space-y-4 sm:items-end">
        <template x-for="toast in toasts" :key="toast.id">
            <div x-show="toast.visible"
                 x-transition:enter="transform ease-out duration-300 transition"
                 x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
                 x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="max-w-sm w-full pointer-events-auto">
                
                <!-- Success Toast -->
                <div x-show="toast.type === 'success'"
                     class="rounded-lg bg-white dark:bg-gray-800 shadow-lg ring-1 ring-black ring-opacity-5 border-l-4 border-green-500">
                    <div class="p-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="ml-3 w-0 flex-1">
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="toast.title"></p>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400" x-text="toast.message"></p>
                            </div>
                            <div class="ml-4 flex-shrink-0 flex">
                                <button @click="removeToast(toast.id)"
                                        class="rounded-md inline-flex text-gray-400 hover:text-gray-500 dark:text-gray-500 dark:hover:text-gray-400">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Error Toast -->
                <div x-show="toast.type === 'error'"
                     class="rounded-lg bg-white dark:bg-gray-800 shadow-lg ring-1 ring-black ring-opacity-5 border-l-4 border-red-500">
                    <div class="p-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="ml-3 w-0 flex-1">
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="toast.title"></p>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400" x-text="toast.message"></p>
                            </div>
                            <div class="ml-4 flex-shrink-0 flex">
                                <button @click="removeToast(toast.id)"
                                        class="rounded-md inline-flex text-gray-400 hover:text-gray-500 dark:text-gray-500 dark:hover:text-gray-400">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Warning Toast -->
                <div x-show="toast.type === 'warning'"
                     class="rounded-lg bg-white dark:bg-gray-800 shadow-lg ring-1 ring-black ring-opacity-5 border-l-4 border-yellow-500">
                    <div class="p-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </div>
                            <div class="ml-3 w-0 flex-1">
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="toast.title"></p>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400" x-text="toast.message"></p>
                            </div>
                            <div class="ml-4 flex-shrink-0 flex">
                                <button @click="removeToast(toast.id)"
                                        class="rounded-md inline-flex text-gray-400 hover:text-gray-500 dark:text-gray-500 dark:hover:text-gray-400">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Info Toast -->
                <div x-show="toast.type === 'info'"
                     class="rounded-lg bg-white dark:bg-gray-800 shadow-lg ring-1 ring-black ring-opacity-5 border-l-4 border-blue-500">
                    <div class="p-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="ml-3 w-0 flex-1">
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="toast.title"></p>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400" x-text="toast.message"></p>
                            </div>
                            <div class="ml-4 flex-shrink-0 flex">
                                <button @click="removeToast(toast.id)"
                                        class="rounded-md inline-flex text-gray-400 hover:text-gray-500 dark:text-gray-500 dark:hover:text-gray-400">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Price Alert Toast (Special Type) -->
                <div x-show="toast.type === 'price_alert'"
                     class="rounded-lg bg-gradient-to-r from-green-50 to-blue-50 dark:from-green-900/20 dark:to-blue-900/20 shadow-lg ring-1 ring-black ring-opacity-5 border border-green-200 dark:border-green-800">
                    <div class="p-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                    <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4 19h6v-2H4v2zM4 14h16v-2H4v2zM4 9h16V7H4v2z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-3 flex-1">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="toast.title"></p>
                                    <span class="text-lg font-bold text-green-600 dark:text-green-400" x-text="toast.price"></span>
                                </div>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400" x-text="toast.event"></p>
                                <div class="mt-3 flex space-x-2">
                                    <button @click="viewTicket(toast.ticketId)"
                                            class="text-sm bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded-md transition-colors">
                                        View Tickets
                                    </button>
                                    <button @click="removeToast(toast.id)"
                                            class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                                        Dismiss
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Purchase Success Toast (Special Type) -->
                <div x-show="toast.type === 'purchase_success'"
                     class="rounded-lg bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 shadow-lg ring-1 ring-black ring-opacity-5 border border-green-200 dark:border-green-800">
                    <div class="p-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                    <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-3 flex-1">
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="toast.title"></p>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400" x-text="toast.message"></p>
                                <div class="mt-3 flex items-center space-x-4">
                                    <div class="text-sm">
                                        <span class="text-gray-500 dark:text-gray-400">Order:</span>
                                        <span class="font-medium text-gray-900 dark:text-gray-100" x-text="toast.orderId"></span>
                                    </div>
                                    <div class="text-sm">
                                        <span class="text-gray-500 dark:text-gray-400">Total:</span>
                                        <span class="font-medium text-green-600 dark:text-green-400" x-text="toast.total"></span>
                                    </div>
                                </div>
                                <div class="mt-3 flex space-x-2">
                                    <button @click="viewOrder(toast.orderId)"
                                            class="text-sm bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded-md transition-colors">
                                        View Order
                                    </button>
                                    <button @click="downloadTickets(toast.orderId)"
                                            class="text-sm bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded-md transition-colors">
                                        Download Tickets
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Progress Bar (for timed toasts) -->
                <div x-show="toast.duration && toast.duration > 0"
                     class="h-1 bg-gray-200 dark:bg-gray-700 rounded-b-lg overflow-hidden">
                    <div class="h-full bg-blue-500 transition-all ease-linear"
                         :style="`width: ${toast.progress}%; transition-duration: ${toast.updateInterval}ms`"></div>
                </div>
            </div>
        </template>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('toastManager', () => ({
        toasts: [],
        
        init() {
            // Listen for custom toast events
            this.$nextTick(() => {
                window.addEventListener('show-toast', (event) => {
                    this.showToast(event.detail);
                });
                
                // Listen for real-time events (WebSocket, SSE, etc.)
                this.initializeRealTimeListeners();
            });
        },
        
        showToast({
            type = 'info',
            title = '',
            message = '',
            duration = 5000,
            data = {}
        }) {
            const toast = {
                id: Date.now() + Math.random(),
                type,
                title,
                message,
                duration,
                visible: false,
                progress: 100,
                updateInterval: 100,
                ...data
            };
            
            this.toasts.push(toast);
            
            // Show toast with slight delay for animation
            setTimeout(() => {
                toast.visible = true;
            }, 50);
            
            // Auto remove after duration (if specified)
            if (duration > 0) {
                this.startProgressTimer(toast);
                setTimeout(() => {
                    this.removeToast(toast.id);
                }, duration);
            }
        },
        
        startProgressTimer(toast) {
            const totalDuration = toast.duration;
            const updateInterval = toast.updateInterval;
            let elapsed = 0;
            
            const timer = setInterval(() => {
                elapsed += updateInterval;
                toast.progress = Math.max(0, ((totalDuration - elapsed) / totalDuration) * 100);
                
                if (elapsed >= totalDuration) {
                    clearInterval(timer);
                }
            }, updateInterval);
        },
        
        removeToast(toastId) {
            const toastIndex = this.toasts.findIndex(toast => toast.id === toastId);
            if (toastIndex > -1) {
                this.toasts[toastIndex].visible = false;
                
                // Remove from array after animation completes
                setTimeout(() => {
                    this.toasts.splice(toastIndex, 1);
                }, 150);
            }
        },
        
        clearAllToasts() {
            this.toasts.forEach(toast => {
                toast.visible = false;
            });
            
            setTimeout(() => {
                this.toasts = [];
            }, 150);
        },
        
        initializeRealTimeListeners() {
            // Listen for price alerts (would be connected to WebSocket/Laravel Echo)
            window.addEventListener('price-alert-triggered', (event) => {
                this.showToast({
                    type: 'price_alert',
                    title: 'Price Alert Triggered! 🎯',
                    duration: 10000,
                    data: {
                        price: event.detail.price,
                        event: event.detail.eventTitle,
                        ticketId: event.detail.ticketId
                    }
                });
            });
            
            // Listen for purchase completions
            window.addEventListener('purchase-completed', (event) => {
                this.showToast({
                    type: 'purchase_success',
                    title: 'Purchase Successful! 🎉',
                    message: 'Your tickets have been confirmed and sent to your email.',
                    duration: 15000,
                    data: {
                        orderId: event.detail.orderId,
                        total: event.detail.total
                    }
                });
            });
            
            // Listen for system notifications
            window.addEventListener('system-notification', (event) => {
                this.showToast({
                    type: event.detail.type || 'info',
                    title: event.detail.title,
                    message: event.detail.message,
                    duration: event.detail.duration || 5000
                });
            });
        },
        
        viewTicket(ticketId) {
            window.location.href = `/tickets/${ticketId}`;
        },
        
        viewOrder(orderId) {
            window.location.href = `/orders/${orderId}`;
        },
        
        downloadTickets(orderId) {
            window.location.href = `/orders/${orderId}/tickets/download`;
        }
    }));
    
    // Global helper function to show toasts from anywhere
    window.showToast = function(options) {
        window.dispatchEvent(new CustomEvent('show-toast', {
            detail: options
        }));
    };
    
    // Convenience functions for different toast types
    window.showSuccessToast = function(title, message, duration = 5000) {
        window.showToast({
            type: 'success',
            title,
            message,
            duration
        });
    };
    
    window.showErrorToast = function(title, message, duration = 7000) {
        window.showToast({
            type: 'error',
            title,
            message,
            duration
        });
    };
    
    window.showWarningToast = function(title, message, duration = 6000) {
        window.showToast({
            type: 'warning',
            title,
            message,
            duration
        });
    };
    
    window.showInfoToast = function(title, message, duration = 5000) {
        window.showToast({
            type: 'info',
            title,
            message,
            duration
        });
    };
});
</script>

<!-- Example Usage:
To show a toast from JavaScript:
showSuccessToast('Alert Created', 'Your price alert has been set up successfully!');

To show a toast from PHP/Blade (add to session):
session()->flash('toast', [
    'type' => 'success',
    'title' => 'Alert Created',
    'message' => 'Your price alert has been set up successfully!'
]);

To trigger from Alpine component:
$dispatch('show-toast', {
    type: 'success',
    title: 'Alert Created',
    message: 'Your price alert has been set up successfully!'
});
-->