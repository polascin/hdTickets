/**
 * Customer Dashboard Enhanced v2 JavaScript
 * Focused on state management, caching, error handling, and accessibility
 */

function customerDashboard() {
  return {
    // State
    loading: false,
    recommendationsLoading: false,
    stats: {},
    tickets: [],
    recommendations: [],
    notifications: [],
    filters: {
      sport: '',
      platform: '',
      maxPrice: '',
      sort: 'created_at:desc'
    },

    // UI state
    showNotifications: false,
    showToast: false,
    toastMessage: '',
    toastType: 'success',
    mobileMenuOpen: false,
    currentTime: '',

    // Cache
    cache: new Map(),
    cacheTimeout: 5 * 60 * 1000, // 5 minutes

    async init() {
      console.log('[Dashboard] Initializing...');
      this.updateTime();
      this.setupTimeUpdate();
      await this.loadInitialData();
      this.setupEcho();
      this.setupKeyboardShortcuts();
      console.log('[Dashboard] Initialization complete');
    },

    updateTime() {
      const now = new Date();
      this.currentTime = now.toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: true
      });
    },

    setupTimeUpdate() {
      setInterval(() => {
        this.updateTime();
      }, 60000);
    },

    async loadInitialData() {
      this.loading = true;
      try {
        await Promise.all([
          this.fetchStats(),
          this.fetchTickets(),
          this.fetchRecommendations()
        ]);
      } catch (error) {
        console.error('[Dashboard] Error loading initial data:', error);
        this.showToastMessage('Failed to load dashboard data', 'error');
      } finally {
        this.loading = false;
      }
    },

    async fetchStats() {
      try {
        const cacheKey = 'stats';
        const cached = this.getFromCache(cacheKey);
        if (cached) {
          this.stats = cached;
          return;
        }

  const response = await fetch('/api/v1/dashboard/stats', {
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          }
        });

        if (!response.ok) throw new Error('Failed to fetch stats');
        
        const data = await response.json();
        this.stats = data.stats || {};
        this.setCache(cacheKey, this.stats);
      } catch (error) {
        console.error('[Dashboard] Error fetching stats:', error);
      }
    },

    async fetchTickets() {
      try {
        const cacheKey = `tickets-${JSON.stringify(this.filters)}`;
        const cached = this.getFromCache(cacheKey);
        if (cached) {
          this.tickets = cached;
          return;
        }

        const params = new URLSearchParams();
        Object.entries(this.filters).forEach(([key, value]) => {
          if (value) params.append(key, value);
        });

  const response = await fetch(`/api/v1/dashboard/tickets?${params}`, {
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          }
        });

        if (!response.ok) throw new Error('Failed to fetch tickets');
        
        const data = await response.json();
        this.tickets = data.tickets || [];
        this.setCache(cacheKey, this.tickets);
      } catch (error) {
        console.error('[Dashboard] Error fetching tickets:', error);
        this.tickets = [];
      }
    },

    async fetchRecommendations() {
      this.recommendationsLoading = true;
      try {
        const cacheKey = 'recommendations';
        const cached = this.getFromCache(cacheKey);
        if (cached) {
          this.recommendations = cached;
          return;
        }

  const response = await fetch('/api/v1/dashboard/recommendations', {
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          }
        });

        if (!response.ok) throw new Error('Failed to fetch recommendations');
        
        const data = await response.json();
        this.recommendations = data.recommendations || [];
        this.setCache(cacheKey, this.recommendations);
      } catch (error) {
        console.error('[Dashboard] Error fetching recommendations:', error);
        this.recommendations = [];
      } finally {
        this.recommendationsLoading = false;
      }
    },

    async applyFilters() {
      this.loading = true;
      await this.fetchTickets();
      this.loading = false;
    },

    resetFilters() {
      this.filters = {
        sport: '',
        platform: '',
        maxPrice: '',
        sort: 'created_at:desc'
      };
      this.applyFilters();
    },

    async refreshData() {
      this.cache.clear();
      await this.loadInitialData();
      this.showToastMessage('Dashboard refreshed successfully', 'success');
    },

    async toggleAlert(ticket) {
      try {
        const method = ticket.has_alert ? 'DELETE' : 'POST';
        const response = await fetch(`/api/alerts/${ticket.id}`, {
          method,
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
          }
        });

        if (!response.ok) throw new Error('Failed to toggle alert');

        ticket.has_alert = !ticket.has_alert;
        const action = ticket.has_alert ? 'created' : 'removed';
        this.showToastMessage(`Alert ${action} successfully`, 'success');
      } catch (error) {
        console.error('[Dashboard] Error toggling alert:', error);
        this.showToastMessage('Failed to toggle alert', 'error');
      }
    },

    setupEcho() {
      if (typeof window.Echo !== 'undefined') {
        window.Echo.channel('dashboard')
          .listen('StatsUpdated', (e) => {
            this.stats = { ...this.stats, ...e.stats };
          })
          .listen('TicketUpdated', (e) => {
            const index = this.tickets.findIndex(t => t.id === e.ticket.id);
            if (index !== -1) {
              this.tickets[index] = e.ticket;
            }
          });
      }
    },

    setupKeyboardShortcuts() {
      document.addEventListener('keydown', (e) => {
        if (e.key === 'r' && e.ctrlKey) {
          e.preventDefault();
          this.refreshData();
        }
      });
    },

    // Utility methods
    getFromCache(key) {
      const item = this.cache.get(key);
      if (!item) return null;
      
      if (Date.now() - item.timestamp > this.cacheTimeout) {
        this.cache.delete(key);
        return null;
      }
      
      return item.data;
    },

    setCache(key, data) {
      this.cache.set(key, {
        data,
        timestamp: Date.now()
      });
    },

    formatCurrency(amount) {
      return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
      }).format(amount);
    },

    formatDate(dateString) {
      const date = new Date(dateString);
      return new Intl.DateTimeFormat('en-US', {
        month: 'short',
        day: 'numeric',
        year: date.getFullYear() !== new Date().getFullYear() ? 'numeric' : undefined
      }).format(date);
    },

    showToastMessage(message, type = 'success') {
      this.toastMessage = message;
      this.toastType = type;
      this.showToast = true;
      
      setTimeout(() => {
        this.hideToast();
      }, 5000);
    },

    hideToast() {
      this.showToast = false;
      setTimeout(() => {
        this.toastMessage = '';
        this.toastType = 'success';
      }, 300);
    }
  };
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
  console.log('[Dashboard] DOM ready, Alpine.js should handle initialization');
});
