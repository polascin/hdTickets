/*
 * Laravel Echo Helpers (non-initializing)
 * Attaches connection status handlers and convenience helpers to an existing
 * Echo instance (window.Echo). Prevents double initialization.
 */
(function setupEchoHelpers() {
  if (typeof window === 'undefined' || !window.Echo) {
    console.warn('[EchoHelpers] Echo not initialized — skipping helpers');
    return;
  }

  const echo = window.Echo;

  // Connection status UI updates
  const pusher = echo.connector && echo.connector.pusher ? echo.connector.pusher : null;
  if (pusher && pusher.connection) {
    pusher.connection.bind('connected', () => {
      try {
        const socketId = pusher.connection.socket_id;
        document.dispatchEvent(new CustomEvent('echo:connected', { detail: { socketId } }));
      } catch {}
      updateConnectionStatus('connected');
    });

    pusher.connection.bind('disconnected', () => {
      document.dispatchEvent(new CustomEvent('echo:disconnected'));
      updateConnectionStatus('disconnected');
    });

    pusher.connection.bind('error', (error) => {
      document.dispatchEvent(new CustomEvent('echo:error', { detail: { error } }));
      updateConnectionStatus('error');
    });

    pusher.connection.bind('unavailable', () => {
      document.dispatchEvent(new CustomEvent('echo:unavailable'));
      updateConnectionStatus('unavailable');
    });

    pusher.connection.bind('connecting', () => {
      updateConnectionStatus('connecting');
    });

    pusher.connection.bind('state_change', (states) => {
      document.dispatchEvent(new CustomEvent('echo:state_change', { detail: states }));
    });
  }

  function updateConnectionStatus(status) {
    const statusIndicators = document.querySelectorAll('[data-connection-status]');
    statusIndicators.forEach((indicator) => {
      indicator.setAttribute('data-connection-status', status);

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

      const iconElement = indicator.querySelector('[data-status-icon]');
      if (iconElement) {
        iconElement.className = getStatusIconClass(status);
      }
    });
  }

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

  // Global helpers
  window.EchoHelpers = {
    subscribeToTicket(ticketId, callbacks = {}) {
      const channel = echo.channel(`ticket.${ticketId}`);
      if (callbacks.onPriceChange) channel.listen('TicketPriceChanged', callbacks.onPriceChange);
      if (callbacks.onAvailabilityChange) channel.listen('TicketAvailabilityChanged', callbacks.onAvailabilityChange);
      if (callbacks.onStatusChange) channel.listen('TicketStatusChanged', callbacks.onStatusChange);
      if (callbacks.onUpdate) channel.listen('TicketUpdated', callbacks.onUpdate);
      return channel;
    },

    subscribeToUserNotifications(userId, callbacks = {}) {
      const channel = echo.private(`user.${userId}`);
      if (callbacks.onPriceAlert) channel.listen('PriceAlertTriggered', callbacks.onPriceAlert);
      if (callbacks.onBookmarkUpdate) channel.listen('BookmarkUpdated', callbacks.onBookmarkUpdate);
      if (callbacks.onNotification) channel.listen('UserNotification', callbacks.onNotification);
      return channel;
    },

    subscribeToSystemAnnouncements(callbacks = {}) {
      const channel = echo.channel('system.announcements');
      if (callbacks.onMaintenance) channel.listen('MaintenanceScheduled', callbacks.onMaintenance);
      if (callbacks.onServiceUpdate) channel.listen('ServiceUpdated', callbacks.onServiceUpdate);
      if (callbacks.onAnnouncement) channel.listen('SystemAnnouncement', callbacks.onAnnouncement);
      return channel;
    },

    subscribeToSearchUpdates(searchId, callbacks = {}) {
      const channel = echo.channel(`search.${searchId}`);
      if (callbacks.onNewResults) channel.listen('SearchResultsUpdated', callbacks.onNewResults);
      if (callbacks.onSearchComplete) channel.listen('SearchCompleted', callbacks.onSearchComplete);
      return channel;
    },

    leaveChannel(channelName) {
      echo.leaveChannel(channelName);
    },

    getSocketId() {
      return echo.connector && echo.connector.pusher && echo.connector.pusher.connection
        ? echo.connector.pusher.connection.socket_id
        : null;
    },

    isConnected() {
      return echo.connector && echo.connector.pusher && echo.connector.pusher.connection
        ? echo.connector.pusher.connection.state === 'connected'
        : false;
    },

    disconnect() {
      if (echo.connector && echo.connector.pusher && echo.connector.pusher.disconnect) {
        echo.connector.pusher.disconnect();
      }
    },

    reconnect() {
      if (echo.connector && echo.connector.pusher && echo.connector.pusher.connect) {
        echo.connector.pusher.connect();
      }
    },
  };

  // Auto-reconnect with backoff
  let reconnectAttempts = 0;
  const maxReconnectAttempts = 5;
  const baseReconnectDelay = 1000;

  function handleReconnection() {
    if (reconnectAttempts >= maxReconnectAttempts) {
      console.error('❌ Max reconnection attempts reached');
      document.dispatchEvent(new CustomEvent('echo:max_reconnect_attempts'));
      return;
    }
    const delay = baseReconnectDelay * Math.pow(2, reconnectAttempts);
    reconnectAttempts++;
    setTimeout(() => {
      if (!window.EchoHelpers.isConnected()) {
        window.EchoHelpers.reconnect();
      }
    }, delay);
  }

  document.addEventListener('echo:connected', () => {
    reconnectAttempts = 0;
  });

  document.addEventListener('echo:disconnected', () => {
    if (!document.hidden) {
      setTimeout(handleReconnection, 1000);
    }
  });

  document.addEventListener('visibilitychange', () => {
    if (!document.hidden && !window.EchoHelpers.isConnected()) {
      window.EchoHelpers.reconnect();
    }
  });
})();

