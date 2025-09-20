/* Auto Refresh Manager for HD Tickets PWA */

export default class AutoRefreshManager {
  constructor() {
    this.refreshIntervals = new Map();
    this.refreshCallbacks = new Map();
    this.isActive = true;
    this.networkStatus = navigator.onLine;

    this.config = {
      // Refresh intervals in milliseconds
      intervals: {
        ticketPrices: 30 * 1000, // 30 seconds
        ticketAlerts: 45 * 1000, // 45 seconds
        watchlist: 60 * 1000, // 1 minute
        dashboard: 120 * 1000, // 2 minutes
        analytics: 300 * 1000, // 5 minutes
        notifications: 15 * 1000, // 15 seconds
      },
      // Exponential backoff for failed requests
      retryDelays: [1000, 2000, 5000, 10000], // 1s, 2s, 5s, 10s
      maxConsecutiveFailures: 3,
    };

    this.retryCounters = new Map();
    this.lastUpdateTimes = new Map();
    this.init();
  }

  init() {
    this.bindEventListeners();
    this.startCriticalRefreshers();
    console.log('Auto Refresh Manager initialized');
  }

  bindEventListeners() {
    // Handle network status changes
    window.addEventListener('online', () => {
      this.networkStatus = true;
      this.resumeAllRefreshers();
      console.log('Network restored - resuming auto refresh');
    });

    window.addEventListener('offline', () => {
      this.networkStatus = false;
      this.pauseAllRefreshers();
      console.log('Network lost - pausing auto refresh');
    });

    // Handle visibility changes (page focus/blur)
    document.addEventListener('visibilitychange', () => {
      if (document.hidden) {
        this.pauseNonCriticalRefreshers();
      } else {
        this.resumeAllRefreshers();
        this.performImmediateRefresh();
      }
    });

    // Handle user activity
    const activityEvents = [
      'mousedown',
      'mousemove',
      'keypress',
      'scroll',
      'touchstart',
    ];
    activityEvents.forEach(event => {
      document.addEventListener(event, () => this.onUserActivity(), {
        passive: true,
      });
    });

    // Listen for manual refresh requests
    document.addEventListener('refresh:request', e => {
      this.refreshData(e.detail.type, e.detail.force);
    });
  }

  startCriticalRefreshers() {
    // Only start refreshers for data that should always be fresh
    this.startRefresher('notifications');

    // Start others based on current page
    this.startContextualRefreshers();
  }

  startContextualRefreshers() {
    const path = window.location.pathname;

    if (path.includes('/dashboard')) {
      this.startRefresher('dashboard');
      this.startRefresher('ticketAlerts');
    }

    if (path.includes('/tickets') || path.includes('/search')) {
      this.startRefresher('ticketPrices');
    }

    if (path.includes('/watchlist')) {
      this.startRefresher('watchlist');
      this.startRefresher('ticketPrices');
    }

    if (path.includes('/analytics')) {
      this.startRefresher('analytics');
    }

    if (path.includes('/alerts') || path.includes('/monitoring')) {
      this.startRefresher('ticketAlerts');
    }
  }

  startRefresher(type) {
    if (this.refreshIntervals.has(type)) {
      return; // Already running
    }

    const interval = this.config.intervals[type];
    if (!interval) {
      console.warn(`No refresh interval configured for: ${type}`);
      return;
    }

    const intervalId = setInterval(() => {
      if (this.shouldRefresh(type)) {
        this.refreshData(type);
      }
    }, interval);

    this.refreshIntervals.set(type, intervalId);
    console.log(`Started auto refresh for ${type} (${interval}ms)`);

    // Initial refresh
    setTimeout(() => this.refreshData(type), 1000);
  }

  stopRefresher(type) {
    const intervalId = this.refreshIntervals.get(type);
    if (intervalId) {
      clearInterval(intervalId);
      this.refreshIntervals.delete(type);
      console.log(`Stopped auto refresh for ${type}`);
    }
  }

  shouldRefresh(type) {
    if (!this.networkStatus || !this.isActive) {
      return false;
    }

    // Check if too many consecutive failures
    const failures = this.retryCounters.get(type) || 0;
    if (failures >= this.config.maxConsecutiveFailures) {
      console.warn(`Too many failures for ${type}, skipping refresh`);
      return false;
    }

    // Throttle refreshes when page is hidden
    if (document.hidden && !this.isCriticalRefresher(type)) {
      return false;
    }

    return true;
  }

  isCriticalRefresher(type) {
    const critical = ['notifications', 'ticketAlerts'];
    return critical.includes(type);
  }

  async refreshData(type, force = false) {
    if (!force && !this.shouldRefresh(type)) {
      return;
    }

    try {
      const endpoint = this.getEndpoint(type);
      const lastUpdate = this.lastUpdateTimes.get(type) || 0;

      const headers = {
        'X-Requested-With': 'XMLHttpRequest',
        'Content-Type': 'application/json',
      };

      // Add conditional headers for efficiency
      if (lastUpdate && !force) {
        headers['If-Modified-Since'] = new Date(lastUpdate).toUTCString();
      }

      const response = await fetch(endpoint, {
        headers,
        credentials: 'same-origin',
      });

      if (response.status === 304) {
        // Not modified, data is still fresh
        console.log(`${type} data not modified`);
        return;
      }

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
      }

      const data = await response.json();

      // Process the updated data
      await this.processRefreshData(type, data);

      // Reset retry counter on success
      this.retryCounters.set(type, 0);
      this.lastUpdateTimes.set(type, Date.now());

      console.log(`Successfully refreshed ${type} data`);

      // Notify UI of data update
      document.dispatchEvent(
        new CustomEvent('data:refreshed', {
          detail: { type, data, timestamp: Date.now() },
        })
      );
    } catch (error) {
      console.error(`Failed to refresh ${type} data:`, error);

      // Increment failure counter
      const failures = (this.retryCounters.get(type) || 0) + 1;
      this.retryCounters.set(type, failures);

      // Implement exponential backoff
      if (failures < this.config.maxConsecutiveFailures) {
        const delay =
          this.config.retryDelays[
            Math.min(failures - 1, this.config.retryDelays.length - 1)
          ];
        setTimeout(() => this.refreshData(type), delay);
      }

      // Notify UI of refresh failure
      document.dispatchEvent(
        new CustomEvent('data:refresh-failed', {
          detail: { type, error: error.message, failures },
        })
      );
    }
  }

  getEndpoint(type) {
    const endpoints = {
      ticketPrices: '/api/refresh/ticket-prices',
      ticketAlerts: '/api/refresh/ticket-alerts',
      watchlist: '/api/refresh/watchlist',
      dashboard: '/api/refresh/dashboard',
      analytics: '/api/refresh/analytics',
      notifications: '/api/refresh/notifications',
    };

    return endpoints[type];
  }

  async processRefreshData(type, data) {
    switch (type) {
      case 'ticketPrices':
        await this.updateTicketPrices(data);
        break;
      case 'ticketAlerts':
        await this.updateTicketAlerts(data);
        break;
      case 'watchlist':
        await this.updateWatchlist(data);
        break;
      case 'dashboard':
        await this.updateDashboard(data);
        break;
      case 'analytics':
        await this.updateAnalytics(data);
        break;
      case 'notifications':
        await this.updateNotifications(data);
        break;
      default:
        console.warn(`No processor for refresh type: ${type}`);
    }
  }

  async updateTicketPrices(data) {
    // Update price displays in the DOM
    if (data.prices) {
      data.prices.forEach(priceUpdate => {
        const priceElements = document.querySelectorAll(
          `[data-ticket-id="${priceUpdate.ticket_id}"] .price`
        );
        priceElements.forEach(el => {
          const oldPrice = parseFloat(el.dataset.price || '0');
          const newPrice = parseFloat(priceUpdate.current_price);

          el.textContent = `$${newPrice.toFixed(2)}`;
          el.dataset.price = newPrice;

          // Add price change indicator
          if (oldPrice !== newPrice) {
            el.classList.remove('price-up', 'price-down');
            if (newPrice > oldPrice) {
              el.classList.add('price-up');
            } else if (newPrice < oldPrice) {
              el.classList.add('price-down');
            }

            // Trigger price change animation
            el.style.animation = 'none';
            el.offsetHeight; // Force reflow
            el.style.animation = 'priceChange 0.5s ease-out';
          }
        });
      });
    }
  }

  async updateTicketAlerts(data) {
    // Update alert counts and triggered alerts
    if (data.alerts) {
      const alertCountEl = document.querySelector('.alert-count');
      if (alertCountEl) {
        alertCountEl.textContent = data.total_active || 0;
      }

      const triggeredAlerts = data.alerts.filter(alert => alert.triggered);
      if (triggeredAlerts.length > 0) {
        // Show notification for triggered alerts
        document.dispatchEvent(
          new CustomEvent('alerts:triggered', {
            detail: { alerts: triggeredAlerts },
          })
        );
      }
    }
  }

  async updateWatchlist(data) {
    // Update watchlist items
    if (data.watchlist_items) {
      const watchlistContainer = document.querySelector('.watchlist-container');
      if (watchlistContainer) {
        // Trigger watchlist update event
        document.dispatchEvent(
          new CustomEvent('watchlist:updated', {
            detail: { items: data.watchlist_items },
          })
        );
      }
    }
  }

  async updateDashboard(data) {
    // Update dashboard widgets
    if (data.stats) {
      Object.entries(data.stats).forEach(([key, value]) => {
        const statEl = document.querySelector(`[data-stat="${key}"]`);
        if (statEl) {
          statEl.textContent = value;
        }
      });
    }
  }

  async updateAnalytics(data) {
    // Update analytics charts and data
    if (data.analytics && window.updateAnalyticsCharts) {
      window.updateAnalyticsCharts(data.analytics);
    }
  }

  async updateNotifications(data) {
    // Update notification count and list
    if (data.notifications !== undefined) {
      const notifCount = data.unread_count || 0;

      // Update notification badge
      const badges = document.querySelectorAll('.notification-badge');
      badges.forEach(badge => {
        badge.style.display = notifCount > 0 ? 'inline-flex' : 'none';
        badge.textContent = notifCount;
      });

      // Trigger notification update event
      document.dispatchEvent(
        new CustomEvent('notifications:updated', {
          detail: {
            notifications: data.notifications,
            unreadCount: notifCount,
          },
        })
      );
    }
  }

  onUserActivity() {
    // Reset retry counters on user activity
    this.retryCounters.clear();

    // Ensure refreshers are active
    if (!this.isActive) {
      this.isActive = true;
      this.resumeAllRefreshers();
    }
  }

  pauseAllRefreshers() {
    this.isActive = false;
    console.log('Paused all auto refreshers');
  }

  pauseNonCriticalRefreshers() {
    // Keep critical refreshers running, pause others
    const nonCritical = ['ticketPrices', 'watchlist', 'dashboard', 'analytics'];
    nonCritical.forEach(type => {
      const intervalId = this.refreshIntervals.get(type);
      if (intervalId) {
        clearInterval(intervalId);
        this.refreshIntervals.delete(type);
      }
    });
    console.log('Paused non-critical auto refreshers');
  }

  resumeAllRefreshers() {
    if (!this.networkStatus) return;

    this.isActive = true;
    this.startContextualRefreshers();
    console.log('Resumed all auto refreshers');
  }

  performImmediateRefresh() {
    // Refresh all active data immediately when page becomes visible
    const activeRefreshers = Array.from(this.refreshIntervals.keys());
    activeRefreshers.forEach(type => {
      setTimeout(() => this.refreshData(type, true), Math.random() * 2000); // Stagger requests
    });
  }

  // Public API
  forceRefresh(type = null) {
    if (type) {
      this.refreshData(type, true);
    } else {
      this.performImmediateRefresh();
    }
  }

  getRefreshStatus() {
    return {
      isActive: this.isActive,
      networkStatus: this.networkStatus,
      activeRefreshers: Array.from(this.refreshIntervals.keys()),
      retryCounters: Object.fromEntries(this.retryCounters),
      lastUpdateTimes: Object.fromEntries(this.lastUpdateTimes),
    };
  }

  destroy() {
    // Clean up all intervals
    this.refreshIntervals.forEach(intervalId => clearInterval(intervalId));
    this.refreshIntervals.clear();
    this.refreshCallbacks.clear();
    this.isActive = false;
    console.log('Auto Refresh Manager destroyed');
  }
}
