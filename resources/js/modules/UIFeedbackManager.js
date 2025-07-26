/**
 * UI Feedback and Loading State Manager
 * Handles loading states, notifications, toasts, and user feedback
 */
class UIFeedbackManager {
    constructor(options = {}) {
        this.options = {
            toastContainer: '.toast-container',
            loadingOverlay: '.loading-overlay',
            defaultToastDuration: 5000,
            enableSounds: false,
            enableVibration: false,
            progressBarAnimation: true,
            ...options
        };

        this.activeToasts = new Map();
        this.loadingStates = new Map();
        this.progressBars = new Map();
        
        this.init();
    }

    init() {
        this.createToastContainer();
        this.createLoadingOverlay();
        this.setupGlobalErrorHandler();
        this.setupProgressBars();
    }

    createToastContainer() {
        if (!document.querySelector(this.options.toastContainer)) {
            const container = document.createElement('div');
            container.className = 'toast-container fixed top-4 right-4 z-50 space-y-2';
            container.style.maxWidth = '420px';
            document.body.appendChild(container);
        }
    }

    createLoadingOverlay() {
        if (!document.querySelector(this.options.loadingOverlay)) {
            const overlay = document.createElement('div');
            overlay.className = 'loading-overlay fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center';
            overlay.style.display = 'none';
            overlay.innerHTML = `
                <div class="bg-white dark:bg-slate-800 rounded-lg p-6 max-w-sm mx-4">
                    <div class="flex items-center space-x-4">
                        <div class="spinner"></div>
                        <div>
                            <div class="loading-title text-lg font-medium text-gray-900 dark:text-white">Loading...</div>
                            <div class="loading-message text-sm text-gray-500 dark:text-gray-400 mt-1"></div>
                        </div>
                    </div>
                    <div class="loading-progress mt-4" style="display: none;">
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                        </div>
                        <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mt-1">
                            <span class="progress-text">0%</span>
                            <span class="progress-eta"></span>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(overlay);
        }
    }

    setupGlobalErrorHandler() {
        // Handle unhandled promise rejections
        window.addEventListener('unhandledrejection', (event) => {
            console.error('Unhandled promise rejection:', event.reason);
            this.showToast('An unexpected error occurred', 'error');
        });

        // Handle JavaScript errors
        window.addEventListener('error', (event) => {
            console.error('JavaScript error:', event.error);
            this.showToast('A system error occurred', 'error');
        });
    }

    setupProgressBars() {
        // Setup existing progress bars on the page
        document.querySelectorAll('[data-progress-bar]').forEach(progressBar => {
            const id = progressBar.dataset.progressBar;
            this.progressBars.set(id, {
                element: progressBar,
                value: 0,
                max: 100
            });
        });
    }

    // Toast Notifications
    showToast(message, type = 'info', options = {}) {
        const config = {
            duration: this.options.defaultToastDuration,
            persistent: false,
            actions: [],
            dismissible: true,
            ...options
        };

        const toastId = `toast_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
        const toast = this.createToastElement(toastId, message, type, config);
        
        const container = document.querySelector(this.options.toastContainer);
        container.appendChild(toast);
        
        // Animate in
        requestAnimationFrame(() => {
            toast.classList.add('translate-x-0', 'opacity-100');
            toast.classList.remove('translate-x-full', 'opacity-0');
        });

        // Store toast reference
        this.activeToasts.set(toastId, {
            element: toast,
            type,
            config,
            timestamp: Date.now()
        });

        // Auto-dismiss if not persistent
        if (!config.persistent && config.duration > 0) {
            setTimeout(() => {
                this.dismissToast(toastId);
            }, config.duration);
        }

        // Play sound if enabled
        if (this.options.enableSounds) {
            this.playNotificationSound(type);
        }

        // Vibrate if enabled and supported
        if (this.options.enableVibration && navigator.vibrate) {
            const pattern = this.getVibrationPattern(type);
            navigator.vibrate(pattern);
        }

        return toastId;
    }

    createToastElement(id, message, type, config) {
        const toast = document.createElement('div');
        toast.id = id;
        toast.className = `toast transform transition-all duration-300 translate-x-full opacity-0 max-w-sm w-full bg-white dark:bg-slate-800 shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden`;
        
        const typeClasses = {
            success: 'border-l-4 border-green-400',
            error: 'border-l-4 border-red-400',
            warning: 'border-l-4 border-yellow-400',
            info: 'border-l-4 border-blue-400'
        };
        
        const typeIcons = {
            success: 'fas fa-check-circle text-green-400',
            error: 'fas fa-exclamation-circle text-red-400',
            warning: 'fas fa-exclamation-triangle text-yellow-400',
            info: 'fas fa-info-circle text-blue-400'
        };

        toast.classList.add(...typeClasses[type].split(' '));

        const actionsHTML = config.actions.map(action => 
            `<button type="button" class="toast-action bg-white dark:bg-slate-700 px-3 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-slate-600 rounded-md border border-gray-300 dark:border-slate-600 transition-colors" data-action="${action.id}">${action.label}</button>`
        ).join('');

        toast.innerHTML = `
            <div class="p-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <i class="${typeIcons[type]}"></i>
                    </div>
                    <div class="ml-3 w-0 flex-1">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">${message}</p>
                        ${config.actions.length > 0 ? `<div class="mt-3 flex space-x-2">${actionsHTML}</div>` : ''}
                    </div>
                    ${config.dismissible ? `
                        <div class="ml-4 flex-shrink-0 flex">
                            <button type="button" class="toast-dismiss bg-white dark:bg-slate-800 rounded-md inline-flex text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 focus:outline-none">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    ` : ''}
                </div>
            </div>
        `;

        // Add event listeners
        if (config.dismissible) {
            toast.querySelector('.toast-dismiss').addEventListener('click', () => {
                this.dismissToast(id);
            });
        }

        config.actions.forEach(action => {
            const button = toast.querySelector(`[data-action="${action.id}"]`);
            if (button) {
                button.addEventListener('click', (e) => {
                    action.callback(e, id);
                    if (action.dismissOnClick !== false) {
                        this.dismissToast(id);
                    }
                });
            }
        });

        return toast;
    }

    dismissToast(toastId) {
        const toast = this.activeToasts.get(toastId);
        if (!toast) return;

        const element = toast.element;
        
        // Animate out
        element.classList.add('translate-x-full', 'opacity-0');
        element.classList.remove('translate-x-0', 'opacity-100');

        setTimeout(() => {
            if (element.parentNode) {
                element.parentNode.removeChild(element);
            }
            this.activeToasts.delete(toastId);
        }, 300);
    }

    dismissAllToasts() {
        this.activeToasts.forEach((toast, id) => {
            this.dismissToast(id);
        });
    }

    // Loading States
    showLoading(message = 'Loading...', options = {}) {
        const config = {
            title: 'Loading...',
            showProgress: false,
            allowCancel: false,
            ...options
        };

        const overlay = document.querySelector(this.options.loadingOverlay);
        const titleElement = overlay.querySelector('.loading-title');
        const messageElement = overlay.querySelector('.loading-message');
        const progressElement = overlay.querySelector('.loading-progress');

        titleElement.textContent = config.title;
        messageElement.textContent = message;
        
        if (config.showProgress) {
            progressElement.style.display = 'block';
            this.updateProgress('loading', 0);
        } else {
            progressElement.style.display = 'none';
        }

        overlay.style.display = 'flex';
        document.body.style.overflow = 'hidden';

        return 'loading';
    }

    hideLoading() {
        const overlay = document.querySelector(this.options.loadingOverlay);
        overlay.style.display = 'none';
        document.body.style.overflow = '';
    }

    updateProgress(progressId, value, options = {}) {
        const config = {
            max: 100,
            showETA: false,
            ...options
        };

        // Update global loading progress
        if (progressId === 'loading') {
            const overlay = document.querySelector(this.options.loadingOverlay);
            const progressBar = overlay.querySelector('.bg-blue-600');
            const progressText = overlay.querySelector('.progress-text');
            const progressETA = overlay.querySelector('.progress-eta');

            if (progressBar) {
                const percentage = Math.min(100, Math.max(0, (value / config.max) * 100));
                progressBar.style.width = `${percentage}%`;
                
                if (progressText) {
                    progressText.textContent = `${Math.round(percentage)}%`;
                }
                
                if (config.showETA && progressETA && config.eta) {
                    progressETA.textContent = config.eta;
                }
            }
            return;
        }

        // Update specific progress bar
        const progress = this.progressBars.get(progressId);
        if (!progress) return;

        const percentage = Math.min(100, Math.max(0, (value / config.max) * 100));
        const progressBar = progress.element.querySelector('.progress-bar');
        const progressText = progress.element.querySelector('.progress-text');

        if (progressBar) {
            if (this.options.progressBarAnimation) {
                progressBar.style.transition = 'width 0.3s ease';
            }
            progressBar.style.width = `${percentage}%`;
        }

        if (progressText) {
            progressText.textContent = `${Math.round(percentage)}%`;
        }

        progress.value = value;
    }

    // Button Loading States
    setButtonLoading(button, loading = true, originalText = null) {
        if (typeof button === 'string') {
            button = document.querySelector(button);
        }

        if (!button) return;

        if (loading) {
            if (!originalText) {
                originalText = button.textContent || button.innerHTML;
            }
            button.dataset.originalContent = originalText;
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Loading...';
            button.classList.add('opacity-75', 'cursor-not-allowed');
        } else {
            button.disabled = false;
            button.innerHTML = button.dataset.originalContent || button.innerHTML;
            button.classList.remove('opacity-75', 'cursor-not-allowed');
            delete button.dataset.originalContent;
        }
    }

    // Form Validation Feedback
    showFieldError(fieldSelector, message) {
        const field = document.querySelector(fieldSelector);
        if (!field) return;

        // Remove existing error
        this.clearFieldError(fieldSelector);

        // Add error class
        field.classList.add('border-red-500', 'focus:border-red-500', 'focus:ring-red-500');

        // Create error message
        const errorElement = document.createElement('div');
        errorElement.className = 'field-error text-red-600 text-sm mt-1';
        errorElement.textContent = message;
        
        field.parentNode.appendChild(errorElement);
    }

    clearFieldError(fieldSelector) {
        const field = document.querySelector(fieldSelector);
        if (!field) return;

        // Remove error classes
        field.classList.remove('border-red-500', 'focus:border-red-500', 'focus:ring-red-500');

        // Remove error message
        const errorElement = field.parentNode.querySelector('.field-error');
        if (errorElement) {
            errorElement.remove();
        }
    }

    showFieldSuccess(fieldSelector, message = null) {
        const field = document.querySelector(fieldSelector);
        if (!field) return;

        // Clear any errors first
        this.clearFieldError(fieldSelector);

        // Add success class
        field.classList.add('border-green-500', 'focus:border-green-500', 'focus:ring-green-500');

        if (message) {
            const successElement = document.createElement('div');
            successElement.className = 'field-success text-green-600 text-sm mt-1';
            successElement.textContent = message;
            field.parentNode.appendChild(successElement);
        }
    }

    // Skeleton Loading
    showSkeleton(container) {
        if (typeof container === 'string') {
            container = document.querySelector(container);
        }

        if (!container) return;

        container.classList.add('skeleton-loading');
        container.innerHTML = this.generateSkeletonHTML(container);
    }

    hideSkeleton(container, originalContent) {
        if (typeof container === 'string') {
            container = document.querySelector(container);
        }

        if (!container) return;

        container.classList.remove('skeleton-loading');
        if (originalContent) {
            container.innerHTML = originalContent;
        }
    }

    generateSkeletonHTML(container) {
        const height = container.offsetHeight || 200;
        const lines = Math.ceil(height / 24);
        
        return Array.from({ length: lines }, (_, i) => 
            `<div class="loading-shimmer h-4 bg-gray-200 dark:bg-gray-700 rounded mb-2 ${i === lines - 1 ? 'w-3/4' : 'w-full'}"></div>`
        ).join('');
    }

    // Utility methods
    playNotificationSound(type) {
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioContext.createOscillator();
        const gainNode = audioContext.createGain();

        oscillator.connect(gainNode);
        gainNode.connect(audioContext.destination);

        const frequencies = {
            success: 800,
            error: 300,
            warning: 600,
            info: 500
        };

        oscillator.frequency.setValueAtTime(frequencies[type] || 500, audioContext.currentTime);
        oscillator.type = 'sine';
        
        gainNode.gain.setValueAtTime(0.1, audioContext.currentTime);
        gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.3);

        oscillator.start();
        oscillator.stop(audioContext.currentTime + 0.3);
    }

    getVibrationPattern(type) {
        const patterns = {
            success: [100, 50, 100],
            error: [200, 100, 200, 100, 200],
            warning: [150, 75, 150],
            info: [100]
        };

        return patterns[type] || [100];
    }

    // Public API shortcuts
    success(message, options = {}) {
        return this.showToast(message, 'success', options);
    }

    error(message, options = {}) {
        return this.showToast(message, 'error', options);
    }

    warning(message, options = {}) {
        return this.showToast(message, 'warning', options);
    }

    info(message, options = {}) {
        return this.showToast(message, 'info', options);
    }

    loading(message, options = {}) {
        return this.showLoading(message, options);
    }

    stopLoading() {
        this.hideLoading();
    }
}

// Auto-initialize if not in module environment
if (typeof module === 'undefined') {
    window.UIFeedbackManager = UIFeedbackManager;
    
    // Auto-initialize feedback manager
    document.addEventListener('DOMContentLoaded', () => {
        if (!window.hdTicketsFeedback) {
            window.hdTicketsFeedback = new UIFeedbackManager();
        }
    });
}

// Export for module environments
if (typeof module !== 'undefined' && module.exports) {
    module.exports = UIFeedbackManager;
}
