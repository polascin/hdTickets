/**
 * HD Tickets - Notification Manager
 * Handles in-app notifications and alerts
 */

class NotificationManager {
    constructor() {
        this.container = null;
        this.notifications = new Map();
        this.defaultDuration = 5000;
        this.maxNotifications = 5;
        
        this.init();
    }

    init() {
        // Create notification container if it doesn't exist
        this.container = document.querySelector('.notification-container');
        if (!this.container) {
            this.container = document.createElement('div');
            this.container.className = 'notification-container';
            document.body.appendChild(this.container);
        }

        // Listen for custom notification events
        document.addEventListener('hdtickets:notification', (e) => {
            this.show(e.detail);
        });

        console.log('✅ NotificationManager initialized');
    }

    show(options = {}) {
        const {
            type = 'info',
            title = 'Notification',
            message = '',
            duration = this.defaultDuration,
            persistent = false,
            actions = []
        } = options;

        // Create unique ID for this notification
        const id = 'notification_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);

        // Limit number of notifications
        if (this.notifications.size >= this.maxNotifications) {
            const oldestId = Array.from(this.notifications.keys())[0];
            this.hide(oldestId);
        }

        // Create notification element
        const notification = this.createElement(id, type, title, message, actions);
        
        // Add to container
        this.container.appendChild(notification);
        this.notifications.set(id, notification);

        // Auto-hide after duration (unless persistent)
        if (!persistent && duration > 0) {
            setTimeout(() => {
                this.hide(id);
            }, duration);
        }

        // Add progress bar animation
        if (!persistent && duration > 0) {
            this.animateProgress(notification, duration);
        }

        return id;
    }

    createElement(id, type, title, message, actions) {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.dataset.id = id;

        const progressBar = document.createElement('div');
        progressBar.className = 'notification-progress';
        progressBar.style.width = '100%';

        notification.innerHTML = `
            <div class="notification-header">
                <h4 class="notification-title">${this.escapeHtml(title)}</h4>
                <button class="notification-close" onclick="notificationManager.hide('${id}')">&times;</button>
            </div>
            <p class="notification-message">${this.escapeHtml(message)}</p>
            ${actions.length > 0 ? this.createActionsHtml(actions, id) : ''}
        `;

        notification.appendChild(progressBar);

        return notification;
    }

    createActionsHtml(actions, notificationId) {
        const actionsHtml = actions.map(action => {
            const actionClass = action.primary ? 'btn btn-primary btn-sm' : 'btn btn-outline-secondary btn-sm';
            return `<button class="${actionClass}" onclick="notificationManager.handleAction('${notificationId}', '${action.id}')">${this.escapeHtml(action.label)}</button>`;
        }).join(' ');

        return `<div class="notification-actions mt-2">${actionsHtml}</div>`;
    }

    handleAction(notificationId, actionId) {
        const notification = this.notifications.get(notificationId);
        if (!notification) return;

        // Emit action event
        document.dispatchEvent(new CustomEvent('hdtickets:notification-action', {
            detail: { notificationId, actionId }
        }));

        // Hide notification after action
        this.hide(notificationId);
    }

    hide(id) {
        const notification = this.notifications.get(id);
        if (!notification) return;

        // Add removing animation
        notification.classList.add('removing');

        // Remove after animation
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
            this.notifications.delete(id);
        }, 300);
    }

    hideAll() {
        Array.from(this.notifications.keys()).forEach(id => {
            this.hide(id);
        });
    }

    animateProgress(notification, duration) {
        const progressBar = notification.querySelector('.notification-progress');
        if (!progressBar) return;

        progressBar.style.transition = `width ${duration}ms linear`;
        
        // Small delay to ensure CSS is applied
        setTimeout(() => {
            progressBar.style.width = '0%';
        }, 10);
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Convenience methods for different notification types
    success(title, message, options = {}) {
        return this.show({ ...options, type: 'success', title, message });
    }

    error(title, message, options = {}) {
        return this.show({ ...options, type: 'error', title, message, persistent: true });
    }

    warning(title, message, options = {}) {
        return this.show({ ...options, type: 'warning', title, message });
    }

    info(title, message, options = {}) {
        return this.show({ ...options, type: 'info', title, message });
    }

    // Ticket-specific notifications
    ticketAlert(ticketData) {
        return this.show({
            type: 'success',
            title: 'New Ticket Found!',
            message: `${ticketData.event} - $${ticketData.price}`,
            duration: 10000,
            actions: [
                { id: 'view', label: 'View Details', primary: true },
                { id: 'dismiss', label: 'Dismiss' }
            ]
        });
    }

    systemStatus(status, message) {
        const type = status === 'online' ? 'success' : 'warning';
        return this.show({
            type,
            title: 'System Status',
            message,
            persistent: status !== 'online'
        });
    }
}

// Initialize notification manager when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.notificationManager = new NotificationManager();
});

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = NotificationManager;
}
