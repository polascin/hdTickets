import { useState, useCallback, useRef } from 'react'

export interface NotificationAction {
  label: string
  action: () => void
  variant?: 'primary' | 'secondary'
}

export interface Notification {
  id: string
  type: 'success' | 'error' | 'warning' | 'info'
  title: string
  message: string
  duration?: number
  persistent?: boolean
  actions?: NotificationAction[]
  timestamp: Date
}

interface NotificationOptions {
  type: Notification['type']
  title: string
  message: string
  duration?: number
  persistent?: boolean
  actions?: NotificationAction[]
}

interface UseNotificationsReturn {
  notifications: Notification[]
  showNotification: (options: NotificationOptions) => string
  dismissNotification: (id: string) => void
  clearAllNotifications: () => void
}

export const useNotifications = (): UseNotificationsReturn => {
  const [notifications, setNotifications] = useState<Notification[]>([])
  const timeoutRefs = useRef<Map<string, NodeJS.Timeout>>(new Map())

  const generateId = useCallback(() => {
    return `notification-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`
  }, [])

  const dismissNotification = useCallback((id: string) => {
    setNotifications(prev => prev.filter(notification => notification.id !== id))
    
    // Clear the timeout if it exists
    const timeoutRef = timeoutRefs.current.get(id)
    if (timeoutRef) {
      clearTimeout(timeoutRef)
      timeoutRefs.current.delete(id)
    }
  }, [])

  const showNotification = useCallback((options: NotificationOptions): string => {
    const id = generateId()
    const notification: Notification = {
      id,
      type: options.type,
      title: options.title,
      message: options.message,
      duration: options.duration,
      persistent: options.persistent || false,
      actions: options.actions,
      timestamp: new Date(),
    }

    setNotifications(prev => [notification, ...prev])

    // Auto-dismiss after duration if not persistent
    if (!notification.persistent && notification.duration !== 0) {
      const duration = notification.duration || getDefaultDuration(notification.type)
      const timeoutId = setTimeout(() => {
        dismissNotification(id)
      }, duration)
      
      timeoutRefs.current.set(id, timeoutId)
    }

    return id
  }, [generateId, dismissNotification])

  const clearAllNotifications = useCallback(() => {
    // Clear all timeouts
    timeoutRefs.current.forEach(timeout => clearTimeout(timeout))
    timeoutRefs.current.clear()
    
    setNotifications([])
  }, [])

  return {
    notifications,
    showNotification,
    dismissNotification,
    clearAllNotifications,
  }
}

// Helper function to get default duration based on notification type
function getDefaultDuration(type: Notification['type']): number {
  switch (type) {
    case 'success':
      return 4000
    case 'info':
      return 5000
    case 'warning':
      return 7000
    case 'error':
      return 10000
    default:
      return 5000
  }
}
