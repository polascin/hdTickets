import { ref, onMounted, onUnmounted } from 'vue'
import { io } from 'socket.io-client'
import mitt from 'mitt'

// Create a global event emitter for cross-component communication
const emitter = mitt()

// Global socket reference
const socket = ref(null)
const isConnected = ref(false)
const connectionError = ref(null)
const reconnectAttempts = ref(0)
const maxReconnectAttempts = 5

export function useWebSocket(options = {}) {
  const {
    url = null,
    autoConnect = true,
    auth = null,
    enableLogging = false,
    reconnection = true,
    reconnectionDelay = 1000,
    reconnectionDelayMax = 5000,
    timeout = 20000
  } = options

  // Setup connection
  const setupSocket = () => {
    // Don't setup a new connection if it already exists
    if (socket.value) {
      return socket.value
    }

    // Default to the window.websocketConfig if available or use provided URL
    const wsUrl = url || (window.websocketConfig?.url || 'ws://localhost:6001')
    
    // Create socket instance with options
    socket.value = io(wsUrl, {
      autoConnect,
      auth: auth || window.websocketConfig?.auth || {},
      reconnection,
      reconnectionAttempts: maxReconnectAttempts,
      reconnectionDelay,
      reconnectionDelayMax,
      timeout
    })

    // Setup connection event handlers
    setupConnectionEvents()

    return socket.value
  }

  // Setup connection event listeners
  const setupConnectionEvents = () => {
    if (!socket.value) return

    // Connection established
    socket.value.on('connect', () => {
      if (enableLogging) console.log('WebSocket connected')
      isConnected.value = true
      connectionError.value = null
      reconnectAttempts.value = 0
      emitter.emit('ws:connected')
    })

    // Connection error
    socket.value.on('connect_error', (error) => {
      if (enableLogging) console.error('WebSocket connection error:', error)
      connectionError.value = error.message
      emitter.emit('ws:error', error)
    })

    // Disconnection
    socket.value.on('disconnect', (reason) => {
      if (enableLogging) console.log('WebSocket disconnected:', reason)
      isConnected.value = false
      emitter.emit('ws:disconnected', { reason })
    })

    // Reconnect attempt
    socket.value.on('reconnect_attempt', (attempt) => {
      if (enableLogging) console.log(`WebSocket reconnect attempt ${attempt}`)
      reconnectAttempts.value = attempt
      emitter.emit('ws:reconnect_attempt', { attempt })
    })

    // Reconnect failed
    socket.value.on('reconnect_failed', () => {
      if (enableLogging) console.error('WebSocket reconnection failed')
      emitter.emit('ws:reconnect_failed')
    })

    // Reconnected
    socket.value.on('reconnect', (attemptNumber) => {
      if (enableLogging) console.log(`WebSocket reconnected after ${attemptNumber} attempts`)
      isConnected.value = true
      emitter.emit('ws:reconnected', { attemptNumber })
    })

    // Error
    socket.value.on('error', (error) => {
      if (enableLogging) console.error('WebSocket error:', error)
      emitter.emit('ws:error', error)
    })
  }

  // Connect manually
  const connect = () => {
    if (!socket.value) {
      setupSocket()
    }
    
    if (socket.value && !isConnected.value) {
      socket.value.connect()
    }
  }

  // Disconnect manually
  const disconnect = () => {
    if (socket.value && isConnected.value) {
      socket.value.disconnect()
    }
  }

  // Reset connection
  const resetConnection = () => {
    disconnect()
    socket.value = null
    isConnected.value = false
    connectionError.value = null
    reconnectAttempts.value = 0
    setupSocket()
    connect()
  }

  // Subscribe to dashboard updates
  const subscribeToDashboard = (callback) => {
    if (!socket.value) {
      console.warn('WebSocket not initialized. Dashboard updates unavailable.')
      return null
    }

    const eventName = 'dashboard-update'
    socket.value.on(eventName, callback)
    
    return () => {
      socket.value?.off(eventName, callback)
    }
  }
  
  // Subscribe to ticket updates
  const subscribeToTicketUpdates = (callback) => {
    if (!socket.value) {
      console.warn('WebSocket not initialized. Ticket updates unavailable.')
      return null
    }

    const eventName = 'ticket-update'
    socket.value.on(eventName, callback)
    
    return () => {
      socket.value?.off(eventName, callback)
    }
  }

  // Subscribe to platform status updates
  const subscribeToPlatformStatus = (callback) => {
    if (!socket.value) {
      console.warn('WebSocket not initialized. Platform status updates unavailable.')
      return null
    }

    const eventName = 'platform-status'
    socket.value.on(eventName, callback)
    
    return () => {
      socket.value?.off(eventName, callback)
    }
  }

  // Subscribe to specific event
  const subscribeToEvent = (eventName, callback) => {
    if (!socket.value) {
      console.warn(`WebSocket not initialized. ${eventName} events unavailable.`)
      return null
    }

    socket.value.on(eventName, callback)
    
    return () => {
      socket.value?.off(eventName, callback)
    }
  }

  // Emit event
  const emitEvent = (eventName, data = {}) => {
    if (!socket.value || !isConnected.value) {
      console.warn(`Cannot emit ${eventName}: WebSocket not connected`)
      return false
    }

    socket.value.emit(eventName, data)
    return true
  }

  // Add local event listener
  const on = (event, callback) => {
    emitter.on(event, callback)
    
    return () => {
      emitter.off(event, callback)
    }
  }

  // Clean up
  onUnmounted(() => {
    // Only disconnect if this instance created the connection
    if (options.autoConnect === false && socket.value) {
      socket.value.disconnect()
      socket.value = null
    }
  })

  // Auto connect on mount
  onMounted(() => {
    if (autoConnect && !socket.value) {
      setupSocket()
    }
  })

  return {
    // State
    socket,
    isConnected,
    connectionError,
    reconnectAttempts,
    
    // Connection methods
    connect,
    disconnect,
    resetConnection,
    
    // Subscription methods
    subscribeToDashboard,
    subscribeToTicketUpdates,
    subscribeToPlatformStatus,
    subscribeToEvent,
    
    // Event methods
    emitEvent,
    on
  }
}

// Export a global instance for use in non-component code
let globalWebSocketInstance = null

export function setupGlobalWebSocket(options = {}) {
  if (!globalWebSocketInstance) {
    globalWebSocketInstance = useWebSocket({
      autoConnect: true,
      enableLogging: process.env.NODE_ENV === 'development',
      ...options
    })
  }
  
  return globalWebSocketInstance
}
