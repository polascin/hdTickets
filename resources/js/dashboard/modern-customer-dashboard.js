/**
 * Modern Customer Dashboard Alpine.js Component
 * 
 * Provides reactive data binding and interactive functionality for the 
 * comprehensive customer dashboard with real-time updates.
 */
export function modernCustomerDashboard() {
  return {
    // State management
    activeTab: 'dashboard',
    sidebarOpen: false,
    isLoading: false,
    isConnected: navigator.onLine,
    isRealTimeConnected: false,
    lastUpdated: 'now',
    errorMessage: '',
    retryCount: 0,
    maxRetries: 3,

    // Data containers
    stats: {
      available_tickets: 0,
      new_today: 0,
      monitored_events: 0,
      active_alerts: 0,
      total_savings: 0.0,
      price_alerts_triggered: 0,
      price_trend: null
    },

    tickets: [],
    alerts: [],
    recommendations: [],
    marketInsights: [],

    // Pagination and infinite scroll
    currentPage: 1,
    hasMoreTickets: true,
    loadingMore: false,

    // Auto-refresh settings
    refreshInterval: null,
    refreshRate: 30000, // 30 seconds
    backgroundRefreshInterval: null,

    // API endpoints
    endpoints: {
      stats: '/ajax/customer-dashboard/stats',
      tickets: '/ajax/customer-dashboard/tickets',
      alerts: '/ajax/customer-dashboard/alerts',
      recommendations: '/ajax/customer-dashboard/recommendations',
      marketInsights: '/ajax/customer-dashboard/market-insights'
    },

    /**
     * Initialize the dashboard
     */
    init() {
      console.log('🚀 Initializing Modern Customer Dashboard');

      this.initializeData();
      this.setupEventListeners();
      this.setupMobileHandling();
      this.setupInfiniteScroll();
      this.setupRealTimeIntegration();
      this.startAutoRefresh();

      // Load initial data with retry logic
      this.loadAllDataWithRetry();

      console.log('✅ Dashboard initialization complete');
    },

    /**
     * Initialize default data from server-side rendered content
     */
    initializeData() {
      // Get initial data from page if available
      const statsElement = document.querySelector('[data-stats]');
      if (statsElement) {
        try {
          this.stats = { ...this.stats, ...JSON.parse(statsElement.dataset.stats) };
        } catch (e) {
          console.warn('Failed to parse initial stats:', e);
        }
      }

      const ticketsElement = document.querySelector('[data-tickets]');
      if (ticketsElement) {
        try {
          this.tickets = JSON.parse(ticketsElement.dataset.tickets);
        } catch (e) {
          console.warn('Failed to parse initial tickets:', e);
        }
      }
    },

    /**
     * Start auto-refresh functionality
     */
    startAutoRefresh() {
      if (this.refreshInterval) {
        clearInterval(this.refreshInterval);
      }

      this.refreshInterval = setInterval(() => {
        if (!document.hidden && this.activeTab === 'dashboard') {
          this.loadStats();
          this.updateLastUpdated();
        }
      }, this.refreshRate);

      // Stop refresh when page is hidden to save bandwidth
      document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
          clearInterval(this.refreshInterval);
        } else {
          this.startAutoRefresh();
        }
      });
    },

    /**
     * Setup event listeners
     */
    setupEventListeners() {
      // Handle connection status with retry logic
      window.addEventListener('online', () => {
        this.isConnected = true;
        this.retryCount = 0;
        this.errorMessage = '';
        this.loadAllDataWithRetry();
        this.showNotification('success', 'Connection restored! Refreshing data...');
      });

      window.addEventListener('offline', () => {
        this.isConnected = false;
        this.showNotification('error', 'You are currently offline. Some features may be limited.');
      });

      // Handle tab changes
      this.$watch('activeTab', (newTab) => {
        this.onTabChange(newTab);
      });

      // Handle keyboard shortcuts
      document.addEventListener('keydown', (e) => {
        this.handleKeyboardShortcuts(e);
      });

      // Handle page visibility changes
      document.addEventListener('visibilitychange', () => {
        if (!document.hidden && this.isConnected) {
          // Page became visible, refresh data if stale
          const now = Date.now();
          const lastUpdate = localStorage.getItem('dashboard_last_update');
          if (!lastUpdate || (now - parseInt(lastUpdate)) > 300000) { // 5 minutes
            this.loadAllData();
          }
        }
      });

      // Handle errors globally
      window.addEventListener('error', (e) => {
        console.error('Global error:', e.error);
        this.handleError(e.error);
      });
    },

    /**
     * Setup mobile-specific handling
     */
    setupMobileHandling() {
      // Close sidebar when clicking outside on mobile
      document.addEventListener('click', (e) => {
        if (window.innerWidth < 768 && this.sidebarOpen) {
          const sidebar = document.querySelector('.sidebar');
          const menuButton = document.querySelector('[\\@click="sidebarOpen = !sidebarOpen"]');

          if (sidebar && !sidebar.contains(e.target) &&
            menuButton && !menuButton.contains(e.target)) {
            this.sidebarOpen = false;
          }
        }
      });

      // Close sidebar on window resize to desktop
      window.addEventListener('resize', () => {
        if (window.innerWidth >= 768) {
          this.sidebarOpen = false;
        }
      });
    },

    /**
     * Setup infinite scroll for tickets
     */
    setupInfiniteScroll() {
      // Add scroll listener for infinite loading
      let scrollTimeout;
      window.addEventListener('scroll', () => {
        if (scrollTimeout) clearTimeout(scrollTimeout);
        
        scrollTimeout = setTimeout(() => {
          if (this.activeTab === 'tickets' && this.hasMoreTickets && !this.loadingMore) {
            const scrollPosition = window.scrollY + window.innerHeight;
            const documentHeight = document.documentElement.scrollHeight;
            
            // Load more when 200px from bottom
            if (scrollPosition >= documentHeight - 200) {
              this.loadMoreTickets();
            }
          }
        }, 100);
      });
    },

    /**
     * Setup real-time WebSocket integration
     */
    setupRealTimeIntegration() {
      // Initialize real-time dashboard if available
      if (window.realTimeDashboard) {
        this.setupRealTimeEventHandlers();
        this.isRealTimeConnected = window.realTimeDashboard.isConnected;
      } else if (window.RealTimeDashboard && window.currentUser?.id) {
        // Import and initialize if not already done
        import('./realtime-dashboard.js').then(module => {
          window.realTimeDashboard = new module.RealTimeDashboard(window.currentUser.id);
          this.setupRealTimeEventHandlers();
        }).catch(error => {
          console.warn('Could not load real-time dashboard:', error);
        });
      }
    },

    /**
     * Setup real-time event handlers
     */
    setupRealTimeEventHandlers() {
      if (!window.realTimeDashboard) return;

      // Connection status changes
      window.realTimeDashboard.on('connected', () => {
        this.isRealTimeConnected = true;
        console.log('✅ Real-time dashboard connected');
      });

      window.realTimeDashboard.on('disconnected', () => {
        this.isRealTimeConnected = false;
        console.log('❌ Real-time dashboard disconnected');
      });

      // Dashboard data updates
      window.realTimeDashboard.on('dashboardUpdated', (event) => {
        this.handleRealTimeUpdate(event);
      });

      // Price alerts
      window.realTimeDashboard.on('priceAlert', (event) => {
        this.handlePriceAlert(event);
      });

      // Price changes
      window.realTimeDashboard.on('priceChanged', (event) => {
        this.handlePriceChange(event);
      });

      // Page visibility changes
      window.realTimeDashboard.on('pageVisible', () => {
        // Refresh data when page becomes visible
        if (this.isConnected) {
          this.loadAllData();
        }
      });
    },

    /**
     * Handle real-time dashboard updates
     */
    handleRealTimeUpdate(event) {
      const { update_type, data } = event;
      
      switch (update_type) {
        case 'stats':
          // Update statistics with smooth animation
          this.updateStatsWithAnimation(data);
          break;
        case 'tickets':
          this.tickets = data;
          break;
        case 'alerts':
          this.alerts = data;
          break;
        default:
          // Generic data update
          console.log('Generic real-time update:', event);
      }
      
      this.updateLastUpdated();
    },

    /**
     * Handle price alert notifications
     */
    handlePriceAlert(event) {
      const { notification, ticket, price_alert } = event;
      
      // Update alerts count if showing stats
      if (this.stats && this.stats.price_alerts_triggered !== undefined) {
        this.stats.price_alerts_triggered += 1;
      }
      
      // Show success message in dashboard
      this.showDashboardMessage('success', `Price Alert: ${notification.title}`);
      
      // Add visual pulse to alerts tab if not active
      if (this.activeTab !== 'alerts') {
        this.pulseTab('alerts');
      }
    },

    /**
     * Handle price changes for visible tickets
     */
    handlePriceChange(event) {
      const { ticket_id, new_price, old_price, change_percentage } = event;
      
      // Update ticket price in current list if visible
      const ticketIndex = this.tickets.findIndex(ticket => ticket.id == ticket_id);
      if (ticketIndex !== -1) {
        const ticket = this.tickets[ticketIndex];
        const oldTicketPrice = ticket.price;
        
        // Update the price
        ticket.price = new_price;
        ticket.price_trend = new_price > old_price ? 'up' : 'down';
        
        // Trigger reactivity
        this.tickets = [...this.tickets];
        
        // Show subtle notification for significant changes
        if (Math.abs(change_percentage) >= 10) {
          const changeText = change_percentage > 0 ? 'increased' : 'dropped';
          this.showDashboardMessage('info', 
            `${ticket.title} price ${changeText} by ${Math.abs(change_percentage)}%`
          );
        }
      }
    },

    /**
     * Update statistics with smooth animation
     */
    updateStatsWithAnimation(newStats) {
      Object.entries(newStats).forEach(([key, newValue]) => {
        if (this.stats[key] !== undefined && this.stats[key] !== newValue) {
          // Animate the change
          const oldValue = this.stats[key];
          this.animateStatChange(key, oldValue, newValue);
          this.stats[key] = newValue;
        }
      });
    },

    /**
     * Animate individual stat changes
     */
    animateStatChange(statKey, oldValue, newValue) {
      const statElement = document.querySelector(`[data-stat="${statKey}"]`);
      if (!statElement) return;
      
      // Add animation class
      statElement.classList.add('stat-updating');
      
      // Remove animation class after animation
      setTimeout(() => {
        statElement.classList.remove('stat-updating');
      }, 600);
    },

    /**
     * Show dashboard message
     */
    showDashboardMessage(type, message, duration = 4000) {
      // Create or update message element
      let messageEl = document.querySelector('[data-dashboard-message]');
      if (!messageEl) {
        messageEl = document.createElement('div');
        messageEl.setAttribute('data-dashboard-message', '');
        messageEl.className = 'dashboard-message';
        document.querySelector('.modern-dashboard')?.appendChild(messageEl);
      }
      
      messageEl.className = `dashboard-message dashboard-message-${type} show`;
      messageEl.textContent = message;
      
      // Auto-hide
      setTimeout(() => {
        messageEl.classList.remove('show');
      }, duration);
    },

    /**
     * Add visual pulse to tab
     */
    pulseTab(tabName) {
      const tabButton = document.querySelector(`[data-tab="${tabName}"]`);
      if (tabButton) {
        tabButton.classList.add('tab-pulse');
        setTimeout(() => {
          tabButton.classList.remove('tab-pulse');
        }, 2000);
      }
    },

    /**
     * Load all dashboard data with retry logic
     */
    async loadAllDataWithRetry() {
      try {
        await this.loadAllData();
        this.retryCount = 0;
        this.errorMessage = '';
      } catch (error) {
        if (this.retryCount < this.maxRetries) {
          this.retryCount++;
          console.log(`Retrying dashboard load (attempt ${this.retryCount}/${this.maxRetries})`);
          setTimeout(() => this.loadAllDataWithRetry(), 2000 * this.retryCount);
        } else {
          this.errorMessage = 'Failed to load dashboard data. Please refresh the page.';
          this.handleError(error);
        }
      }
    },

    /**
     * Load all dashboard data
     */
    async loadAllData() {
      if (!this.isConnected) {
        this.errorMessage = 'No internet connection available';
        return;
      }

      try {
        this.isLoading = true;
        this.errorMessage = '';

        const results = await Promise.allSettled([
          this.loadStats(),
          this.loadTickets(),
          this.loadAlerts()
        ]);

        // Check if any critical operations failed
        const failedRequests = results.filter(result => result.status === 'rejected');
        if (failedRequests.length > 0) {
          console.warn('Some requests failed:', failedRequests);
          this.showNotification('warning', 'Some data may be outdated. Refresh to try again.');
        }

        // Update last successful update time
        localStorage.setItem('dashboard_last_update', Date.now().toString());

      } catch (error) {
        console.error('Failed to load dashboard data:', error);
        this.handleError(error);
        throw error; // Re-throw for retry logic
      } finally {
        this.isLoading = false;
        this.updateLastUpdated();
      }
    },

    /**
     * Load dashboard statistics
     */
    async loadStats() {
      try {
        const response = await this.apiCall(this.endpoints.stats);
        if (response.success) {
          this.stats = { ...this.stats, ...response.data };
        }
      } catch (error) {
        console.error('Failed to load stats:', error);
        this.handleError(error);
      }
    },

    /**
     * Load recent tickets with pagination
     */
    async loadTickets(page = 1, append = false) {
      try {
        const params = new URLSearchParams({
          page: page.toString(),
          limit: '20'
        });

        const response = await this.apiCall(`${this.endpoints.tickets}?${params}`);

        if (response.success) {
          if (append) {
            this.tickets.push(...response.data.tickets);
          } else {
            this.tickets = response.data.tickets;
          }

          this.currentPage = response.data.pagination.current_page;
          this.hasMoreTickets = this.currentPage < response.data.pagination.last_page;
        }
      } catch (error) {
        console.error('Failed to load tickets:', error);
        this.handleError(error);
      }
    },

    /**
     * Load user alerts
     */
    async loadAlerts() {
      try {
        const response = await this.apiCall(this.endpoints.alerts);
        if (response.success) {
          this.alerts = response.data;
        }
      } catch (error) {
        console.error('Failed to load alerts:', error);
        this.handleError(error);
      }
    },

    /**
     * Load personalized recommendations
     */
    async loadRecommendations() {
      try {
        const response = await this.apiCall(this.endpoints.recommendations);
        if (response.success) {
          this.recommendations = response.data;
        }
      } catch (error) {
        console.error('Failed to load recommendations:', error);
        this.handleError(error);
      }
    },

    /**
     * Load market insights
     */
    async loadMarketInsights() {
      try {
        const response = await this.apiCall(this.endpoints.marketInsights);
        if (response.success) {
          this.marketInsights = response.data;
        }
      } catch (error) {
        console.error('Failed to load market insights:', error);
        this.handleError(error);
      }
    },

    /**
     * Load more tickets (infinite scroll)
     */
    async loadMoreTickets() {
      if (!this.hasMoreTickets || this.loadingMore || !this.isConnected) return;

      try {
        this.loadingMore = true;
        await this.loadTickets(this.currentPage + 1, true);
      } catch (error) {
        this.showNotification('error', 'Failed to load more tickets');
        this.handleError(error);
      } finally {
        this.loadingMore = false;
      }
    },

    /**
     * Handle tab changes
     */
    onTabChange(newTab) {
      // Load data specific to the active tab
      switch (newTab) {
        case 'recommendations':
          if (this.recommendations.length === 0) {
            this.loadRecommendations();
          }
          break;
        case 'alerts':
          this.loadAlerts();
          break;
        case 'tickets':
          if (this.tickets.length === 0) {
            this.loadTickets();
          }
          break;
      }

      // Close mobile sidebar when changing tabs
      if (window.innerWidth < 768) {
        this.sidebarOpen = false;
      }
    },

    /**
     * Handle keyboard shortcuts
     */
    handleKeyboardShortcuts(e) {
      // Only handle shortcuts when no input is focused
      if (document.activeElement.tagName === 'INPUT' ||
        document.activeElement.tagName === 'TEXTAREA') {
        return;
      }

      switch (e.key) {
        case '1':
          this.activeTab = 'dashboard';
          break;
        case '2':
          this.activeTab = 'tickets';
          break;
        case '3':
          this.activeTab = 'alerts';
          break;
        case '4':
          this.activeTab = 'recommendations';
          break;
        case 'r':
          if (e.metaKey || e.ctrlKey) {
            e.preventDefault();
            this.loadAllData();
          }
          break;
        case 'Escape':
          this.sidebarOpen = false;
          break;
      }
    },

    /**
     * Make API calls with enhanced error handling and retry logic
     */
    async apiCall(url, options = {}, retries = 2) {
      const defaultOptions = {
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
        },
        credentials: 'same-origin',
        timeout: 10000, // 10 second timeout
        ...options
      };

      // Create AbortController for timeout
      const controller = new AbortController();
      const timeoutId = setTimeout(() => controller.abort(), defaultOptions.timeout);
      
      try {
        const response = await fetch(url, {
          ...defaultOptions,
          signal: controller.signal
        });

        clearTimeout(timeoutId);

        if (!response.ok) {
          // Handle specific HTTP errors
          switch (response.status) {
            case 401:
              window.location.href = '/login';
              return;
            case 403:
              throw new Error('Access denied. Please refresh and try again.');
            case 429:
              throw new Error('Too many requests. Please wait a moment and try again.');
            case 500:
              throw new Error('Server error. Please try again later.');
            default:
              throw new Error(`Request failed: ${response.status} ${response.statusText}`);
          }
        }

        const data = await response.json();
        
        // Validate response structure
        if (data.success === false && data.error) {
          throw new Error(data.error);
        }
        
        return data;
        
      } catch (error) {
        clearTimeout(timeoutId);
        
        // Handle network/timeout errors with retry
        if ((error.name === 'AbortError' || error.name === 'TypeError') && retries > 0) {
          console.log(`API call failed, retrying... (${retries} attempts left)`);
          await new Promise(resolve => setTimeout(resolve, 1000)); // Wait 1 second
          return this.apiCall(url, options, retries - 1);
        }
        
        // Re-throw the error for handling upstream
        throw error;
      }
    },

    /**
     * Handle errors gracefully with detailed logging
     */
    handleError(error) {
      console.error('Dashboard error:', error);

      // Determine error type and show appropriate message
      let errorMessage = 'An unexpected error occurred.';
      
      if (error.name === 'TypeError' && error.message.includes('fetch')) {
        errorMessage = 'Network error. Please check your connection.';
        this.isConnected = false;
      } else if (error.name === 'AbortError') {
        errorMessage = 'Request timed out. Please try again.';
      } else if (error.message) {
        errorMessage = error.message;
      }

      // Show user-friendly error message
      this.showNotification('error', errorMessage);

      // Log additional context for debugging
      const errorContext = {
        timestamp: new Date().toISOString(),
        userAgent: navigator.userAgent,
        url: window.location.href,
        isOnline: navigator.onLine,
        error: {
          name: error.name,
          message: error.message,
          stack: error.stack
        }
      };
      
      console.error('Error context:', errorContext);
      
      // Store error for potential reporting
      localStorage.setItem('last_dashboard_error', JSON.stringify(errorContext));
    },
    /**
     * Enhanced notification system with multiple types
     */
    showNotification(type, message, duration = 5000) {
      // Create notification element
      const notification = document.createElement('div');
      
      const typeColors = {
        'error': 'bg-red-500',
        'warning': 'bg-amber-500',
        'success': 'bg-green-500',
        'info': 'bg-blue-500'
      };
      
      const typeIcons = {
        'error': '⚠️',
        'warning': '⚡',
        'success': '✅',
        'info': 'ℹ️'
      };
      
      notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-xl text-white max-w-sm flex items-start space-x-2 ${typeColors[type] || 'bg-gray-500'}`;
      notification.innerHTML = `
        <span class="text-lg">${typeIcons[type] || '•'}</span>
        <div class="flex-1">
          <p class="font-medium">${message}</p>
        </div>
        <button class="ml-2 text-white hover:text-gray-200 transition-colors" onclick="this.parentElement.remove()">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      `;

      document.body.appendChild(notification);

      // Animate in
      notification.style.transform = 'translateX(100%)';
      notification.style.opacity = '0';
      requestAnimationFrame(() => {
        notification.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
        notification.style.transform = 'translateX(0)';
        notification.style.opacity = '1';
      });

      // Auto-remove after delay (unless it's an error, which stays longer)
      const autoRemoveDelay = type === 'error' ? duration * 2 : duration;
      setTimeout(() => {
        if (notification.parentNode) {
          notification.style.transform = 'translateX(100%)';
          notification.style.opacity = '0';
          setTimeout(() => {
            if (notification.parentNode) {
              notification.parentNode.removeChild(notification);
            }
          }, 300);
        }
      }, autoRemoveDelay);
      
      return notification;
    },

    /**
     * Update last updated timestamp
     */
    updateLastUpdated() {
      const now = new Date();
      const timeString = now.toLocaleTimeString([], {
        hour: '2-digit',
        minute: '2-digit'
      });
      this.lastUpdated = timeString;
    },

    /**
     * Utility functions
     */
    formatNumber(num) {
      if (num >= 1000000) {
        return (num / 1000000).toFixed(1) + 'M';
      } else if (num >= 1000) {
        return (num / 1000).toFixed(1) + 'K';
      }
      return num.toString();
    },

    getTrendClass(direction) {
      switch (direction) {
        case 'up':
          return 'text-green-600';
        case 'down':
          return 'text-red-600';
        default:
          return 'text-gray-600';
      }
    },

    formatCurrency(amount) {
      return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
      }).format(amount);
    },

    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
      });
    },

    /**
     * Cleanup when component is destroyed
     */
    destroy() {
      if (this.refreshInterval) {
        clearInterval(this.refreshInterval);
      }
    }
  };
}

// Auto-register with Alpine.js if available
if (window.Alpine) {
  window.Alpine.data('modernCustomerDashboard', modernCustomerDashboard);
}

// Export for module systems
export default modernCustomerDashboard;