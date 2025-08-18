import { useState, useEffect, useRef, useCallback } from 'react';
import { useQueryClient } from '@tanstack/react-query';
import type { 
  WebSocketMessage, 
  WebSocketMessageType, 
  PriceUpdateMessage, 
  AlertTriggeredMessage,
  Platform,
  PlatformStatus 
} from '@/types';

export interface WebSocketConfig {
  url: string;
  reconnectAttempts?: number;
  reconnectInterval?: number;
  heartbeatInterval?: number;
  protocols?: string[];
}

export interface WebSocketState {
  isConnected: boolean;
  isConnecting: boolean;
  error: string | null;
  lastMessage: WebSocketMessage | null;
  reconnectCount: number;
}

export function useWebSocket(config: WebSocketConfig) {
  const [state, setState] = useState<WebSocketState>({
    isConnected: false,
    isConnecting: false,
    error: null,
    lastMessage: null,
    reconnectCount: 0,
  });

  const wsRef = useRef<WebSocket | null>(null);
  const reconnectTimeoutRef = useRef<NodeJS.Timeout>();
  const heartbeatIntervalRef = useRef<NodeJS.Timeout>();
  const messageHandlersRef = useRef<Map<WebSocketMessageType, ((data: any) => void)[]>>(new Map());

  const connect = useCallback(() => {
    if (wsRef.current?.readyState === WebSocket.OPEN) return;

    setState(prev => ({ ...prev, isConnecting: true, error: null }));

    try {
      wsRef.current = new WebSocket(config.url, config.protocols);

      wsRef.current.onopen = () => {
        setState(prev => ({
          ...prev,
          isConnected: true,
          isConnecting: false,
          error: null,
          reconnectCount: 0,
        }));

        // Start heartbeat
        if (config.heartbeatInterval) {
          heartbeatIntervalRef.current = setInterval(() => {
            if (wsRef.current?.readyState === WebSocket.OPEN) {
              wsRef.current.send(JSON.stringify({ type: 'ping' }));
            }
          }, config.heartbeatInterval);
        }
      };

      wsRef.current.onmessage = (event) => {
        try {
          const message: WebSocketMessage = JSON.parse(event.data);
          
          setState(prev => ({ ...prev, lastMessage: message }));

          // Call registered handlers
          const handlers = messageHandlersRef.current.get(message.type) || [];
          handlers.forEach(handler => handler(message.data));
        } catch (error) {
          console.error('Failed to parse WebSocket message:', error);
        }
      };

      wsRef.current.onclose = () => {
        setState(prev => ({
          ...prev,
          isConnected: false,
          isConnecting: false,
        }));

        if (heartbeatIntervalRef.current) {
          clearInterval(heartbeatIntervalRef.current);
        }

        // Attempt reconnection
        if (state.reconnectCount < (config.reconnectAttempts || 5)) {
          reconnectTimeoutRef.current = setTimeout(() => {
            setState(prev => ({ ...prev, reconnectCount: prev.reconnectCount + 1 }));
            connect();
          }, config.reconnectInterval || 3000);
        }
      };

      wsRef.current.onerror = (error) => {
        setState(prev => ({
          ...prev,
          error: 'WebSocket connection error',
          isConnecting: false,
        }));
      };
    } catch (error) {
      setState(prev => ({
        ...prev,
        error: 'Failed to create WebSocket connection',
        isConnecting: false,
      }));
    }
  }, [config, state.reconnectCount]);

  const disconnect = useCallback(() => {
    if (reconnectTimeoutRef.current) {
      clearTimeout(reconnectTimeoutRef.current);
    }
    if (heartbeatIntervalRef.current) {
      clearInterval(heartbeatIntervalRef.current);
    }
    if (wsRef.current) {
      wsRef.current.close();
      wsRef.current = null;
    }
    setState({
      isConnected: false,
      isConnecting: false,
      error: null,
      lastMessage: null,
      reconnectCount: 0,
    });
  }, []);

  const sendMessage = useCallback((message: any) => {
    if (wsRef.current?.readyState === WebSocket.OPEN) {
      wsRef.current.send(JSON.stringify(message));
      return true;
    }
    return false;
  }, []);

  const subscribe = useCallback((type: WebSocketMessageType, handler: (data: any) => void) => {
    const handlers = messageHandlersRef.current.get(type) || [];
    handlers.push(handler);
    messageHandlersRef.current.set(type, handlers);

    // Return unsubscribe function
    return () => {
      const currentHandlers = messageHandlersRef.current.get(type) || [];
      const index = currentHandlers.indexOf(handler);
      if (index > -1) {
        currentHandlers.splice(index, 1);
        messageHandlersRef.current.set(type, currentHandlers);
      }
    };
  }, []);

  useEffect(() => {
    connect();
    return disconnect;
  }, []);

  return {
    ...state,
    connect,
    disconnect,
    sendMessage,
    subscribe,
  };
}

// Price monitoring hook
export function usePriceMonitoring() {
  const queryClient = useQueryClient();
  const [priceUpdates, setPriceUpdates] = useState<PriceUpdateMessage[]>([]);

  const { isConnected, subscribe } = useWebSocket({
    url: process.env.NODE_ENV === 'development' 
      ? 'ws://localhost:8080/ws' 
      : 'wss://api.hdtickets.com/ws',
    reconnectAttempts: 5,
    reconnectInterval: 3000,
    heartbeatInterval: 30000,
  });

  useEffect(() => {
    const unsubscribePriceUpdate = subscribe('price_update', (data: PriceUpdateMessage) => {
      setPriceUpdates(prev => [data, ...prev.slice(0, 99)]); // Keep last 100 updates
      
      // Invalidate ticket queries to refresh data
      queryClient.invalidateQueries({ queryKey: ['tickets'] });
    });

    const unsubscribeAlertTriggered = subscribe('alert_triggered', (data: AlertTriggeredMessage) => {
      // Show notification for triggered alert
      console.log('Alert triggered:', data);
      
      // Invalidate alerts to refresh status
      queryClient.invalidateQueries({ queryKey: ['alerts'] });
    });

    return () => {
      unsubscribePriceUpdate();
      unsubscribeAlertTriggered();
    };
  }, [subscribe, queryClient]);

  return {
    isConnected,
    priceUpdates,
    clearUpdates: () => setPriceUpdates([]),
  };
}

// Platform monitoring hook
export function usePlatformMonitoring() {
  const queryClient = useQueryClient();
  const [platformStatuses, setPlatformStatuses] = useState<Map<string, PlatformStatus>>(new Map());

  const { isConnected, subscribe } = useWebSocket({
    url: process.env.NODE_ENV === 'development' 
      ? 'ws://localhost:8080/ws' 
      : 'wss://api.hdtickets.com/ws',
  });

  useEffect(() => {
    const unsubscribe = subscribe('platform_status', (data: { 
      platformId: string; 
      status: PlatformStatus; 
      timestamp: string; 
    }) => {
      setPlatformStatuses(prev => new Map(prev.set(data.platformId, data.status)));
      
      // Invalidate platform queries
      queryClient.invalidateQueries({ queryKey: ['platforms'] });
    });

    return unsubscribe;
  }, [subscribe, queryClient]);

  return {
    isConnected,
    platformStatuses,
    getPlatformStatus: (platformId: string) => platformStatuses.get(platformId),
  };
}

// Real-time notifications hook
export function useRealTimeNotifications() {
  const [notifications, setNotifications] = useState<any[]>([]);

  const { isConnected, subscribe } = useWebSocket({
    url: process.env.NODE_ENV === 'development' 
      ? 'ws://localhost:8080/ws' 
      : 'wss://api.hdtickets.com/ws',
  });

  useEffect(() => {
    const unsubscribe = subscribe('system_message', (data: any) => {
      setNotifications(prev => [data, ...prev.slice(0, 49)]); // Keep last 50 notifications
    });

    return unsubscribe;
  }, [subscribe]);

  const clearNotifications = useCallback(() => {
    setNotifications([]);
  }, []);

  const removeNotification = useCallback((id: string) => {
    setNotifications(prev => prev.filter(n => n.id !== id));
  }, []);

  return {
    isConnected,
    notifications,
    clearNotifications,
    removeNotification,
  };
}

// Custom hook for specific ticket monitoring
export function useTicketMonitoring(ticketIds: string[]) {
  const queryClient = useQueryClient();
  const [updates, setUpdates] = useState<Map<string, PriceUpdateMessage>>(new Map());

  const { isConnected, subscribe, sendMessage } = useWebSocket({
    url: process.env.NODE_ENV === 'development' 
      ? 'ws://localhost:8080/ws' 
      : 'wss://api.hdtickets.com/ws',
  });

  useEffect(() => {
    if (isConnected && ticketIds.length > 0) {
      // Subscribe to specific tickets
      sendMessage({
        type: 'subscribe_tickets',
        data: { ticketIds }
      });
    }

    const unsubscribe = subscribe('price_update', (data: PriceUpdateMessage) => {
      if (ticketIds.includes(data.ticketId)) {
        setUpdates(prev => new Map(prev.set(data.ticketId, data)));
        
        // Invalidate specific ticket query
        queryClient.invalidateQueries({ queryKey: ['tickets', data.ticketId] });
      }
    });

    return () => {
      unsubscribe();
      if (isConnected) {
        sendMessage({
          type: 'unsubscribe_tickets',
          data: { ticketIds }
        });
      }
    };
  }, [isConnected, ticketIds, subscribe, sendMessage, queryClient]);

  return {
    isConnected,
    updates,
    getUpdate: (ticketId: string) => updates.get(ticketId),
  };
}
