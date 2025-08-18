import { useEffect, useRef, useCallback, useState } from 'react'

interface UseWebSocketOptions {
  url: string
  onMessage?: (data: any) => void
  onConnect?: () => void
  onDisconnect?: () => void
  onError?: (error: Event) => void
  reconnectAttempts?: number
  reconnectInterval?: number
  autoConnect?: boolean
}

interface UseWebSocketReturn {
  send: (data: any) => void
  disconnect: () => void
  connect: () => void
  isConnected: boolean
  isConnecting: boolean
  error: Event | null
  readyState: number
}

export const useWebSocket = ({
  url,
  onMessage,
  onConnect,
  onDisconnect,
  onError,
  reconnectAttempts = 3,
  reconnectInterval = 3000,
  autoConnect = true,
}: UseWebSocketOptions): UseWebSocketReturn => {
  const [isConnected, setIsConnected] = useState(false)
  const [isConnecting, setIsConnecting] = useState(false)
  const [error, setError] = useState<Event | null>(null)
  const [readyState, setReadyState] = useState(WebSocket.CLOSED)

  const wsRef = useRef<WebSocket | null>(null)
  const reconnectTimeoutRef = useRef<NodeJS.Timeout | null>(null)
  const attemptCountRef = useRef(0)
  const shouldReconnectRef = useRef(true)

  const connect = useCallback(() => {
    if (wsRef.current?.readyState === WebSocket.OPEN) {
      return
    }

    setIsConnecting(true)
    setError(null)

    try {
      const ws = new WebSocket(url)
      wsRef.current = ws

      ws.onopen = (event) => {
        setIsConnected(true)
        setIsConnecting(false)
        setReadyState(WebSocket.OPEN)
        attemptCountRef.current = 0
        onConnect?.()
      }

      ws.onmessage = (event) => {
        try {
          const data = JSON.parse(event.data)
          onMessage?.(data)
        } catch (error) {
          console.error('Failed to parse WebSocket message:', error)
        }
      }

      ws.onclose = (event) => {
        setIsConnected(false)
        setIsConnecting(false)
        setReadyState(WebSocket.CLOSED)
        wsRef.current = null
        onDisconnect?.()

        // Attempt reconnection if enabled and within retry limits
        if (
          shouldReconnectRef.current &&
          attemptCountRef.current < reconnectAttempts &&
          !event.wasClean
        ) {
          attemptCountRef.current++
          reconnectTimeoutRef.current = setTimeout(() => {
            connect()
          }, reconnectInterval)
        }
      }

      ws.onerror = (event) => {
        setError(event)
        setIsConnecting(false)
        onError?.(event)
      }

      // Update ready state on state changes
      const updateReadyState = () => {
        if (wsRef.current) {
          setReadyState(wsRef.current.readyState)
        }
      }

      // Poll for ready state changes
      const readyStateInterval = setInterval(() => {
        updateReadyState()
        if (wsRef.current?.readyState === WebSocket.CLOSED) {
          clearInterval(readyStateInterval)
        }
      }, 100)

    } catch (error) {
      console.error('Failed to create WebSocket connection:', error)
      setIsConnecting(false)
      setError(error as Event)
    }
  }, [url, onMessage, onConnect, onDisconnect, onError, reconnectAttempts, reconnectInterval])

  const disconnect = useCallback(() => {
    shouldReconnectRef.current = false
    
    if (reconnectTimeoutRef.current) {
      clearTimeout(reconnectTimeoutRef.current)
      reconnectTimeoutRef.current = null
    }

    if (wsRef.current) {
      wsRef.current.close(1000, 'User initiated disconnect')
      wsRef.current = null
    }

    setIsConnected(false)
    setIsConnecting(false)
    setReadyState(WebSocket.CLOSED)
  }, [])

  const send = useCallback((data: any) => {
    if (wsRef.current?.readyState === WebSocket.OPEN) {
      try {
        const message = typeof data === 'string' ? data : JSON.stringify(data)
        wsRef.current.send(message)
      } catch (error) {
        console.error('Failed to send WebSocket message:', error)
      }
    } else {
      console.warn('WebSocket is not connected. Cannot send message.')
    }
  }, [])

  // Auto-connect on mount if enabled
  useEffect(() => {
    if (autoConnect) {
      shouldReconnectRef.current = true
      connect()
    }

    return () => {
      shouldReconnectRef.current = false
      if (reconnectTimeoutRef.current) {
        clearTimeout(reconnectTimeoutRef.current)
      }
      if (wsRef.current) {
        wsRef.current.close()
      }
    }
  }, [connect, autoConnect])

  // Cleanup on URL change
  useEffect(() => {
    return () => {
      disconnect()
    }
  }, [url, disconnect])

  return {
    send,
    disconnect,
    connect,
    isConnected,
    isConnecting,
    error,
    readyState,
  }
}
