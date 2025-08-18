/*!
 * HD Tickets - Simple Customer Dashboard JavaScript
 * Sports Events Entry Tickets Monitoring System
 * Version: 2.0.0 (Simplified)
 * Environment: Ubuntu 24.04 LTS, Apache2, PHP8.4, MySQL/MariaDB 10.4
 * 
 * Features:
 * - Simple periodic refresh (60 seconds)
 * - Basic click tracking for analytics
 * - Progressive enhancement
 * - No external dependencies
 */

class SimpleCustomerDashboard {
    constructor(options = {}) {
        this.refreshInterval = options.refreshInterval || 60000; // 60 seconds
        this.refreshTimer = null;
        this.isVisible = true;
        this.lastRefresh = Date.now();
        
        this.init();
    }

    /**
     * Initialize the simplified dashboard
     */
    init() {
        console.log('HD Tickets: Initializing Simple Customer Dashboard');
        
        // Add progressive enhancement class
        document.body.classList.add('js-enhanced');
        
        // Setup event listeners
        this.setupEventListeners();
        
        // Start periodic refresh
        this.startPeriodicRefresh();
        
        // Handle page visibility changes
        this.handleVisibilityChanges();
        
        console.log('HD Tickets: Dashboard initialized successfully');
    }

    /**
     * Setup basic event listeners
     */
    setupEventListeners() {
        // Track action button clicks for analytics
        document.querySelectorAll('.action-card').forEach(card => {
            card.addEventListener('click', (e) => {
                const action = card.dataset.action;
                if (action) {
                    this.trackAction(action, 'quick_action_click');
                }
            });
        });

        // Add loading states to buttons
        document.querySelectorAll('.btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                this.showButtonLoading(btn);
            });
        });

        // Handle table row clicks for better UX
        document.querySelectorAll('.ticket-row').forEach(row => {
            row.addEventListener('click', (e) => {
                const ticketId = row.dataset.ticketId;
                if (ticketId && !e.target.closest('button')) {
                    this.trackAction('ticket_view', 'ticket_row_click', { ticketId });
                }
            });
        });
    }

    /**
     * Start periodic refresh of dashboard content
     */
    startPeriodicRefresh() {
        // Only refresh if page is visible and enough time has passed
        this.refreshTimer = setInterval(() => {
            if (this.isVisible && (Date.now() - this.lastRefresh) >= this.refreshInterval) {
                this.refreshDashboard();
            }
        }, this.refreshInterval);
    }

    /**
     * Stop periodic refresh
     */
    stopPeriodicRefresh() {
        if (this.refreshTimer) {
            clearInterval(this.refreshTimer);
            this.refreshTimer = null;
        }
    }

    /**
     * Handle page visibility changes to optimize performance
     */
    handleVisibilityChanges() {
        document.addEventListener('visibilitychange', () => {
            this.isVisible = !document.hidden;
            
            if (this.isVisible) {
                // Page became visible - check if we need to refresh
                const timeSinceLastRefresh = Date.now() - this.lastRefresh;
                if (timeSinceLastRefresh >= this.refreshInterval) {
                    this.refreshDashboard();
                }
            }
        });
    }

    /**
     * Refresh dashboard statistics and recent tickets
     */
    async refreshDashboard() {
        try {
            console.log('HD Tickets: Refreshing dashboard data');
            
            // Show loading indicators
            this.showLoadingStates();
            
            // Fetch updated statistics
            await this.updateStatistics();
            
            // Fetch updated recent tickets
            await this.updateRecentTickets();
            
            // Update last refresh timestamp
            this.lastRefresh = Date.now();
            this.updateLastRefreshIndicator();
            
            console.log('HD Tickets: Dashboard refresh completed');
            
        } catch (error) {
            console.error('HD Tickets: Error refreshing dashboard:', error);
            this.showErrorState();
        } finally {
            this.hideLoadingStates();
        }
    }

    /**
     * Update dashboard statistics
     */
    async updateStatistics() {
        try {
            const response = await fetch('/ajax/dashboard/stats', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();
            
            if (data.success && data.data) {
                this.updateStatisticCards(data.data);
            }
        } catch (error) {
            console.warn('HD Tickets: Could not update statistics:', error);
        }
    }

    /**
     * Update recent tickets section
     */
    async updateRecentTickets() {
        try {
            const response = await fetch('/ajax/dashboard/recent-tickets', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();
            
            if (data.success && data.html) {
                const ticketsContainer = document.querySelector('#recent-tickets-container');
                if (ticketsContainer) {
                    ticketsContainer.innerHTML = data.html;
                    this.setupEventListeners(); // Re-setup listeners for new content
                }
            }
        } catch (error) {
            console.warn('HD Tickets: Could not update recent tickets:', error);
        }
    }

    /**
     * Update statistic cards with new data
     */
    updateStatisticCards(stats) {
        const statElements = {
            'available-tickets': document.querySelector('[data-stat="available-tickets"] .stat-value'),
            'high-demand': document.querySelector('[data-stat="high-demand"] .stat-value'),
            'alerts': document.querySelector('[data-stat="alerts"] .stat-value'),
            'queue': document.querySelector('[data-stat="queue"] .stat-value')
        };

        Object.keys(statElements).forEach(key => {
            const element = statElements[key];
            if (element && stats[key] !== undefined) {
                const oldValue = parseInt(element.textContent) || 0;
                const newValue = parseInt(stats[key]) || 0;
                
                // Animate value change if different
                if (oldValue !== newValue) {
                    this.animateValueChange(element, oldValue, newValue);
                }
            }
        });
    }

    /**
     * Animate value change with simple counter effect
     */
    animateValueChange(element, fromValue, toValue) {
        const duration = 1000; // 1 second
        const start = Date.now();
        const diff = toValue - fromValue;
        
        const updateValue = () => {
            const elapsed = Date.now() - start;
            const progress = Math.min(elapsed / duration, 1);
            
            // Easing function (ease-out)
            const easeOut = 1 - Math.pow(1 - progress, 3);
            const currentValue = Math.round(fromValue + (diff * easeOut));
            
            element.textContent = currentValue.toLocaleString();
            
            if (progress < 1) {
                requestAnimationFrame(updateValue);
            } else {
                element.textContent = toValue.toLocaleString();
            }
        };
        
        requestAnimationFrame(updateValue);
    }

    /**
     * Show loading states for dashboard sections
     */
    showLoadingStates() {
        document.querySelectorAll('.stat-value').forEach(element => {
            element.classList.add('loading');
        });
    }

    /**
     * Hide loading states
     */
    hideLoadingStates() {
        document.querySelectorAll('.stat-value').forEach(element => {
            element.classList.remove('loading');
        });
    }

    /**
     * Show loading state for buttons
     */
    showButtonLoading(button) {
        const originalText = button.innerHTML;
        button.dataset.originalText = originalText;
        button.innerHTML = '<span class="loading">Loading...</span>';
        button.disabled = true;
        
        // Reset after 3 seconds if no page navigation occurs
        setTimeout(() => {
            if (button.dataset.originalText) {
                button.innerHTML = button.dataset.originalText;
                button.disabled = false;
                delete button.dataset.originalText;
            }
        }, 3000);
    }

    /**
     * Show error state
     */
    showErrorState() {
        const errorMessage = document.createElement('div');
        errorMessage.className = 'error-banner';
        errorMessage.innerHTML = `
            <div class="error-content">
                <strong>Update Error:</strong> Some data may be outdated. The page will retry automatically.
            </div>
        `;
        
        document.body.insertBefore(errorMessage, document.body.firstChild);
        
        // Remove error message after 5 seconds
        setTimeout(() => {
            if (errorMessage.parentNode) {
                errorMessage.parentNode.removeChild(errorMessage);
            }
        }, 5000);
    }

    /**
     * Update last refresh indicator
     */
    updateLastRefreshIndicator() {
        const indicator = document.querySelector('#last-refresh-time');
        if (indicator) {
            indicator.textContent = new Date().toLocaleTimeString();
        }
    }

    /**
     * Track user actions for analytics (simplified)
     */
    trackAction(action, type, data = {}) {
        // Simple analytics tracking - could be expanded with actual analytics service
        const eventData = {
            action,
            type,
            timestamp: Date.now(),
            url: window.location.pathname,
            ...data
        };
        
        console.log('HD Tickets: Action tracked:', eventData);
        
        // Send to analytics endpoint (optional)
        this.sendAnalytics(eventData);
    }

    /**
     * Send analytics data (optional)
     */
    async sendAnalytics(data) {
        try {
            await fetch('/ajax/analytics/track', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                body: JSON.stringify(data)
            });
        } catch (error) {
            // Fail silently for analytics
            console.debug('Analytics tracking failed:', error);
        }
    }

    /**
     * Destroy the dashboard instance
     */
    destroy() {
        this.stopPeriodicRefresh();
        console.log('HD Tickets: Dashboard destroyed');
    }
}

// Initialize dashboard when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Check if we're on the customer dashboard page
    if (document.body.classList.contains('customer-dashboard')) {
        window.customerDashboard = new SimpleCustomerDashboard({
            refreshInterval: 60000 // 60 seconds
        });
    }
});

// Handle page unload
window.addEventListener('beforeunload', function() {
    if (window.customerDashboard) {
        window.customerDashboard.destroy();
    }
});

// Export for manual initialization if needed
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SimpleCustomerDashboard;
}
