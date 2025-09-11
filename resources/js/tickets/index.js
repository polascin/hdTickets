/**
 * HD Tickets - Main JavaScript Module
 * 
 * Initializes and coordinates all ticket system components including
 * filtering, price monitoring, comparison, and real-time features.
 */

// Import all ticket components
import('./TicketFilters.js');
import('./PriceMonitor.js');
import('./TicketComparison.js');

/**
 * HD Tickets Application
 */
class HDTicketsApp {
    constructor(options = {}) {
        this.options = {
            enablePriceMonitoring: true,
            enableComparison: true,
            enableFiltering: true,
            enableAnalytics: true,
            debugMode: false,
            ...options
        };
        
        this.components = {};
        this.isInitialized = false;
        
        this.init();
    }
    
    init() {
        console.log('🎫 HD Tickets App initializing...');
        
        this.setupGlobalErrorHandling();
        this.initializeComponents();
        this.setupGlobalEventListeners();
        this.setupAnalytics();
        
        this.isInitialized = true;
        
        console.log('✅ HD Tickets App initialized successfully');
        
        // Dispatch initialization event
        this.dispatchEvent('hdtickets:initialized', {
            components: Object.keys(this.components),
            version: '1.0.0'
        });
    }
    
    setupGlobalErrorHandling() {
        window.addEventListener('error', (event) => {
            if (this.options.debugMode) {
                console.error('HDTickets Error:', event.error);
            }
            
            this.trackError(event.error);
        });
        
        window.addEventListener('unhandledrejection', (event) => {
            if (this.options.debugMode) {
                console.error('HDTickets Unhandled Rejection:', event.reason);
            }
            
            this.trackError(event.reason);
        });
    }
    
    initializeComponents() {
        // Initialize filtering system
        if (this.options.enableFiltering && window.TicketFilters) {
            try {
                this.components.filters = new window.TicketFilters({
                    formSelector: '#filters-form',
                    resultsSelector: '#tickets-grid',
                    loadingSelector: '#loading-indicator',
                    enableUrlSync: true,
                    cacheEnabled: true
                });
                console.log('✅ Ticket filters initialized');
            } catch (error) {
                console.error('❌ Failed to initialize ticket filters:', error);
            }
        }
        
        // Initialize price monitoring
        if (this.options.enablePriceMonitoring && window.PriceMonitor && this.hasWebSocketSupport()) {
            try {
                this.components.priceMonitor = new window.PriceMonitor({
                    enableNotifications: this.hasNotificationSupport(),
                    enableSound: true,
                    priceThreshold: 0.05 // 5% change threshold
                });
                console.log('✅ Price monitoring initialized');
            } catch (error) {
                console.error('❌ Failed to initialize price monitoring:', error);
            }
        }
        
        // Initialize ticket comparison
        if (this.options.enableComparison && window.TicketComparison) {
            try {
                this.components.comparison = new window.TicketComparison({
                    maxCompare: 6,
                    enableExport: true,
                    enableSharing: this.hasSharingSupport(),
                    autoSave: true
                });
                console.log('✅ Ticket comparison initialized');
            } catch (error) {
                console.error('❌ Failed to initialize ticket comparison:', error);
            }
        }
        
        // Initialize additional features
        this.initializeBookmarkSystem();
        this.initializeShareButtons();
        this.initializeSearchSuggestions();
        this.initializeLazyLoading();
        this.initializeProgressiveEnhancement();
    }
    
    initializeBookmarkSystem() {
        const bookmarkButtons = document.querySelectorAll('.bookmark-toggle');
        
        bookmarkButtons.forEach(button => {
            button.addEventListener('click', async (e) => {
                e.preventDefault();
                e.stopPropagation();
                
                const ticketId = button.dataset.ticketId;
                if (!ticketId) return;
                
                
                try {
                    // Show loading state
                    button.disabled = true;
                    button.classList.add('loading');
                    
                    const response = await fetch('/tickets/scraping/bookmark-toggle', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ ticket_id: ticketId })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        this.updateBookmarkButton(button, data.is_bookmarked);
                        this.showNotification(
                            data.is_bookmarked ? 'Ticket bookmarked!' : 'Bookmark removed',
                            'success'
                        );
                        
                        // Track analytics
                        this.trackEvent('ticket_bookmark', {
                            ticket_id: ticketId,
                            action: data.is_bookmarked ? 'add' : 'remove'
                        });
                    } else {
                        throw new Error(data.message || 'Bookmark failed');
                    }
                } catch (error) {
                    console.error('Bookmark error:', error);
                    this.showNotification('Failed to update bookmark', 'error');
                } finally {
                    button.disabled = false;
                    button.classList.remove('loading');
                }
            });
        });
    }
    
    updateBookmarkButton(button, isBookmarked) {
        const icon = button.querySelector('svg');
        const text = button.querySelector('.bookmark-text');
        
        if (isBookmarked) {
            button.classList.add('bookmarked');
            icon.classList.remove('text-gray-400');
            icon.classList.add('text-yellow-500');
            if (text) text.textContent = 'Bookmarked';
        } else {
            button.classList.remove('bookmarked');
            icon.classList.remove('text-yellow-500');
            icon.classList.add('text-gray-400');
            if (text) text.textContent = 'Bookmark';
        }
    }
    
    initializeShareButtons() {
        const shareButtons = document.querySelectorAll('.share-button');
        
        shareButtons.forEach(button => {
            button.addEventListener('click', async (e) => {
                e.preventDefault();
                
                const ticketId = button.dataset.ticketId;
                const title = button.dataset.title || 'Check out this ticket';
                const url = button.dataset.url || window.location.href;
                
                if (this.hasSharingSupport()) {
                    try {
                        await navigator.share({
                            title,
                            text: 'Found this great ticket on HD Tickets',
                            url
                        });
                        
                        this.trackEvent('ticket_share', {
                            ticket_id: ticketId,
                            method: 'native'
                        });
                    } catch (error) {
                        if (error.name !== 'AbortError') {
                            this.fallbackShare(url, title);
                        }
                    }
                } else {
                    this.fallbackShare(url, title);
                }
            });
        });
    }
    
    fallbackShare(url, title) {
        // Copy to clipboard as fallback
        navigator.clipboard.writeText(url).then(() => {
            this.showNotification('Link copied to clipboard!', 'success');
        }).catch(() => {
            // Show share modal as last resort
            this.showShareModal(url, title);
        });
    }
    
    showShareModal(url, title) {
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 z-50 overflow-y-auto';
        modal.innerHTML = `
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"></div>
                <div class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-sm sm:w-full sm:p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Share Ticket</h3>
                    <div class="flex space-x-2 mb-4">
                        <a href="https://twitter.com/intent/tweet?text=${encodeURIComponent(title)}&url=${encodeURIComponent(url)}" 
                           target="_blank" 
                           class="flex-1 bg-blue-500 text-white px-3 py-2 rounded text-center text-sm">Twitter</a>
                        <a href="https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}" 
                           target="_blank" 
                           class="flex-1 bg-blue-600 text-white px-3 py-2 rounded text-center text-sm">Facebook</a>
                    </div>
                    <div class="flex items-center space-x-2 mb-4">
                        <input type="text" value="${url}" readonly class="flex-1 px-3 py-2 border border-gray-300 rounded text-sm">
                        <button class="copy-url-btn px-3 py-2 bg-gray-500 text-white rounded text-sm">Copy</button>
                    </div>
                    <button class="close-modal w-full bg-gray-200 text-gray-800 px-4 py-2 rounded text-sm">Close</button>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Event listeners
        modal.querySelector('.copy-url-btn').addEventListener('click', () => {
            const input = modal.querySelector('input');
            input.select();
            document.execCommand('copy');
            this.showNotification('Link copied!', 'success');
        });
        
        modal.querySelector('.close-modal').addEventListener('click', () => {
            document.body.removeChild(modal);
        });
        
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                document.body.removeChild(modal);
            }
        });
    }
    
    initializeSearchSuggestions() {
        const searchInputs = document.querySelectorAll('input[name="keywords"]');
        
        searchInputs.forEach(input => {
            let timeout;
            let suggestionsContainer;
            
            // Create suggestions container
            const container = document.createElement('div');
            container.className = 'absolute z-50 w-full bg-white border border-gray-300 rounded-md shadow-lg mt-1 max-h-60 overflow-y-auto hidden';
            input.parentElement.style.position = 'relative';
            input.parentElement.appendChild(container);
            suggestionsContainer = container;
            
            input.addEventListener('input', (e) => {
                clearTimeout(timeout);
                const query = e.target.value.trim();
                
                if (query.length >= 2) {
                    timeout = setTimeout(() => {
                        this.loadSearchSuggestions(query, suggestionsContainer, input);
                    }, 300);
                } else {
                    suggestionsContainer.classList.add('hidden');
                }
            });
            
            input.addEventListener('blur', () => {
                setTimeout(() => {
                    suggestionsContainer.classList.add('hidden');
                }, 150);
            });
        });
    }
    
    async loadSearchSuggestions(query, container, input) {
        try {
            const response = await fetch(`/tickets/scraping/search-suggestions?term=${encodeURIComponent(query)}`);
            const data = await response.json();
            
            if (data.success && data.suggestions.length > 0) {
                container.innerHTML = data.suggestions.map(suggestion => `
                    <div class="px-3 py-2 hover:bg-gray-100 cursor-pointer suggestion-item flex items-center"
                         data-value="${this.escapeHtml(suggestion.value)}">
                        <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <span class="flex-1">${this.escapeHtml(suggestion.value)}</span>
                        <span class="text-xs text-gray-500 capitalize">${this.escapeHtml(suggestion.type)}</span>
                    </div>
                `).join('');
                
                // Add click handlers
                container.querySelectorAll('.suggestion-item').forEach(item => {
                    item.addEventListener('click', () => {
                        input.value = item.dataset.value;
                        container.classList.add('hidden');
                        
                        // Trigger form submission if filters component exists
                        if (this.components.filters) {
                            this.components.filters.submitFilters();
                        }
                    });
                });
                
                container.classList.remove('hidden');
            } else {
                container.classList.add('hidden');
            }
        } catch (error) {
            console.error('Search suggestions error:', error);
            container.classList.add('hidden');
        }
    }
    
    initializeLazyLoading() {
        if ('IntersectionObserver' in window) {
            const lazyImages = document.querySelectorAll('img[data-src]');
            
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.removeAttribute('data-src');
                        img.classList.remove('lazy');
                        observer.unobserve(img);
                    }
                });
            }, {
                rootMargin: '50px 0px'
            });
            
            lazyImages.forEach(img => imageObserver.observe(img));
        }
    }
    
    initializeProgressiveEnhancement() {
        // Add CSS classes for JavaScript-enabled features
        document.documentElement.classList.add('js-enabled');
        
        // Progressive enhancement for forms
        const forms = document.querySelectorAll('form[data-enhance]');
        forms.forEach(form => {
            form.addEventListener('submit', (e) => {
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.classList.add('loading');
                    
                    setTimeout(() => {
                        submitBtn.disabled = false;
                        submitBtn.classList.remove('loading');
                    }, 2000);
                }
            });
        });
        
        // Progressive enhancement for buttons
        document.querySelectorAll('button[data-loading-text]').forEach(button => {
            button.addEventListener('click', () => {
                const originalText = button.textContent;
                const loadingText = button.dataset.loadingText;
                
                button.textContent = loadingText;
                button.disabled = true;
                
                setTimeout(() => {
                    button.textContent = originalText;
                    button.disabled = false;
                }, 2000);
            });
        });
    }
    
    setupGlobalEventListeners() {
        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            // Ctrl/Cmd + / for help
            if ((e.ctrlKey || e.metaKey) && e.key === '/') {
                e.preventDefault();
                this.showKeyboardShortcuts();
            }
            
            // Alt + F for filters
            if (e.altKey && e.key.toLowerCase() === 'f') {
                e.preventDefault();
                const searchInput = document.getElementById('keywords');
                if (searchInput) {
                    searchInput.focus();
                }
            }
        });
        
        // Component event listeners
        document.addEventListener('filtersApplied', (e) => {
            this.trackEvent('filters_applied', e.detail);
        });
        
        // Visibility change handling
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.handlePageHidden();
            } else {
                this.handlePageVisible();
            }
        });
    }
    
    showKeyboardShortcuts() {
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 z-50 overflow-y-auto';
        modal.innerHTML = `
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"></div>
                <div class="inline-block px-6 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Keyboard Shortcuts</h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span><kbd class="px-2 py-1 bg-gray-100 rounded text-xs">Ctrl/⌘ + K</kbd></span>
                            <span class="text-gray-600">Focus search</span>
                        </div>
                        <div class="flex justify-between">
                            <span><kbd class="px-2 py-1 bg-gray-100 rounded text-xs">Ctrl/⌘ + ⇧ + C</kbd></span>
                            <span class="text-gray-600">Open comparison</span>
                        </div>
                        <div class="flex justify-between">
                            <span><kbd class="px-2 py-1 bg-gray-100 rounded text-xs">Alt + F</kbd></span>
                            <span class="text-gray-600">Focus filters</span>
                        </div>
                        <div class="flex justify-between">
                            <span><kbd class="px-2 py-1 bg-gray-100 rounded text-xs">Escape</kbd></span>
                            <span class="text-gray-600">Close modals</span>
                        </div>
                        <div class="flex justify-between">
                            <span><kbd class="px-2 py-1 bg-gray-100 rounded text-xs">Ctrl/⌘ + /</kbd></span>
                            <span class="text-gray-600">Show this help</span>
                        </div>
                    </div>
                    <button class="close-shortcuts-modal mt-4 w-full bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Close</button>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        modal.querySelector('.close-shortcuts-modal').addEventListener('click', () => {
            document.body.removeChild(modal);
        });
        
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                document.body.removeChild(modal);
            }
        });
    }
    
    handlePageHidden() {
        // Pause expensive operations when page is hidden
        if (this.components.priceMonitor) {
            this.components.priceMonitor.pauseMonitoring();
        }
    }
    
    handlePageVisible() {
        // Resume operations when page becomes visible
        if (this.components.priceMonitor) {
            this.components.priceMonitor.resumeMonitoring();
        }
    }
    
    setupAnalytics() {
        if (!this.options.enableAnalytics) return;
        
        // Track page view
        this.trackEvent('page_view', {
            page: window.location.pathname,
            referrer: document.referrer
        });
        
        // Track user interactions
        document.addEventListener('click', (e) => {
            const target = e.target.closest('[data-track]');
            if (target) {
                this.trackEvent('interaction', {
                    element: target.dataset.track,
                    page: window.location.pathname
                });
            }
        });
    }
    
    // Utility Methods
    
    hasWebSocketSupport() {
        return 'WebSocket' in window && window.Echo;
    }
    
    hasNotificationSupport() {
        return 'Notification' in window;
    }
    
    hasSharingSupport() {
        return 'share' in navigator;
    }
    
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 px-4 py-3 rounded-lg shadow-lg transition-all duration-300 transform translate-x-full ${
            type === 'success' ? 'bg-green-500 text-white' :
            type === 'warning' ? 'bg-yellow-500 text-white' :
            type === 'error' ? 'bg-red-500 text-white' :
            'bg-blue-500 text-white'
        }`;
        
        notification.innerHTML = `
            <div class="flex items-center space-x-3">
                <span>${message}</span>
                <button class="ml-auto text-white hover:text-gray-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.classList.remove('translate-x-full');
        }, 50);
        
        // Close button
        notification.querySelector('button').addEventListener('click', () => {
            this.removeNotification(notification);
        });
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            this.removeNotification(notification);
        }, 5000);
    }
    
    removeNotification(notification) {
        if (notification.parentElement) {
            notification.classList.add('translate-x-full');
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.parentElement.removeChild(notification);
                }
            }, 300);
        }
    }
    
    dispatchEvent(eventName, detail = {}) {
        const event = new CustomEvent(eventName, { detail });
        document.dispatchEvent(event);
    }
    
    trackEvent(eventName, data = {}) {
        if (!this.options.enableAnalytics) return;
        
        // Google Analytics 4
        if (typeof gtag !== 'undefined') {
            gtag('event', eventName, data);
        }
        
        // Custom analytics endpoint
        if (this.options.analyticsEndpoint) {
            fetch(this.options.analyticsEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    event: eventName,
                    data,
                    timestamp: Date.now(),
                    url: window.location.href,
                    userAgent: navigator.userAgent
                })
            }).catch(error => {
                if (this.options.debugMode) {
                    console.warn('Analytics tracking failed:', error);
                }
            });
        }
        
        if (this.options.debugMode) {
            console.log('📊 Analytics Event:', eventName, data);
        }
    }
    
    trackError(error) {
        this.trackEvent('javascript_error', {
            message: error.message || error.toString(),
            stack: error.stack,
            url: window.location.href,
            userAgent: navigator.userAgent
        });
    }
    
    // Public API
    
    getComponent(name) {
        return this.components[name];
    }
    
    isComponentEnabled(name) {
        return !!this.components[name];
    }
    
    reload() {
        window.location.reload();
    }
    
    destroy() {
        // Cleanup all components
        Object.values(this.components).forEach(component => {
            if (component.destroy) {
                component.destroy();
            }
        });
        
        this.components = {};
        this.isInitialized = false;
        
        console.log('🎫 HD Tickets App destroyed');
    }
}

// Global initialization
let hdTicketsApp = null;

function initializeHDTickets(options = {}) {
    if (hdTicketsApp) {
        hdTicketsApp.destroy();
    }
    
    hdTicketsApp = new HDTicketsApp(options);
    return hdTicketsApp;
}

// Auto-initialize on DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        initializeHDTickets(window.hdTicketsConfig || {});
    });
} else {
    initializeHDTickets(window.hdTicketsConfig || {});
}

// Export for global access
window.HDTicketsApp = HDTicketsApp;
window.initializeHDTickets = initializeHDTickets;

export default HDTicketsApp;
