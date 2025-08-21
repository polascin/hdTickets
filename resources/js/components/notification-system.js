import Alpine from 'alpinejs';

document.addEventListener('alpine:init', () => {
    Alpine.data('notificationSystem', () => ({
        notifications: [],
        maxNotifications: 5,
        
        init() {
            // Listen for global notification events
            window.addEventListener('show-notification', (event) => {
                this.show(event.detail.message, event.detail.type, event.detail.duration);
            });
            
            // Request notification permission if supported
            if ('Notification' in window && Notification.permission === 'default') {
                this.requestPermission();
            }
        },

        show(message, type = 'info', duration = 5000) {
            const notification = {
                id: Date.now() + Math.random(),
                message,
                type,
                duration,
                visible: true,
                progress: 100
            };
            
            // Add to notifications array
            this.notifications.unshift(notification);
            
            // Remove oldest if exceeding max
            if (this.notifications.length > this.maxNotifications) {
                this.notifications = this.notifications.slice(0, this.maxNotifications);
            }
            
            // Auto remove after duration
            if (duration > 0) {
                this.startProgress(notification);
                setTimeout(() => {
                    this.remove(notification.id);
                }, duration);
            }
            
            // Show browser notification if permission granted
            if (type === 'success' || type === 'warning' || type === 'error') {
                this.showBrowserNotification(message, type);
            }
        },

        remove(id) {
            const index = this.notifications.findIndex(n => n.id === id);
            if (index > -1) {
                this.notifications[index].visible = false;
                setTimeout(() => {
                    this.notifications.splice(index, 1);
                }, 300); // Wait for fade out animation
            }
        },

        startProgress(notification) {
            if (notification.duration <= 0) return;
            
            const startTime = Date.now();
            const updateInterval = 50; // Update every 50ms
            
            const progressTimer = setInterval(() => {
                const elapsed = Date.now() - startTime;
                const remaining = Math.max(0, notification.duration - elapsed);
                notification.progress = (remaining / notification.duration) * 100;
                
                if (remaining <= 0) {
                    clearInterval(progressTimer);
                }
            }, updateInterval);
        },

        async requestPermission() {
            if ('Notification' in window) {
                const permission = await Notification.requestPermission();
                return permission === 'granted';
            }
            return false;
        },

        showBrowserNotification(message, type) {
            if ('Notification' in window && Notification.permission === 'granted') {
                const options = {
                    body: message,
                    icon: '/assets/images/hdTicketsLogo.png',
                    badge: '/assets/images/hdTicketsLogo.png',
                    tag: 'hdtickets-' + type,
                    requireInteraction: type === 'error',
                    silent: type === 'info'
                };
                
                const notification = new Notification('HD Tickets', options);
                
                notification.onclick = () => {
                    window.focus();
                    notification.close();
                };
                
                // Auto close after 5 seconds for non-error notifications
                if (type !== 'error') {
                    setTimeout(() => notification.close(), 5000);
                }
            }
        },

        getIcon(type) {
            const icons = {
                success: '✅',
                error: '❌',
                warning: '⚠️',
                info: 'ℹ️'
            };
            return icons[type] || icons.info;
        },

        getClasses(type) {
            const baseClasses = 'transform transition-all duration-300 ease-in-out p-4 rounded-lg shadow-lg border-l-4 max-w-sm';
            const typeClasses = {
                success: 'bg-green-50 border-green-400 text-green-800 dark:bg-green-900 dark:text-green-200',
                error: 'bg-red-50 border-red-400 text-red-800 dark:bg-red-900 dark:text-red-200',
                warning: 'bg-yellow-50 border-yellow-400 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                info: 'bg-blue-50 border-blue-400 text-blue-800 dark:bg-blue-900 dark:text-blue-200'
            };
            
            return `${baseClasses} ${typeClasses[type] || typeClasses.info}`;
        },

        // Predefined notification helpers
        success(message, duration) {
            this.show(message, 'success', duration);
        },

        error(message, duration = 8000) {
            this.show(message, 'error', duration);
        },

        warning(message, duration) {
            this.show(message, 'warning', duration);
        },

        info(message, duration) {
            this.show(message, 'info', duration);
        },

        // Sports-themed notifications
        goal(message = 'Goal! You scored with HD Tickets!') {
            this.show(`⚽ ${message}`, 'success');
        },

        touchdown(message = 'Touchdown! Ticket secured!') {
            this.show(`🏈 ${message}`, 'success');
        },

        homerun(message = 'Home run! Best seats found!') {
            this.show(`⚾ ${message}`, 'success');
        },

        assist(message = 'Great assist! Price drop detected!') {
            this.show(`🏀 ${message}`, 'info');
        }
    }));

    // Global notification helpers
    window.notify = {
        show: (message, type, duration) => {
            const event = new CustomEvent('show-notification', {
                detail: { message, type, duration }
            });
            window.dispatchEvent(event);
        },
        success: (message, duration) => window.notify.show(message, 'success', duration),
        error: (message, duration) => window.notify.show(message, 'error', duration),
        warning: (message, duration) => window.notify.show(message, 'warning', duration),
        info: (message, duration) => window.notify.show(message, 'info', duration),
        goal: (message) => {
            const event = new CustomEvent('show-notification', {
                detail: { message: `⚽ ${message || 'Goal! You scored with HD Tickets!'}`, type: 'success' }
            });
            window.dispatchEvent(event);
        }
    };
});
