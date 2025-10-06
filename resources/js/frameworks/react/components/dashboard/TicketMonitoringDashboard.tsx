/**
 * HD Tickets - React Ticket Monitoring Dashboard
 * 
 * Real-time ticket monitoring dashboard with complex state management
 * Features: Live price updates, filtering, alerts, purchase tracking
 */

import React, { useEffect, useState, useCallback, useMemo } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import {
  RootState,
  AppDispatch,
  fetchTickets,
  fetchDashboardStats,
  ticketsActions,
  selectTickets,
  selectTicketsLoading,
  selectTicketsError,
  selectDashboardStats,
  selectTicketFilters
} from '../../store/store';
import { Ticket, TicketFilters, MonitoringStats } from '@shared/types';
import { formatUtils, perfUtils, globalEventBus } from '@shared';

interface TicketMonitoringDashboardProps {
  userId?: string;
  autoRefresh?: boolean;
  refreshInterval?: number;
  maxTickets?: number;
  onTicketSelect?: (ticket: Ticket) => void;
  onPurchaseComplete?: (purchase: any) => void;
  designTokens?: Record<string, string>;
}

const TicketMonitoringDashboard: React.FC<TicketMonitoringDashboardProps> = ({
  userId,
  autoRefresh = true,
  refreshInterval = 30000,
  maxTickets = 100,
  onTicketSelect,
  onPurchaseComplete,
  designTokens = {}
}) => {
  const dispatch = useDispatch<AppDispatch>();
  
  // Redux selectors
  const tickets = useSelector(selectTickets);
  const ticketsLoading = useSelector(selectTicketsLoading);
  const ticketsError = useSelector(selectTicketsError);
  const dashboardStats = useSelector(selectDashboardStats);
  const filters = useSelector(selectTicketFilters);
  
  // Local state
  const [selectedTicket, setSelectedTicket] = useState<Ticket | null>(null);
  const [sortBy, setSortBy] = useState<'price' | 'date' | 'discount'>('price');
  const [sortOrder, setSortOrder] = useState<'asc' | 'desc'>('asc');
  const [viewMode, setViewMode] = useState<'grid' | 'list'>('grid');
  const [priceAlerts, setPriceAlerts] = useState<string[]>([]);

  // Memoized filtered and sorted tickets
  const processedTickets = useMemo(() => {
    let filtered = [...tickets];
    
    // Apply filters
    if (filters.platform && filters.platform.length > 0) {
      filtered = filtered.filter(ticket => filters.platform!.includes(ticket.platform));
    }
    
    if (filters.priceRange) {
      filtered = filtered.filter(ticket => 
        ticket.price >= filters.priceRange!.min && 
        ticket.price <= filters.priceRange!.max
      );
    }
    
    if (filters.availability && filters.availability.length > 0) {
      filtered = filtered.filter(ticket => filters.availability!.includes(ticket.availability));
    }
    
    // Sort tickets
    filtered.sort((a, b) => {
      let comparison = 0;
      
      switch (sortBy) {
        case 'price':
          comparison = a.price - b.price;
          break;
        case 'date':
          comparison = new Date(a.event_date).getTime() - new Date(b.event_date).getTime();
          break;
        case 'discount':
          const aDiscount = a.discount_percentage || 0;
          const bDiscount = b.discount_percentage || 0;
          comparison = bDiscount - aDiscount;
          break;
      }
      
      return sortOrder === 'asc' ? comparison : -comparison;
    });
    
    return filtered.slice(0, maxTickets);
  }, [tickets, filters, sortBy, sortOrder, maxTickets]);

  // Debounced filter update
  const debouncedUpdateFilters = useCallback(
    perfUtils.debounce((newFilters: Partial<TicketFilters>) => {
      dispatch(ticketsActions.updateFilters(newFilters));
      dispatch(fetchTickets({ filters: { ...filters, ...newFilters } }));
    }, 500),
    [dispatch, filters]
  );

  // Handle ticket selection
  const handleTicketSelect = useCallback((ticket: Ticket) => {
    setSelectedTicket(ticket);
    dispatch(ticketsActions.selectTicket(ticket));
    onTicketSelect?.(ticket);
    
    // Emit global event for other frameworks
    globalEventBus.emit('ticket-selected', ticket);
  }, [dispatch, onTicketSelect]);

  // Handle purchase initiation
  const handlePurchaseInitiate = useCallback((ticket: Ticket, quantity: number = 1) => {
    // Emit purchase start event
    document.dispatchEvent(new CustomEvent('purchase:started', { 
      detail: { ticket, quantity } 
    }));
    
    console.log('Purchase initiated for ticket:', ticket.id);
  }, []);

  // Handle price alert toggle
  const handlePriceAlertToggle = useCallback((ticketId: string) => {
    setPriceAlerts(prev => 
      prev.includes(ticketId) 
        ? prev.filter(id => id !== ticketId)
        : [...prev, ticketId]
    );
  }, []);

  // Auto-refresh effect
  useEffect(() => {
    if (!autoRefresh) return;

    const interval = setInterval(() => {
      dispatch(fetchTickets({ filters }));
      dispatch(fetchDashboardStats());
    }, refreshInterval);

    return () => clearInterval(interval);
  }, [dispatch, autoRefresh, refreshInterval, filters]);

  // Initial data fetch
  useEffect(() => {
    dispatch(fetchTickets({ filters }));
    dispatch(fetchDashboardStats());
  }, [dispatch, filters]);

  // Real-time updates via WebSocket/EventBus
  useEffect(() => {
    const unsubscribeTicketUpdate = globalEventBus.on('ticket-updated', (updatedTicket: Ticket) => {
      dispatch(ticketsActions.updateTicketPrice({ 
        id: updatedTicket.id, 
        price: updatedTicket.price 
      }));
    });

    const unsubscribePurchaseComplete = globalEventBus.on('purchase-completed', (purchase: any) => {
      onPurchaseComplete?.(purchase);
      // Refresh tickets to update availability
      dispatch(fetchTickets({ filters }));
    });

    return () => {
      unsubscribeTicketUpdate();
      unsubscribePurchaseComplete();
    };
  }, [dispatch, onPurchaseComplete, filters]);

  // Render loading state
  if (ticketsLoading && tickets.length === 0) {
    return (
      <div className="flex items-center justify-center p-8">
        <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        <span className="ml-3 text-gray-600">Loading tickets...</span>
      </div>
    );
  }

  // Render error state
  if (ticketsError) {
    return (
      <div className="bg-red-50 border border-red-200 rounded-lg p-4">
        <div className="flex">
          <div className="flex-shrink-0">
            <svg className="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
              <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clipRule="evenodd" />
            </svg>
          </div>
          <div className="ml-3">
            <h3 className="text-sm font-medium text-red-800">Error Loading Tickets</h3>
            <p className="mt-1 text-sm text-red-700">{ticketsError}</p>
            <button 
              onClick={() => dispatch(fetchTickets({ filters }))}
              className="mt-2 bg-red-600 text-white px-3 py-1 rounded text-sm hover:bg-red-700"
            >
              Retry
            </button>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      {/* Dashboard Stats */}
      {dashboardStats && (
        <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
          <div className="bg-white rounded-lg shadow p-4">
            <div className="text-2xl font-bold text-blue-600">
              {dashboardStats.active_monitors.toLocaleString()}
            </div>
            <div className="text-sm text-gray-600">Active Monitors</div>
          </div>
          <div className="bg-white rounded-lg shadow p-4">
            <div className="text-2xl font-bold text-green-600">
              {dashboardStats.tickets_tracked.toLocaleString()}
            </div>
            <div className="text-sm text-gray-600">Tickets Tracked</div>
          </div>
          <div className="bg-white rounded-lg shadow p-4">
            <div className="text-2xl font-bold text-orange-600">
              {dashboardStats.price_changes.toLocaleString()}
            </div>
            <div className="text-sm text-gray-600">Price Changes</div>
          </div>
          <div className="bg-white rounded-lg shadow p-4">
            <div className="text-2xl font-bold text-purple-600">
              {dashboardStats.alerts_sent.toLocaleString()}
            </div>
            <div className="text-sm text-gray-600">Alerts Sent</div>
          </div>
        </div>
      )}

      {/* Controls */}
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white rounded-lg shadow p-4">
        {/* Sort Controls */}
        <div className="flex items-center space-x-4">
          <div className="flex items-center space-x-2">
            <label htmlFor="sort-by" className="text-sm font-medium text-gray-700">
              Sort by:
            </label>
            <select
              id="sort-by"
              value={sortBy}
              onChange={(e) => setSortBy(e.target.value as any)}
              className="border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
            >
              <option value="price">Price</option>
              <option value="date">Event Date</option>
              <option value="discount">Discount</option>
            </select>
          </div>
          
          <button
            onClick={() => setSortOrder(prev => prev === 'asc' ? 'desc' : 'asc')}
            className="p-2 text-gray-400 hover:text-gray-600"
            title={`Sort ${sortOrder === 'asc' ? 'Descending' : 'Ascending'}`}
          >
            <svg className={`w-4 h-4 transform ${sortOrder === 'desc' ? 'rotate-180' : ''}`} fill="currentColor" viewBox="0 0 20 20">
              <path fillRule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clipRule="evenodd" />
            </svg>
          </button>
        </div>

        {/* View Mode Toggle */}
        <div className="flex items-center space-x-2">
          <span className="text-sm font-medium text-gray-700">View:</span>
          <div className="flex rounded-md shadow-sm">
            <button
              onClick={() => setViewMode('grid')}
              className={`px-3 py-2 text-sm font-medium rounded-l-md border ${
                viewMode === 'grid'
                  ? 'bg-blue-50 text-blue-700 border-blue-500'
                  : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
              }`}
            >
              Grid
            </button>
            <button
              onClick={() => setViewMode('list')}
              className={`px-3 py-2 text-sm font-medium rounded-r-md border-l-0 border ${
                viewMode === 'list'
                  ? 'bg-blue-50 text-blue-700 border-blue-500'
                  : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
              }`}
            >
              List
            </button>
          </div>
        </div>
      </div>

      {/* Tickets Display */}
      <div className={viewMode === 'grid' 
        ? 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4'
        : 'space-y-4'
      }>
        {processedTickets.map((ticket) => (
          <TicketCard
            key={ticket.id}
            ticket={ticket}
            viewMode={viewMode}
            isSelected={selectedTicket?.id === ticket.id}
            hasAlert={priceAlerts.includes(ticket.id)}
            onSelect={() => handleTicketSelect(ticket)}
            onPurchase={() => handlePurchaseInitiate(ticket)}
            onToggleAlert={() => handlePriceAlertToggle(ticket.id)}
          />
        ))}
      </div>

      {/* Empty State */}
      {processedTickets.length === 0 && !ticketsLoading && (
        <div className="text-center py-12">
          <svg className="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
          </svg>
          <h3 className="mt-2 text-sm font-medium text-gray-900">No tickets found</h3>
          <p className="mt-1 text-sm text-gray-500">Try adjusting your filters or check back later for new listings.</p>
        </div>
      )}

      {/* Auto-refresh indicator */}
      {autoRefresh && (
        <div className="fixed bottom-4 right-4 bg-blue-600 text-white px-3 py-2 rounded-lg shadow-lg text-sm">
          <div className="flex items-center space-x-2">
            <div className="animate-pulse w-2 h-2 bg-blue-300 rounded-full"></div>
            <span>Live updates active</span>
          </div>
        </div>
      )}
    </div>
  );
};

// Ticket Card Component
interface TicketCardProps {
  ticket: Ticket;
  viewMode: 'grid' | 'list';
  isSelected: boolean;
  hasAlert: boolean;
  onSelect: () => void;
  onPurchase: () => void;
  onToggleAlert: () => void;
}

const TicketCard: React.FC<TicketCardProps> = ({
  ticket,
  viewMode,
  isSelected,
  hasAlert,
  onSelect,
  onPurchase,
  onToggleAlert
}) => {
  const cardClasses = `
    bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-200 border-2
    ${isSelected ? 'border-blue-500' : 'border-transparent hover:border-gray-200'}
    ${viewMode === 'list' ? 'flex items-center p-4' : 'p-4'}
    cursor-pointer
  `;

  return (
    <div className={cardClasses} onClick={onSelect}>
      {/* Badge for featured/discount */}
      {(ticket.is_featured || ticket.discount_percentage) && (
        <div className="absolute top-2 left-2">
          {ticket.discount_percentage && (
            <span className="bg-red-500 text-white text-xs px-2 py-1 rounded-full">
              -{formatUtils.percentage(ticket.discount_percentage)}
            </span>
          )}
          {ticket.is_featured && (
            <span className="bg-yellow-500 text-white text-xs px-2 py-1 rounded-full ml-1">
              Featured
            </span>
          )}
        </div>
      )}

      <div className={viewMode === 'list' ? 'flex-1 flex items-center justify-between' : 'space-y-3'}>
        {/* Event Info */}
        <div className={viewMode === 'list' ? 'flex-1' : ''}>
          <h3 className="font-semibold text-gray-900 truncate">{ticket.event_name}</h3>
          <p className="text-sm text-gray-600">{ticket.venue}</p>
          <p className="text-xs text-gray-500">{formatUtils.date(ticket.event_date)}</p>
        </div>

        {/* Ticket Details */}
        <div className={viewMode === 'list' ? 'flex items-center space-x-4' : 'space-y-2'}>
          <div className="flex items-center justify-between">
            <span className="text-sm text-gray-600">Section {ticket.section}</span>
            <span className={`text-xs px-2 py-1 rounded-full ${
              ticket.availability === 'available' ? 'bg-green-100 text-green-800' :
              ticket.availability === 'limited' ? 'bg-yellow-100 text-yellow-800' :
              'bg-red-100 text-red-800'
            }`}>
              {ticket.availability.toUpperCase()}
            </span>
          </div>

          {/* Price */}
          <div className="flex items-center justify-between">
            <div>
              <div className="text-lg font-bold text-gray-900">
                {formatUtils.price(ticket.price, ticket.currency)}
              </div>
              {ticket.original_price && ticket.original_price > ticket.price && (
                <div className="text-sm text-gray-500 line-through">
                  {formatUtils.price(ticket.original_price, ticket.currency)}
                </div>
              )}
            </div>
            
            <div className="flex items-center space-x-2">
              {/* Price Alert Button */}
              <button
                onClick={(e) => {
                  e.stopPropagation();
                  onToggleAlert();
                }}
                className={`p-2 rounded-full ${
                  hasAlert 
                    ? 'bg-yellow-100 text-yellow-600 hover:bg-yellow-200' 
                    : 'bg-gray-100 text-gray-400 hover:bg-gray-200'
                }`}
                title={hasAlert ? 'Remove price alert' : 'Add price alert'}
              >
                <svg className="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M10 2L8.59 8.59 2 10l6.59 1.41L10 18l1.41-6.59L18 10l-6.59-1.41z" />
                </svg>
              </button>

              {/* Purchase Button */}
              <button
                onClick={(e) => {
                  e.stopPropagation();
                  onPurchase();
                }}
                disabled={ticket.availability === 'sold_out'}
                className={`px-3 py-1 rounded text-sm font-medium ${
                  ticket.availability === 'sold_out'
                    ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                    : 'bg-blue-600 text-white hover:bg-blue-700'
                }`}
              >
                {ticket.availability === 'sold_out' ? 'Sold Out' : 'Buy'}
              </button>
            </div>
          </div>
        </div>
      </div>

      {/* Platform badge */}
      <div className="absolute bottom-2 right-2">
        <span className="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded">
          {ticket.platform.toUpperCase()}
        </span>
      </div>
    </div>
  );
};

export default TicketMonitoringDashboard;