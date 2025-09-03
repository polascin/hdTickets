/**
 * HD Tickets Enhanced Profile Features
 * Progressive Web App and Real-time Updates Module
 */

class HDTicketsProfileEnhancer {
  constructor() {
    this.isOnline = navigator.onLine;
    this.websocket = null;
    this.retryAttempts = 0;
    this.maxRetries = 3;
    this.updateInterval = null;
    this.observers = new Map();

    this.init();
  }

  async init() {
    console.log('🚀 Initializing HD Tickets Profile Enhancer...');

    // Initialize core features
    await this.registerServiceWorker();
    this.setupNetworkStatusListener();
    this.setupIntersectionObserver();
    this.initializeLazyLoading();
    this.setupRealTimeUpdates();
    this.initializeWebSocket();
    this.setupPushNotifications();
    this.enhanceProfileFeatures();

    console.log('✅ Profile Enhancer initialized successfully');
  }

  // Service Worker Registration
  async registerServiceWorker() {
    if ('serviceWorker' in navigator) {
      try {
        const registration = await navigator.serviceWorker.register('/sw.js');
        console.log('Service Worker registered:', registration);

        // Listen for updates
        registration.addEventListener('updatefound', () => {
          console.log('New Service Worker version available');
          this.showUpdateNotification();
        });

        // Handle messages from service worker
        navigator.serviceWorker.addEventListener('message', (event) => {
          this.handleServiceWorkerMessage(event.data);
        });

      } catch (error) {
        console.error('Service Worker registration failed:', error);
      }
    }
  }

  // Network Status Management
  setupNetworkStatusListener() {
    window.addEventListener('online', () => {
      this.isOnline = true;
      this.onNetworkStatusChange(true);
    });

    window.addEventListener('offline', () => {
      this.isOnline = false;
      this.onNetworkStatusChange(false);
    });
  }

  onNetworkStatusChange(isOnline) {
    console.log(`Network status changed: ${isOnline ? 'Online' : 'Offline'}`);

    const statusElement = document.getElementById('network-status');
    if (statusElement) {
      statusElement.className = `network-status ${isOnline ? 'online' : 'offline'}`;
      statusElement.innerHTML = `
                <i class="fas fa-${isOnline ? 'wifi' : 'wifi-slash'}"></i>
                ${isOnline ? 'Online' : 'Offline'}
            `;
    }

    if (isOnline) {
      this.syncOfflineData();
      this.reconnectWebSocket();
    }
  }

  // Lazy Loading Implementation
  setupIntersectionObserver() {
    this.lazyObserver = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          this.loadLazyContent(entry.target);
          this.lazyObserver.unobserve(entry.target);
        }
      });
    }, {
      rootMargin: '50px'
    });
  }

  initializeLazyLoading() {
    // Lazy load profile sections
    document.querySelectorAll('.lazy-section').forEach(section => {
      this.lazyObserver.observe(section);
    });

    // Lazy load images
    document.querySelectorAll('img[data-src]').forEach(img => {
      this.lazyObserver.observe(img);
    });
  }

  async loadLazyContent(element) {
    element.classList.add('loading');

    try {
      if (element.classList.contains('lazy-section')) {
        await this.loadSectionContent(element);
      } else if (element.tagName === 'IMG') {
        await this.loadImage(element);
      }

      element.classList.remove('loading');
      element.classList.add('loaded');

      // Trigger animation
      element.style.opacity = '0';
      element.style.transform = 'translateY(20px)';

      requestAnimationFrame(() => {
        element.style.transition = 'all 0.5s ease';
        element.style.opacity = '1';
        element.style.transform = 'translateY(0)';
      });

    } catch (error) {
      console.error('Failed to load lazy content:', error);
      element.classList.remove('loading');
      element.classList.add('error');
    }
  }

  async loadSectionContent(section) {
    const endpoint = section.dataset.endpoint;
    if (!endpoint) return;

    try {
      const response = await fetch(endpoint);
      if (!response.ok) throw new Error('Network response was not ok');

      const data = await response.json();
      this.renderSectionContent(section, data);

    } catch (error) {
      if (!this.isOnline) {
        // Try to load from cache
        const cachedData = await this.getCachedData(endpoint);
        if (cachedData) {
          this.renderSectionContent(section, cachedData);
        } else {
          this.renderOfflineMessage(section);
        }
      } else {
        throw error;
      }
    }
  }

  async loadImage(img) {
    return new Promise((resolve, reject) => {
      const image = new Image();
      image.onload = () => {
        img.src = img.dataset.src;
        img.removeAttribute('data-src');
        resolve();
      };
      image.onerror = reject;
      image.src = img.dataset.src;
    });
  }

  // Real-time Updates
  setupRealTimeUpdates() {
    // Update profile stats every 5 minutes
    this.updateInterval = setInterval(() => {
      if (this.isOnline) {
        this.updateProfileStats();
      }
    }, 5 * 60 * 1000);

    // Update on page focus
    document.addEventListener('visibilitychange', () => {
      if (!document.hidden && this.isOnline) {
        setTimeout(() => this.updateProfileStats(), 1000);
      }
    });
  }

  async updateProfileStats() {
    try {
      const response = await fetch('/profile/stats');
      if (!response.ok) throw new Error('Failed to fetch stats');

      const data = await response.json();
      this.updateStatsDisplay(data);

      // Cache the updated data
      if ('caches' in window) {
        const cache = await caches.open('hdtickets-dynamic-v1.2.0');
        cache.put('/profile/stats', response.clone());
      }

    } catch (error) {
      console.error('Failed to update profile stats:', error);
    }
  }

  updateStatsDisplay(data) {
    // Update progress rings
    this.updateProgressRings(data);

    // Update statistics cards
    this.updateStatsCards(data);

    // Update timestamps
    this.updateTimestamps();
  }

  updateProgressRings(data) {
    const progressRings = document.querySelectorAll('.progress-ring circle:last-child');

    progressRings.forEach((ring, index) => {
      const percentage = index === 0 ? data.profile_completion : data.security_score;
      const circumference = 2 * Math.PI * ring.r.baseVal.value;
      const offset = circumference - (percentage / 100) * circumference;

      ring.style.strokeDasharray = circumference;
      ring.style.strokeDashoffset = offset;
    });

    // Update text values
    const progressTexts = document.querySelectorAll('.progress-text');
    progressTexts.forEach((text, index) => {
      const value = index === 0 ? data.profile_completion + '%' : data.security_score;
      text.textContent = value;
    });
  }

  updateStatsCards(data) {
    const statsCards = {
      'total_tickets': data.total_tickets,
      'active_filters': data.active_filters,
      'successful_purchases': data.successful_purchases,
      'average_response_time': data.average_response_time + 'ms'
    };

    Object.entries(statsCards).forEach(([key, value]) => {
      const element = document.getElementById(`stat-${key}`);
      if (element) {
        this.animateValue(element, value);
      }
    });
  }

  animateValue(element, newValue) {
    const currentValue = element.textContent;
    if (currentValue !== newValue.toString()) {
      element.style.transform = 'scale(1.1)';
      element.style.color = '#10b981';

      setTimeout(() => {
        element.textContent = newValue;
        element.style.transform = 'scale(1)';
        element.style.color = '';
      }, 150);
    }
  }

  updateTimestamps() {
    document.querySelectorAll('.stats-updated').forEach(el => {
      el.textContent = `Last updated: ${new Date().toLocaleTimeString()}`;
    });
  }

  // WebSocket for Real-time Communication
  initializeWebSocket() {
    if (!window.Echo) return;

    try {
      // Listen for profile updates
      window.Echo.private(`user.${window.userId}`)
        .listen('ProfileStatsUpdated', (event) => {
          console.log('Real-time profile update received:', event);
          this.updateStatsDisplay(event.stats);
        });

      console.log('WebSocket listeners registered');
    } catch (error) {
      console.error('Failed to initialize WebSocket:', error);
    }
  }

  reconnectWebSocket() {
    if (this.websocket) {
      this.websocket.close();
    }
    setTimeout(() => {
      this.initializeWebSocket();
    }, 2000);
  }

  // Push Notifications
  async setupPushNotifications() {
    if (!('Notification' in window) || !('serviceWorker' in navigator)) {
      console.log('Push notifications not supported');
      return;
    }

    // Request permission
    if (Notification.permission === 'default') {
      const permission = await Notification.requestPermission();
      console.log('Notification permission:', permission);
    }

    if (Notification.permission === 'granted') {
      await this.subscribeToPushNotifications();
    }
  }

  async subscribeToPushNotifications() {
    try {
      const registration = await navigator.serviceWorker.ready;
      const subscription = await registration.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: this.urlBase64ToUint8Array(window.vapidPublicKey || '')
      });

      // Send subscription to server
      await fetch('/api/push-subscriptions', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
        },
        body: JSON.stringify(subscription)
      });

      console.log('Push notification subscription successful');
    } catch (error) {
      console.error('Failed to subscribe to push notifications:', error);
    }
  }

  urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);
    for (let i = 0; i < rawData.length; ++i) {
      outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
  }

  // Enhanced Profile Features
  enhanceProfileFeatures() {
    this.setupProfilePhotoUpload();
    this.setupPreferencesForm();
    this.setupKeyboardShortcuts();
    this.addProfileInsights();
  }

  setupProfilePhotoUpload() {
    const photoInput = document.getElementById('profile-photo-input');
    const photoPreview = document.getElementById('profile-photo-preview');

    if (photoInput && photoPreview) {
      photoInput.addEventListener('change', (event) => {
        const file = event.target.files[0];
        if (file) {
          const reader = new FileReader();
          reader.onload = (e) => {
            photoPreview.src = e.target.result;
            photoPreview.style.display = 'block';
          };
          reader.readAsDataURL(file);
        }
      });
    }
  }

  setupPreferencesForm() {
    const preferencesForm = document.getElementById('preferences-form');
    if (preferencesForm) {
      preferencesForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        await this.savePreferences(new FormData(preferencesForm));
      });
    }
  }

  async savePreferences(formData) {
    try {
      const response = await fetch('/profile/preferences', {
        method: 'POST',
        body: formData,
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
        }
      });

      const result = await response.json();
      if (result.success) {
        this.showNotification('Preferences saved successfully!', 'success');
      } else {
        this.showNotification('Failed to save preferences', 'error');
      }
    } catch (error) {
      console.error('Failed to save preferences:', error);
      this.showNotification('Failed to save preferences', 'error');
    }
  }

  setupKeyboardShortcuts() {
    document.addEventListener('keydown', (event) => {
      if (event.ctrlKey || event.metaKey) {
        switch (event.key) {
          case 'p':
            event.preventDefault();
            window.location.href = '/profile';
            break;
          case 'd':
            event.preventDefault();
            window.location.href = '/dashboard';
            break;
          case 's':
            event.preventDefault();
            window.location.href = '/profile/security';
            break;
        }
      }
    });
  }

  addProfileInsights() {
    // Add performance insights
    if (window.performance) {
      const navigationTiming = performance.getEntriesByType('navigation')[0];
      const loadTime = navigationTiming.loadEventEnd - navigationTiming.loadEventStart;

      if (loadTime > 0) {
        this.addInsight({
          type: 'performance',
          title: 'Page Performance',
          value: `${Math.round(loadTime)}ms`,
          description: 'Page load time'
        });
      }
    }
  }

  addInsight(insight) {
    const insightsContainer = document.getElementById('profile-insights');
    if (insightsContainer) {
      const insightElement = document.createElement('div');
      insightElement.className = 'insight-item';
      insightElement.innerHTML = `
                <div class="insight-icon">
                    <i class="fas fa-${insight.type === 'performance' ? 'tachometer-alt' : 'info-circle'}"></i>
                </div>
                <div class="insight-content">
                    <h6>${insight.title}</h6>
                    <span class="insight-value">${insight.value}</span>
                    <small class="text-muted">${insight.description}</small>
                </div>
            `;
      insightsContainer.appendChild(insightElement);
    }
  }

  // Utility Functions
  showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

    document.body.appendChild(notification);

    setTimeout(() => {
      notification.remove();
    }, 5000);
  }

  async getCachedData(endpoint) {
    if ('caches' in window) {
      try {
        const cache = await caches.open('hdtickets-dynamic-v1.2.0');
        const response = await cache.match(endpoint);
        return response ? await response.json() : null;
      } catch (error) {
        console.error('Failed to get cached data:', error);
        return null;
      }
    }
    return null;
  }

  async syncOfflineData() {
    console.log('Syncing offline data...');

    // Trigger background sync
    if ('serviceWorker' in navigator && 'sync' in window.ServiceWorkerRegistration.prototype) {
      try {
        const registration = await navigator.serviceWorker.ready;
        await registration.sync.register('profile-sync');
        console.log('Background sync registered');
      } catch (error) {
        console.error('Background sync registration failed:', error);
      }
    }
  }

  renderSectionContent(section, data) {
    // Implement section-specific rendering logic
    const sectionType = section.dataset.section;

    switch (sectionType) {
      case 'analytics':
        this.renderAnalytics(section, data);
        break;
      case 'security':
        this.renderSecurity(section, data);
        break;
      default:
        section.innerHTML = '<p>Content loaded successfully</p>';
    }
  }

  renderAnalytics(section, data) {
    section.innerHTML = `
            <div class="analytics-summary">
                <h5>Analytics Overview</h5>
                <div class="row">
                    <div class="col-md-6">
                        <div class="metric">
                            <label>Activity Score</label>
                            <div class="value">${data.activity_score || 'N/A'}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="metric">
                            <label>Engagement Level</label>
                            <div class="value">${data.engagement_level || 'N/A'}</div>
                        </div>
                    </div>
                </div>
            </div>
        `;
  }

  renderSecurity(section, data) {
    section.innerHTML = `
            <div class="security-summary">
                <h5>Security Status</h5>
                <div class="security-indicators">
                    <div class="indicator ${data.two_factor_enabled ? 'enabled' : 'disabled'}">
                        <i class="fas fa-shield-alt"></i>
                        Two-Factor Authentication
                    </div>
                    <div class="indicator ${data.email_verified ? 'enabled' : 'disabled'}">
                        <i class="fas fa-envelope-check"></i>
                        Email Verified
                    </div>
                </div>
            </div>
        `;
  }

  renderOfflineMessage(section) {
    section.innerHTML = `
            <div class="offline-message text-center py-4">
                <i class="fas fa-wifi-slash text-muted mb-3" style="font-size: 2rem;"></i>
                <p class="text-muted">This content is not available offline.</p>
                <small class="text-muted">Connect to the internet to view this section.</small>
            </div>
        `;
  }

  showUpdateNotification() {
    const updateBanner = document.createElement('div');
    updateBanner.className = 'update-banner alert alert-info position-fixed';
    updateBanner.style.cssText = 'top: 0; left: 0; right: 0; z-index: 10000; border-radius: 0;';
    updateBanner.innerHTML = `
            <div class="container d-flex justify-content-between align-items-center">
                <span><i class="fas fa-download me-2"></i>A new version is available!</span>
                <button class="btn btn-sm btn-primary" onclick="window.location.reload()">Update Now</button>
            </div>
        `;

    document.body.insertBefore(updateBanner, document.body.firstChild);
  }

  handleServiceWorkerMessage(data) {
    console.log('Message from Service Worker:', data);

    switch (data.type) {
      case 'CACHE_UPDATED':
        this.showNotification('Content updated in background', 'info');
        break;
      case 'SYNC_COMPLETE':
        this.showNotification('Data synchronized successfully', 'success');
        break;
    }
  }

  // Cleanup
  destroy() {
    if (this.updateInterval) {
      clearInterval(this.updateInterval);
    }

    if (this.lazyObserver) {
      this.lazyObserver.disconnect();
    }

    if (this.websocket) {
      this.websocket.close();
    }
  }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
  if (typeof window !== 'undefined') {
    window.hdTicketsProfileEnhancer = new HDTicketsProfileEnhancer();
  }
});

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
  module.exports = HDTicketsProfileEnhancer;
}
