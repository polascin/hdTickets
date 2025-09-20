/**
 * HD Tickets Real-time Notification System
 *
 * Comprehensive notification system with WebSocket integration, browser push notifications,
 * and intelligent notification management for sports ticket monitoring alerts.
 *
 * Features:
 * - Real-time WebSocket notifications via Laravel Echo
 * - Browser push notifications with service worker
 * - Notification queue management and prioritization
 * - Sound notifications with user preferences
 * - Notification history and persistence
 * - Smart notification batching and throttling
 *
 * @version 1.0.0
 */

class NotificationSystem {
  constructor(config = {}) {
    this.config = {
      websocketUrl: config.websocketUrl || `ws://${window.location.host}:6001`,
      pushServerKey: config.pushServerKey || null,
      audioEnabled: config.audioEnabled !== false,
      batchTimeout: config.batchTimeout || 5000,
      maxBatchSize: config.maxBatchSize || 5,
      retryAttempts: config.retryAttempts || 3,
      retryDelay: config.retryDelay || 5000,
      ...config,
    };

    this.isConnected = false;
    this.echo = null;
    this.serviceWorker = null;
    this.pushSubscription = null;
    this.notificationQueue = [];
    this.batchTimer = null;
    this.audioContext = null;
    this.sounds = {};
    this.preferences = this.loadPreferences();
    this.retryCount = 0;

    this.initializeSystem();
  }

  /**
   * Initialize the notification system
   */
  async initializeSystem() {
    console.log('🔔 Initializing HD Tickets Notification System...');

    try {
      // Initialize service worker for push notifications
      if ('serviceWorker' in navigator) {
        await this.initializeServiceWorker();
      }

      // Initialize WebSocket connection
      await this.initializeWebSocket();

      // Initialize browser notifications
      await this.initializeBrowserNotifications();

      // Initialize audio system
      await this.initializeAudioSystem();

      // Setup event listeners
      this.setupEventListeners();

      // Connect to user-specific channels
      this.subscribeToUserChannels();

      console.log('✅ Notification system initialized successfully');
      this.showSystemNotification('Notification system activated', 'success');
    } catch (error) {
      console.error('❌ Failed to initialize notification system:', error);
      this.handleInitializationError(error);
    }
  }

  /**
   * Initialize service worker for push notifications
   */
  async initializeServiceWorker() {
    try {
      const registration = await navigator.serviceWorker.register(
        '/sw-notifications.js'
      );
      this.serviceWorker = registration;

      registration.addEventListener('updatefound', () => {
        console.log('📥 Service worker update found');
      });

      console.log('✅ Service worker registered');
    } catch (error) {
      console.error('❌ Service worker registration failed:', error);
      throw error;
    }
  }

  /**
   * Initialize WebSocket connection with Laravel Echo
   */
  async initializeWebSocket() {
    try {
      // Check if Echo is available (loaded from CDN or bundled)
      if (typeof Echo === 'undefined') {
        console.warn('⚠️ Laravel Echo not found, using fallback polling');
        this.initializeFallbackPolling();
        return;
      }

      this.echo = new Echo({
        broadcaster: 'pusher',
        key: window.pusherKey || 'hd-tickets-key',
        wsHost: window.location.hostname,
        wsPort: 6001,
        forceTLS: false,
        disableStats: true,
        auth: {
          headers: {
            'X-CSRF-TOKEN': document
              .querySelector('meta[name="csrf-token"]')
              ?.getAttribute('content'),
            Authorization: `Bearer ${this.getAuthToken()}`,
          },
        },
      });

      // Connection event handlers
      this.echo.connector.pusher.connection.bind('connected', () => {
        this.isConnected = true;
        this.retryCount = 0;
        console.log('🔗 WebSocket connected');
        this.showSystemNotification(
          'Real-time notifications connected',
          'success'
        );
      });

      this.echo.connector.pusher.connection.bind('disconnected', () => {
        this.isConnected = false;
        console.log('🔌 WebSocket disconnected');
        this.handleDisconnection();
      });

      this.echo.connector.pusher.connection.bind('error', error => {
        console.error('🚨 WebSocket error:', error);
        this.handleConnectionError(error);
      });
    } catch (error) {
      console.error('❌ WebSocket initialization failed:', error);
      this.initializeFallbackPolling();
    }
  }

  /**
   * Initialize browser notifications
   */
  async initializeBrowserNotifications() {
    if (!('Notification' in window)) {
      console.warn('⚠️ Browser notifications not supported');
      return;
    }

    const permission = await this.requestNotificationPermission();

    if (permission === 'granted') {
      console.log('✅ Browser notifications enabled');
      await this.setupPushNotifications();
    } else {
      console.warn('⚠️ Browser notification permission denied');
    }
  }

  /**
   * Request browser notification permission
   */
  async requestNotificationPermission() {
    if (Notification.permission === 'default') {
      const permission = await Notification.requestPermission();
      this.preferences.browserNotifications = permission === 'granted';
      this.savePreferences();
      return permission;
    }
    return Notification.permission;
  }

  /**
   * Setup push notifications
   */
  async setupPushNotifications() {
    try {
      if (!this.serviceWorker || !this.config.pushServerKey) {
        console.warn('⚠️ Push notifications not configured');
        return;
      }

      const subscription = await this.serviceWorker.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: this.urlBase64ToUint8Array(
          this.config.pushServerKey
        ),
      });

      this.pushSubscription = subscription;

      // Send subscription to server
      await this.registerPushSubscription(subscription);

      console.log('✅ Push notifications configured');
    } catch (error) {
      console.error('❌ Push notification setup failed:', error);
    }
  }

  /**
   * Initialize audio system for notification sounds
   */
  async initializeAudioSystem() {
    if (!this.preferences.audioEnabled) {
      return;
    }

    try {
      this.audioContext = new (window.AudioContext ||
        window.webkitAudioContext)();

      // Load notification sounds
      await Promise.all([
        this.loadSound('alert', '/sounds/alert.mp3'),
        this.loadSound('success', '/sounds/success.mp3'),
        this.loadSound('warning', '/sounds/warning.mp3'),
        this.loadSound('error', '/sounds/error.mp3'),
      ]);

      console.log('🔊 Audio system initialized');
    } catch (error) {
      console.warn('⚠️ Audio system initialization failed:', error);
    }
  }

  /**
   * Setup event listeners
   */
  setupEventListeners() {
    // Page visibility change
    document.addEventListener('visibilitychange', () => {
      if (document.visibilityState === 'visible') {
        this.handlePageVisible();
      } else {
        this.handlePageHidden();
      }
    });

    // Online/offline events
    window.addEventListener('online', () => {
      this.handleOnline();
    });

    window.addEventListener('offline', () => {
      this.handleOffline();
    });

    // User preferences changes
    document.addEventListener('notification-preferences-changed', event => {
      this.updatePreferences(event.detail);
    });

    // Custom notification events
    document.addEventListener('show-notification', event => {
      this.processNotification(event.detail);
    });
  }

  /**
   * Subscribe to user-specific notification channels
   */
  subscribeToUserChannels() {
    if (!this.echo || !this.isConnected) {
      setTimeout(() => this.subscribeToUserChannels(), 1000);
      return;
    }

    const userId = window.authUser?.id;
    if (!userId) {
      console.warn('⚠️ No authenticated user found');
      return;
    }

    // Subscribe to private user channel
    this.echo
      .private(`user.${userId}`)
      .listen('PriceAlertTriggered', event => {
        this.handlePriceAlert(event);
      })
      .listen('TicketAvailabilityChanged', event => {
        this.handleAvailabilityAlert(event);
      })
      .listen('SystemNotification', event => {
        this.handleSystemNotification(event);
      })
      .listen('PurchaseStatusUpdate', event => {
        this.handlePurchaseUpdate(event);
      });

    // Subscribe to general notifications channel
    this.echo
      .channel('notifications')
      .listen('SystemMaintenance', event => {
        this.handleMaintenanceNotification(event);
      })
      .listen('PlatformAlert', event => {
        this.handlePlatformAlert(event);
      });

    console.log('📡 Subscribed to notification channels');
  }

  /**
   * Handle different types of notifications
   */
  handlePriceAlert(event) {
    const notification = {
      id: `price_alert_${event.alert_id}`,
      type: 'price_alert',
      title: 'Price Alert Triggered!',
      message: `${event.event_name} tickets dropped to $${event.new_price} (target: $${event.target_price})`,
      data: event,
      priority: 'high',
      actions: [
        {
          action: 'view',
          title: 'View Details',
          url: `/monitoring/alerts/${event.alert_id}`,
        },
        {
          action: 'purchase',
          title: 'Buy Tickets',
          url: `/tickets/purchase?alert=${event.alert_id}`,
        },
      ],
      sound: 'alert',
      vibrate: [200, 100, 200],
      timestamp: Date.now(),
    };

    this.queueNotification(notification);
  }

  handleAvailabilityAlert(event) {
    const notification = {
      id: `availability_${event.ticket_id}`,
      type: 'availability',
      title: 'Tickets Available!',
      message: `${event.event_name} tickets are now available`,
      data: event,
      priority: 'high',
      actions: [
        {
          action: 'view',
          title: 'View Tickets',
          url: `/tickets/${event.ticket_id}`,
        },
      ],
      sound: 'success',
      vibrate: [100, 50, 100, 50, 100],
      timestamp: Date.now(),
    };

    this.queueNotification(notification);
  }

  handleSystemNotification(event) {
    const notification = {
      id: `system_${Date.now()}`,
      type: 'system',
      title: event.title,
      message: event.message,
      data: event,
      priority: event.priority || 'medium',
      sound: event.type === 'error' ? 'error' : 'success',
      timestamp: Date.now(),
    };

    this.queueNotification(notification);
  }

  handlePurchaseUpdate(event) {
    const notification = {
      id: `purchase_${event.purchase_id}`,
      type: 'purchase_update',
      title: 'Purchase Update',
      message: `Your purchase for ${event.event_name} is ${event.status}`,
      data: event,
      priority: 'medium',
      sound: event.status === 'completed' ? 'success' : 'warning',
      timestamp: Date.now(),
    };

    this.queueNotification(notification);
  }

  handleMaintenanceNotification(event) {
    const notification = {
      id: `maintenance_${Date.now()}`,
      type: 'maintenance',
      title: 'System Maintenance',
      message: event.message,
      data: event,
      priority: 'low',
      persistent: true,
      timestamp: Date.now(),
    };

    this.queueNotification(notification);
  }

  handlePlatformAlert(event) {
    const notification = {
      id: `platform_${event.platform}`,
      type: 'platform_alert',
      title: `${event.platform} Alert`,
      message: event.message,
      data: event,
      priority: event.severity === 'critical' ? 'high' : 'medium',
      sound: 'warning',
      timestamp: Date.now(),
    };

    this.queueNotification(notification);
  }

  /**
   * Queue notification for processing
   */
  queueNotification(notification) {
    if (!this.shouldShowNotification(notification)) {
      return;
    }

    this.notificationQueue.push(notification);

    // Process immediately for high priority
    if (notification.priority === 'high') {
      this.processNotificationQueue();
    } else {
      // Batch process for other priorities
      this.scheduleBatchProcessing();
    }
  }

  /**
   * Schedule batch processing of notifications
   */
  scheduleBatchProcessing() {
    if (this.batchTimer) {
      return;
    }

    this.batchTimer = setTimeout(() => {
      this.processNotificationQueue();
      this.batchTimer = null;
    }, this.config.batchTimeout);
  }

  /**
   * Process notification queue
   */
  processNotificationQueue() {
    if (this.notificationQueue.length === 0) {
      return;
    }

    const notifications = this.notificationQueue.splice(
      0,
      this.config.maxBatchSize
    );

    for (const notification of notifications) {
      this.processNotification(notification);
    }

    // Process remaining notifications if queue is not empty
    if (this.notificationQueue.length > 0) {
      setTimeout(() => this.processNotificationQueue(), 1000);
    }
  }

  /**
   * Process individual notification
   */
  async processNotification(notification) {
    try {
      // Play sound if enabled
      if (this.preferences.audioEnabled && notification.sound) {
        this.playSound(notification.sound);
      }

      // Vibrate if supported and enabled
      if (
        'vibrate' in navigator &&
        notification.vibrate &&
        this.preferences.vibrateEnabled
      ) {
        navigator.vibrate(notification.vibrate);
      }

      // Show browser notification
      if (
        (this.preferences.browserNotifications &&
          document.visibilityState === 'hidden') ||
        notification.priority === 'high'
      ) {
        await this.showBrowserNotification(notification);
      }

      // Show in-app notification
      this.showInAppNotification(notification);

      // Store in history
      this.storeNotificationHistory(notification);

      // Trigger custom events
      this.triggerNotificationEvents(notification);
    } catch (error) {
      console.error('❌ Failed to process notification:', error);
    }
  }

  /**
   * Show browser notification
   */
  async showBrowserNotification(notification) {
    if (!('Notification' in window) || Notification.permission !== 'granted') {
      return;
    }

    const options = {
      body: notification.message,
      icon: '/images/icons/notification-icon.png',
      badge: '/images/icons/badge-icon.png',
      image: notification.data?.image,
      tag: notification.id,
      requireInteraction: notification.priority === 'high',
      silent: !this.preferences.audioEnabled,
      data: notification.data,
      actions: notification.actions?.slice(0, 2) || [],
    };

    const browserNotification = new Notification(notification.title, options);

    browserNotification.onclick = event => {
      event.preventDefault();
      window.focus();

      if (notification.actions?.[0]?.url) {
        window.location.href = notification.actions[0].url;
      }

      browserNotification.close();
    };

    // Auto-close after delay (except for persistent notifications)
    if (!notification.persistent) {
      setTimeout(() => {
        browserNotification.close();
      }, 8000);
    }
  }

  /**
   * Show in-app notification
   */
  showInAppNotification(notification) {
    const event = new CustomEvent('notify', {
      detail: {
        title: notification.title,
        message: notification.message,
        type: this.mapNotificationTypeToUIType(notification.type),
        data: notification.data,
        actions: notification.actions,
        duration: notification.persistent ? 0 : 5000,
      },
    });

    window.dispatchEvent(event);
  }

  /**
   * Map notification types to UI types
   */
  mapNotificationTypeToUIType(type) {
    const mapping = {
      price_alert: 'success',
      availability: 'info',
      system: 'info',
      purchase_update: 'success',
      maintenance: 'warning',
      platform_alert: 'warning',
      error: 'error',
    };

    return mapping[type] || 'info';
  }

  /**
   * Play notification sound
   */
  async playSound(soundName) {
    try {
      if (!this.audioContext || !this.sounds[soundName]) {
        return;
      }

      const source = this.audioContext.createBufferSource();
      source.buffer = this.sounds[soundName];
      source.connect(this.audioContext.destination);
      source.start();
    } catch (error) {
      console.warn('⚠️ Failed to play sound:', error);
    }
  }

  /**
   * Load audio file
   */
  async loadSound(name, url) {
    try {
      const response = await fetch(url);
      const arrayBuffer = await response.arrayBuffer();
      const audioBuffer = await this.audioContext.decodeAudioData(arrayBuffer);
      this.sounds[name] = audioBuffer;
    } catch (error) {
      console.warn(`⚠️ Failed to load sound ${name}:`, error);
    }
  }

  /**
   * Check if notification should be shown based on user preferences
   */
  shouldShowNotification(notification) {
    // Check if notifications are enabled
    if (!this.preferences.notificationsEnabled) {
      return false;
    }

    // Check quiet hours
    if (this.preferences.quietHours && this.isInQuietHours()) {
      return notification.priority === 'high';
    }

    // Check notification type preferences
    const typePreferences = this.preferences.types || {};
    if (typePreferences[notification.type] === false) {
      return false;
    }

    return true;
  }

  /**
   * Check if current time is in quiet hours
   */
  isInQuietHours() {
    if (!this.preferences.quietHoursStart || !this.preferences.quietHoursEnd) {
      return false;
    }

    const now = new Date();
    const currentTime = now.getHours() * 60 + now.getMinutes();

    const start = this.parseTimeString(this.preferences.quietHoursStart);
    const end = this.parseTimeString(this.preferences.quietHoursEnd);

    if (start < end) {
      return currentTime >= start && currentTime <= end;
    } else {
      return currentTime >= start || currentTime <= end;
    }
  }

  /**
   * Parse time string to minutes
   */
  parseTimeString(timeString) {
    const [hours, minutes] = timeString.split(':').map(Number);
    return hours * 60 + minutes;
  }

  /**
   * Store notification in history
   */
  storeNotificationHistory(notification) {
    let history = this.getNotificationHistory();

    // Add to beginning of array
    history.unshift({
      ...notification,
      readAt: null,
    });

    // Keep only last 100 notifications
    history = history.slice(0, 100);

    localStorage.setItem('hd_notification_history', JSON.stringify(history));
  }

  /**
   * Get notification history
   */
  getNotificationHistory() {
    try {
      const history = localStorage.getItem('hd_notification_history');
      return history ? JSON.parse(history) : [];
    } catch (error) {
      return [];
    }
  }

  /**
   * Mark notification as read
   */
  markNotificationAsRead(notificationId) {
    const history = this.getNotificationHistory();
    const notification = history.find(n => n.id === notificationId);

    if (notification) {
      notification.readAt = Date.now();
      localStorage.setItem('hd_notification_history', JSON.stringify(history));
    }
  }

  /**
   * Trigger notification-related events
   */
  triggerNotificationEvents(notification) {
    // Trigger type-specific events
    const typeEvent = new CustomEvent(`notification-${notification.type}`, {
      detail: notification,
    });
    document.dispatchEvent(typeEvent);

    // Trigger general notification event
    const generalEvent = new CustomEvent('notification-received', {
      detail: notification,
    });
    document.dispatchEvent(generalEvent);
  }

  /**
   * Handle various system events
   */
  handlePageVisible() {
    // Refresh connection if needed
    if (!this.isConnected) {
      this.reconnect();
    }
  }

  handlePageHidden() {
    // Reduce polling frequency or pause non-essential operations
  }

  handleOnline() {
    console.log('🌐 Network connection restored');
    this.reconnect();
  }

  handleOffline() {
    console.log('🔌 Network connection lost');
    this.showSystemNotification(
      'Network connection lost. Notifications may be delayed.',
      'warning'
    );
  }

  handleDisconnection() {
    this.showSystemNotification(
      'Real-time notifications disconnected. Attempting to reconnect...',
      'warning'
    );
    this.attemptReconnection();
  }

  handleConnectionError(error) {
    console.error('🚨 Connection error:', error);
    this.attemptReconnection();
  }

  /**
   * Attempt to reconnect
   */
  attemptReconnection() {
    if (this.retryCount >= this.config.retryAttempts) {
      this.showSystemNotification(
        'Unable to establish real-time connection. Please refresh the page.',
        'error'
      );
      return;
    }

    this.retryCount++;
    const delay = this.config.retryDelay * this.retryCount;

    setTimeout(() => {
      console.log(
        `🔄 Reconnection attempt ${this.retryCount}/${this.config.retryAttempts}`
      );
      this.reconnect();
    }, delay);
  }

  /**
   * Reconnect WebSocket
   */
  reconnect() {
    if (this.echo) {
      this.echo.disconnect();
    }

    this.initializeWebSocket().then(() => {
      this.subscribeToUserChannels();
    });
  }

  /**
   * Initialize fallback polling for notifications
   */
  initializeFallbackPolling() {
    console.log('📡 Using fallback polling for notifications');

    const poll = async () => {
      try {
        const response = await fetch('/api/notifications/poll', {
          headers: {
            Authorization: `Bearer ${this.getAuthToken()}`,
            'X-Requested-With': 'XMLHttpRequest',
          },
        });

        if (response.ok) {
          const notifications = await response.json();
          notifications.forEach(notification => {
            this.queueNotification(notification);
          });
        }
      } catch (error) {
        console.error('❌ Polling error:', error);
      }

      setTimeout(poll, 30000); // Poll every 30 seconds
    };

    poll();
  }

  /**
   * Utility functions
   */
  getAuthToken() {
    return (
      document
        .querySelector('meta[name="auth-token"]')
        ?.getAttribute('content') ||
      localStorage.getItem('auth_token') ||
      ''
    );
  }

  urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
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

  async registerPushSubscription(subscription) {
    try {
      await fetch('/api/push-notifications/subscribe', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content'),
        },
        body: JSON.stringify({
          subscription: subscription,
        }),
      });
    } catch (error) {
      console.error('❌ Failed to register push subscription:', error);
    }
  }

  showSystemNotification(message, type = 'info') {
    const notification = {
      id: `system_${Date.now()}`,
      type: 'system',
      title: 'HD Tickets',
      message: message,
      priority: 'low',
    };

    this.showInAppNotification(notification);
  }

  handleInitializationError(error) {
    console.error('❌ Notification system initialization failed:', error);
    this.showSystemNotification(
      'Notification system failed to start. Some features may not work correctly.',
      'error'
    );
  }

  /**
   * Preference management
   */
  loadPreferences() {
    const defaults = {
      notificationsEnabled: true,
      browserNotifications: true,
      audioEnabled: true,
      vibrateEnabled: true,
      quietHours: false,
      quietHoursStart: '22:00',
      quietHoursEnd: '08:00',
      types: {
        price_alert: true,
        availability: true,
        system: true,
        purchase_update: true,
        maintenance: false,
        platform_alert: true,
      },
    };

    try {
      const stored = localStorage.getItem('hd_notification_preferences');
      return stored ? { ...defaults, ...JSON.parse(stored) } : defaults;
    } catch (error) {
      return defaults;
    }
  }

  savePreferences() {
    try {
      localStorage.setItem(
        'hd_notification_preferences',
        JSON.stringify(this.preferences)
      );
    } catch (error) {
      console.error('❌ Failed to save preferences:', error);
    }
  }

  updatePreferences(newPreferences) {
    this.preferences = { ...this.preferences, ...newPreferences };
    this.savePreferences();
    console.log('✅ Notification preferences updated');
  }

  /**
   * Public API methods
   */
  getPreferences() {
    return { ...this.preferences };
  }

  setPreferences(preferences) {
    this.updatePreferences(preferences);
  }

  getHistory() {
    return this.getNotificationHistory();
  }

  clearHistory() {
    localStorage.removeItem('hd_notification_history');
  }

  testNotification() {
    const testNotification = {
      id: `test_${Date.now()}`,
      type: 'system',
      title: 'Test Notification',
      message:
        'This is a test notification to verify the system is working correctly.',
      priority: 'medium',
      sound: 'success',
    };

    this.queueNotification(testNotification);
  }

  getConnectionStatus() {
    return {
      isConnected: this.isConnected,
      hasServiceWorker: !!this.serviceWorker,
      hasPushSubscription: !!this.pushSubscription,
      hasAudio: !!this.audioContext,
      permissions: {
        notifications: Notification.permission,
        serviceWorker: 'serviceWorker' in navigator,
      },
    };
  }

  /**
   * Cleanup
   */
  destroy() {
    if (this.echo) {
      this.echo.disconnect();
    }

    if (this.batchTimer) {
      clearTimeout(this.batchTimer);
    }

    if (this.audioContext) {
      this.audioContext.close();
    }

    this.notificationQueue = [];
    console.log('🗑️ Notification system destroyed');
  }
}

// Export for use in other modules
window.NotificationSystem = NotificationSystem;

// Auto-initialize if config is available
document.addEventListener('DOMContentLoaded', () => {
  if (window.notificationConfig) {
    window.hdNotifications = new NotificationSystem(window.notificationConfig);
  }
});

export default NotificationSystem;
