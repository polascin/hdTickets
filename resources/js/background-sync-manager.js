/* Background Sync Manager for HD Tickets PWA */

export default class BackgroundSyncManager {
  constructor() {
    this.syncTags = {
      TICKET_ALERTS: 'ticket-alerts-sync',
      PRICE_UPDATES: 'price-updates-sync',
      USER_PREFERENCES: 'user-preferences-sync',
      PURCHASE_QUEUE: 'purchase-queue-sync',
      WATCHLIST_SYNC: 'watchlist-sync',
      ANALYTICS_SYNC: 'analytics-sync'
    };
    
    this.endpoints = {
      alerts: '/api/sync/alerts',
      prices: '/api/sync/prices',
      preferences: '/api/sync/preferences',
      purchases: '/api/sync/purchases',
      watchlist: '/api/sync/watchlist',
      analytics: '/api/sync/analytics'
    };

    this.syncIntervals = new Map();
    this.retryAttempts = new Map();
    this.maxRetries = 3;
    this.init();
  }

  async init() {
    if (!('serviceWorker' in navigator) || !('sync' in window.ServiceWorkerRegistration.prototype)) {
      console.warn('Background Sync not supported, falling back to periodic sync');
      this.initPeriodicSync();
      return;
    }

    try {
      const registration = await navigator.serviceWorker.ready;
      this.registration = registration;
      this.initEventListeners();
      console.log('Background Sync Manager initialized');
    } catch (error) {
      console.error('Failed to initialize Background Sync:', error);
      this.initPeriodicSync();
    }
  }

  initEventListeners() {
    // Listen for data changes that trigger sync
    document.addEventListener('data:changed', (e) => {
      this.scheduleSync(e.detail.type, e.detail.data);
    });

    // Listen for network status changes
    window.addEventListener('online', () => {
      this.onNetworkStatusChange(true);
    });

    window.addEventListener('offline', () => {
      this.onNetworkStatusChange(false);
    });

    // Listen for visibility changes (app becomes active)
    document.addEventListener('visibilitychange', () => {
      if (!document.hidden) {
        this.syncCriticalData();
      }
    });

    // Listen for custom sync requests
    document.addEventListener('sync:request', (e) => {
      this.scheduleSync(e.detail.tag, e.detail.data);
    });
  }

  async scheduleSync(type, data = {}) {
    if (!this.registration) {
      console.warn('Service worker not available, storing for later sync');
      await this.storeForLaterSync(type, data);
      return;
    }

    try {
      const tag = this.getSyncTag(type);
      if (data && Object.keys(data).length > 0) {
        await this.storeSyncData(tag, data);
      }
      
      await this.registration.sync.register(tag);
      console.log(`Scheduled background sync: ${tag}`);
      
      // Also attempt immediate sync if online
      if (navigator.onLine) {
        this.attemptImmediateSync(type, data);
      }
    } catch (error) {
      console.error('Failed to schedule background sync:', error);
      await this.storeForLaterSync(type, data);
    }
  }

  getSyncTag(type) {
    const tagMap = {
      'ticket-alerts': this.syncTags.TICKET_ALERTS,
      'price-updates': this.syncTags.PRICE_UPDATES,
      'user-preferences': this.syncTags.USER_PREFERENCES,
      'purchase-queue': this.syncTags.PURCHASE_QUEUE,
      'watchlist': this.syncTags.WATCHLIST_SYNC,
      'analytics': this.syncTags.ANALYTICS_SYNC
    };
    return tagMap[type] || type;
  }

  async storeSyncData(tag, data) {
    try {
      const syncData = {
        tag,
        data,
        timestamp: Date.now(),
        retries: 0
      };
      
      localStorage.setItem(`sync_${tag}`, JSON.stringify(syncData));
    } catch (error) {
      console.error('Failed to store sync data:', error);
    }
  }

  async storeForLaterSync(type, data) {
    try {
      const pendingSync = JSON.parse(localStorage.getItem('pending_sync') || '[]');
      pendingSync.push({
        type,
        data,
        timestamp: Date.now()
      });
      localStorage.setItem('pending_sync', JSON.stringify(pendingSync));
    } catch (error) {
      console.error('Failed to store pending sync:', error);
    }
  }

  async attemptImmediateSync(type, data = {}) {
    try {
      const endpoint = this.getEndpoint(type);
      if (!endpoint) return;

      const response = await fetch(endpoint, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        credentials: 'same-origin',
        body: JSON.stringify(data)
      });

      if (response.ok) {
        console.log(`Immediate sync successful: ${type}`);
        this.clearSyncData(this.getSyncTag(type));
        this.notifyUI('sync:success', { type, timestamp: Date.now() });
      } else {
        throw new Error(`Sync failed: ${response.status}`);
      }
    } catch (error) {
      console.error(`Immediate sync failed for ${type}:`, error);
      // Let background sync handle it
    }
  }

  getEndpoint(type) {
    const endpointMap = {
      'ticket-alerts': this.endpoints.alerts,
      'price-updates': this.endpoints.prices,
      'user-preferences': this.endpoints.preferences,
      'purchase-queue': this.endpoints.purchases,
      'watchlist': this.endpoints.watchlist,
      'analytics': this.endpoints.analytics
    };
    return endpointMap[type];
  }

  clearSyncData(tag) {
    try {
      localStorage.removeItem(`sync_${tag}`);
    } catch (error) {
      console.error('Failed to clear sync data:', error);
    }
  }

  async onNetworkStatusChange(isOnline) {
    if (isOnline) {
      console.log('Network restored, processing pending syncs');
      await this.processPendingSyncs();
      this.notifyUI('network:online', { timestamp: Date.now() });
    } else {
      console.log('Network lost, enabling offline mode');
      this.notifyUI('network:offline', { timestamp: Date.now() });
    }
  }

  async processPendingSyncs() {
    try {
      const pendingSync = JSON.parse(localStorage.getItem('pending_sync') || '[]');
      
      for (const item of pendingSync) {
        await this.scheduleSync(item.type, item.data);
      }
      
      localStorage.removeItem('pending_sync');
      console.log(`Processed ${pendingSync.length} pending syncs`);
    } catch (error) {
      console.error('Failed to process pending syncs:', error);
    }
  }

  async syncCriticalData() {
    const criticalSyncs = [
      'ticket-alerts',
      'price-updates',
      'watchlist'
    ];

    console.log('Syncing critical data...');
    
    for (const syncType of criticalSyncs) {
      await this.scheduleSync(syncType);
    }
  }

  // Periodic sync fallback for browsers without Background Sync
  initPeriodicSync() {
    console.log('Initializing periodic sync fallback');
    
    // Sync critical data every 2 minutes
    this.syncIntervals.set('critical', setInterval(() => {
      if (navigator.onLine && !document.hidden) {
        this.syncCriticalData();
      }
    }, 2 * 60 * 1000));

    // Sync user data every 5 minutes
    this.syncIntervals.set('user', setInterval(() => {
      if (navigator.onLine && !document.hidden) {
        this.scheduleSync('user-preferences');
        this.scheduleSync('analytics');
      }
    }, 5 * 60 * 1000));

    // Sync less critical data every 10 minutes
    this.syncIntervals.set('background', setInterval(() => {
      if (navigator.onLine) {
        this.scheduleSync('purchase-queue');
      }
    }, 10 * 60 * 1000));
  }

  // Public API methods
  async syncTicketAlerts(alertData = {}) {
    return this.scheduleSync('ticket-alerts', alertData);
  }

  async syncPriceUpdates(priceData = {}) {
    return this.scheduleSync('price-updates', priceData);
  }

  async syncUserPreferences(preferences = {}) {
    return this.scheduleSync('user-preferences', preferences);
  }

  async syncWatchlist(watchlistData = {}) {
    return this.scheduleSync('watchlist', watchlistData);
  }

  async syncPurchaseQueue(purchaseData = {}) {
    return this.scheduleSync('purchase-queue', purchaseData);
  }

  async syncAnalytics(analyticsData = {}) {
    return this.scheduleSync('analytics', analyticsData);
  }

  async forceSyncAll() {
    console.log('Force syncing all data...');
    const syncTypes = [
      'ticket-alerts',
      'price-updates', 
      'user-preferences',
      'watchlist',
      'purchase-queue',
      'analytics'
    ];

    const promises = syncTypes.map(type => this.scheduleSync(type));
    await Promise.allSettled(promises);
    console.log('Force sync completed');
  }

  notifyUI(event, data = {}) {
    document.dispatchEvent(new CustomEvent(event, { detail: data }));
  }

  // Cleanup method
  destroy() {
    this.syncIntervals.forEach(interval => clearInterval(interval));
    this.syncIntervals.clear();
    console.log('Background Sync Manager destroyed');
  }

  // Get sync status for UI
  getSyncStatus() {
    const pendingSync = JSON.parse(localStorage.getItem('pending_sync') || '[]');
    const hasPendingSync = pendingSync.length > 0;
    
    return {
      online: navigator.onLine,
      hasPendingSync,
      pendingCount: pendingSync.length,
      lastSync: localStorage.getItem('last_sync_timestamp'),
      supportsBgSync: 'sync' in window.ServiceWorkerRegistration.prototype
    };
  }
}
