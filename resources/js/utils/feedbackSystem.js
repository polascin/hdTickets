/**
 * HD Tickets Enhanced Feedback System
 * Provides comprehensive user feedback including toasts, modals, and inline notifications
 */

class FeedbackSystem {
    constructor() {
        this.toastContainer = null;
        this.init();
    }

    init() {
        this.createToastContainer();
        this.setupGlobalErrorHandling();
        window.hdTicketsFeedback = this;
    }

    createToastContainer() {
        // Create toast container if it doesn't exist
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'fixed top-4 right-4 z-50 space-y-2';
            container.setAttribute('aria-live', 'polite');
            container.setAttribute('aria-label', 'Notifications');
            document.body.appendChild(container);
        }
        this.toastContainer = container;
    }

    /**
     * Show a success toast
     */
    success(title, message = '', options = {}) {
        return this.showToast('success', title, message, {
            duration: 4000,
            icon: '✓',
            ...options
        });
    }

    /**
     * Show an error toast
     */
    error(title, message = '', options = {}) {
        return this.showToast('error', title, message, {
            duration: 6000,
            icon: '✕',
            persistent: true,
            ...options
        });
    }

    /**
     * Show an info toast
     */
    info(title, message = '', options = {}) {
        return this.showToast('info', title, message, {
            duration: 4000,
            icon: 'ℹ',
            ...options
        });
    }

    /**
     * Show a warning toast
     */
    warning(title, message = '', options = {}) {
        return this.showToast('warning', title, message, {
            duration: 5000,
            icon: '⚠',
            ...options
        });
    }

    /**
     * Show a loading toast
     */
    loading(title, message = '', options = {}) {
        return this.showToast('loading', title, message, {
            persistent: true,
            icon: '<div class="animate-spin rounded-full h-4 w-4 border-b-2 border-current"></div>',
            ...options
        });
    }

    /**
     * Generic toast method
     */
    showToast(type, title, message, options = {}) {
        const defaults = {
            duration: 4000,
            persistent: false,
            icon: '',
            actions: []
        };

        const config = { ...defaults, ...options };
        const toastId = 'toast-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);

        const toast = this.createToastElement(toastId, type, title, message, config);
        this.toastContainer.appendChild(toast);

        // Animate in
        requestAnimationFrame(() => {
            toast.classList.remove('translate-x-full', 'opacity-0');
            toast.classList.add('translate-x-0', 'opacity-100');
        });

        // Auto-dismiss if not persistent
        let timeoutId;
        if (!config.persistent && config.duration > 0) {
            timeoutId = setTimeout(() => {
                this.dismissToast(toastId);
            }, config.duration);
        }

        // Return dismiss function
        return {
            dismiss: () => {
                if (timeoutId) clearTimeout(timeoutId);
                this.dismissToast(toastId);
            },
            update: (newTitle, newMessage) => {
                const titleEl = toast.querySelector('.toast-title');
                const messageEl = toast.querySelector('.toast-message');
                if (titleEl) titleEl.textContent = newTitle;
                if (messageEl) messageEl.textContent = newMessage;
            }
        };
    }

    createToastElement(id, type, title, message, config) {
        const toast = document.createElement('div');
        toast.id = id;
        toast.className = `
            max-w-sm w-full bg-white shadow-lg rounded-lg pointer-events-auto 
            transform transition-all duration-300 ease-in-out 
            translate-x-full opacity-0
            border-l-4 ${this.getTypeClasses(type).border}
        `;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');

        const typeClasses = this.getTypeClasses(type);
        
        toast.innerHTML = `
            <div class="p-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <div class="${typeClasses.iconBg} rounded-full p-1">
                            ${typeof config.icon === 'string' && config.icon.startsWith('<') 
                                ? config.icon 
                                : `<span class="${typeClasses.iconText} text-sm font-bold">${config.icon}</span>`
                            }
                        </div>
                    </div>
                    <div class="ml-3 w-0 flex-1 pt-0.5">
                        <p class="text-sm font-medium text-gray-900 toast-title">${title}</p>
                        ${message ? `<p class="mt-1 text-sm text-gray-500 toast-message">${message}</p>` : ''}
                        ${config.actions && config.actions.length > 0 ? `
                            <div class="mt-3 flex space-x-2">
                                ${config.actions.map(action => `
                                    <button 
                                        class="bg-white rounded-md text-sm font-medium ${typeClasses.actionText} hover:${typeClasses.actionHover} focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-${type}-500"
                                        onclick="${action.onclick}"
                                    >
                                        ${action.text}
                                    </button>
                                `).join('')}
                            </div>
                        ` : ''}
                    </div>
                    <div class="ml-4 flex-shrink-0 flex">
                        <button 
                            class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            onclick="hdTicketsFeedback.dismissToast('${id}')"
                            aria-label="Dismiss notification"
                        >
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        `;

        return toast;
    }

    getTypeClasses(type) {
        const classes = {
            success: {
                border: 'border-green-400',
                iconBg: 'bg-green-100',
                iconText: 'text-green-600',
                actionText: 'text-green-600',
                actionHover: 'text-green-500'
            },
            error: {
                border: 'border-red-400',
                iconBg: 'bg-red-100',
                iconText: 'text-red-600',
                actionText: 'text-red-600',
                actionHover: 'text-red-500'
            },
            warning: {
                border: 'border-yellow-400',
                iconBg: 'bg-yellow-100',
                iconText: 'text-yellow-600',
                actionText: 'text-yellow-600',
                actionHover: 'text-yellow-500'
            },
            info: {
                border: 'border-blue-400',
                iconBg: 'bg-blue-100',
                iconText: 'text-blue-600',
                actionText: 'text-blue-600',
                actionHover: 'text-blue-500'
            },
            loading: {
                border: 'border-gray-400',
                iconBg: 'bg-gray-100',
                iconText: 'text-gray-600',
                actionText: 'text-gray-600',
                actionHover: 'text-gray-500'
            }
        };
        
        return classes[type] || classes.info;
    }

    dismissToast(toastId) {
        const toast = document.getElementById(toastId);
        if (toast) {
            toast.classList.remove('translate-x-0', 'opacity-100');
            toast.classList.add('translate-x-full', 'opacity-0');
            
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 300);
        }
    }

    /**
     * Clear all toasts
     */
    clearAll() {
        const toasts = this.toastContainer.querySelectorAll('[id^="toast-"]');
        toasts.forEach(toast => {
            this.dismissToast(toast.id);
        });
    }

    /**
     * Show a confirmation modal
     */
    confirm(title, message, options = {}) {
        return new Promise((resolve) => {
            const defaults = {
                confirmText: 'Confirm',
                cancelText: 'Cancel',
                confirmClass: 'bg-red-600 hover:bg-red-700 text-white',
                cancelClass: 'bg-gray-300 hover:bg-gray-400 text-gray-800'
            };

            const config = { ...defaults, ...options };
            const modalId = 'confirm-modal-' + Date.now();

            const modal = this.createModal(modalId, title, message, [
                {
                    text: config.cancelText,
                    class: config.cancelClass,
                    onclick: () => {
                        this.dismissModal(modalId);
                        resolve(false);
                    }
                },
                {
                    text: config.confirmText,
                    class: config.confirmClass,
                    onclick: () => {
                        this.dismissModal(modalId);
                        resolve(true);
                    }
                }
            ]);

            document.body.appendChild(modal);
            
            // Focus management
            setTimeout(() => {
                const firstButton = modal.querySelector('button');
                if (firstButton) firstButton.focus();
            }, 100);
        });
    }

    createModal(id, title, message, actions) {
        const modal = document.createElement('div');
        modal.id = id;
        modal.className = 'fixed inset-0 z-50 overflow-y-auto';
        modal.setAttribute('aria-labelledby', `${id}-title`);
        modal.setAttribute('aria-modal', 'true');
        modal.setAttribute('role', 'dialog');

        modal.innerHTML = `
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="${id}-title">
                                ${title}
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">${message}</p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                        ${actions.map(action => `
                            <button type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 text-base font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm ${action.class}" onclick="(${action.onclick})()">
                                ${action.text}
                            </button>
                        `).join('')}
                    </div>
                </div>
            </div>
        `;

        return modal;
    }

    dismissModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.remove();
        }
    }

    /**
     * Setup global error handling
     */
    setupGlobalErrorHandling() {
        // Handle global errors
        window.addEventListener('error', (event) => {
            console.error('Global error:', event.error);
            this.error('An error occurred', 'Please try again or contact support if the problem persists.');
        });

        // Handle unhandled promise rejections
        window.addEventListener('unhandledrejection', (event) => {
            console.error('Unhandled promise rejection:', event.reason);
            this.error('Something went wrong', 'Please try again or contact support if the problem persists.');
        });

        // Handle network errors
        window.addEventListener('offline', () => {
            this.warning('You are offline', 'Some features may not work until you reconnect.', {
                persistent: true
            });
        });

        window.addEventListener('online', () => {
            this.success('You are back online', 'All features are now available.');
        });
    }

    /**
     * Form validation feedback
     */
    validateForm(form) {
        const errors = [];
        const requiredFields = form.querySelectorAll('[required]');

        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                errors.push(`${field.name || field.id || 'A field'} is required`);
                this.highlightField(field, true);
            } else {
                this.highlightField(field, false);
            }
        });

        // Email validation
        const emailFields = form.querySelectorAll('[type="email"]');
        emailFields.forEach(field => {
            if (field.value && !this.isValidEmail(field.value)) {
                errors.push('Please enter a valid email address');
                this.highlightField(field, true);
            }
        });

        if (errors.length > 0) {
            this.error('Form validation failed', errors.join(', '));
            return false;
        }

        return true;
    }

    highlightField(field, hasError) {
        if (hasError) {
            field.classList.add('border-red-500', 'ring-red-500');
            field.classList.remove('border-gray-300');
        } else {
            field.classList.remove('border-red-500', 'ring-red-500');
            field.classList.add('border-gray-300');
        }
    }

    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    /**
     * Progress indicator
     */
    showProgress(title, current, total) {
        const percentage = Math.round((current / total) * 100);
        
        return this.loading(title, `${current} of ${total} (${percentage}%)`, {
            icon: `
                <div class="relative w-4 h-4">
                    <div class="absolute inset-0 rounded-full bg-gray-200"></div>
                    <div class="absolute inset-0 rounded-full bg-blue-600" style="clip-path: circle(${percentage}% at 50% 50%)"></div>
                </div>
            `
        });
    }
}

// Initialize the feedback system when the DOM is loaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => new FeedbackSystem());
} else {
    new FeedbackSystem();
}

export default FeedbackSystem;
