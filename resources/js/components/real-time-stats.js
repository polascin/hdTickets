import Alpine from 'alpinejs';

document.addEventListener('alpine:init', () => {
  Alpine.data('realTimeStats', (initialStats = {}) => ({
    stats: {
      platforms: 50,
      monitoring: '24/7',
      users: '15K+',
      alerts: 0,
      priceDrops: 0,
      activeMonitors: 0,
      ...initialStats,
    },
    isConnected: false,
    isLoading: true,
    lastUpdate: null,
    reconnectAttempts: 0,
    maxReconnectAttempts: 5,

    init() {
      this.loadInitialStats();
      this.connectWebSocket();

      // Fallback polling if WebSocket fails
      this.startPolling();

      // Listen for online/offline events
      window.addEventListener('online', () => this.handleOnline());
      window.addEventListener('offline', () => this.handleOffline());
    },

    async loadInitialStats() {
      try {
        const response = await fetch('/api/v1/stats/welcome');
        if (response.ok) {
          const data = await response.json();
          this.updateStats(data);
        }
      } catch (error) {
        console.warn('Failed to load initial stats:', error);
      } finally {
        this.isLoading = false;
      }
    },

    connectWebSocket() {
      if (typeof window.Echo !== 'undefined') {
        try {
          // Listen for stats updates
          window.Echo.channel('stats').listen('.stats.updated', data => {
            this.updateStats(data.stats);
            this.isConnected = true;
            this.reconnectAttempts = 0;
          });

          // Listen for individual metric updates
          window.Echo.channel('metrics').listen('.metric.updated', data => {
            this.updateMetric(data.metric, data.value);
          });

          this.isConnected = true;
        } catch (error) {
          console.warn('WebSocket connection failed:', error);
          this.handleReconnect();
        }
      }
    },

    updateStats(newStats) {
      // Animate number changes
      Object.keys(newStats).forEach(key => {
        if (Object.prototype.hasOwnProperty.call(this.stats, key) && key !== 'monitoring') {
          this.animateNumber(key, this.stats[key], newStats[key]);
        } else {
          this.stats[key] = newStats[key];
        }
      });

      this.lastUpdate = new Date();
    },

    updateMetric(metric, value) {
      if (Object.prototype.hasOwnProperty.call(this.stats, metric)) {
        this.animateNumber(metric, this.stats[metric], value);
      }
    },

    animateNumber(key, oldValue, newValue) {
      // Only animate if both values are numbers
      const oldNum = parseInt(oldValue);
      const newNum = parseInt(newValue);

      if (isNaN(oldNum) || isNaN(newNum)) {
        this.stats[key] = newValue;
        return;
      }

      const duration = 1000; // 1 second
      const steps = 30;
      const increment = (newNum - oldNum) / steps;
      let currentStep = 0;

      const interval = setInterval(() => {
        currentStep++;
        const currentValue = Math.round(oldNum + increment * currentStep);

        if (currentStep >= steps) {
          this.stats[key] = newValue;
          clearInterval(interval);
        } else {
          this.stats[key] = this.formatNumber(currentValue);
        }
      }, duration / steps);
    },

    formatNumber(value) {
      if (typeof value === 'string' && value.includes('K')) {
        return value; // Already formatted
      }

      const num = parseInt(value);
      if (num >= 1000) {
        return Math.round(num / 1000) + 'K+';
      }
      return num.toString();
    },

    startPolling() {
      // Poll every 30 seconds as fallback
      setInterval(async () => {
        if (!this.isConnected && navigator.onLine) {
          try {
            const response = await fetch('/api/v1/stats/welcome');
            if (response.ok) {
              const data = await response.json();
              this.updateStats(data);
            }
          } catch (error) {
            console.warn('Polling failed:', error);
          }
        }
      }, 30000);
    },

    handleReconnect() {
      if (this.reconnectAttempts >= this.maxReconnectAttempts) {
        console.warn('Max reconnection attempts reached');
        return;
      }

      this.reconnectAttempts++;
      const delay = Math.min(1000 * Math.pow(2, this.reconnectAttempts), 30000);

      setTimeout(() => {
        console.warn(
          `Reconnection attempt ${this.reconnectAttempts}/${this.maxReconnectAttempts}`
        );
        this.connectWebSocket();
      }, delay);
    },

    handleOnline() {
      this.isConnected = false;
      this.reconnectAttempts = 0;
      this.connectWebSocket();
      this.loadInitialStats();
    },

    handleOffline() {
      this.isConnected = false;
    },

    get connectionStatus() {
      if (!navigator.onLine) return 'offline';
      if (this.isConnected) return 'connected';
      return 'connecting';
    },

    get statusColor() {
      switch (this.connectionStatus) {
        case 'connected':
          return 'text-green-500';
        case 'connecting':
          return 'text-yellow-500';
        case 'offline':
          return 'text-red-500';
        default:
          return 'text-gray-500';
      }
    },

    get statusIcon() {
      switch (this.connectionStatus) {
        case 'connected':
          return '🟢';
        case 'connecting':
          return '🟡';
        case 'offline':
          return '🔴';
        default:
          return '⚪';
      }
    },
  }));
});
