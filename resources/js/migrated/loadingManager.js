/**
 * Loading States and Error Handling Manager for HD Tickets
 * Comprehensive system for managing loading states, error boundaries, and progressive loading
 */

class LoadingManager {
    constructor() {
        this.loadingStates = new Map();
        this.errorBoundaries = new Map();
        this.retryCallbacks = new Map();
        this.loadingOverlay = null;
        this.offlineDetection = new OfflineDetection();
        this.init();
    }

    init() {
        this.setupGlobalErrorHandling();
        this.setupProgressiveImageLoading();
        this.setupInfiniteScrollLoading();
        this.bindEvents();
    }

    /**
     * Show loading state for a specific component or page
     * @param {string} identifier - Unique identifier for the loading state
     * @param {Object} options - Configuration options
     */
    showLoading(identifier, options = {}) {
        const config = {
            type: 'spinner', // 'spinner', 'skeleton', 'overlay', 'progress'
            message: 'Loading...',
            progress: null,
            target: null,
            overlay: false,
            ...options
        };

        this.loadingStates.set(identifier, {
            ...config,
            startTime: Date.now(),
            isVisible: true
        });

        this.renderLoading(identifier, config);
        this.announceLoading(config.message);
    }

    /**
     * Hide loading state
     * @param {string} identifier - Unique identifier for the loading state
     */
    hideLoading(identifier) {
        const loadingState = this.loadingStates.get(identifier);
        if (!loadingState) return;

        this.removeLoading(identifier);
        this.loadingStates.delete(identifier);
        this.announceLoadingComplete();
    }

    /**
     * Update loading progress
     * @param {string} identifier - Loading state identifier
     * @param {number} progress - Progress percentage (0-100)
     * @param {string} message - Optional progress message
     */
    updateProgress(identifier, progress, message = null) {
        const loadingState = this.loadingStates.get(identifier);
        if (!loadingState) return;

        loadingState.progress = Math.max(0, Math.min(100, progress));
        if (message) loadingState.message = message;

        this.updateProgressBar(identifier, loadingState);
    }

    /**
     * Show error boundary with retry functionality
     * @param {string} identifier - Unique identifier for the error
     * @param {Object} error - Error configuration
     */
    showError(identifier, error = {}) {
        const config = {
            type: 'general', // 'network', 'permission', 'not-found', 'general'
            title: 'Something went wrong',
            message: 'An unexpected error occurred. Please try again.',
            showRetry: true,
            showSupport: true,
            target: null,
            fullPage: false,
            ...error
        };

        this.errorBoundaries.set(identifier, config);
        this.renderError(identifier, config);
        this.announceError(config.title);
    }

    /**
     * Hide error boundary
     * @param {string} identifier - Error identifier
     */
    hideError(identifier) {
        const errorBoundary = this.errorBoundaries.get(identifier);
        if (!errorBoundary) return;

        this.removeError(identifier);
        this.errorBoundaries.delete(identifier);
        this.retryCallbacks.delete(identifier);
    }

    /**
     * Set retry callback for error boundary
     * @param {string} identifier - Error identifier
     * @param {Function} callback - Retry callback function
     */
    setRetryCallback(identifier, callback) {
        this.retryCallbacks.set(identifier, callback);
    }

    /**
     * Show skeleton loading for dashboard components
     * @param {string} target - CSS selector for target element
     * @param {string} type - Skeleton type ('stats', 'tickets', 'chart', 'table')
     */
    showSkeleton(target, type = 'default') {
        const targetElement = document.querySelector(target);
        if (!targetElement) return;

        const skeletonHTML = this.generateSkeletonHTML(type);
        targetElement.innerHTML = skeletonHTML;
        targetElement.classList.add('is-loading');
    }

    /**
     * Hide skeleton and restore content
     * @param {string} target - CSS selector for target element
     * @param {string} content - Content to restore
     */
    hideSkeleton(target, content = '') {
        const targetElement = document.querySelector(target);
        if (!targetElement) return;

        targetElement.classList.remove('is-loading');
        if (content) {
            targetElement.innerHTML = content;
        }
    }

    /**
     * Render loading state based on type
     * @private
     */
    renderLoading(identifier, config) {
        if (config.overlay) {
            this.showOverlay(identifier, config);
        } else if (config.target) {
            this.showComponentLoading(config.target, config);
        } else {
            this.showInlineLoading(identifier, config);
        }
    }

    /**
     * Show full-page or component overlay
     * @private
     */
    showOverlay(identifier, config) {
        const overlay = document.createElement('div');
        overlay.className = `loading-overlay ${config.fullPage ? '' : 'loading-overlay--component'}`;
        overlay.id = `loading-${identifier}`;
        overlay.setAttribute('role', 'progressbar');
        overlay.setAttribute('aria-label', config.message);

        const content = document.createElement('div');
        content.className = 'loading-content';

        // Add spinner
        const spinner = this.createSpinner(config.type);
        content.appendChild(spinner);

        // Add message
        const message = document.createElement('h3');
        message.textContent = config.message;
        content.appendChild(message);

        // Add progress bar if needed
        if (config.progress !== null) {
            const progressBar = this.createProgressBar(identifier, config.progress);
            content.appendChild(progressBar);
        }

        overlay.appendChild(content);

        if (config.target) {
            const target = document.querySelector(config.target);
            if (target) {
                target.style.position = 'relative';
                target.appendChild(overlay);
            }
        } else {
            document.body.appendChild(overlay);
        }

        this.loadingOverlay = overlay;
    }

    /**
     * Show component-specific loading
     * @private
     */
    showComponentLoading(target, config) {
        const targetElement = document.querySelector(target);
        if (!targetElement) return;

        targetElement.classList.add('is-loading');
        
        if (config.type === 'skeleton') {
            this.showSkeleton(target, config.skeletonType || 'default');
        } else {
            const loadingElement = this.createLoadingElement(config);
            targetElement.appendChild(loadingElement);
        }
    }

    /**
     * Create spinner element
     * @private
     */
    createSpinner(type = 'spinner') {
        const spinner = document.createElement('div');
        
        switch (type) {
            case 'dots':
                spinner.className = 'spinner-dots';
                spinner.innerHTML = '<div></div><div></div><div></div><div></div>';
                break;
            case 'pulse':
                spinner.className = 'spinner-pulse';
                break;
            default:
                spinner.className = 'spinner spinner--lg';
                break;
        }

        return spinner;
    }

    /**
     * Create progress bar element
     * @private
     */
    createProgressBar(identifier, progress = 0) {
        const progressContainer = document.createElement('div');
        progressContainer.className = 'progress-bar';
        progressContainer.setAttribute('role', 'progressbar');
        progressContainer.setAttribute('aria-valuenow', progress);
        progressContainer.setAttribute('aria-valuemin', '0');
        progressContainer.setAttribute('aria-valuemax', '100');

        const progressFill = document.createElement('div');
        progressFill.className = 'progress-bar__fill';
        progressFill.style.width = `${progress}%`;
        progressFill.id = `progress-${identifier}`;

        progressContainer.appendChild(progressFill);
        return progressContainer;
    }

    /**
     * Update progress bar
     * @private
     */
    updateProgressBar(identifier, loadingState) {
        const progressFill = document.getElementById(`progress-${identifier}`);
        if (progressFill) {
            progressFill.style.width = `${loadingState.progress}%`;
            progressFill.parentElement.setAttribute('aria-valuenow', loadingState.progress);
        }

        // Update message if present
        const overlay = document.getElementById(`loading-${identifier}`);
        if (overlay && loadingState.message) {
            const message = overlay.querySelector('h3');
            if (message) {
                message.textContent = loadingState.message;
            }
        }
    }

    /**
     * Generate skeleton HTML based on type
     * @private
     */
    generateSkeletonHTML(type) {
        const skeletonTemplates = {
            stats: `
                <div class="skeleton-stats-grid">
                    ${Array(4).fill().map(() => `
                        <div class="skeleton-stat-card">
                            <div class="skeleton skeleton--title"></div>
                            <div class="skeleton skeleton--value"></div>
                            <div class="skeleton skeleton--change"></div>
                        </div>
                    `).join('')}
                </div>
            `,
            tickets: `
                <div class="skeleton-ticket-list">
                    ${Array(5).fill().map(() => `
                        <div class="skeleton-ticket-item">
                            <div class="skeleton skeleton--image"></div>
                            <div class="skeleton-ticket-content">
                                <div class="skeleton skeleton--title"></div>
                                <div class="skeleton skeleton--text"></div>
                                <div class="skeleton skeleton--text"></div>
                            </div>
                            <div class="skeleton-ticket-price">
                                <div class="skeleton skeleton--price"></div>
                                <div class="skeleton skeleton--button"></div>
                            </div>
                        </div>
                    `).join('')}
                </div>
            `,
            chart: `
                <div class="skeleton-widget">
                    <div class="skeleton-widget-header">
                        <div class="skeleton skeleton--title"></div>
                        <div class="skeleton skeleton--button"></div>
                    </div>
                    <div class="skeleton skeleton--chart"></div>
                </div>
            `,
            table: `
                <table class="skeleton-table">
                    <thead>
                        <tr>
                            ${Array(4).fill().map(() => `
                                <th><div class="skeleton skeleton--table-header"></div></th>
                            `).join('')}
                        </tr>
                    </thead>
                    <tbody>
                        ${Array(8).fill().map(() => `
                            <tr>
                                ${Array(4).fill().map(() => `
                                    <td><div class="skeleton skeleton--table-row"></div></td>
                                `).join('')}
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            `,
            default: `
                <div class="skeleton-widget">
                    <div class="skeleton skeleton--title"></div>
                    <div class="skeleton skeleton--paragraph"></div>
                    <div class="skeleton skeleton--paragraph"></div>
                    <div class="skeleton skeleton--paragraph"></div>
                </div>
            `
        };

        return skeletonTemplates[type] || skeletonTemplates.default;
    }

    /**
     * Render error boundary
     * @private
     */
    renderError(identifier, config) {
        const errorContainer = document.createElement('div');
        errorContainer.className = `error-boundary error-boundary--${config.type} ${config.fullPage ? 'error-boundary--full-page' : ''}`;
        errorContainer.id = `error-${identifier}`;
        errorContainer.setAttribute('role', 'alert');

        const errorContent = document.createElement('div');
        errorContent.className = 'error-content';

        // Error icon
        const icon = this.createErrorIcon(config.type);
        errorContent.appendChild(icon);

        // Error title
        const title = document.createElement('h3');
        title.className = 'error-title';
        title.textContent = config.title;
        errorContent.appendChild(title);

        // Error message
        const message = document.createElement('p');
        message.className = 'error-message';
        message.textContent = config.message;
        errorContent.appendChild(message);

        // Action buttons
        const actions = document.createElement('div');
        actions.className = 'error-actions';

        if (config.showRetry) {
            const retryBtn = this.createButton('Try Again', 'btn btn--primary', () => {
                this.handleRetry(identifier);
            });
            actions.appendChild(retryBtn);
        }

        if (config.showSupport) {
            const supportBtn = this.createButton('Contact Support', 'btn btn--secondary', () => {
                this.handleSupport();
            });
            actions.appendChild(supportBtn);
        }

        errorContent.appendChild(actions);
        errorContainer.appendChild(errorContent);

        if (config.target) {
            const target = document.querySelector(config.target);
            if (target) {
                target.innerHTML = '';
                target.appendChild(errorContainer);
            }
        } else {
            document.body.appendChild(errorContainer);
        }
    }

    /**
     * Create error icon based on type
     * @private
     */
    createErrorIcon(type) {
        const icon = document.createElement('div');
        icon.className = 'error-icon';

        const iconSVGs = {
            network: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 18.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>`,
            permission: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>`,
            'not-found': `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-3-8a9 9 0 11-9 9 9 9 0 019-9z"></path></svg>`,
            general: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`
        };

        icon.innerHTML = iconSVGs[type] || iconSVGs.general;
        return icon;
    }

    /**
     * Create button element
     * @private
     */
    createButton(text, className, onClick) {
        const button = document.createElement('button');
        button.textContent = text;
        button.className = className;
        button.onclick = onClick;
        return button;
    }

    /**
     * Handle retry action
     * @private
     */
    handleRetry(identifier) {
        const callback = this.retryCallbacks.get(identifier);
        if (callback) {
            this.hideError(identifier);
            callback();
        }
    }

    /**
     * Handle support action
     * @private
     */
    handleSupport() {
        // Redirect to support or show contact modal
        window.location.href = '/support';
    }

    /**
     * Remove loading state
     * @private
     */
    removeLoading(identifier) {
        const overlay = document.getElementById(`loading-${identifier}`);
        if (overlay) {
            overlay.remove();
        }

        // Remove is-loading class from components
        document.querySelectorAll('.is-loading').forEach(element => {
            element.classList.remove('is-loading');
        });
    }

    /**
     * Remove error boundary
     * @private
     */
    removeError(identifier) {
        const errorElement = document.getElementById(`error-${identifier}`);
        if (errorElement) {
            errorElement.remove();
        }
    }

    /**
     * Setup progressive image loading
     * @private
     */
    setupProgressiveImageLoading() {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    const src = img.dataset.src;
                    
                    if (src) {
                        img.src = src;
                        img.classList.add('progressive-image__image--loading');
                        
                        img.onload = () => {
                            img.classList.remove('progressive-image__image--loading');
                            img.classList.add('progressive-image__image--loaded');
                            const placeholder = img.previousElementSibling;
                            if (placeholder && placeholder.classList.contains('progressive-image__placeholder')) {
                                placeholder.remove();
                            }
                        };
                        
                        img.onerror = () => {
                            img.classList.add('progressive-image__image--error');
                        };
                        
                        observer.unobserve(img);
                    }
                }
            });
        });

        // Observe all progressive images
        document.querySelectorAll('[data-src]').forEach(img => {
            imageObserver.observe(img);
        });
    }

    /**
     * Setup infinite scroll loading
     * @private
     */
    setupInfiniteScrollLoading() {
        const scrollObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const target = entry.target;
                    const callback = target.dataset.loadCallback;
                    
                    if (callback && window[callback]) {
                        window[callback]();
                    }
                    
                    // Dispatch custom event
                    target.dispatchEvent(new CustomEvent('infinite-scroll-load'));
                }
            });
        }, {
            rootMargin: '100px'
        });

        document.querySelectorAll('.lazy-load-target').forEach(target => {
            scrollObserver.observe(target);
        });
    }

    /**
     * Setup global error handling
     * @private
     */
    setupGlobalErrorHandling() {
        window.addEventListener('error', (event) => {
            console.error('Global error:', event.error);
            this.handleGlobalError(event.error);
        });

        window.addEventListener('unhandledrejection', (event) => {
            console.error('Unhandled promise rejection:', event.reason);
            this.handleGlobalError(event.reason);
        });
    }

    /**
     * Handle global errors
     * @private
     */
    handleGlobalError(error) {
        if (this.isNetworkError(error)) {
            this.showError('global-network', {
                type: 'network',
                title: 'Connection Problem',
                message: 'Unable to connect to our servers. Please check your internet connection and try again.',
                fullPage: true
            });
        } else {
            this.showError('global-error', {
                type: 'general',
                title: 'Something went wrong',
                message: 'An unexpected error occurred. Please refresh the page and try again.',
                fullPage: true
            });
        }
    }

    /**
     * Check if error is network-related
     * @private
     */
    isNetworkError(error) {
        const networkIndicators = [
            'NetworkError',
            'fetch',
            'network',
            'ERR_NETWORK',
            'ERR_INTERNET_DISCONNECTED'
        ];
        
        const errorString = error.toString().toLowerCase();
        return networkIndicators.some(indicator => 
            errorString.includes(indicator.toLowerCase())
        );
    }

    /**
     * Announce loading state to screen readers
     * @private
     */
    announceLoading(message) {
        const announcement = document.createElement('div');
        announcement.className = 'sr-loading-announcement';
        announcement.setAttribute('aria-live', 'polite');
        announcement.textContent = message;
        document.body.appendChild(announcement);
        
        setTimeout(() => {
            announcement.remove();
        }, 1000);
    }

    /**
     * Announce loading completion to screen readers
     * @private
     */
    announceLoadingComplete() {
        this.announceLoading('Content loaded');
    }

    /**
     * Announce error to screen readers
     * @private
     */
    announceError(message) {
        const announcement = document.createElement('div');
        announcement.className = 'sr-loading-announcement';
        announcement.setAttribute('aria-live', 'assertive');
        announcement.textContent = `Error: ${message}`;
        document.body.appendChild(announcement);
        
        setTimeout(() => {
            announcement.remove();
        }, 1000);
    }

    /**
     * Bind events
     * @private
     */
    bindEvents() {
        // Handle offline/online events
        window.addEventListener('offline', () => {
            this.showError('offline', {
                type: 'network',
                title: 'No Internet Connection',
                message: 'You are currently offline. Some features may not be available.',
                showRetry: false,
                fullPage: false
            });
        });

        window.addEventListener('online', () => {
            this.hideError('offline');
            this.announceLoading('Connection restored');
        });
    }

    /**
     * Public API for Alpine.js components
     */
    getAlpineData() {
        return {
            showLoading: this.showLoading.bind(this),
            hideLoading: this.hideLoading.bind(this),
            showError: this.showError.bind(this),
            hideError: this.hideError.bind(this),
            showSkeleton: this.showSkeleton.bind(this),
            hideSkeleton: this.hideSkeleton.bind(this),
            updateProgress: this.updateProgress.bind(this),
            setRetryCallback: this.setRetryCallback.bind(this)
        };
    }
}

/**
 * Offline Detection Manager
 */
class OfflineDetection {
    constructor() {
        this.isOnline = navigator.onLine;
        this.callbacks = new Set();
        this.init();
    }

    init() {
        window.addEventListener('online', () => {
            this.isOnline = true;
            this.notifyCallbacks('online');
        });

        window.addEventListener('offline', () => {
            this.isOnline = false;
            this.notifyCallbacks('offline');
        });

        // Periodic connectivity check
        setInterval(() => {
            this.checkConnectivity();
        }, 30000); // Check every 30 seconds
    }

    async checkConnectivity() {
        try {
            const response = await fetch('/api/health', {
                method: 'HEAD',
                cache: 'no-cache'
            });
            
            const wasOnline = this.isOnline;
            this.isOnline = response.ok;
            
            if (wasOnline !== this.isOnline) {
                this.notifyCallbacks(this.isOnline ? 'online' : 'offline');
            }
        } catch (error) {
            const wasOnline = this.isOnline;
            this.isOnline = false;
            
            if (wasOnline) {
                this.notifyCallbacks('offline');
            }
        }
    }

    onStatusChange(callback) {
        this.callbacks.add(callback);
    }

    offStatusChange(callback) {
        this.callbacks.delete(callback);
    }

    notifyCallbacks(status) {
        this.callbacks.forEach(callback => {
            try {
                callback(status, this.isOnline);
            } catch (error) {
                console.error('Error in offline detection callback:', error);
            }
        });
    }
}

// Initialize global loading manager
const loadingManager = new LoadingManager();

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { LoadingManager, OfflineDetection };
}

// Make available globally
window.LoadingManager = LoadingManager;
window.loadingManager = loadingManager;
