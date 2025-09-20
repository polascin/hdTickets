/**
 * HD Tickets Dashboard - Common Utilities and Shared Components
 * Sports Events Entry System
 */

// Global dashboard utilities
window.HDTickets = window.HDTickets || {};

HDTickets.Dashboard = {
    /**
     * Common configuration
     */
    config: {
        refreshInterval: 30000,
        heartbeatInterval: 60000,
        animationDuration: 300,
        toastDuration: 5000,
        apiRetryAttempts: 3,
        apiRetryDelay: 1000
    },

    /**
     * API utilities
     */
    api: {
        /**
         * Make authenticated API request
         */
        async request(url, options = {}) {
            const defaultOptions = {
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            };

            const mergedOptions = {
                ...defaultOptions,
                ...options,
                headers: { ...defaultOptions.headers, ...options.headers }
            };

            let attempts = 0;
            while (attempts < HDTickets.Dashboard.config.apiRetryAttempts) {
                try {
                    const response = await fetch(url, mergedOptions);
                    
                    if (response.ok) {
                        return response;
                    } else if (response.status === 429) {
                        // Rate limited - wait longer
                        await HDTickets.Dashboard.utils.sleep(HDTickets.Dashboard.config.apiRetryDelay * (attempts + 1) * 2);
                        attempts++;
                    } else if (response.status >= 500) {
                        // Server error - retry
                        await HDTickets.Dashboard.utils.sleep(HDTickets.Dashboard.config.apiRetryDelay * (attempts + 1));
                        attempts++;
                    } else {
                        // Client error - don't retry
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                } catch (error) {
                    if (attempts === HDTickets.Dashboard.config.apiRetryAttempts - 1) {
                        throw error;
                    }
                    await HDTickets.Dashboard.utils.sleep(HDTickets.Dashboard.config.apiRetryDelay * (attempts + 1));
                    attempts++;
                }
            }
        },

        /**
         * Get dashboard data
         */
        async getDashboardData(type = 'customer') {
            const apiUrl = document.querySelector(`meta[name="${type}-dashboard-api"]`)?.getAttribute('content');
            if (!apiUrl) {
                throw new Error(`${type} dashboard API URL not found`);
            }
            return this.request(apiUrl);
        },

        /**
         * Heartbeat check
         */
        async heartbeat() {
            return this.request('/api/heartbeat', { method: 'HEAD' });
        }
    },

    /**
     * UI utilities
     */
    ui: {
        /**
         * Show toast notification
         */
        showToast(message, type = 'info', duration = null) {
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            toast.textContent = message;
            
            // Add to container
            let container = document.getElementById('toast-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'toast-container';
                container.className = 'fixed top-4 right-4 z-50 space-y-2';
                document.body.appendChild(container);
            }
            
            container.appendChild(toast);
            
            // Animate in
            requestAnimationFrame(() => {
                toast.classList.add('toast-show');
            });
            
            // Auto remove
            const timeoutDuration = duration || HDTickets.Dashboard.config.toastDuration;
            setTimeout(() => {
                this.removeToast(toast);
            }, timeoutDuration);
            
            return toast;
        },

        /**
         * Remove toast
         */
        removeToast(toast) {
            toast.classList.add('toast-hide');
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, HDTickets.Dashboard.config.animationDuration);
        },

        /**
         * Show loading spinner
         */
        showLoading(element) {
            if (element) {
                element.classList.add('loading');
            }
        },

        /**
         * Hide loading spinner
         */
        hideLoading(element) {
            if (element) {
                element.classList.remove('loading');
            }
        },

        /**
         * Update badge count
         */
        updateBadge(element, count) {
            if (!element) return;
            
            if (count > 0) {
                element.textContent = count > 99 ? '99+' : count.toString();
                element.classList.remove('hidden');
            } else {
                element.classList.add('hidden');
            }
        },

        /**
         * Animate number counter
         */
        animateCounter(element, from, to, duration = 1000) {
            const startTime = performance.now();
            const difference = to - from;
            
            function updateCount(currentTime) {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);
                
                // Easing function
                const easedProgress = 1 - Math.pow(1 - progress, 3);
                const current = Math.round(from + (difference * easedProgress));
                
                element.textContent = HDTickets.Dashboard.utils.formatNumber(current);
                
                if (progress < 1) {
                    requestAnimationFrame(updateCount);
                }
            }
            
            requestAnimationFrame(updateCount);
        }
    },

    /**
     * General utilities
     */
    utils: {
        /**
         * Sleep/delay function
         */
        sleep(ms) {
            return new Promise(resolve => setTimeout(resolve, ms));
        },

        /**
         * Format numbers for display
         */
        formatNumber(number) {
            if (typeof number !== 'number') return '0';
            
            if (number >= 1000000) {
                return (number / 1000000).toFixed(1) + 'M';
            } else if (number >= 1000) {
                return (number / 1000).toFixed(1) + 'K';
            }
            
            return number.toLocaleString();
        },

        /**
         * Format currency
         */
        formatCurrency(amount, currency = 'USD') {
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: currency
            }).format(amount);
        },

        /**
         * Format percentage
         */
        formatPercentage(value, showSign = true) {
            if (typeof value !== 'number') return '';
            
            const formatted = Math.abs(value).toFixed(1) + '%';
            
            if (showSign) {
                return value > 0 ? '+' + formatted : value < 0 ? '-' + formatted : formatted;
            }
            
            return formatted;
        },

        /**
         * Format date/time
         */
        formatDateTime(date, options = {}) {
            const defaultOptions = {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            };
            
            return new Intl.DateTimeFormat('en-US', { ...defaultOptions, ...options }).format(new Date(date));
        },

        /**
         * Calculate time ago
         */
        timeAgo(date) {
            const now = new Date();
            const past = new Date(date);
            const diffInMinutes = Math.floor((now - past) / (1000 * 60));
            
            if (diffInMinutes < 1) return 'just now';
            if (diffInMinutes < 60) return `${diffInMinutes}m ago`;
            
            const diffInHours = Math.floor(diffInMinutes / 60);
            if (diffInHours < 24) return `${diffInHours}h ago`;
            
            const diffInDays = Math.floor(diffInHours / 24);
            if (diffInDays < 30) return `${diffInDays}d ago`;
            
            return this.formatDateTime(date, { year: '2-digit', month: 'short', day: 'numeric' });
        },

        /**
         * Debounce function
         */
        debounce(func, delay) {
            let timeoutId;
            return function (...args) {
                clearTimeout(timeoutId);
                timeoutId = setTimeout(() => func.apply(this, args), delay);
            };
        },

        /**
         * Throttle function
         */
        throttle(func, limit) {
            let inThrottle;
            return function (...args) {
                if (!inThrottle) {
                    func.apply(this, args);
                    inThrottle = true;
                    setTimeout(() => inThrottle = false, limit);
                }
            };
        },

        /**
         * Deep merge objects
         */
        deepMerge(target, ...sources) {
            if (!sources.length) return target;
            const source = sources.shift();
            
            if (this.isObject(target) && this.isObject(source)) {
                for (const key in source) {
                    if (this.isObject(source[key])) {
                        if (!target[key]) Object.assign(target, { [key]: {} });
                        this.deepMerge(target[key], source[key]);
                    } else {
                        Object.assign(target, { [key]: source[key] });
                    }
                }
            }
            
            return this.deepMerge(target, ...sources);
        },

        /**
         * Check if value is object
         */
        isObject(item) {
            return item && typeof item === 'object' && !Array.isArray(item);
        },

        /**
         * Generate unique ID
         */
        generateId() {
            return Date.now().toString(36) + Math.random().toString(36).substr(2);
        },

        /**
         * Escape HTML
         */
        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    },

    /**
     * Chart utilities
     */
    charts: {
        /**
         * Default chart colors
         */
        colors: {
            primary: '#3b82f6',
            secondary: '#8b5cf6',
            success: '#10b981',
            warning: '#f59e0b',
            error: '#ef4444',
            info: '#06b6d4',
            gray: '#6b7280'
        },

        /**
         * Get color with opacity
         */
        getColorWithOpacity(color, opacity) {
            // Convert hex to rgba
            const r = parseInt(color.slice(1, 3), 16);
            const g = parseInt(color.slice(3, 5), 16);
            const b = parseInt(color.slice(5, 7), 16);
            
            return `rgba(${r}, ${g}, ${b}, ${opacity})`;
        },

        /**
         * Default chart options
         */
        getDefaultOptions(type = 'line') {
            const baseOptions = {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            };

            switch (type) {
                case 'line':
                    return {
                        ...baseOptions,
                        scales: {
                            x: { display: false },
                            y: { display: false }
                        }
                    };

                case 'doughnut':
                    return {
                        ...baseOptions,
                        cutout: '70%'
                    };

                case 'bar':
                    return {
                        ...baseOptions,
                        scales: {
                            x: { display: false },
                            y: { display: false }
                        }
                    };

                default:
                    return baseOptions;
            }
        }
    },

    /**
     * Event system
     */
    events: {
        listeners: {},

        /**
         * Add event listener
         */
        on(event, callback) {
            if (!this.listeners[event]) {
                this.listeners[event] = [];
            }
            this.listeners[event].push(callback);
        },

        /**
         * Remove event listener
         */
        off(event, callback) {
            if (!this.listeners[event]) return;
            
            const index = this.listeners[event].indexOf(callback);
            if (index > -1) {
                this.listeners[event].splice(index, 1);
            }
        },

        /**
         * Emit event
         */
        emit(event, ...args) {
            if (!this.listeners[event]) return;
            
            this.listeners[event].forEach(callback => {
                try {
                    callback(...args);
                } catch (error) {
                    console.error(`Error in event listener for ${event}:`, error);
                }
            });
        }
    }
};

// Initialize global error handler
window.addEventListener('error', (event) => {
    console.error('🚨 Global error:', event.error);
    HDTickets.Dashboard.ui.showToast('An unexpected error occurred', 'error');
});

// Initialize unhandled promise rejection handler
window.addEventListener('unhandledrejection', (event) => {
    console.error('🚨 Unhandled promise rejection:', event.reason);
    HDTickets.Dashboard.ui.showToast('A network error occurred', 'error');
    event.preventDefault();
});

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = HDTickets.Dashboard;
}