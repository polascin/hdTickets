/**
 * Enhanced Loading States Manager
 * Provides comprehensive loading indicators and error handling for AJAX operations
 */

export class LoadingManager {
    constructor(options = {}) {
        this.options = {
            globalContainer: 'body',
            spinnerTemplate: this.getDefaultSpinnerTemplate(),
            skeletonTemplate: this.getDefaultSkeletonTemplate(),
            errorTemplate: this.getDefaultErrorTemplate(),
            successTemplate: this.getDefaultSuccessTemplate(),
            defaultTimeout: 30000, // 30 seconds
            autoHide: true,
            autoHideDelay: 3000,
            showProgressBar: true,
            animationDuration: 300,
            ...options
        };

        this.activeLoaders = new Map();
        this.progressBars = new Map();
        this.timeouts = new Map();
        this.loadingOverlays = new Map();

        this.init();
    }

    init() {
        this.injectStyles();
        this.bindGlobalEvents();
    }

    /**
     * Show loading state for a specific element or global
     */
    show(elementOrId, options = {}) {
        const config = {
            type: 'spinner', // spinner, skeleton, overlay
            message: 'Loading...',
            showProgress: false,
            timeout: this.options.defaultTimeout,
            position: 'center', // center, top, bottom, inline
            backdrop: false,
            ...options
        };

        const element = this.getElement(elementOrId);
        const loaderId = this.generateLoaderId(element);

        // Clear any existing loader
        this.hide(elementOrId);

        // Create loader
        const loader = this.createLoader(config);
        
        // Position and show loader
        this.positionLoader(element, loader, config);
        
        // Store reference
        this.activeLoaders.set(loaderId, {
            element,
            loader,
            config,
            startTime: Date.now()
        });

        // Set timeout if specified
        if (config.timeout > 0) {
            const timeoutId = setTimeout(() => {
                this.showError(elementOrId, {
                    message: 'Request timed out. Please try again.',
                    type: 'timeout'
                });
            }, config.timeout);
            
            this.timeouts.set(loaderId, timeoutId);
        }

        return loaderId;
    }

    /**
     * Hide loading state
     */
    hide(elementOrId, options = {}) {
        const element = this.getElement(elementOrId);
        const loaderId = this.generateLoaderId(element);
        const loaderData = this.activeLoaders.get(loaderId);

        if (!loaderData) return;

        const config = {
            showSuccess: false,
            successMessage: 'Completed successfully!',
            delay: 0,
            ...options
        };

        // Show success message if requested
        if (config.showSuccess) {
            this.showSuccess(elementOrId, {
                message: config.successMessage,
                duration: this.options.autoHideDelay
            });
        }

        // Remove with animation
        setTimeout(() => {
            this.removeLoader(loaderId);
        }, config.delay);
    }

    /**
     * Show error state
     */
    showError(elementOrId, options = {}) {
        const config = {
            message: 'An error occurred. Please try again.',
            type: 'error',
            duration: 0, // 0 means don't auto-hide
            retryCallback: null,
            dismissible: true,
            ...options
        };

        const element = this.getElement(elementOrId);
        const loaderId = this.generateLoaderId(element);

        // Remove existing loader
        this.removeLoader(loaderId);

        // Create error display
        const errorElement = this.createErrorDisplay(config);
        this.positionLoader(element, errorElement, { position: 'inline' });

        // Store reference
        this.activeLoaders.set(loaderId, {
            element,
            loader: errorElement,
            config: { ...config, type: 'error' },
            startTime: Date.now()
        });

        // Auto-hide if duration specified
        if (config.duration > 0) {
            setTimeout(() => {
                this.hide(elementOrId);
            }, config.duration);
        }
    }

    /**
     * Show success state
     */
    showSuccess(elementOrId, options = {}) {
        const config = {
            message: 'Success!',
            type: 'success',
            duration: this.options.autoHideDelay,
            icon: '✓',
            ...options
        };

        const element = this.getElement(elementOrId);
        const loaderId = this.generateLoaderId(element);

        // Remove existing loader
        this.removeLoader(loaderId);

        // Create success display
        const successElement = this.createSuccessDisplay(config);
        this.positionLoader(element, successElement, { position: 'inline' });

        // Store reference
        this.activeLoaders.set(loaderId, {
            element,
            loader: successElement,
            config: { ...config, type: 'success' },
            startTime: Date.now()
        });

        // Auto-hide
        if (config.duration > 0) {
            setTimeout(() => {
                this.hide(elementOrId);
            }, config.duration);
        }
    }

    /**
     * Update progress (0-100)
     */
    updateProgress(elementOrId, progress, message = null) {
        const element = this.getElement(elementOrId);
        const loaderId = this.generateLoaderId(element);
        const loaderData = this.activeLoaders.get(loaderId);

        if (!loaderData) return;

        const progressBar = loaderData.loader.querySelector('.loading-progress-bar');
        const progressText = loaderData.loader.querySelector('.loading-progress-text');
        const messageElement = loaderData.loader.querySelector('.loading-message');

        if (progressBar) {
            const fill = progressBar.querySelector('.loading-progress-fill');
            if (fill) {
                fill.style.width = `${Math.max(0, Math.min(100, progress))}%`;
            }
        }

        if (progressText) {
            progressText.textContent = `${Math.round(progress)}%`;
        }

        if (message && messageElement) {
            messageElement.textContent = message;
        }
    }

    /**
     * Wrap AJAX operations with loading states
     */
    async wrapAjax(elementOrId, ajaxPromise, options = {}) {
        const config = {
            showProgress: false,
            successMessage: 'Operation completed successfully',
            errorMessage: 'An error occurred',
            autoHideSuccess: true,
            ...options
        };

        const loaderId = this.show(elementOrId, config);

        try {
            const result = await ajaxPromise;
            
            this.hide(elementOrId, {
                showSuccess: config.autoHideSuccess,
                successMessage: config.successMessage
            });

            return result;
        } catch (error) {
            console.error('AJAX Error:', error);
            
            const errorMessage = this.extractErrorMessage(error);
            this.showError(elementOrId, {
                message: errorMessage || config.errorMessage,
                type: 'network',
                retryCallback: config.retryCallback
            });

            throw error;
        }
    }

    /**
     * Create skeleton loading for content areas
     */
    showSkeleton(elementOrId, options = {}) {
        const config = {
            lines: 3,
            height: '1rem',
            spacing: '0.5rem',
            showAvatar: false,
            showButton: false,
            animated: true,
            ...options
        };

        return this.show(elementOrId, {
            type: 'skeleton',
            ...config
        });
    }

    /**
     * Show overlay loading for entire sections
     */
    showOverlay(elementOrId, options = {}) {
        const config = {
            backdrop: true,
            message: 'Loading...',
            ...options
        };

        return this.show(elementOrId, {
            type: 'overlay',
            ...config
        });
    }

    // Private methods

    getElement(elementOrId) {
        if (typeof elementOrId === 'string') {
            return elementOrId === 'global' 
                ? document.querySelector(this.options.globalContainer)
                : document.getElementById(elementOrId) || document.querySelector(elementOrId);
        }
        return elementOrId;
    }

    generateLoaderId(element) {
        return element === document.querySelector(this.options.globalContainer) 
            ? 'global-loader' 
            : element.id || `loader-${Date.now()}-${Math.random()}`;
    }

    createLoader(config) {
        switch (config.type) {
            case 'skeleton':
                return this.createSkeletonLoader(config);
            case 'overlay':
                return this.createOverlayLoader(config);
            default:
                return this.createSpinnerLoader(config);
        }
    }

    createSpinnerLoader(config) {
        const wrapper = document.createElement('div');
        wrapper.className = 'loading-wrapper loading-spinner';
        
        let progressHtml = '';
        if (config.showProgress) {
            progressHtml = `
                <div class="loading-progress">
                    <div class="loading-progress-bar">
                        <div class="loading-progress-fill"></div>
                    </div>
                    <div class="loading-progress-text">0%</div>
                </div>
            `;
        }

        wrapper.innerHTML = `
            <div class="loading-content">
                <div class="loading-spinner-icon"></div>
                <div class="loading-message">${config.message}</div>
                ${progressHtml}
            </div>
        `;

        return wrapper;
    }

    createSkeletonLoader(config) {
        const wrapper = document.createElement('div');
        wrapper.className = 'loading-wrapper loading-skeleton';

        let avatarHtml = '';
        if (config.showAvatar) {
            avatarHtml = '<div class="skeleton-avatar"></div>';
        }

        let linesHtml = '';
        for (let i = 0; i < config.lines; i++) {
            const width = i === config.lines - 1 ? '70%' : '100%';
            linesHtml += `<div class="skeleton-line" style="width: ${width}; margin-bottom: ${config.spacing};"></div>`;
        }

        let buttonHtml = '';
        if (config.showButton) {
            buttonHtml = '<div class="skeleton-button"></div>';
        }

        wrapper.innerHTML = `
            <div class="skeleton-content">
                ${avatarHtml}
                <div class="skeleton-text">
                    ${linesHtml}
                </div>
                ${buttonHtml}
            </div>
        `;

        return wrapper;
    }

    createOverlayLoader(config) {
        const wrapper = document.createElement('div');
        wrapper.className = 'loading-wrapper loading-overlay';
        
        if (config.backdrop) {
            wrapper.classList.add('loading-backdrop');
        }

        wrapper.innerHTML = `
            <div class="loading-overlay-content">
                <div class="loading-spinner-icon"></div>
                <div class="loading-message">${config.message}</div>
            </div>
        `;

        return wrapper;
    }

    createErrorDisplay(config) {
        const wrapper = document.createElement('div');
        wrapper.className = 'loading-wrapper loading-error';

        let retryHtml = '';
        if (config.retryCallback) {
            retryHtml = `
                <button class="loading-retry-btn" onclick="(${config.retryCallback})()">
                    Try Again
                </button>
            `;
        }

        let dismissHtml = '';
        if (config.dismissible) {
            dismissHtml = `
                <button class="loading-dismiss-btn" onclick="this.closest('.loading-wrapper').remove()">
                    ×
                </button>
            `;
        }

        wrapper.innerHTML = `
            <div class="loading-error-content">
                <div class="loading-error-icon">⚠️</div>
                <div class="loading-error-message">${config.message}</div>
                <div class="loading-error-actions">
                    ${retryHtml}
                </div>
                ${dismissHtml}
            </div>
        `;

        return wrapper;
    }

    createSuccessDisplay(config) {
        const wrapper = document.createElement('div');
        wrapper.className = 'loading-wrapper loading-success';

        wrapper.innerHTML = `
            <div class="loading-success-content">
                <div class="loading-success-icon">${config.icon}</div>
                <div class="loading-success-message">${config.message}</div>
            </div>
        `;

        return wrapper;
    }

    positionLoader(element, loader, config) {
        if (!element) return;

        element.appendChild(loader);

        // Apply positioning
        switch (config.position) {
            case 'top':
                element.insertBefore(loader, element.firstChild);
                break;
            case 'bottom':
                element.appendChild(loader);
                break;
            case 'overlay':
                loader.style.position = 'absolute';
                loader.style.top = '0';
                loader.style.left = '0';
                loader.style.right = '0';
                loader.style.bottom = '0';
                loader.style.zIndex = '1000';
                break;
            default: // center, inline
                break;
        }

        // Animate in
        requestAnimationFrame(() => {
            loader.classList.add('loading-visible');
        });
    }

    removeLoader(loaderId) {
        const loaderData = this.activeLoaders.get(loaderId);
        if (!loaderData) return;

        const { loader } = loaderData;

        // Clear timeout
        const timeoutId = this.timeouts.get(loaderId);
        if (timeoutId) {
            clearTimeout(timeoutId);
            this.timeouts.delete(loaderId);
        }

        // Animate out
        loader.classList.remove('loading-visible');
        loader.classList.add('loading-hiding');

        setTimeout(() => {
            if (loader.parentNode) {
                loader.parentNode.removeChild(loader);
            }
        }, this.options.animationDuration);

        this.activeLoaders.delete(loaderId);
    }

    extractErrorMessage(error) {
        if (typeof error === 'string') {
            return error;
        }

        if (error.response) {
            // Axios-style error
            const data = error.response.data;
            if (data.message) return data.message;
            if (data.error) return data.error;
            if (typeof data === 'string') return data;
            
            return `Request failed with status ${error.response.status}`;
        }

        if (error.message) {
            return error.message;
        }

        if (error.toString() !== '[object Object]') {
            return error.toString();
        }

        return 'An unexpected error occurred';
    }

    bindGlobalEvents() {
        // Handle AJAX errors globally
        document.addEventListener('ajaxError', (event) => {
            if (event.detail && event.detail.elementId) {
                this.showError(event.detail.elementId, {
                    message: this.extractErrorMessage(event.detail.error)
                });
            }
        });

        // Handle page unload
        window.addEventListener('beforeunload', () => {
            this.activeLoaders.forEach((_, loaderId) => {
                this.removeLoader(loaderId);
            });
        });
    }

    injectStyles() {
        const styleId = 'loading-manager-styles';
        if (document.getElementById(styleId)) return;

        const style = document.createElement('style');
        style.id = styleId;
        style.textContent = `
            .loading-wrapper {
                opacity: 0;
                transition: opacity ${this.options.animationDuration}ms ease;
                pointer-events: none;
            }

            .loading-wrapper.loading-visible {
                opacity: 1;
                pointer-events: auto;
            }

            .loading-wrapper.loading-hiding {
                opacity: 0;
            }

            .loading-spinner {
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 2rem;
            }

            .loading-content {
                text-align: center;
                max-width: 200px;
            }

            .loading-spinner-icon {
                width: 2rem;
                height: 2rem;
                border: 3px solid #f3f4f6;
                border-top: 3px solid #3b82f6;
                border-radius: 50%;
                animation: loading-spin 1s linear infinite;
                margin: 0 auto 1rem;
            }

            @keyframes loading-spin {
                to { transform: rotate(360deg); }
            }

            .loading-message {
                color: #6b7280;
                font-size: 0.875rem;
                margin-bottom: 1rem;
            }

            .loading-progress {
                margin-top: 1rem;
            }

            .loading-progress-bar {
                width: 100%;
                height: 4px;
                background-color: #e5e7eb;
                border-radius: 2px;
                overflow: hidden;
                margin-bottom: 0.5rem;
            }

            .loading-progress-fill {
                height: 100%;
                background-color: #3b82f6;
                transition: width 0.3s ease;
                width: 0%;
            }

            .loading-progress-text {
                font-size: 0.75rem;
                color: #6b7280;
            }

            .loading-skeleton {
                padding: 1rem;
            }

            .skeleton-content {
                display: flex;
                gap: 1rem;
            }

            .skeleton-avatar {
                width: 3rem;
                height: 3rem;
                border-radius: 50%;
                background: linear-gradient(90deg, #f3f4f6 25%, #e5e7eb 50%, #f3f4f6 75%);
                background-size: 200% 100%;
                animation: skeleton-loading 1.5s infinite;
            }

            .skeleton-text {
                flex: 1;
            }

            .skeleton-line {
                height: 1rem;
                background: linear-gradient(90deg, #f3f4f6 25%, #e5e7eb 50%, #f3f4f6 75%);
                background-size: 200% 100%;
                border-radius: 0.25rem;
                animation: skeleton-loading 1.5s infinite;
            }

            .skeleton-button {
                width: 6rem;
                height: 2rem;
                background: linear-gradient(90deg, #f3f4f6 25%, #e5e7eb 50%, #f3f4f6 75%);
                background-size: 200% 100%;
                border-radius: 0.375rem;
                animation: skeleton-loading 1.5s infinite;
                margin-top: 1rem;
            }

            @keyframes skeleton-loading {
                0% { background-position: 200% 0; }
                100% { background-position: -200% 0; }
            }

            .loading-overlay {
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 1000;
            }

            .loading-overlay.loading-backdrop {
                background-color: rgba(255, 255, 255, 0.8);
            }

            .loading-overlay-content {
                text-align: center;
                padding: 2rem;
                background: white;
                border-radius: 0.5rem;
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            }

            .loading-error {
                padding: 1rem;
                border: 1px solid #fecaca;
                border-radius: 0.5rem;
                background-color: #fef2f2;
                position: relative;
            }

            .loading-error-content {
                display: flex;
                align-items: center;
                gap: 0.75rem;
            }

            .loading-error-icon {
                font-size: 1.25rem;
                flex-shrink: 0;
            }

            .loading-error-message {
                flex: 1;
                color: #dc2626;
                font-size: 0.875rem;
            }

            .loading-error-actions {
                flex-shrink: 0;
            }

            .loading-retry-btn {
                background: #dc2626;
                color: white;
                border: none;
                padding: 0.25rem 0.75rem;
                border-radius: 0.25rem;
                font-size: 0.75rem;
                cursor: pointer;
            }

            .loading-retry-btn:hover {
                background: #b91c1c;
            }

            .loading-dismiss-btn {
                position: absolute;
                top: 0.5rem;
                right: 0.5rem;
                background: none;
                border: none;
                font-size: 1.25rem;
                cursor: pointer;
                color: #6b7280;
                line-height: 1;
                padding: 0;
                width: 1.5rem;
                height: 1.5rem;
            }

            .loading-success {
                padding: 1rem;
                border: 1px solid #bbf7d0;
                border-radius: 0.5rem;
                background-color: #f0fdf4;
            }

            .loading-success-content {
                display: flex;
                align-items: center;
                gap: 0.75rem;
            }

            .loading-success-icon {
                color: #059669;
                font-weight: bold;
                font-size: 1.25rem;
                flex-shrink: 0;
            }

            .loading-success-message {
                color: #059669;
                font-size: 0.875rem;
                flex: 1;
            }
        `;

        document.head.appendChild(style);
    }

    getDefaultSpinnerTemplate() {
        return '<div class="loading-spinner-icon"></div>';
    }

    getDefaultSkeletonTemplate() {
        return '<div class="skeleton-line"></div>';
    }

    getDefaultErrorTemplate() {
        return '<div class="loading-error-content">{{message}}</div>';
    }

    getDefaultSuccessTemplate() {
        return '<div class="loading-success-content">{{message}}</div>';
    }

    // Public API methods

    /**
     * Clear all active loaders
     */
    clearAll() {
        this.activeLoaders.forEach((_, loaderId) => {
            this.removeLoader(loaderId);
        });
    }

    /**
     * Get active loader count
     */
    getActiveCount() {
        return this.activeLoaders.size;
    }

    /**
     * Check if element has active loader
     */
    isLoading(elementOrId) {
        const element = this.getElement(elementOrId);
        const loaderId = this.generateLoaderId(element);
        return this.activeLoaders.has(loaderId);
    }
}

// Create global instance
window.LoadingManager = new LoadingManager();

export default LoadingManager;
