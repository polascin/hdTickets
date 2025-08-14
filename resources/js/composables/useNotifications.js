import { ref, computed, nextTick } from 'vue'
import { useStorage } from '@vueuse/core'

// Notification types with their configurations
const NOTIFICATION_TYPES = {
  success: {
    icon: '✅',
    duration: 4000,
    color: 'green',
    priority: 1
  },
  error: {
    icon: '❌',
    duration: 6000,
    color: 'red',
    priority: 3
  },
  warning: {
    icon: '⚠️',
    duration: 5000,
    color: 'yellow',
    priority: 2
  },
  info: {
    icon: 'ℹ️',
    duration: 4000,
    color: 'blue',
    priority: 1
  },
  loading: {
    icon: '⏳',
    duration: 0, // Persistent until manually dismissed
    color: 'gray',
    priority: 1
  }
}

// Global notification state
const notifications = ref([])
const nextId = ref(1)
const maxNotifications = ref(5)

// User preferences
const notificationPreferences = useStorage('hd-tickets-notification-preferences', {
  enabled: true,
  position: 'top-right',
  duration: {
    success: 4000,
    error: 6000,
    warning: 5000,
    info: 4000,
    loading: 0
  },
  sound: {
    enabled: false,
    volume: 0.5
  },
  desktop: {
    enabled: false
  },
  groupSimilar: true,
  maxVisible: 5
})

// Sound effects
const sounds = {
  success: new Audio('/sounds/success.mp3'),
  error: new Audio('/sounds/error.mp3'),
  warning: new Audio('/sounds/warning.mp3'),
  info: new Audio('/sounds/info.mp3')
}

export function useNotifications() {
  
  // Check if notifications are supported
  const isSupported = computed(() => {
    return typeof window !== 'undefined' && 'Notification' in window
  })

  // Check desktop notification permission
  const hasDesktopPermission = computed(() => {
    return isSupported.value && Notification.permission === 'granted'
  })

  // Request desktop notification permission
  const requestDesktopPermission = async () => {
    if (!isSupported.value) return false
    
    try {
      const permission = await Notification.requestPermission()
      return permission === 'granted'
    } catch (error) {
      console.warn('Failed to request notification permission:', error)
      return false
    }
  }

  // Play notification sound
  const playSound = (type) => {
    if (!notificationPreferences.value.sound.enabled) return
    
    const sound = sounds[type]
    if (sound) {
      sound.volume = notificationPreferences.value.sound.volume
      sound.play().catch(() => {
        // Ignore sound play errors (user interaction required, etc.)
      })
    }
  }

  // Show desktop notification
  const showDesktopNotification = (notification) => {
    if (!hasDesktopPermission.value || !notificationPreferences.value.desktop.enabled) {
      return
    }

    const desktopNotification = new Notification(notification.title || 'HD Tickets', {
      body: notification.message,
      icon: '/favicon.ico',
      tag: notification.id.toString(),
      requireInteraction: notification.type === 'error',
      silent: !notificationPreferences.value.sound.enabled
    })

    // Auto close desktop notification
    if (notification.duration > 0) {
      setTimeout(() => {
        desktopNotification.close()
      }, notification.duration)
    }

    desktopNotification.onclick = () => {
      window.focus()
      desktopNotification.close()
    }
  }

  // Group similar notifications
  const shouldGroupNotification = (newNotification) => {
    if (!notificationPreferences.value.groupSimilar) return null

    return notifications.value.find(existing => 
      existing.message === newNotification.message &&
      existing.type === newNotification.type &&
      !existing.dismissed
    )
  }

  // Create notification object
  const createNotification = (message, type = 'info', options = {}) => {
    const config = NOTIFICATION_TYPES[type] || NOTIFICATION_TYPES.info
    const duration = options.duration ?? notificationPreferences.value.duration[type] ?? config.duration

    return {
      id: nextId.value++,
      message,
      type,
      title: options.title,
      icon: options.icon || config.icon,
      color: config.color,
      duration,
      priority: options.priority ?? config.priority,
      timestamp: Date.now(),
      dismissed: false,
      actions: options.actions || [],
      data: options.data || {},
      persistent: options.persistent || duration === 0,
      group: options.group
    }
  }

  // Add notification
  const addNotification = (notification) => {
    // Check if notifications are enabled
    if (!notificationPreferences.value.enabled) return null

    // Check for grouping
    const existingNotification = shouldGroupNotification(notification)
    if (existingNotification) {
      existingNotification.count = (existingNotification.count || 1) + 1
      existingNotification.timestamp = Date.now()
      return existingNotification
    }

    // Add to notifications array
    notifications.value.unshift(notification)

    // Limit max notifications
    if (notifications.value.length > maxNotifications.value) {
      notifications.value = notifications.value.slice(0, maxNotifications.value)
    }

    // Play sound
    playSound(notification.type)

    // Show desktop notification
    showDesktopNotification(notification)

    // Auto dismiss if not persistent
    if (notification.duration > 0) {
      setTimeout(() => {
        dismissNotification(notification.id)
      }, notification.duration)
    }

    return notification
  }

  // Show notification (main method)
  const showNotification = (message, type = 'info', options = {}) => {
    const notification = createNotification(message, type, options)
    return addNotification(notification)
  }

  // Convenience methods
  const showSuccess = (message, options = {}) => showNotification(message, 'success', options)
  const showError = (message, options = {}) => showNotification(message, 'error', options)
  const showWarning = (message, options = {}) => showNotification(message, 'warning', options)
  const showInfo = (message, options = {}) => showNotification(message, 'info', options)
  const showLoading = (message, options = {}) => showNotification(message, 'loading', options)

  // Dismiss notification
  const dismissNotification = (id) => {
    const notification = notifications.value.find(n => n.id === id)
    if (notification) {
      notification.dismissed = true
      
      // Remove from array after animation time
      setTimeout(() => {
        const index = notifications.value.findIndex(n => n.id === id)
        if (index > -1) {
          notifications.value.splice(index, 1)
        }
      }, 300)
    }
  }

  // Dismiss all notifications
  const dismissAll = () => {
    notifications.value.forEach(notification => {
      if (!notification.persistent) {
        dismissNotification(notification.id)
      }
    })
  }

  // Clear all notifications (including persistent)
  const clearAll = () => {
    notifications.value.forEach(notification => {
      dismissNotification(notification.id)
    })
  }

  // Update notification
  const updateNotification = (id, updates) => {
    const notification = notifications.value.find(n => n.id === id)
    if (notification) {
      Object.assign(notification, updates)
      return notification
    }
    return null
  }

  // Get notifications by type
  const getNotificationsByType = (type) => {
    return computed(() => 
      notifications.value.filter(n => n.type === type && !n.dismissed)
    )
  }

  // Get active notifications
  const activeNotifications = computed(() => 
    notifications.value.filter(n => !n.dismissed)
  )

  // Get visible notifications (respecting max visible preference)
  const visibleNotifications = computed(() => 
    activeNotifications.value
      .sort((a, b) => b.priority - a.priority || b.timestamp - a.timestamp)
      .slice(0, notificationPreferences.value.maxVisible)
  )

  // Check if there are any error notifications
  const hasErrors = computed(() => 
    activeNotifications.value.some(n => n.type === 'error')
  )

  // Check if there are any loading notifications
  const isLoading = computed(() => 
    activeNotifications.value.some(n => n.type === 'loading')
  )

  // Progress notification for long-running operations
  const showProgressNotification = (message, options = {}) => {
    const notification = showNotification(message, 'loading', {
      ...options,
      persistent: true
    })

    return {
      update: (message, progress) => {
        if (notification) {
          updateNotification(notification.id, {
            message,
            data: { ...notification.data, progress }
          })
        }
      },
      success: (message) => {
        if (notification) {
          updateNotification(notification.id, {
            type: 'success',
            message,
            duration: 3000,
            persistent: false
          })
          setTimeout(() => dismissNotification(notification.id), 3000)
        }
      },
      error: (message) => {
        if (notification) {
          updateNotification(notification.id, {
            type: 'error',
            message,
            duration: 5000,
            persistent: false
          })
          setTimeout(() => dismissNotification(notification.id), 5000)
        }
      },
      dismiss: () => {
        if (notification) {
          dismissNotification(notification.id)
        }
      }
    }
  }

  // Bulk operations
  const showBulkNotifications = (notifications) => {
    return notifications.map(({ message, type, options }) => 
      showNotification(message, type, options)
    )
  }

  // Notification queue for complex flows
  const notificationQueue = ref([])
  
  const queueNotification = (message, type, options = {}) => {
    notificationQueue.value.push({ message, type, options })
  }

  const processNotificationQueue = async (delay = 500) => {
    while (notificationQueue.value.length > 0) {
      const notification = notificationQueue.value.shift()
      showNotification(notification.message, notification.type, notification.options)
      
      if (notificationQueue.value.length > 0) {
        await new Promise(resolve => setTimeout(resolve, delay))
      }
    }
  }

  // Update preferences
  const updatePreferences = (newPreferences) => {
    Object.assign(notificationPreferences.value, newPreferences)
  }

  // Reset preferences
  const resetPreferences = () => {
    notificationPreferences.value = {
      enabled: true,
      position: 'top-right',
      duration: {
        success: 4000,
        error: 6000,
        warning: 5000,
        info: 4000,
        loading: 0
      },
      sound: {
        enabled: false,
        volume: 0.5
      },
      desktop: {
        enabled: false
      },
      groupSimilar: true,
      maxVisible: 5
    }
  }

  return {
    // State
    notifications: activeNotifications,
    visibleNotifications,
    hasErrors,
    isLoading,
    isSupported,
    hasDesktopPermission,
    preferences: notificationPreferences,

    // Main methods
    showNotification,
    showSuccess,
    showError,
    showWarning,
    showInfo,
    showLoading,
    
    // Management methods
    dismissNotification,
    dismissAll,
    clearAll,
    updateNotification,
    
    // Utility methods
    getNotificationsByType,
    showProgressNotification,
    showBulkNotifications,
    
    // Queue methods
    queueNotification,
    processNotificationQueue,
    
    // Preferences
    updatePreferences,
    resetPreferences,
    requestDesktopPermission,
    
    // Advanced features
    NOTIFICATION_TYPES
  }
}
