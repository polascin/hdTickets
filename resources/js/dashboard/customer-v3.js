// Customer v3 Dashboard Module
// Extracted from blade inline script for maintainability

export function customerDashboard() {
  return {
    loading: false,
    errorMessage: '',
    showNotifications: false,
    statistics: window.__DASHBOARD_INITIAL__?.statistics || {},
    recent_tickets: window.__DASHBOARD_INITIAL__?.recent_tickets || [],
    system_status: window.__DASHBOARD_INITIAL__?.system_status || null,
    notifications: window.__DASHBOARD_INITIAL__?.notifications || {},
    lastUpdate: new Date(),
    refreshInterval: null,
    retryCount: 0,
    maxRetries: 3,

    init() {
      this.startAutoRefresh();
      this.updateTimeInterval();
      document.addEventListener('visibilitychange', () => {
        if (!document.hidden) {
          this.refreshData();
        }
      });
    },

    getCurrentTime() {
      return new Date().toLocaleString('en-US', {
        weekday: 'long', year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'
      });
    },

    formatNumber(value) {
      if (value === null || value === undefined) return '0';
      if (typeof value === 'object') return '0';
      return Number(value).toLocaleString();
    },

    async refreshData() {
      if (this.loading) return;
      this.loading = true;
      this.errorMessage = '';
      try {
        const baseMeta = document.querySelector('meta[name="dashboard-api-base"]');
        const base = (baseMeta ? baseMeta.getAttribute('content') : null) || '/api/v1/dashboard';
        const response = await this.fetchWithAuth(`${base}/realtime`);
        if (!response.ok) {
          if (response.status === 401) {
            this.errorMessage = 'Authentication expired. Please refresh the page.';
            return;
          }
          throw new Error(`Server error (${response.status})`);
        }
        const result = await response.json();
        if (!result.success || !result.data) throw new Error(result.error || 'Invalid response');
        this.updateData(result.data);
        this.retryCount = 0;
      } catch (e) {
        console.error('[Dashboard] refresh failed', e);
        this.handleRefreshError(e);
      } finally {
        this.loading = false;
      }
    },

    async fetchWithAuth(url) {
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
      return fetch(url, {
        method: 'GET',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          'Content-Type': 'application/json'
        },
        credentials: 'same-origin'
      });
    },

    updateData(data) {
      if (data.statistics) this.statistics = { ...this.statistics, ...data.statistics };
      if (data.recent_tickets) this.recent_tickets = data.recent_tickets;
      if (data.system_status) this.system_status = data.system_status;
      if (data.notifications) this.notifications = data.notifications;
      this.lastUpdate = new Date();
    },

    handleRefreshError(error) {
      this.retryCount++;
      if (this.retryCount <= this.maxRetries) {
        this.errorMessage = `Update failed (${this.retryCount}/${this.maxRetries}). Retrying...`;
        setTimeout(() => this.refreshData(), 5000);
      } else {
        this.errorMessage = error.message || 'Failed to update dashboard data. Please refresh the page.';
      }
    },

    startAutoRefresh() {
      const interval = document.querySelector('meta[name="dashboard-refresh-interval"]')?.getAttribute('content') || 120000;
      this.refreshInterval = setInterval(() => {
        if (!document.hidden) this.refreshData();
      }, parseInt(interval));
    },

    updateTimeInterval() {
      setInterval(() => { /* reactive tick */ }, 60000);
    },

    destroy() {
      if (this.refreshInterval) clearInterval(this.refreshInterval);
    }
  };
}

// Auto-register for Alpine if present
if (window.Alpine) {
  window.Alpine.data('customerDashboard', customerDashboard);
}
