<template>
  <div class="websocket-status-indicator">
    <!-- Main Status Badge -->
    <div 
      :class="[
        'status-badge',
        'flex',
        'items-center',
        'space-x-2',
        'px-3',
        'py-2',
        'rounded-lg',
        'text-sm',
        'font-medium',
        'transition-all',
        'duration-300',
        statusBadgeClass
      ]"
      @click="toggleDetails"
      :title="statusTooltip"
    >
      <div :class="['status-dot', 'w-2', 'h-2', 'rounded-full', statusDotClass]"></div>
      <span>{{ statusText }}</span>
      <svg 
        v-if="showDetails"
        class="w-4 h-4 transform transition-transform duration-200"
        :class="{ 'rotate-180': detailsVisible }"
        fill="none" 
        stroke="currentColor" 
        viewBox="0 0 24 24"
      >
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
      </svg>
    </div>

    <!-- Detailed Status Panel -->
    <transition name="slide-fade">
      <div v-if="detailsVisible" class="status-details mt-2 p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
        <div class="grid grid-cols-2 gap-4 text-sm">
          <div>
            <label class="font-medium text-gray-700 dark:text-gray-300">Connection Type:</label>
            <p class="text-gray-600 dark:text-gray-400">{{ connectionType }}</p>
          </div>
          <div>
            <label class="font-medium text-gray-700 dark:text-gray-300">Status:</label>
            <p :class="statusTextClass">{{ isConnected ? 'Connected' : 'Disconnected' }}</p>
          </div>
          <div>
            <label class="font-medium text-gray-700 dark:text-gray-300">Reconnect Attempts:</label>
            <p class="text-gray-600 dark:text-gray-400">{{ reconnectAttempts }}</p>
          </div>
          <div>
            <label class="font-medium text-gray-700 dark:text-gray-300">Last Update:</label>
            <p class="text-gray-600 dark:text-gray-400">{{ formattedLastUpdate }}</p>
          </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="flex space-x-2 mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
          <button 
            @click="reconnect"
            class="px-3 py-1 bg-blue-500 text-white rounded text-xs hover:bg-blue-600 transition-colors"
            :disabled="isConnected"
          >
            Reconnect
          </button>
          <button 
            @click="runDiagnostics"
            class="px-3 py-1 bg-gray-500 text-white rounded text-xs hover:bg-gray-600 transition-colors"
          >
            Diagnostics
          </button>
          <button 
            @click="testConnection"
            class="px-3 py-1 bg-green-500 text-white rounded text-xs hover:bg-green-600 transition-colors"
          >
            Test
          </button>
        </div>

        <!-- Active Subscriptions -->
        <div v-if="activeSubscriptions.length > 0" class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
          <label class="font-medium text-gray-700 dark:text-gray-300 text-sm">Active Subscriptions:</label>
          <div class="flex flex-wrap gap-1 mt-2">
            <span 
              v-for="subscription in activeSubscriptions" 
              :key="subscription"
              class="px-2 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded text-xs"
            >
              {{ subscription }}
            </span>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script>
export default {
  name: 'WebSocketStatusIndicator',
  props: {
    showDetails: {
      type: Boolean,
      default: true
    },
    autoUpdate: {
      type: Boolean,
      default: true
    },
    updateInterval: {
      type: Number,
      default: 5000
    }
  },
  data() {
    return {
      isConnected: false,
      connectionType: 'unknown',
      reconnectAttempts: 0,
      lastUpdate: new Date(),
      detailsVisible: false,
      activeSubscriptions: [],
      updateTimer: null,
      websocketManager: null
    }
  },
  computed: {
    statusText() {
      switch (this.connectionType) {
        case 'pusher':
          return this.isConnected ? 'Real-time Active' : 'Real-time Inactive';
        case 'socketio':
          return this.isConnected ? 'Socket.IO Connected' : 'Socket.IO Disconnected';
        case 'fallback':
          return 'Polling Mode';
        default:
          return 'Connection Unknown';
      }
    },
    statusBadgeClass() {
      if (this.isConnected || this.connectionType === 'fallback') {
        return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 hover:bg-green-200 dark:hover:bg-green-800';
      } else if (this.reconnectAttempts > 0) {
        return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 hover:bg-yellow-200 dark:hover:bg-yellow-800';
      } else {
        return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 hover:bg-red-200 dark:hover:bg-red-800';
      }
    },
    statusDotClass() {
      if (this.isConnected || this.connectionType === 'fallback') {
        return 'bg-green-500 animate-pulse';
      } else if (this.reconnectAttempts > 0) {
        return 'bg-yellow-500 animate-bounce';
      } else {
        return 'bg-red-500';
      }
    },
    statusTextClass() {
      if (this.isConnected || this.connectionType === 'fallback') {
        return 'text-green-600 dark:text-green-400';
      } else {
        return 'text-red-600 dark:text-red-400';
      }
    },
    statusTooltip() {
      return `WebSocket Status: ${this.statusText} (${this.connectionType})`;
    },
    formattedLastUpdate() {
      return this.lastUpdate.toLocaleTimeString();
    }
  },
  mounted() {
    this.initializeWebSocketManager();
    this.updateStatus();
    
    if (this.autoUpdate) {
      this.startAutoUpdate();
    }
  },
  beforeUnmount() {
    this.stopAutoUpdate();
  },
  methods: {
    initializeWebSocketManager() {
      // Get WebSocket manager from global scope
      this.websocketManager = window.websocketManager;
      
      if (this.websocketManager) {
        // Listen for connection events
        this.websocketManager.on('connected', () => {
          this.updateStatus();
          this.$emit('connected');
        });
        
        this.websocketManager.on('disconnected', () => {
          this.updateStatus();
          this.$emit('disconnected');
        });
        
        this.websocketManager.on('reconnected', () => {
          this.updateStatus();
          this.$emit('reconnected');
        });
        
        this.websocketManager.on('error', (error) => {
          this.updateStatus();
          this.$emit('error', error);
        });
      }
    },
    
    updateStatus() {
      if (this.websocketManager) {
        const status = this.websocketManager.getConnectionStatus();
        this.isConnected = status.isConnected || false;
        this.connectionType = status.connectionType || 'unknown';
        this.reconnectAttempts = status.reconnectAttempts || 0;
        this.lastUpdate = new Date();
        
        // Update active subscriptions (if available)
        if (this.websocketManager.channels && typeof this.websocketManager.channels.keys === 'function') {
          this.activeSubscriptions = Array.from(this.websocketManager.channels.keys());
        }
      } else {
        this.isConnected = false;
        this.connectionType = 'unavailable';
        this.reconnectAttempts = 0;
        this.activeSubscriptions = [];
      }
    },
    
    toggleDetails() {
      if (this.showDetails) {
        this.detailsVisible = !this.detailsVisible;
        if (this.detailsVisible) {
          this.updateStatus();
        }
      }
    },
    
    reconnect() {
      if (this.websocketManager && typeof this.websocketManager.reconnect === 'function') {
        this.websocketManager.reconnect();
        this.updateStatus();
        this.$emit('reconnect-requested');
      }
    },
    
    runDiagnostics() {
      if (window.WebSocketTester) {
        window.WebSocketTester.diagnose();
      } else {
        console.log('WebSocket diagnostics not available');
      }
      this.$emit('diagnostics-requested');
    },
    
    testConnection() {
      if (window.WebSocketTester) {
        window.WebSocketTester.testConnection();
      } else if (this.websocketManager) {
        // Basic test
        this.websocketManager.emit('connection-test', {
          timestamp: new Date().toISOString(),
          source: 'status-indicator'
        });
      }
      this.$emit('test-requested');
    },
    
    startAutoUpdate() {
      this.updateTimer = setInterval(() => {
        this.updateStatus();
      }, this.updateInterval);
    },
    
    stopAutoUpdate() {
      if (this.updateTimer) {
        clearInterval(this.updateTimer);
        this.updateTimer = null;
      }
    }
  }
}
</script>

<style scoped>
.status-badge {
  cursor: pointer;
  user-select: none;
}

.status-badge:hover {
  transform: translateY(-1px);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.slide-fade-enter-active {
  transition: all 0.3s ease;
}

.slide-fade-leave-active {
  transition: all 0.3s ease;
}

.slide-fade-enter-from,
.slide-fade-leave-to {
  transform: translateY(-10px);
  opacity: 0;
}

.websocket-status-indicator {
  position: relative;
  z-index: 10;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}

@keyframes bounce {
  0%, 20%, 53%, 80%, 100% {
    animation-timing-function: cubic-bezier(0.215, 0.61, 0.355, 1);
    transform: translate3d(0, 0, 0);
  }
  40%, 43% {
    animation-timing-function: cubic-bezier(0.755, 0.05, 0.855, 0.06);
    transform: translate3d(0, -8px, 0);
  }
  70% {
    animation-timing-function: cubic-bezier(0.755, 0.05, 0.855, 0.06);
    transform: translate3d(0, -4px, 0);
  }
  90% {
    transform: translate3d(0, -2px, 0);
  }
}

.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

.animate-bounce {
  animation: bounce 1s infinite;
}
</style>
