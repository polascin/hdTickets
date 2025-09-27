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
    isConnected: true,
    lastUpdated: 'now',

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

    // Pagination
    currentPage: 1,
    hasMoreTickets: true,

    // Auto-refresh settings
    refreshInterval: null,
    refreshRate: 30000, // 30 seconds

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
      this.startAutoRefresh();
      this.setupEventListeners();
      this.setupMobileHandling();

      // Load initial data
      this.loadAllData();

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
      // Handle connection status
      window.addEventListener('online', () => {
        this.isConnected = true;
        this.loadAllData();
      });

      window.addEventListener('offline', () => {
        this.isConnected = false;
      });

      // Handle tab changes
      this.$watch('activeTab', (newTab) => {
        this.onTabChange(newTab);
      });

      // Handle keyboard shortcuts
      document.addEventListener('keydown', (e) => {
        this.handleKeyboardShortcuts(e);
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
     * Load all dashboard data
     */
    async loadAllData() {
      try {
        this.isLoading = true;

        await Promise.allSettled([
          this.loadStats(),
          this.loadTickets(),
          this.loadAlerts()
        ]);

      } catch (error) {
        console.error('Failed to load dashboard data:', error);
        this.handleError(error);
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
      if (!this.hasMoreTickets) return;

      await this.loadTickets(this.currentPage + 1, true);
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
     * Make API calls with error handling
     */
    async apiCall(url, options = {}) {
      const defaultOptions = {
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
        },
        credentials: 'same-origin',
        ...options
      };

      const response = await fetch(url, defaultOptions);

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
      }

      const data = await response.json();
      return data;
    },

    /**
     * Handle errors gracefully
     */
    handleError(error) {
      console.error('Dashboard error:', error);

      // Show user-friendly error message
      this.showNotification('error', 'Failed to load data. Please check your connection and try again.');

      // Set connection status
      this.isConnected = false;
    },

    /**
     * Show notification to user
     */
    showNotification(type, message) {
      // Create a simple notification system
      const notification = document.createElement('div');
      notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg text-white max-w-sm ${type === 'error' ? 'bg-red-500' : 'bg-green-500'
        }`;
      notification.textContent = message;

      document.body.appendChild(notification);

      // Animate in
      notification.style.transform = 'translateX(100%)';
      requestAnimationFrame(() => {
        notification.style.transition = 'transform 0.3s ease-out';
        notification.style.transform = 'translateX(0)';
      });

      // Remove after delay
      setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
          if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
          }
        }, 300);
      }, 5000);
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