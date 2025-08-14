/**
 * HD Tickets Notification Manager v2.1.0
 * Handles push notifications, real-time updates, and user preferences
 */

class HDTicketsNotificationManager {
  constructor() {
    this.isSupported = 'Notification' in window && 'serviceWorker' in navigator;
    this.permission = this.isSupported ? Notification.permission : 'denied';
    this.eventSource = null;
    this.reconnectAttempts = 0;
    this.maxReconnectAttempts = 5;
    this.reconnectDelay = 1000;
    this.subscribers = new Map();
    this.notificationQueue = [];
    this.isVisible = !document.hidden;
    this.settings = this.loadSettings();
    
    this.init();
  }

  async init() {
    if (!this.isSupported) {
      console.warn('[NotificationManager] Push notifications not supported');
      return;
    }

    // Listen for visibility changes
    document.addEventListener('visibilitychange', () => {
      this.isVisible = !document.hidden;
      if (this.isVisible) {
        this.clearNotificationQueue();
      }
    });

    // Set up service worker message handling
    if ('serviceWorker' in navigator) {
      navigator.serviceWorker.addEventListener('message', (event) => {
        this.handleServiceWorkerMessage(event.data);
      });
    }

    // Initialize real-time connection
    this.setupRealTimeConnection();
    
    // Load user preferences
    await this.loadUserPreferences();
  }

  // Request notification permission
  async requestPermission() {
    if (!this.isSupported) {
      return 'denied';
    }

    if (this.permission === 'granted') {
      return 'granted';
    }

    try {
      const permission = await Notification.requestPermission();
      this.permission = permission;
      
      if (permission === 'granted') {
        await this.subscribeToPushNotifications();
        this.showSuccessMessage('Notifications enabled successfully!');
      }
      
      return permission;
    } catch (error) {
      console.error('[NotificationManager] Permission request failed:', error);
      return 'denied';
    }
  }

  // Subscribe to push notifications
  async subscribeToPushNotifications() {
    try {
      const registration = await navigator.serviceWorker.ready;
      
      // Check if already subscribed
      const existingSubscription = await registration.pushManager.getSubscription();
      if (existingSubscription) {
        console.log('[NotificationManager] Already subscribed to push notifications');
        return existingSubscription;
      }

      // Subscribe to push notifications
      const subscription = await registration.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: this.urlBase64ToUint8Array(this.getVAPIDPublicKey())
      });

      // Send subscription to server
      await this.sendSubscriptionToServer(subscription);
      
      console.log('[NotificationManager] Subscribed to push notifications');
      return subscription;
    } catch (error) {
      console.error('[NotificationManager] Push subscription failed:', error);
      throw error;
    }
  }

  // Setup real-time SSE connection
  setupRealTimeConnection() {
    if (!this.settings.realTimeUpdates) {
      return;
    }

    try {
      this.eventSource = new EventSource('/api/stream/notifications');
      
      this.eventSource.onopen = () => {
        console.log('[NotificationManager] Real-time connection established');
        this.reconnectAttempts = 0;
        this.emit('connection-established');
      };

      this.eventSource.onmessage = (event) => {
        try {
          const data = JSON.parse(event.data);
          this.handleRealTimeUpdate(data);
        } catch (error) {
          console.error('[NotificationManager] Failed to parse SSE data:', error);
        }
      };

      this.eventSource.addEventListener('ticket-update', (event) => {
        const data = JSON.parse(event.data);
        this.handleTicketUpdate(data);
      });

      this.eventSource.addEventListener('price-alert', (event) => {
        const data = JSON.parse(event.data);
        this.handlePriceAlert(data);
      });

      this.eventSource.addEventListener('system-alert', (event) => {
        const data = JSON.parse(event.data);
        this.handleSystemAlert(data);
      });

      this.eventSource.onerror = (error) => {
        console.error('[NotificationManager] SSE connection error:', error);
        this.handleConnectionError();
      };

    } catch (error) {
      console.error('[NotificationManager] Failed to setup real-time connection:', error);
    }
  }

  // Handle real-time updates
  handleRealTimeUpdate(data) {
    switch (data.type) {
      case 'ticket_price_change':
        this.showPriceChangeNotification(data);
        break;
      case 'new_tickets_available':
        this.showTicketAvailabilityNotification(data);
        break;
      case 'scraping_status_change':
        this.showScrapingStatusNotification(data);
        break;
      case 'system_maintenance':
        this.showSystemMaintenanceNotification(data);
        break;
      default:
        console.log('[NotificationManager] Unknown update type:', data.type);
    }
  }

  // Handle ticket updates
  handleTicketUpdate(data) {
    if (!this.settings.ticketUpdates) return;

    const notification = {
      title: 'Ticket Update',
      body: `${data.event_name}: ${data.message}`,
      icon: '/assets/images/pwa/icon-ticket.png',
      tag: `ticket-${data.ticket_id}`,
      data: {
        url: `/tickets/${data.ticket_id}`,
        ticketId: data.ticket_id,
        eventName: data.event_name
      },
      actions: [
        { action: 'view', title: 'View Ticket' },
        { action: 'dismiss', title: 'Dismiss' }
      ]
    };

    this.showNotification(notification);
    this.emit('ticket-updated', data);
  }

  // Handle price alerts
  handlePriceAlert(data) {
    if (!this.settings.priceAlerts) return;

    const priceChange = data.old_price - data.new_price;
    const isDecrease = priceChange > 0;
    const changePercent = Math.abs((priceChange / data.old_price) * 100).toFixed(1);

    const notification = {
      title: isDecrease ? '💰 Price Drop Alert!' : '📈 Price Increase Alert',
      body: `${data.event_name}\n${isDecrease ? 'Decreased' : 'Increased'} by $${Math.abs(priceChange)} (${changePercent}%)\nNew price: $${data.new_price}`,
      icon: isDecrease ? '/assets/images/pwa/icon-price-down.png' : '/assets/images/pwa/icon-price-up.png',
      tag: `price-${data.ticket_id}`,
      requireInteraction: isDecrease && changePercent > 10, // Keep important alerts visible
      data: {
        url: `/tickets/${data.ticket_id}?alert=price`,
        ticketId: data.ticket_id,
        oldPrice: data.old_price,
        newPrice: data.new_price,
        eventName: data.event_name
      },
      actions: [
        { action: 'buy', title: 'Buy Now' },
        { action: 'view', title: 'View Details' },
        { action: 'dismiss', title: 'Dismiss' }
      ]
    };

    this.showNotification(notification);
    this.playNotificationSound(isDecrease ? 'price-drop' : 'price-increase');
    this.emit('price-alert', data);
  }

  // Handle system alerts
  handleSystemAlert(data) {
    if (!this.settings.systemAlerts) return;

    const notification = {
      title: '⚠️ System Alert',
      body: data.message,
      icon: '/assets/images/pwa/icon-system.png',
      tag: `system-${data.id}`,
      requireInteraction: data.priority === 'high',
      data: {
        url: data.url || '/dashboard',
        alertId: data.id,
        priority: data.priority
      }
    };

    this.showNotification(notification);
    this.emit('system-alert', data);
  }

  // Show notification (handles both browser and push notifications)
  async showNotification(options) {
    if (!this.isSupported || this.permission !== 'granted') {
      this.showFallbackNotification(options);
      return;
    }

    try {
      // If page is visible, show in-app notification
      if (this.isVisible) {
        this.showInAppNotification(options);
      } else {
        // Page is hidden, show browser notification
        await this.showBrowserNotification(options);
      }
    } catch (error) {
      console.error('[NotificationManager] Failed to show notification:', error);
      this.showFallbackNotification(options);
    }
  }

  // Show browser notification
  async showBrowserNotification(options) {
    if ('serviceWorker' in navigator) {
      const registration = await navigator.serviceWorker.ready;
      return registration.showNotification(options.title, options);
    } else {
      return new Notification(options.title, options);
    }
  }

  // Show in-app notification
  showInAppNotification(options) {
    const container = this.getNotificationContainer();
    const notificationElement = this.createNotificationElement(options);
    
    container.appendChild(notificationElement);
    
    // Auto-remove after delay
    setTimeout(() => {
      this.removeNotification(notificationElement);
    }, options.duration || 5000);

    // Add click handler
    notificationElement.addEventListener('click', () => {
      if (options.data?.url) {
        window.location.href = options.data.url;
      }
      this.removeNotification(notificationElement);
    });
  }

  // Create notification element
  createNotificationElement(options) {
    const notification = document.createElement('div');
    notification.className = 'hd-notification';
    notification.dataset.tag = options.tag || 'notification';
    
    const iconClass = this.getIconClass(options);
    
    notification.innerHTML = `
      <div class="hd-notification-icon ${iconClass}">
        ${options.icon ? `<img src="${options.icon}" alt="">` : '📢'}
      </div>
      <div class="hd-notification-content">
        <div class="hd-notification-title">${options.title}</div>
        <div class="hd-notification-body">${options.body}</div>
      </div>
      <button class="hd-notification-close" aria-label="Close notification">×</button>
    `;
    
    // Add close handler
    const closeBtn = notification.querySelector('.hd-notification-close');
    closeBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      this.removeNotification(notification);
    });
    
    return notification;
  }

  // Get notification container
  getNotificationContainer() {
    let container = document.getElementById('hd-notification-container');
    
    if (!container) {
      container = document.createElement('div');
      container.id = 'hd-notification-container';
      container.className = 'hd-notification-container';
      document.body.appendChild(container);
    }
    
    return container;
  }

  // Remove notification
  removeNotification(element) {
    element.classList.add('removing');
    setTimeout(() => {
      if (element.parentNode) {
        element.parentNode.removeChild(element);
      }
    }, 300);
  }

  // Fallback notification for unsupported browsers
  showFallbackNotification(options) {
    console.log('[NotificationManager] Fallback notification:', options.title, options.body);
    
    // Show toast message or other fallback
    this.showToast(options.title, options.body);
  }

  // Show toast message
  showToast(title, body) {
    const toast = document.createElement('div');
    toast.className = 'hd-toast';
    toast.innerHTML = `
      <strong>${title}</strong>
      <p>${body}</p>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
      toast.classList.add('show');
    }, 100);
    
    setTimeout(() => {
      toast.classList.remove('show');
      setTimeout(() => {
        document.body.removeChild(toast);
      }, 300);
    }, 4000);
  }

  // Play notification sound
  playNotificationSound(type = 'default') {
    if (!this.settings.sounds) return;
    
    const sounds = {
      'default': '/assets/audio/notification.mp3',
      'price-drop': '/assets/audio/price-drop.mp3',
      'price-increase': '/assets/audio/price-increase.mp3',
      'alert': '/assets/audio/alert.mp3'
    };
    
    const audioUrl = sounds[type] || sounds.default;
    
    try {
      const audio = new Audio(audioUrl);
      audio.volume = this.settings.volume || 0.5;
      audio.play().catch(error => {
        console.log('[NotificationManager] Could not play sound:', error);
      });
    } catch (error) {
      console.log('[NotificationManager] Sound not available:', error);
    }
  }

  // Event system
  on(event, callback) {
    if (!this.subscribers.has(event)) {
      this.subscribers.set(event, []);
    }
    this.subscribers.get(event).push(callback);
  }

  off(event, callback) {
    if (this.subscribers.has(event)) {
      const callbacks = this.subscribers.get(event);
      const index = callbacks.indexOf(callback);
      if (index > -1) {
        callbacks.splice(index, 1);
      }
    }
  }

  emit(event, data) {
    if (this.subscribers.has(event)) {
      this.subscribers.get(event).forEach(callback => {
        try {
          callback(data);
        } catch (error) {
          console.error('[NotificationManager] Event callback error:', error);
        }
      });
    }
  }

  // Settings management
  loadSettings() {
    const defaultSettings = {
      enabled: true,
      realTimeUpdates: true,
      priceAlerts: true,
      ticketUpdates: true,
      systemAlerts: true,
      sounds: true,
      volume: 0.5,
      quietHours: {
        enabled: false,
        start: '22:00',
        end: '08:00'
      }
    };
    
    try {
      const stored = localStorage.getItem('hd-notification-settings');
      return stored ? { ...defaultSettings, ...JSON.parse(stored) } : defaultSettings;
    } catch (error) {
      console.error('[NotificationManager] Failed to load settings:', error);
      return defaultSettings;
    }
  }

  saveSettings() {
    try {
      localStorage.setItem('hd-notification-settings', JSON.stringify(this.settings));
    } catch (error) {
      console.error('[NotificationManager] Failed to save settings:', error);
    }
  }

  updateSetting(key, value) {
    this.settings[key] = value;
    this.saveSettings();
    this.emit('settings-updated', { key, value });
  }

  // Utility methods
  handleConnectionError() {
    if (this.eventSource) {
      this.eventSource.close();
    }
    
    if (this.reconnectAttempts < this.maxReconnectAttempts) {
      this.reconnectAttempts++;
      const delay = this.reconnectDelay * Math.pow(2, this.reconnectAttempts - 1);
      
      console.log(`[NotificationManager] Reconnecting in ${delay}ms (attempt ${this.reconnectAttempts})`);
      
      setTimeout(() => {
        this.setupRealTimeConnection();
      }, delay);
    } else {
      console.error('[NotificationManager] Max reconnect attempts reached');
      this.emit('connection-failed');
    }
  }

  handleServiceWorkerMessage(data) {
    switch (data.type) {
      case 'SYNC_SUCCESS':
        this.showSuccessMessage('Data synchronized successfully');
        break;
      case 'SW_ACTIVATED':
        console.log('[NotificationManager] Service Worker activated:', data.version);
        break;
    }
  }

  getIconClass(options) {
    if (options.title.includes('Price')) return 'price-alert';
    if (options.title.includes('System')) return 'system-alert';
    if (options.title.includes('Ticket')) return 'ticket-alert';
    return 'default-alert';
  }

  urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding)
      .replace(/\-/g, '+')
      .replace(/_/g, '/');
    
    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);
    
    for (let i = 0; i < rawData.length; ++i) {
      outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
  }

  getVAPIDPublicKey() {
    // This should be your VAPID public key from the server
    return 'BH7Lcy4pE1LjHdJB8Qz7v9A1kD2E3FgH8IjKlMnO6pQrS7tUvWxYz1A2B3C4D5E6F7G8H9I0J1K2L3M4N5O6P7Q8R9S0T1U2V3W4X5Y6Z7';
  }

  async sendSubscriptionToServer(subscription) {
    try {
      const response = await fetch('/api/notifications/subscribe', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
        },
        body: JSON.stringify({
          subscription: subscription.toJSON()
        })
      });
      
      if (!response.ok) {
        throw new Error('Failed to send subscription to server');
      }
    } catch (error) {
      console.error('[NotificationManager] Failed to send subscription:', error);
    }
  }

  async loadUserPreferences() {
    try {
      const response = await fetch('/api/user/notification-preferences');
      if (response.ok) {
        const preferences = await response.json();
        this.settings = { ...this.settings, ...preferences };
      }
    } catch (error) {
      console.error('[NotificationManager] Failed to load user preferences:', error);
    }
  }

  showSuccessMessage(message) {
    this.showInAppNotification({
      title: '✅ Success',
      body: message,
      icon: '/assets/images/pwa/icon-success.png',
      tag: 'success',
      duration: 3000
    });
  }

  clearNotificationQueue() {
    this.notificationQueue.forEach(notification => {
      this.showNotification(notification);
    });
    this.notificationQueue = [];
  }

  // Public API methods
  async enable() {
    const permission = await this.requestPermission();
    if (permission === 'granted') {
      this.updateSetting('enabled', true);
      return true;
    }
    return false;
  }

  disable() {
    this.updateSetting('enabled', false);
    if (this.eventSource) {
      this.eventSource.close();
      this.eventSource = null;
    }
  }

  isEnabled() {
    return this.settings.enabled && this.permission === 'granted';
  }

  getSettings() {
    return { ...this.settings };
  }
}

// Export for use
window.HDTicketsNotificationManager = HDTicketsNotificationManager;

// Initialize when DOM is ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    window.hdNotificationManager = new HDTicketsNotificationManager();
  });
} else {
  window.hdNotificationManager = new HDTicketsNotificationManager();
}

console.log('[NotificationManager] HD Tickets Notification Manager v2.1.0 loaded');
