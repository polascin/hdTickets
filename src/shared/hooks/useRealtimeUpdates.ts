import { useEffect, useRef } from 'react'
import { useWebSocket } from './useWebSocket'

interface RealtimeUpdateOptions {
  enabled?: boolean
  reconnectAttempts?: number
  reconnectInterval?: number
}

export const useRealtimeUpdates = (
  channel: string,
  onUpdate: (data: any) => void,
  options: RealtimeUpdateOptions = {}
) => {
  const {
    enabled = true,
    reconnectAttempts = 5,
    reconnectInterval = 3000,
  } = options
  
  const onUpdateRef = useRef(onUpdate)
  onUpdateRef.current = onUpdate
  
  const { send, isConnected } = useWebSocket({
    url: process.env.NEXT_PUBLIC_WS_URL || 'ws://localhost:6001',
    onMessage: (data) => {
      if (data.channel === channel) {
        onUpdateRef.current(data.data)
      }
    },
    reconnectAttempts,
    reconnectInterval,
  })
  
  // Subscribe to channel when connected
  useEffect(() => {
    if (enabled && isConnected) {
      send({
        type: 'subscribe',
        channel: channel,
        timestamp: Date.now(),
      })
      
      return () => {
        send({
          type: 'unsubscribe',
          channel: channel,
          timestamp: Date.now(),
        })
      }
    }
  }, [enabled, isConnected, channel, send])
  
  return {
    isConnected,
    subscribe: (newChannel: string) => {
      if (isConnected) {
        send({
          type: 'subscribe',
          channel: newChannel,
          timestamp: Date.now(),
        })
      }
    },
    unsubscribe: (channelToRemove: string) => {
      if (isConnected) {
        send({
          type: 'unsubscribe',
          channel: channelToRemove,
          timestamp: Date.now(),
        })
      }
    },
  }
}

export default useRealtimeUpdates
