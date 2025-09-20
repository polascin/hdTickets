/**
 * Laravel Echo Configuration
 *
 * Configures real-time WebSocket connections for the HD Tickets system
 * including price monitoring, ticket availability updates, and notifications.
 */

import Echo from 'laravel-echo';

// Import Pusher if using Pusher
import Pusher from 'pusher-js';
window.Pusher = Pusher;

/**
 * Echo Configuration
 */
const echoConfig = {
  broadcaster: 'pusher',
  key: process.env.MIX_PUSHER_APP_KEY || window.pusherKey,
  cluster: process.env.MIX_PUSHER_APP_CLUSTER || window.pusherCluster || 'mt1',
  forceTLS: true,
  encrypted: true,

  // Authentication for private/presence channels
  auth: {
    headers: {
      'X-CSRF-TOKEN':
        document
          .querySelector('meta[name="csrf-token"]')
          ?.getAttribute('content') || '',
      Accept: 'application/json',
    },
  },

  // Pusher-specific options
  pusherOptions: {
    cluster:
      process.env.MIX_PUSHER_APP_CLUSTER || window.pusherCluster || 'mt1',
    forceTLS: true,
    encrypted: true,

    // Connection configuration
    enabledTransports: ['ws', 'wss'],
    disabledTransports: ['xhr_polling', 'xhr_streaming'],

    // Timeout settings
    activityTimeout: 120000, // 2 minutes
    pongTimeout: 30000, // 30 seconds
    unavailableTimeout: 16000, // 16 seconds

    // Auto-reconnection
    enableStats: false,
    enableLogging: process.env.NODE_ENV === 'development',

    // Custom authorizer for private channels
    authorizer: (channel, options) => {
      return {
        authorize: (socketId, callback) => {
          axios
            .post(
              '/broadcasting/auth',
              {
                socket_id: socketId,
                channel_name: channel.name,
              },
              {
                headers: {
                  'X-CSRF-TOKEN':
                    document
                      .querySelector('meta[name="csrf-token"]')
                      ?.getAttribute('content') || '',
                },
              }
            )
            .then(response => {
              callback(null, response.data);
            })
            .catch(error => {
              callback(error, null);
            });
        },
      };
    },
  },
};

/**
 * Initialize Laravel Echo
 */
const echo = new Echo(echoConfig);

// Store Echo instance globally for access in components
window.Echo = echo;

/**
 * Connection Event Handlers
 */
if (echo.connector && echo.connector.pusher) {
  const pusher = echo.connector.pusher;

  // Connection established
  pusher.connection.bind('connected', () => {
    console.log('✅ WebSocket connected successfully');

    // Dispatch custom event
    document.dispatchEvent(
      new CustomEvent('echo:connected', {
        detail: { socketId: pusher.connection.socket_id },
      })
    );

    // Update connection status in UI
    updateConnectionStatus('connected');
  });

  // Connection disconnected
  pusher.connection.bind('disconnected', () => {
    console.log('❌ WebSocket disconnected');

    // Dispatch custom event
    document.dispatchEvent(new CustomEvent('echo:disconnected'));

    // Update connection status in UI
    updateConnectionStatus('disconnected');
  });

  // Connection error
  pusher.connection.bind('error', error => {
    console.error('🔴 WebSocket connection error:', error);

    // Dispatch custom event
    document.dispatchEvent(
      new CustomEvent('echo:error', {
        detail: { error },
      })
    );

    // Update connection status in UI
    updateConnectionStatus('error');

    // Track error for analytics
    if (window.hdTicketsApp) {
      window.hdTicketsApp.trackError(new Error(`WebSocket error: ${error}`));
    }
  });

  // Connection unavailable
  pusher.connection.bind('unavailable', () => {
    console.warn('⚠️ WebSocket unavailable - will retry');

    // Dispatch custom event
    document.dispatchEvent(new CustomEvent('echo:unavailable'));

    // Update connection status in UI
    updateConnectionStatus('unavailable');
  });

  // Connection reconnecting
  pusher.connection.bind('connecting', () => {
    console.log('🔄 WebSocket reconnecting...');

    // Update connection status in UI
    updateConnectionStatus('connecting');
  });

  // Connection state changed
  pusher.connection.bind('state_change', states => {
    console.log(`🔄 WebSocket state: ${states.previous} → ${states.current}`);

    // Dispatch custom event
    document.dispatchEvent(
      new CustomEvent('echo:state_change', {
        detail: states,
      })
    );
  });
}

/**
 * Update Connection Status UI
 */
function updateConnectionStatus(status) {
  const statusIndicators = document.querySelectorAll(
    '[data-connection-status]'
  );

  statusIndicators.forEach(indicator => {
    indicator.setAttribute('data-connection-status', status);

    // Update text content if element has it
    const textElement = indicator.querySelector('[data-status-text]');
    if (textElement) {
      const statusTexts = {
        connected: 'Connected',
        disconnected: 'Disconnected',
        connecting: 'Connecting...',
        error: 'Connection Error',
        unavailable: 'Service Unavailable',
      };
      textElement.textContent = statusTexts[status] || status;
    }

    // Update icon if element has it
    const iconElement = indicator.querySelector('[data-status-icon]');
    if (iconElement) {
      iconElement.className = getStatusIconClass(status);
    }
  });
}

/**
 * Get CSS class for connection status icon
 */
function getStatusIconClass(status) {
  const statusClasses = {
    connected: 'w-3 h-3 bg-green-500 rounded-full',
    disconnected: 'w-3 h-3 bg-red-500 rounded-full',
    connecting: 'w-3 h-3 bg-yellow-500 rounded-full animate-pulse',
    error: 'w-3 h-3 bg-red-600 rounded-full animate-bounce',
    unavailable: 'w-3 h-3 bg-gray-500 rounded-full',
  };
  return statusClasses[status] || 'w-3 h-3 bg-gray-400 rounded-full';
}

/**
 * Global Channel Helpers
 */
window.EchoHelpers = {
  /**
   * Subscribe to ticket-specific updates
   */
  subscribeToTicket(ticketId, callbacks = {}) {
    const channel = echo.channel(`ticket.${ticketId}`);

    // Price changes
    if (callbacks.onPriceChange) {
      channel.listen('TicketPriceChanged', callbacks.onPriceChange);
    }

    // Availability changes
    if (callbacks.onAvailabilityChange) {
      channel.listen(
        'TicketAvailabilityChanged',
        callbacks.onAvailabilityChange
      );
    }

    // Status changes
    if (callbacks.onStatusChange) {
      channel.listen('TicketStatusChanged', callbacks.onStatusChange);
    }

    // Generic ticket updates
    if (callbacks.onUpdate) {
      channel.listen('TicketUpdated', callbacks.onUpdate);
    }

    return channel;
  },

  /**
   * Subscribe to user-specific notifications
   */
  subscribeToUserNotifications(userId, callbacks = {}) {
    const channel = echo.private(`user.${userId}`);

    // Price alerts
    if (callbacks.onPriceAlert) {
      channel.listen('PriceAlertTriggered', callbacks.onPriceAlert);
    }

    // Bookmark notifications
    if (callbacks.onBookmarkUpdate) {
      channel.listen('BookmarkUpdated', callbacks.onBookmarkUpdate);
    }

    // General notifications
    if (callbacks.onNotification) {
      channel.listen('UserNotification', callbacks.onNotification);
    }

    return channel;
  },

  /**
   * Subscribe to system-wide announcements
   */
  subscribeToSystemAnnouncements(callbacks = {}) {
    const channel = echo.channel('system.announcements');

    // System maintenance
    if (callbacks.onMaintenance) {
      channel.listen('MaintenanceScheduled', callbacks.onMaintenance);
    }

    // Service updates
    if (callbacks.onServiceUpdate) {
      channel.listen('ServiceUpdated', callbacks.onServiceUpdate);
    }

    // General announcements
    if (callbacks.onAnnouncement) {
      channel.listen('SystemAnnouncement', callbacks.onAnnouncement);
    }

    return channel;
  },

  /**
   * Subscribe to search-related updates
   */
  subscribeToSearchUpdates(searchId, callbacks = {}) {
    const channel = echo.channel(`search.${searchId}`);

    // New results available
    if (callbacks.onNewResults) {
      channel.listen('SearchResultsUpdated', callbacks.onNewResults);
    }

    // Search completed
    if (callbacks.onSearchComplete) {
      channel.listen('SearchCompleted', callbacks.onSearchComplete);
    }

    return channel;
  },

  /**
   * Leave a channel
   */
  leaveChannel(channelName) {
    echo.leaveChannel(channelName);
  },

  /**
   * Get current socket ID
   */
  getSocketId() {
    return echo.connector?.pusher?.connection?.socket_id || null;
  },

  /**
   * Check if connected
   */
  isConnected() {
    return echo.connector?.pusher?.connection?.state === 'connected';
  },

  /**
   * Manually disconnect
   */
  disconnect() {
    if (echo.connector?.pusher) {
      echo.connector.pusher.disconnect();
    }
  },

  /**
   * Manually reconnect
   */
  reconnect() {
    if (echo.connector?.pusher) {
      echo.connector.pusher.connect();
    }
  },
};

/**
 * Auto-reconnection Logic
 */
let reconnectAttempts = 0;
const maxReconnectAttempts = 5;
const baseReconnectDelay = 1000; // 1 second

function handleReconnection() {
  if (reconnectAttempts >= maxReconnectAttempts) {
    console.error('❌ Max reconnection attempts reached');

    // Dispatch event for UI to handle
    document.dispatchEvent(new CustomEvent('echo:max_reconnect_attempts'));
    return;
  }

  const delay = baseReconnectDelay * Math.pow(2, reconnectAttempts); // Exponential backoff
  reconnectAttempts++;

  console.log(
    `🔄 Attempting reconnection ${reconnectAttempts}/${maxReconnectAttempts} in ${delay}ms`
  );

  setTimeout(() => {
    if (!window.EchoHelpers.isConnected()) {
      window.EchoHelpers.reconnect();
    }
  }, delay);
}

// Reset reconnect attempts on successful connection
document.addEventListener('echo:connected', () => {
  reconnectAttempts = 0;
});

// Handle disconnection with automatic reconnection
document.addEventListener('echo:disconnected', () => {
  // Only attempt reconnection if page is visible
  if (!document.hidden) {
    setTimeout(handleReconnection, 1000);
  }
});

// Handle page visibility changes
document.addEventListener('visibilitychange', () => {
  if (document.hidden) {
    // Page is hidden - can pause some operations
    console.log('📱 Page hidden - maintaining connection');
  } else {
    // Page is visible - ensure connection is active
    console.log('👀 Page visible - checking connection');
    if (!window.EchoHelpers.isConnected()) {
      window.EchoHelpers.reconnect();
    }
  }
});

/**
 * Debugging helpers (development only)
 */
if (process.env.NODE_ENV === 'development') {
  // Global access for debugging
  window.Echo = echo;
  window.debugEcho = {
    echo,
    helpers: window.EchoHelpers,
    pusher: echo.connector?.pusher,

    // Debug methods
    logChannels() {
      console.log('Active channels:', Object.keys(echo.connector.channels));
    },

    logConnection() {
      const pusher = echo.connector?.pusher;
      if (pusher) {
        console.log('Connection state:', pusher.connection.state);
        console.log('Socket ID:', pusher.connection.socket_id);
        console.log('Activity timeout:', pusher.config.activityTimeout);
      }
    },

    simulateDisconnection() {
      echo.connector?.pusher?.disconnect();
    },

    forceReconnection() {
      window.EchoHelpers.reconnect();
    },
  };

  // Log initial connection info
  console.log('🎫 HD Tickets WebSocket initialized', {
    config: echoConfig,
    echo: echo,
    helpers: window.EchoHelpers,
  });
}

/**
 * Graceful shutdown on page unload
 */
window.addEventListener('beforeunload', () => {
  if (echo.connector?.pusher) {
    echo.connector.pusher.disconnect();
  }
});

export default echo;
