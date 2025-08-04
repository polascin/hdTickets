/**
 * Application Core Module
 * Central coordination for all application functionality
 */
class AppCore {
    constructor() {
        this.modules = new Map();
        this.config = {
            apiBaseUrl: '/api',
            wsUrl: null,
            debugMode: false,
            version: '2.0.0'
        };
        this.state = {
            authenticated: false,
            user: null,
            theme: 'light',
            locale: 'en'
        };
        this.eventBus = new EventTarget();
        this.isInitialized = false;
    }

    /**
     * Initialize the application
     */
    async init(config = {}) {
        if (this.isInitialized) {
            console.warn('AppCore already initialized');
            return;
        }

        try {
            // Merge configuration
            this.config = { ...this.config, ...config };
            
            // Set debug mode
            if (this.config.debugMode) {
                console.log('🚀 HD Tickets Application Core initializing...');
            }

            // Initialize core modules in order
            await this.initializeModules();
            
            // Setup global error handling
            this.setupErrorHandling();
            
            // Setup performance monitoring
            this.setupPerformanceMonitoring();
            
            // Load user state if authenticated
            await this.loadUserState();
            
            // Initialize theme
            this.initializeTheme();
            
            // Setup auto-save intervals
            this.setupAutoSave();
            
            this.isInitialized = true;
            this.emit('app:initialized', { version: this.config.version });
            
            if (this.config.debugMode) {
                console.log('✅ HD Tickets Application Core initialized successfully');
            }
        } catch (error) {
            console.error('Failed to initialize AppCore:', error);
            this.emit('app:error', { error, phase: 'initialization' });
            throw error;
        }
    }

    /**
     * Initialize all core modules
     */
    async initializeModules() {
        const moduleConfigs = [
            { name: 'responsive', module: () => import('../utils/responsiveUtils.js') },
            { name: 'theme', module: () => import('../modules/ThemeManager.js') },
            { name: 'feedback', module: () => import('../modules/UIFeedbackManager.js') },
            { name: 'preferences', module: () => import('../modules/UserPreferences.js') },
            { name: 'websocket', module: () => import('../modules/WebSocketManager.js') },
            { name: 'mobile', module: () => import('../utils/mobileOptimization.js') }
        ];

        for (const { name, module } of moduleConfigs) {
            try {
                const moduleInstance = await module();
                this.modules.set(name, moduleInstance.default || moduleInstance);
                
                if (this.config.debugMode) {
                    console.log(`📦 Module '${name}' loaded`);
                }
            } catch (error) {
                console.error(`Failed to load module '${name}':`, error);
                // Continue loading other modules
            }
        }
    }

    /**
     * Setup global error handling
     */
    setupErrorHandling() {
        // Unhandled promise rejections
        window.addEventListener('unhandledrejection', (event) => {
            console.error('Unhandled promise rejection:', event.reason);
            this.emit('app:error', { 
                type: 'unhandled_rejection', 
                error: event.reason 
            });
            
            // Show user-friendly message
            this.showErrorMessage('An unexpected error occurred. Please try again.');
        });

        // JavaScript errors
        window.addEventListener('error', (event) => {
            console.error('JavaScript error:', event.error);
            this.emit('app:error', { 
                type: 'javascript_error', 
                error: event.error,
                filename: event.filename,
                lineno: event.lineno,
                colno: event.colno
            });
        });

        // Resource loading errors
        document.addEventListener('error', (event) => {
            if (event.target !== window) {
                console.error('Resource loading error:', event.target);
                this.emit('app:error', { 
                    type: 'resource_error', 
                    target: event.target.tagName,
                    src: event.target.src || event.target.href
                });
            }
        }, true);
    }

    /**
     * Setup performance monitoring
     */
    setupPerformanceMonitoring() {
        // Monitor page load performance
        window.addEventListener('load', () => {
            setTimeout(() => {
                const perfData = performance.getEntriesByType('navigation')[0];
                if (perfData) {
                    this.emit('app:performance', {
                        type: 'page_load',
                        loadTime: perfData.loadEventEnd - perfData.fetchStart,
                        domContentLoaded: perfData.domContentLoadedEventEnd - perfData.fetchStart,
                        networkTime: perfData.responseEnd - perfData.fetchStart
                    });
                }
            }, 0);
        });

        // Monitor long tasks (if supported)
        if ('PerformanceObserver' in window) {
            try {
                const observer = new PerformanceObserver((list) => {
                    list.getEntries().forEach((entry) => {
                        if (entry.duration > 50) { // Long task threshold
                            this.emit('app:performance', {
                                type: 'long_task',
                                duration: entry.duration,
                                startTime: entry.startTime
                            });
                        }
                    });
                });
                observer.observe({ entryTypes: ['longtask'] });
            } catch (e) {
                // Silently ignore if not supported
            }
        }
    }

    /**
     * Load user state from server
     */
    async loadUserState() {
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (!csrfToken) return;

            const response = await fetch('/api/user/state', {
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            });

            if (response.ok) {
                const userData = await response.json();
                this.state.authenticated = true;
                this.state.user = userData.user;
                this.emit('user:state-loaded', userData);
            }
        } catch (error) {
            console.warn('Failed to load user state:', error);
        }
    }

    /**
     * Initialize theme system
     */
    initializeTheme() {
        const savedTheme = localStorage.getItem('theme') || 'light';
        this.state.theme = savedTheme;
        document.documentElement.classList.toggle('dark', savedTheme === 'dark');
        this.emit('theme:changed', { theme: savedTheme });
    }

    /**
     * Setup auto-save intervals
     */
    setupAutoSave() {
        // Auto-save user preferences every 30 seconds
        setInterval(() => {
            if (this.state.authenticated && this.modules.has('preferences')) {
                const prefs = this.modules.get('preferences');
                if (prefs && typeof prefs.savePreferences === 'function') {
                    prefs.savePreferences();
                }
            }
        }, 30000);

        // Save state before page unload
        window.addEventListener('beforeunload', () => {
            this.saveState();
        });
    }

    /**
     * Event system
     */
    on(event, callback) {
        this.eventBus.addEventListener(event, callback);
        return () => this.eventBus.removeEventListener(event, callback);
    }

    emit(event, data = {}) {
        this.eventBus.dispatchEvent(new CustomEvent(event, { detail: data }));
    }

    /**
     * Module management
     */
    getModule(name) {
        return this.modules.get(name);
    }

    hasModule(name) {
        return this.modules.has(name);
    }

    /**
     * API utilities
     */
    async api(endpoint, options = {}) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        const config = {
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                ...options.headers
            },
            ...options
        };

        if (config.body && typeof config.body === 'object') {
            config.body = JSON.stringify(config.body);
        }

        try {
            const response = await fetch(`${this.config.apiBaseUrl}${endpoint}`, config);
            
            if (!response.ok) {
                throw new Error(`API request failed: ${response.status} ${response.statusText}`);
            }
            
            return await response.json();
        } catch (error) {
            this.emit('api:error', { endpoint, error });
            throw error;
        }
    }

    /**
     * Utility methods
     */
    showErrorMessage(message) {
        if (this.modules.has('feedback')) {
            const feedback = this.modules.get('feedback');
            if (feedback && typeof feedback.error === 'function') {
                feedback.error(message);
            }
        } else {
            // Fallback to browser alert
            console.error(message);
        }
    }

    showSuccessMessage(message) {
        if (this.modules.has('feedback')) {
            const feedback = this.modules.get('feedback');
            if (feedback && typeof feedback.success === 'function') {
                feedback.success(message);
            }
        }
    }

    /**
     * State management
     */
    setState(key, value) {
        const oldValue = this.state[key];
        this.state[key] = value;
        this.emit('state:changed', { key, value, oldValue });
    }

    getState(key) {
        return this.state[key];
    }

    saveState() {
        try {
            const stateToSave = {
                theme: this.state.theme,
                locale: this.state.locale,
                timestamp: Date.now()
            };
            localStorage.setItem('app_state', JSON.stringify(stateToSave));
        } catch (error) {
            console.warn('Failed to save app state:', error);
        }
    }

    /**
     * Debug utilities
     */
    debug(message, ...args) {
        if (this.config.debugMode) {
            console.log(`[AppCore] ${message}`, ...args);
        }
    }

    getDebugInfo() {
        return {
            version: this.config.version,
            initialized: this.isInitialized,
            modules: Array.from(this.modules.keys()),
            state: { ...this.state },
            config: { ...this.config }
        };
    }

    /**
     * Cleanup
     */
    destroy() {
        // Clear intervals and event listeners
        this.modules.clear();
        this.eventBus = new EventTarget();
        this.isInitialized = false;
    }
}

// Create singleton instance
const appCore = new AppCore();

// Make globally available
window.AppCore = appCore;

// Global initialization function for Alpine.js
window.appStore = () => ({
    loading: false,
    notifications: [],
    
    init() {
        // This will be called by Alpine.js
    }
});

window.initializeApp = async () => {
    try {
        await appCore.init({
            debugMode: window.location.hostname === 'localhost' || window.location.search.includes('debug=true')
        });
    } catch (error) {
        console.error('Failed to initialize application:', error);
    }
};

// Export for module environments
export default appCore;
