import './bootstrap';
import Alpine from 'alpinejs';
import { createApp } from 'vue';

// Import Alpine.js plugins
import focus from '@alpinejs/focus';
import persist from '@alpinejs/persist';
import collapse from '@alpinejs/collapse';
import intersect from '@alpinejs/intersect';

// Import core application module
import AppCore from './core/AppCore.js';

// Import utilities
import cssTimestamp from '@utils/cssTimestamp';
import { ChartJS } from '@utils/chartConfig';
import pwaManager from '@utils/pwaManager';
import websocketManager from '@utils/websocketManager';
import errorReporter from '@utils/errorReporting';

// Import performance optimizations
import '@utils/performanceMonitoring';
import '@utils/lazyImageLoader';

// Import WebSocket testing utility
import '@utils/websocketTest';

// Alpine.js component imports
import { dashboardManager } from './components/dashboardManager.js';
import { 
    formHandler, 
    tableManager, 
    searchFilter,
    confirmDialog,
    tooltip,
    dropdown,
    tabs,
    accordion
} from './alpine/components/index.js';

// Vue Components (lazy loaded)
const RealTimeMonitoringDashboard = () => import('@components/RealTimeMonitoringDashboard.vue');
const AnalyticsDashboard = () => import('@components/AnalyticsDashboard.vue');
const UserPreferencesPanel = () => import('@components/UserPreferencesPanel.vue');
const TicketDashboard = () => import('@components/TicketDashboard.vue');
const AdminDashboard = () => import('@components/admin/AdminDashboard.vue');

// Setup Alpine.js plugins
Alpine.plugin(focus);
Alpine.plugin(persist);
Alpine.plugin(collapse);
Alpine.plugin(intersect);

// Make Alpine available globally
window.Alpine = Alpine;

// Initialize application core
console.log('🚀 Initializing HD Tickets Application v2.0...');
console.log('📦 Loading core modules...');

// Module initialization tracking
const moduleStatus = {
    cssTimestamp: false,
    chartJS: false,
    pwaManager: false,
    responsiveUtils: false,
    websocketManager: false,
    appCore: false,
    alpine: false
};

// Initialize modules with error handling
try {
    console.log('✅ CSS Timestamp module loaded');
    moduleStatus.cssTimestamp = true;
    
    // Setup CSS timestamp watching in development
    if (import.meta.env.DEV) {
        cssTimestamp.watchCSS(['app.css', 'components.css'], (file, timestamp) => {
            console.log(`📄 CSS file updated: ${file} at ${new Date(timestamp).toLocaleTimeString()}`);
        });
    }
} catch (error) {
    console.error('❌ Failed to initialize CSS Timestamp module:', error);
}

try {
    console.log('✅ Chart.js module loaded');
    moduleStatus.chartJS = true;
} catch (error) {
    console.error('❌ Failed to initialize Chart.js module:', error);
}

try {
    console.log('✅ PWA Manager module loaded');
    moduleStatus.pwaManager = true;
} catch (error) {
    console.error('❌ Failed to initialize PWA Manager module:', error);
}

try {
    console.log('✅ Responsive Utils module loaded');
    moduleStatus.responsiveUtils = true;
} catch (error) {
    console.error('❌ Failed to initialize Responsive Utils module:', error);
}

try {
    console.log('✅ WebSocket Manager module loaded');
    moduleStatus.websocketManager = true;
} catch (error) {
    console.error('❌ Failed to initialize WebSocket Manager module:', error);
    // Create fallback websocketManager
    window.websocketManager = {
        subscribeToTicketUpdates: () => console.log('WebSocket fallback: subscribeToTicketUpdates'),
        subscribeToAnalytics: () => console.log('WebSocket fallback: subscribeToAnalytics'),
        subscribeToPlatformMonitoring: () => console.log('WebSocket fallback: subscribeToPlatformMonitoring'),
        on: () => console.log('WebSocket fallback: event listener'),
        getConnectionStatus: () => ({ isConnected: false, connectionType: 'fallback' })
    };
}

try {
    console.log('✅ App Core module loaded');
    moduleStatus.appCore = true;
} catch (error) {
    console.error('❌ Failed to initialize App Core module:', error);
}

// Register Alpine.js components with error handling
try {
    Alpine.data('dashboardManager', dashboardManager);
    Alpine.data('formHandler', formHandler);
    Alpine.data('tableManager', tableManager);
    Alpine.data('searchFilter', searchFilter);
    Alpine.data('confirmDialog', confirmDialog);
    Alpine.data('tooltip', tooltip);
    Alpine.data('dropdown', dropdown);
    Alpine.data('tabs', tabs);
    Alpine.data('accordion', accordion);
    console.log('✅ Alpine.js components registered');
    
    // Navigation dropdown component
    Alpine.data('navigationData', () => ({
        open: false,
        mobileMenuOpen: false,
        adminDropdownOpen: false,
        profileDropdownOpen: false,
        
        init() {
            // Close dropdowns when clicking outside
            document.addEventListener('click', (e) => {
                if (!this.$el.contains(e.target)) {
                    this.adminDropdownOpen = false;
                    this.profileDropdownOpen = false;
                }
            });
        },
        
        closeAll() {
            this.adminDropdownOpen = false;
            this.profileDropdownOpen = false;
            this.mobileMenuOpen = false;
        },
        
        toggleMobileMenu() {
            this.mobileMenuOpen = !this.mobileMenuOpen;
        },
        
        toggleAdminDropdown() {
            this.adminDropdownOpen = !this.adminDropdownOpen;
            this.profileDropdownOpen = false;
        },
        
        toggleProfileDropdown() {
            this.profileDropdownOpen = !this.profileDropdownOpen;
            this.adminDropdownOpen = false;
        }
    }));
    console.log('✅ Alpine.js navigation component registered');
    
    // Loading overlay component
    Alpine.data('loadingOverlay', () => ({
        loading: false,
        loadingMessage: 'Loading...',
        duration: 0,
        progress: null,
        canCancel: false,
        startTime: null,
        interval: null,
        
        init() {
            // Initialize loading state
            this.loading = false;
            this.stopLoading();
            
            // Listen for global loading events
            window.addEventListener('show-loading', (event) => {
                this.setLoading(event.detail || { show: true });
            });
            
            window.addEventListener('hide-loading', () => {
                this.stopLoading();
            });
            
            window.addEventListener('force-stop-loading', () => {
                console.log('🔧 Force stopping loading overlay');
                this.stopLoading();
            });
        },
        
        setLoading(options = {}) {
            if (typeof options === 'boolean') {
                options = { show: options };
            }
            
            this.loading = options.show || false;
            this.loadingMessage = options.message || 'Loading...';
            this.progress = options.progress || null;
            this.canCancel = options.canCancel || false;
            
            if (this.loading) {
                this.startTime = Date.now();
                this.duration = 0;
                this.interval = setInterval(() => {
                    this.duration = Math.floor((Date.now() - this.startTime) / 1000);
                }, 1000);
            } else {
                this.stopLoading();
            }
        },
        
        stopLoading() {
            this.loading = false;
            this.duration = 0;
            this.progress = null;
            if (this.interval) {
                clearInterval(this.interval);
                this.interval = null;
            }
        },
        
        cancelLoading() {
            this.stopLoading();
            window.dispatchEvent(new CustomEvent('loading-cancelled'));
            
            if (window.hdTicketsUtils?.notify) {
                window.hdTicketsUtils.notify('Loading cancelled', 'info');
            }
        },
        
        get loadingDuration() {
            return this.duration;
        },
        
        get progressPercentage() {
            return this.progress ? `${Math.min(100, Math.max(0, this.progress))}%` : null;
        }
    }));
    console.log('✅ Alpine.js loading overlay component registered');
    
    // Modal component for enhanced UI
    Alpine.data('modal', () => ({
        show: false,
        title: '',
        size: 'md',
        closable: true,
        
        init() {
            // Listen for modal events
            this.$watch('show', value => {
                if (value) {
                    document.body.style.overflow = 'hidden';
                } else {
                    document.body.style.overflow = '';
                }
            });
        },
        
        open(options = {}) {
            this.title = options.title || '';
            this.size = options.size || 'md';
            this.closable = options.closable !== false;
            this.show = true;
        },
        
        close() {
            if (this.closable) {
                this.show = false;
            }
        }
    }));
    
    // Notification/Toast component
    Alpine.data('notifications', () => ({
        notifications: [],
        
        init() {
            window.addEventListener('notify', (event) => {
                this.add(event.detail);
            });
        },
        
        add(notification) {
            const id = Date.now();
            const toast = {
                id,
                type: notification.type || 'info',
                title: notification.title || '',
                message: notification.message || '',
                duration: notification.duration || 5000,
                persistent: notification.persistent || false
            };
            
            this.notifications.push(toast);
            
            if (!toast.persistent) {
                setTimeout(() => this.remove(id), toast.duration);
            }
        },
        
        remove(id) {
            this.notifications = this.notifications.filter(n => n.id !== id);
        },
        
        clear() {
            this.notifications = [];
        }
    }));
    
    // Alpine.js global store for app state
    Alpine.store('app', {
        loading: false,
        darkMode: Alpine.$persist(localStorage.getItem('darkMode') === 'true'),
        sidebarOpen: Alpine.$persist(true),
        notifications: [],
        
        init() {
            // Initialize dark mode
            this.applyDarkMode();
        },
        
        toggleDarkMode() {
            this.darkMode = !this.darkMode;
            this.applyDarkMode();
        },
        
        applyDarkMode() {
            document.documentElement.classList.toggle('dark', this.darkMode);
        },
        
        toggleSidebar() {
            this.sidebarOpen = !this.sidebarOpen;
        },
        
        setLoading(state, options = {}) {
            this.loading = state;
            if (state) {
                window.dispatchEvent(new CustomEvent('show-loading', {
                    detail: { show: true, message: options.message || 'Loading...', ...options }
                }));
            } else {
                window.dispatchEvent(new CustomEvent('hide-loading'));
            }
        },
        
        notify(title, message, type = 'info', options = {}) {
            window.dispatchEvent(new CustomEvent('notify', {
                detail: { title, message, type, ...options }
            }));
        }
    });
    console.log('✅ Alpine.js global store registered');
} catch (error) {
    console.error('❌ Failed to register Alpine.js dashboard manager:', error);
}

// Initialize Alpine.js with error handling
try {
    Alpine.start();
    console.log('✅ Alpine.js started successfully');
    moduleStatus.alpine = true;
} catch (error) {
    console.error('❌ Failed to start Alpine.js:', error);
    // Fallback initialization
    setTimeout(() => {
        try {
            Alpine.start();
            console.log('✅ Alpine.js started on retry');
            moduleStatus.alpine = true;
        } catch (retryError) {
            console.error('❌ Alpine.js retry failed:', retryError);
        }
    }, 1000);
}

// Initialize AppCore and dependent functionality
(async () => {
    // Initialize AppCore early to ensure modules are available
    try {
        await AppCore.init({
            debugMode: window.location.hostname === 'localhost' || window.location.search.includes('debug=true')
        });
        console.log('✅ AppCore initialized successfully');
        moduleStatus.appCore = true;
    } catch (error) {
        console.error('❌ Failed to initialize AppCore:', error);
    }

    // Setup application event listeners with error handling
    try {
        AppCore.on('app:initialized', () => {
            console.log('✅ Application core initialized successfully');
            
            // Initialize WebSocket if available with fallback
            let wsManager = null;
            try {
                wsManager = AppCore.getModule('websocket') || window.websocketManager;
            } catch (error) {
                console.warn('WebSocket module not available:', error);
                wsManager = window.websocketManager; // Use fallback
            }
            
            if (wsManager && typeof wsManager.on === 'function') {
                wsManager.on('connected', () => {
                    console.log('🔗 WebSocket connected successfully');
                    // Subscribe to global ticket updates
                    if (typeof wsManager.subscribeToTicketUpdates === 'function') {
                        wsManager.subscribeToTicketUpdates((data) => {
                            AppCore.emit('ticket:updated', data);
                        });
                    }
                });
            } else {
                console.log('📡 WebSocket manager not available, using fallback mode');
            }
            
            // Show success message
            try {
                AppCore.showSuccessMessage('Application loaded successfully');
            } catch (error) {
                console.log('✅ Application loaded successfully (fallback message)');
            }
        });

        AppCore.on('app:error', (event) => {
            console.error('❌ Application error:', event.detail);
        });
    } catch (error) {
        console.error('❌ Failed to setup AppCore event listeners:', error);
    }

    // Log module status
    console.log('🎯 Module Status:', moduleStatus);
    console.log('🎯 Alpine.js loaded and initialized:', !!window.Alpine);
    console.log('🎯 WebSocket Manager available:', !!window.websocketManager);
    console.log('🎯 App Core available:', !!window.AppCore);
})();

// Vue 3 Composition API app factory with error handling
function createVueApp(rootComponent, props = {}) {
    try {
        const app = createApp(rootComponent, props);
        
        // Global properties available in all components with fallbacks
        try {
            app.config.globalProperties.$websocket = window.websocketManager || websocketManager || null;
            app.config.globalProperties.$responsive = AppCore.getModule('responsive') || window.responsiveUtils || null;
            app.config.globalProperties.$cssTimestamp = cssTimestamp || null;
            app.config.globalProperties.$charts = ChartJS || null;
        } catch (error) {
            console.error('❌ Failed to set Vue global properties:', error);
        }
        
        // Global error handler
        app.config.errorHandler = (err, instance, info) => {
            console.error('Vue error:', err, info);
            // You could send this to an error tracking service
            if (window.AppCore && typeof window.AppCore.emit === 'function') {
                window.AppCore.emit('vue:error', { error: err, info });
            }
        };
        
        return app;
    } catch (error) {
        console.error('❌ Failed to create Vue app:', error);
        throw error;
    }
}

// Initialize Vue components where needed with error handling
if (document.getElementById('realtime-monitoring-dashboard')) {
    try {
        const app = createVueApp({
            components: {
                RealTimeMonitoringDashboard
            },
            mounted() {
                // Subscribe to real-time updates for this dashboard with fallback
                try {
                    const wsManager = window.websocketManager || websocketManager;
                    if (wsManager && typeof wsManager.subscribeToAnalytics === 'function') {
                        wsManager.subscribeToAnalytics((data) => {
                            this.$emit('analytics-updated', data);
                        });
                    } else {
                        console.log('📡 WebSocket not available for real-time monitoring dashboard');
                    }
                } catch (error) {
                    console.error('❌ Failed to setup WebSocket for real-time monitoring:', error);
                }
            }
        });
        app.mount('#realtime-monitoring-dashboard');
        console.log('✅ Real-time monitoring dashboard mounted');
    } catch (error) {
        console.error('❌ Failed to mount real-time monitoring dashboard:', error);
    }
}

if (document.getElementById('analytics-dashboard')) {
    try {
        const app = createVueApp({
            components: {
                AnalyticsDashboard
            },
            mounted() {
                // Subscribe to analytics updates with fallback
                try {
                    const wsManager = window.websocketManager || websocketManager;
                    if (wsManager && typeof wsManager.subscribeToAnalytics === 'function') {
                        wsManager.subscribeToAnalytics((data) => {
                            this.$emit('analytics-data', data);
                        });
                    } else {
                        console.log('📡 WebSocket not available for analytics dashboard');
                    }
                } catch (error) {
                    console.error('❌ Failed to setup WebSocket for analytics dashboard:', error);
                }
            }
        });
        app.mount('#analytics-dashboard');
        console.log('✅ Analytics dashboard mounted');
    } catch (error) {
        console.error('❌ Failed to mount analytics dashboard:', error);
    }
}

if (document.getElementById('user-preferences-panel')) {
    try {
        const app = createVueApp({
            components: {
                UserPreferencesPanel
            }
        });
        app.mount('#user-preferences-panel');
        console.log('✅ User preferences panel mounted');
    } catch (error) {
        console.error('❌ Failed to mount user preferences panel:', error);
    }
}

if (document.getElementById('ticket-dashboard')) {
    try {
        const app = createVueApp({
            components: {
                TicketDashboard
            },
            mounted() {
                // Subscribe to ticket updates for this dashboard with fallback
                try {
                    const wsManager = window.websocketManager || websocketManager;
                    if (wsManager && typeof wsManager.subscribeToTicketUpdates === 'function') {
                        wsManager.subscribeToTicketUpdates((data) => {
                            this.$emit('ticket-updated', data);
                        });
                    } else {
                        console.log('📡 WebSocket not available for ticket dashboard');
                    }
                } catch (error) {
                    console.error('❌ Failed to setup WebSocket for ticket dashboard:', error);
                }
            }
        });
        app.mount('#ticket-dashboard');
        console.log('✅ Ticket dashboard mounted');
    } catch (error) {
        console.error('❌ Failed to mount ticket dashboard:', error);
    }
}

if (document.getElementById('admin-dashboard')) {
    try {
        const app = createVueApp({
            components: {
                AdminDashboard
            },
            mounted() {
                // Subscribe to platform monitoring updates with fallback
                try {
                    const wsManager = window.websocketManager || websocketManager;
                    if (wsManager && typeof wsManager.subscribeToPlatformMonitoring === 'function') {
                        wsManager.subscribeToPlatformMonitoring((data) => {
                            this.$emit('platform-status-updated', data);
                        });
                    } else {
                        console.log('📡 WebSocket not available for admin dashboard');
                    }
                } catch (error) {
                    console.error('❌ Failed to setup WebSocket for admin dashboard:', error);
                }
            }
        });
        app.mount('#admin-dashboard');
        console.log('✅ Admin dashboard mounted');
    } catch (error) {
        console.error('❌ Failed to mount admin dashboard:', error);
    }
}

// Global functions for ticket management
window.TicketManager = {
    updateStatus: async function(ticketId, status) {
        try {
            const response = await axios.patch(`/tickets/${ticketId}/status`, {
                status: status
            });
            
            if (response.status === 200) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Ticket status updated successfully',
                    timer: 2000,
                    showConfirmButton: false
                });
                location.reload();
            }
        } catch (error) {
            console.error('Error updating ticket status:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Failed to update ticket status'
            });
        }
    },

    updatePriority: async function(ticketId, priority) {
        try {
            const response = await axios.patch(`/tickets/${ticketId}/priority`, {
                priority: priority
            });
            
            if (response.status === 200) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Ticket priority updated successfully',
                    timer: 2000,
                    showConfirmButton: false
                });
                location.reload();
            }
        } catch (error) {
            console.error('Error updating ticket priority:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Failed to update ticket priority'
            });
        }
    },

    assignTicket: async function(ticketId, agentId) {
        try {
            const response = await axios.patch(`/tickets/${ticketId}/assign`, {
                assigned_to: agentId
            });
            
            if (response.status === 200) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Ticket assigned successfully',
                    timer: 2000,
                    showConfirmButton: false
                });
                location.reload();
            }
        } catch (error) {
            console.error('Error assigning ticket:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Failed to assign ticket'
            });
        }
    }
};

// Initialize basic DOM functionality
document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM loaded, Alpine should be available:', !!window.Alpine);
});

// Global utility functions
window.hdTicketsUtils = {
    // Show notification shortcut
    notify: (message, type = 'info', options = {}) => {
        if (window.hdTicketsFeedback) {
            return window.hdTicketsFeedback.showToast(message, type, options);
        }
    },
    
    // Show loading shortcut
    loading: (message = 'Loading...', options = {}) => {
        if (window.hdTicketsFeedback) {
            return window.hdTicketsFeedback.showLoading(message, options);
        }
    },
    
    // Hide loading shortcut
    stopLoading: () => {
        if (window.hdTicketsFeedback) {
            window.hdTicketsFeedback.hideLoading();
        }
    },
    
    // Get user preference
    getPref: (key, defaultValue = null) => {
        if (window.hdTicketsPrefs) {
            return window.hdTicketsPrefs.get(key, defaultValue);
        }
        return defaultValue;
    },
    
    // Set user preference
    setPref: (key, value) => {
        if (window.hdTicketsPrefs) {
            return window.hdTicketsPrefs.set(key, value);
        }
    },
    
    // Toggle theme
    toggleTheme: () => {
        if (window.hdTicketsTheme) {
            window.hdTicketsTheme.toggleTheme();
        }
    }
};

// Initialize real-time notifications if Echo is available
if (typeof window.Echo !== 'undefined' && window.Laravel.user) {
    window.Echo.private(`user.${window.Laravel.user.id}`)
        .notification((notification) => {
            // Use new UI feedback system if available, fallback to Swal
            if (window.hdTicketsFeedback) {
                window.hdTicketsFeedback.info(
                    notification.title || 'New Notification',
                    {
                        persistent: false,
                        duration: 5000
                    }
                );
            } else {
                // Fallback to original Swal implementation
                const toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 5000,
                    timerProgressBar: true,
                });

                toast.fire({
                    icon: 'info',
                    title: notification.title || 'New Notification',
                    text: notification.message || ''
                });
            }

            // Update notification counter
            const counter = document.getElementById('notification-counter');
            if (counter) {
                const currentCount = parseInt(counter.textContent) || 0;
                counter.textContent = currentCount + 1;
                counter.style.display = 'inline';
            }
        });
}
