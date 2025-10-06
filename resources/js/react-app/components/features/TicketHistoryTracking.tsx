/**
 * HD Tickets - Ticket History Tracking
 * Price history charts and availability tracking with trends and patterns analysis
 */

import React, { useState, useMemo } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import {
  ChartBarIcon,
  ArrowTrendingUpIcon,
  ArrowTrendingDownIcon,
  ClockIcon,
  CalendarDaysIcon,
  CurrencyPoundIcon,
  TrendingUpIcon,
  TrendingDownIcon,
  MinusIcon,
  EyeIcon,
  ArrowDownIcon,
  ArrowUpIcon,
  InformationCircleIcon,
  AdjustmentsHorizontalIcon,
  FunnelIcon,
  MagnifyingGlassIcon,
} from '@heroicons/react/24/outline';
import { 
  ArrowTrendingUpIcon as ArrowUpSolidIcon,
  ArrowTrendingDownIcon as ArrowDownSolidIcon,
} from '@heroicons/react/24/solid';

import { cn, formatCurrency, formatDate } from '../../utils/design';
import Button from '../ui/Button';

interface PricePoint {
  timestamp: string;
  price: number;
  platform: string;
  availability: number;
  section?: string;
  row?: string;
}

interface TicketHistory {
  id: string;
  eventId: string;
  eventName: string;
  venue: string;
  eventDate: string;
  sport: string;
  section: string;
  row?: string;
  priceHistory: PricePoint[];
  currentPrice: number;
  lowestPrice: number;
  highestPrice: number;
  averagePrice: number;
  priceChange24h: number;
  priceChange7d: number;
  priceChangePct24h: number;
  priceChangePct7d: number;
  totalDataPoints: number;
  firstTracked: string;
  lastUpdated: string;
  isTracking: boolean;
  platforms: string[];
}

interface TicketHistoryTrackingProps {
  ticketHistories?: TicketHistory[];
  onToggleTracking?: (ticketId: string) => void;
  onViewDetails?: (ticketId: string) => void;
  onExportData?: (ticketId: string) => void;
}

// Mock data
const mockTicketHistories: TicketHistory[] = [
  {
    id: 'history-1',
    eventId: 'match-1',
    eventName: 'Manchester United vs Liverpool',
    venue: 'Old Trafford',
    eventDate: '2024-03-15T15:00:00Z',
    sport: 'Football',
    section: 'Lower Tier Block 134',
    row: '12',
    priceHistory: [
      { timestamp: '2024-01-01T10:00:00Z', price: 180, platform: 'Ticketmaster', availability: 100 },
      { timestamp: '2024-01-02T10:00:00Z', price: 175, platform: 'Ticketmaster', availability: 95 },
      { timestamp: '2024-01-03T10:00:00Z', price: 170, platform: 'StubHub', availability: 92 },
      { timestamp: '2024-01-05T10:00:00Z', price: 165, platform: 'StubHub', availability: 88 },
      { timestamp: '2024-01-08T10:00:00Z', price: 160, platform: 'Viagogo', availability: 85 },
      { timestamp: '2024-01-10T10:00:00Z', price: 155, platform: 'Viagogo', availability: 80 },
      { timestamp: '2024-01-12T10:00:00Z', price: 150, platform: 'StubHub', availability: 75 },
      { timestamp: '2024-01-15T10:00:00Z', price: 145, platform: 'Ticketmaster', availability: 70 },
    ],
    currentPrice: 145,
    lowestPrice: 145,
    highestPrice: 180,
    averagePrice: 162.5,
    priceChange24h: -5,
    priceChange7d: -15,
    priceChangePct24h: -3.3,
    priceChangePct7d: -9.4,
    totalDataPoints: 8,
    firstTracked: '2024-01-01T10:00:00Z',
    lastUpdated: '2024-01-15T10:00:00Z',
    isTracking: true,
    platforms: ['Ticketmaster', 'StubHub', 'Viagogo'],
  },
  {
    id: 'history-2',
    eventId: 'match-2',
    eventName: 'Arsenal vs Chelsea',
    venue: 'Emirates Stadium',
    eventDate: '2024-03-20T17:30:00Z',
    sport: 'Football',
    section: 'Upper Tier Block 112',
    priceHistory: [
      { timestamp: '2024-01-05T10:00:00Z', price: 85, platform: 'Ticketmaster', availability: 150 },
      { timestamp: '2024-01-07T10:00:00Z', price: 90, platform: 'Ticketmaster', availability: 145 },
      { timestamp: '2024-01-10T10:00:00Z', price: 95, platform: 'StubHub', availability: 140 },
      { timestamp: '2024-01-12T10:00:00Z', price: 100, platform: 'StubHub', availability: 135 },
      { timestamp: '2024-01-15T10:00:00Z', price: 105, platform: 'See Tickets', availability: 130 },
    ],
    currentPrice: 105,
    lowestPrice: 85,
    highestPrice: 105,
    averagePrice: 95,
    priceChange24h: 5,
    priceChange7d: 10,
    priceChangePct24h: 5.0,
    priceChangePct7d: 10.5,
    totalDataPoints: 5,
    firstTracked: '2024-01-05T10:00:00Z',
    lastUpdated: '2024-01-15T10:00:00Z',
    isTracking: true,
    platforms: ['Ticketmaster', 'StubHub', 'See Tickets'],
  },
  {
    id: 'history-3',
    eventId: 'fight-1',
    eventName: 'Anthony Joshua vs Francis Ngannou',
    venue: 'Wembley Stadium',
    eventDate: '2024-04-12T19:00:00Z',
    sport: 'Boxing',
    section: 'Lower Bowl Section 145',
    priceHistory: [
      { timestamp: '2024-01-08T10:00:00Z', price: 250, platform: 'Ticketmaster', availability: 80 },
      { timestamp: '2024-01-09T10:00:00Z', price: 275, platform: 'Ticketmaster', availability: 75 },
      { timestamp: '2024-01-11T10:00:00Z', price: 300, platform: 'StubHub', availability: 70 },
      { timestamp: '2024-01-13T10:00:00Z', price: 320, platform: 'StubHub', availability: 65 },
      { timestamp: '2024-01-15T10:00:00Z', price: 340, platform: 'Viagogo', availability: 60 },
    ],
    currentPrice: 340,
    lowestPrice: 250,
    highestPrice: 340,
    averagePrice: 297,
    priceChange24h: 20,
    priceChange7d: 40,
    priceChangePct24h: 6.3,
    priceChangePct7d: 13.3,
    totalDataPoints: 5,
    firstTracked: '2024-01-08T10:00:00Z',
    lastUpdated: '2024-01-15T10:00:00Z',
    isTracking: true,
    platforms: ['Ticketmaster', 'StubHub', 'Viagogo'],
  },
];

const TicketHistoryTracking: React.FC<TicketHistoryTrackingProps> = ({
  ticketHistories = mockTicketHistories,
  onToggleTracking,
  onViewDetails,
  onExportData,
}) => {
  const [selectedHistory, setSelectedHistory] = useState<string | null>(null);
  const [timeRange, setTimeRange] = useState<'7d' | '30d' | '90d' | 'all'>('30d');
  const [sortBy, setSortBy] = useState<'name' | 'price_change' | 'last_updated'>('last_updated');
  const [filterSport, setFilterSport] = useState<string>('all');
  const [searchQuery, setSearchQuery] = useState('');
  const [showFilters, setShowFilters] = useState(false);

  // Filter and sort histories
  const filteredHistories = useMemo(() => {
    let filtered = ticketHistories.filter(history => {
      const matchesSearch = searchQuery === '' ||
        history.eventName.toLowerCase().includes(searchQuery.toLowerCase()) ||
        history.venue.toLowerCase().includes(searchQuery.toLowerCase()) ||
        history.section.toLowerCase().includes(searchQuery.toLowerCase());
      const matchesSport = filterSport === 'all' || history.sport === filterSport;
      
      return matchesSearch && matchesSport;
    });

    return filtered.sort((a, b) => {
      switch (sortBy) {
        case 'name':
          return a.eventName.localeCompare(b.eventName);
        case 'price_change':
          return b.priceChangePct7d - a.priceChangePct7d;
        case 'last_updated':
          return new Date(b.lastUpdated).getTime() - new Date(a.lastUpdated).getTime();
        default:
          return 0;
      }
    });
  }, [ticketHistories, searchQuery, filterSport, sortBy]);

  // Get unique sports for filter
  const availableSports = useMemo(() => {
    return Array.from(new Set(ticketHistories.map(h => h.sport))).sort();
  }, [ticketHistories]);

  const getTrendIcon = (change: number) => {
    if (change > 0) {
      return <ArrowTrendingUpIcon className="h-4 w-4 text-red-500" />;
    } else if (change < 0) {
      return <ArrowTrendingDownIcon className="h-4 w-4 text-green-500" />;
    } else {
      return <MinusIcon className="h-4 w-4 text-gray-500" />;
    }
  };

  const getTrendColor = (change: number) => {
    if (change > 0) return 'text-red-600 dark:text-red-400';
    if (change < 0) return 'text-green-600 dark:text-green-400';
    return 'text-gray-600 dark:text-gray-400';
  };

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div>
          <h2 className="text-2xl font-bold text-gray-900 dark:text-white">
            Ticket History Tracking
          </h2>
          <p className="mt-1 text-gray-600 dark:text-gray-300">
            Monitor price trends and availability patterns for your tracked tickets
          </p>
        </div>
        <div className="flex items-center space-x-2">
          <span className="px-3 py-1 text-sm bg-blue-100 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 rounded-full">
            {ticketHistories.filter(h => h.isTracking).length} Tracking
          </span>
        </div>
      </div>

      {/* Controls */}
      <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div className="flex items-center space-x-2">
          <Button
            variant="outline"
            size="sm"
            leftIcon={<FunnelIcon className="h-4 w-4" />}
            onClick={() => setShowFilters(!showFilters)}
          >
            Filters
          </Button>
          <select
            value={sortBy}
            onChange={(e) => setSortBy(e.target.value as any)}
            className="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
          >
            <option value="last_updated">Sort by Last Updated</option>
            <option value="name">Sort by Event Name</option>
            <option value="price_change">Sort by Price Change</option>
          </select>
        </div>

        <div className="flex items-center space-x-2">
          <span className="text-sm text-gray-600 dark:text-gray-300">Time Range:</span>
          <div className="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-1">
            {(['7d', '30d', '90d', 'all'] as const).map((range) => (
              <button
                key={range}
                onClick={() => setTimeRange(range)}
                className={cn(
                  'px-3 py-1 text-sm rounded-md transition-colors',
                  timeRange === range
                    ? 'bg-blue-100 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300'
                    : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'
                )}
              >
                {range === 'all' ? 'All Time' : range.toUpperCase()}
              </button>
            ))}
          </div>
        </div>
      </div>

      {/* Filters */}
      <AnimatePresence>
        {showFilters && (
          <motion.div
            initial={{ opacity: 0, height: 0 }}
            animate={{ opacity: 1, height: 'auto' }}
            exit={{ opacity: 0, height: 0 }}
            className="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4"
          >
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              {/* Search */}
              <div className="relative">
                <MagnifyingGlassIcon className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                <input
                  type="text"
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  placeholder="Search events, venues, sections..."
                  className="w-full pl-10 pr-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                />
              </div>

              {/* Sport Filter */}
              <select
                value={filterSport}
                onChange={(e) => setFilterSport(e.target.value)}
                className="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
              >
                <option value="all">All Sports</option>
                {availableSports.map(sport => (
                  <option key={sport} value={sport}>{sport}</option>
                ))}
              </select>

              {/* Clear Filters */}
              <Button
                variant="outline"
                size="sm"
                onClick={() => {
                  setSearchQuery('');
                  setFilterSport('all');
                }}
              >
                Clear Filters
              </Button>
            </div>
          </motion.div>
        )}
      </AnimatePresence>

      {/* Summary Cards */}
      <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div className="bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
          <div className="text-sm text-gray-500 dark:text-gray-400">Total Tracked</div>
          <div className="text-2xl font-bold text-gray-900 dark:text-white">
            {ticketHistories.length}
          </div>
        </div>
        
        <div className="bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
          <div className="text-sm text-gray-500 dark:text-gray-400">Price Decreases</div>
          <div className="text-2xl font-bold text-green-600 dark:text-green-400">
            {ticketHistories.filter(h => h.priceChangePct7d < 0).length}
          </div>
        </div>
        
        <div className="bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
          <div className="text-sm text-gray-500 dark:text-gray-400">Price Increases</div>
          <div className="text-2xl font-bold text-red-600 dark:text-red-400">
            {ticketHistories.filter(h => h.priceChangePct7d > 0).length}
          </div>
        </div>
        
        <div className="bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
          <div className="text-sm text-gray-500 dark:text-gray-400">Avg. Change (7d)</div>
          <div className={cn(
            'text-2xl font-bold',
            getTrendColor(ticketHistories.reduce((sum, h) => sum + h.priceChangePct7d, 0) / ticketHistories.length)
          )}>
            {((ticketHistories.reduce((sum, h) => sum + h.priceChangePct7d, 0) / ticketHistories.length) || 0).toFixed(1)}%
          </div>
        </div>
      </div>

      {/* History List */}
      <div className="space-y-4">
        {filteredHistories.length === 0 ? (
          <div className="text-center py-12">
            <ChartBarIcon className="mx-auto h-12 w-12 text-gray-400" />
            <h3 className="mt-4 text-lg font-medium text-gray-900 dark:text-white">
              No ticket histories found
            </h3>
            <p className="mt-1 text-gray-600 dark:text-gray-300">
              {ticketHistories.length === 0 
                ? 'Start tracking tickets to see price history and trends here.'
                : 'Try adjusting your filters to see more results.'}
            </p>
          </div>
        ) : (
          filteredHistories.map((history, index) => (
            <TicketHistoryCard
              key={history.id}
              history={history}
              isSelected={selectedHistory === history.id}
              onSelect={() => setSelectedHistory(history.id === selectedHistory ? null : history.id)}
              onToggleTracking={onToggleTracking}
              onViewDetails={onViewDetails}
              onExportData={onExportData}
              index={index}
              timeRange={timeRange}
            />
          ))
        )}
      </div>
    </div>
  );
};

// Ticket History Card Component
interface TicketHistoryCardProps {
  history: TicketHistory;
  isSelected: boolean;
  onSelect: () => void;
  onToggleTracking?: (ticketId: string) => void;
  onViewDetails?: (ticketId: string) => void;
  onExportData?: (ticketId: string) => void;
  index: number;
  timeRange: string;
}

const TicketHistoryCard: React.FC<TicketHistoryCardProps> = ({
  history,
  isSelected,
  onSelect,
  onToggleTracking,
  onViewDetails,
  onExportData,
  index,
  timeRange,
}) => {
  const getTrendIcon = (change: number) => {
    if (change > 0) {
      return <ArrowTrendingUpIcon className="h-4 w-4 text-red-500" />;
    } else if (change < 0) {
      return <ArrowTrendingDownIcon className="h-4 w-4 text-green-500" />;
    } else {
      return <MinusIcon className="h-4 w-4 text-gray-500" />;
    }
  };

  const getTrendColor = (change: number) => {
    if (change > 0) return 'text-red-600 dark:text-red-400';
    if (change < 0) return 'text-green-600 dark:text-green-400';
    return 'text-gray-600 dark:text-gray-400';
  };

  // Simple price chart visualization
  const createMiniChart = (priceHistory: PricePoint[]) => {
    if (priceHistory.length < 2) return null;
    
    const prices = priceHistory.map(p => p.price);
    const min = Math.min(...prices);
    const max = Math.max(...prices);
    const range = max - min;
    
    if (range === 0) return null;
    
    const points = priceHistory.map((point, idx) => {
      const x = (idx / (priceHistory.length - 1)) * 100;
      const y = 100 - ((point.price - min) / range) * 100;
      return `${x},${y}`;
    }).join(' ');
    
    return (
      <svg className="w-full h-16" viewBox="0 0 100 100" preserveAspectRatio="none">
        <polyline
          fill="none"
          stroke="currentColor"
          strokeWidth="2"
          points={points}
          className={getTrendColor(history.priceChangePct7d)}
        />
        <circle
          cx={100}
          cy={100 - ((prices[prices.length - 1] - min) / range) * 100}
          r="2"
          className={cn("fill-current", getTrendColor(history.priceChangePct7d))}
        />
      </svg>
    );
  };

  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ delay: index * 0.05 }}
      className={cn(
        'bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden transition-all hover:shadow-lg cursor-pointer',
        isSelected && 'ring-2 ring-blue-500/20 border-blue-500/50'
      )}
      onClick={onSelect}
    >
      <div className="p-6">
        <div className="flex items-start justify-between">
          <div className="flex-1 min-w-0">
            <div className="flex items-center space-x-3">
              <div>
                <h3 className="font-semibold text-gray-900 dark:text-white">
                  {history.eventName}
                </h3>
                <div className="flex items-center space-x-4 mt-1 text-sm text-gray-500 dark:text-gray-400">
                  <span>{history.venue}</span>
                  <span>•</span>
                  <span>{formatDate(history.eventDate)}</span>
                  <span>•</span>
                  <span>{history.section}</span>
                  {history.row && (
                    <>
                      <span>•</span>
                      <span>Row {history.row}</span>
                    </>
                  )}
                </div>
              </div>
            </div>

            {/* Price Information */}
            <div className="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4">
              <div>
                <div className="text-xs text-gray-500 dark:text-gray-400 mb-1">Current Price</div>
                <div className="text-lg font-bold text-gray-900 dark:text-white">
                  {formatCurrency(history.currentPrice)}
                </div>
              </div>
              
              <div>
                <div className="text-xs text-gray-500 dark:text-gray-400 mb-1">7d Change</div>
                <div className={cn('text-lg font-bold flex items-center space-x-1', getTrendColor(history.priceChangePct7d))}>
                  {getTrendIcon(history.priceChangePct7d)}
                  <span>{history.priceChangePct7d > 0 ? '+' : ''}{history.priceChangePct7d.toFixed(1)}%</span>
                </div>
              </div>
              
              <div>
                <div className="text-xs text-gray-500 dark:text-gray-400 mb-1">Range</div>
                <div className="text-sm text-gray-600 dark:text-gray-300">
                  {formatCurrency(history.lowestPrice)} - {formatCurrency(history.highestPrice)}
                </div>
              </div>
              
              <div>
                <div className="text-xs text-gray-500 dark:text-gray-400 mb-1">Platforms</div>
                <div className="text-sm text-gray-600 dark:text-gray-300">
                  {history.platforms.length} sources
                </div>
              </div>
            </div>

            {/* Mini Chart */}
            <div className="mt-4">
              <div className="text-xs text-gray-500 dark:text-gray-400 mb-2">Price Trend</div>
              <div className="h-16 bg-gray-50 dark:bg-gray-700 rounded p-2">
                {createMiniChart(history.priceHistory)}
              </div>
            </div>

            {/* Metadata */}
            <div className="mt-4 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
              <div className="flex items-center space-x-4">
                <span>Tracking since {formatDate(history.firstTracked, { relative: true })}</span>
                <span>•</span>
                <span>{history.totalDataPoints} data points</span>
              </div>
              <div className="flex items-center space-x-1">
                <ClockIcon className="h-3 w-3" />
                <span>Updated {formatDate(history.lastUpdated, { relative: true })}</span>
              </div>
            </div>
          </div>

          {/* Actions */}
          <div className="flex items-center space-x-2 ml-4">
            <Button
              size="xs"
              variant="outline"
              onClick={(e) => {
                e.stopPropagation();
                onViewDetails?.(history.id);
              }}
              leftIcon={<EyeIcon className="h-3 w-3" />}
            >
              Details
            </Button>
            
            <Button
              size="xs"
              variant={history.isTracking ? "outline" : "default"}
              onClick={(e) => {
                e.stopPropagation();
                onToggleTracking?.(history.id);
              }}
              className={cn(
                !history.isTracking && 'bg-blue-600 hover:bg-blue-700 text-white'
              )}
            >
              {history.isTracking ? 'Stop' : 'Track'}
            </Button>
          </div>
        </div>
      </div>

      {/* Expanded Details */}
      <AnimatePresence>
        {isSelected && (
          <motion.div
            initial={{ opacity: 0, height: 0 }}
            animate={{ opacity: 1, height: 'auto' }}
            exit={{ opacity: 0, height: 0 }}
            className="border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50"
          >
            <div className="p-6">
              <TicketHistoryDetails 
                history={history} 
                timeRange={timeRange}
                onExportData={onExportData}
              />
            </div>
          </motion.div>
        )}
      </AnimatePresence>
    </motion.div>
  );
};

// Ticket History Details Component
interface TicketHistoryDetailsProps {
  history: TicketHistory;
  timeRange: string;
  onExportData?: (ticketId: string) => void;
}

const TicketHistoryDetails: React.FC<TicketHistoryDetailsProps> = ({
  history,
  timeRange,
  onExportData,
}) => {
  // Filter price history based on time range
  const filteredPriceHistory = useMemo(() => {
    if (timeRange === 'all') return history.priceHistory;
    
    const now = new Date();
    let cutoffDate = new Date();
    
    switch (timeRange) {
      case '7d':
        cutoffDate.setDate(now.getDate() - 7);
        break;
      case '30d':
        cutoffDate.setDate(now.getDate() - 30);
        break;
      case '90d':
        cutoffDate.setDate(now.getDate() - 90);
        break;
      default:
        return history.priceHistory;
    }
    
    return history.priceHistory.filter(point => 
      new Date(point.timestamp) >= cutoffDate
    );
  }, [history.priceHistory, timeRange]);

  const stats = useMemo(() => {
    if (filteredPriceHistory.length === 0) return null;
    
    const prices = filteredPriceHistory.map(p => p.price);
    const min = Math.min(...prices);
    const max = Math.max(...prices);
    const avg = prices.reduce((sum, price) => sum + price, 0) / prices.length;
    const latest = prices[prices.length - 1];
    const first = prices[0];
    const change = latest - first;
    const changePct = first > 0 ? (change / first) * 100 : 0;
    
    return { min, max, avg, latest, first, change, changePct };
  }, [filteredPriceHistory]);

  if (!stats) return null;

  return (
    <div className="space-y-6">
      {/* Detailed Stats */}
      <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div className="text-center">
          <div className="text-lg font-bold text-gray-900 dark:text-white">
            {formatCurrency(stats.min)}
          </div>
          <div className="text-xs text-gray-500 dark:text-gray-400">Lowest</div>
        </div>
        
        <div className="text-center">
          <div className="text-lg font-bold text-gray-900 dark:text-white">
            {formatCurrency(stats.max)}
          </div>
          <div className="text-xs text-gray-500 dark:text-gray-400">Highest</div>
        </div>
        
        <div className="text-center">
          <div className="text-lg font-bold text-gray-900 dark:text-white">
            {formatCurrency(stats.avg)}
          </div>
          <div className="text-xs text-gray-500 dark:text-gray-400">Average</div>
        </div>
        
        <div className="text-center">
          <div className={cn(
            'text-lg font-bold',
            stats.changePct > 0 ? 'text-red-600 dark:text-red-400' : 
            stats.changePct < 0 ? 'text-green-600 dark:text-green-400' : 
            'text-gray-600 dark:text-gray-400'
          )}>
            {stats.changePct > 0 ? '+' : ''}{stats.changePct.toFixed(1)}%
          </div>
          <div className="text-xs text-gray-500 dark:text-gray-400">Change ({timeRange})</div>
        </div>
      </div>

      {/* Price History Table */}
      <div>
        <div className="flex items-center justify-between mb-4">
          <h4 className="text-sm font-medium text-gray-900 dark:text-white">
            Recent Price History ({timeRange})
          </h4>
          <Button
            size="xs"
            variant="outline"
            onClick={() => onExportData?.(history.id)}
          >
            Export Data
          </Button>
        </div>
        
        <div className="max-h-64 overflow-y-auto">
          <table className="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead className="bg-gray-50 dark:bg-gray-800">
              <tr>
                <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                  Date
                </th>
                <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                  Price
                </th>
                <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                  Platform
                </th>
                <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                  Availability
                </th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-200 dark:divide-gray-700">
              {filteredPriceHistory.slice().reverse().slice(0, 10).map((point, index) => (
                <tr key={index} className="hover:bg-gray-50 dark:hover:bg-gray-700">
                  <td className="px-4 py-2 text-sm text-gray-900 dark:text-white">
                    {formatDate(point.timestamp)}
                  </td>
                  <td className="px-4 py-2 text-sm font-medium text-gray-900 dark:text-white">
                    {formatCurrency(point.price)}
                  </td>
                  <td className="px-4 py-2 text-sm text-gray-600 dark:text-gray-300">
                    {point.platform}
                  </td>
                  <td className="px-4 py-2 text-sm text-gray-600 dark:text-gray-300">
                    {point.availability} tickets
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>

      {/* Insights */}
      <div className="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
        <div className="flex items-start space-x-3">
          <InformationCircleIcon className="h-5 w-5 text-blue-600 dark:text-blue-400 mt-0.5 flex-shrink-0" />
          <div>
            <h4 className="text-sm font-medium text-blue-800 dark:text-blue-200 mb-2">
              Price Insights
            </h4>
            <ul className="text-sm text-blue-700 dark:text-blue-300 space-y-1">
              {stats.changePct < -10 && (
                <li>• Significant price drop detected - consider purchasing soon</li>
              )}
              {stats.changePct > 15 && (
                <li>• Prices rising rapidly - demand may be increasing</li>
              )}
              {Math.abs(stats.changePct) < 5 && (
                <li>• Prices relatively stable over this period</li>
              )}
              <li>• Best time to buy was when prices hit {formatCurrency(stats.min)}</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  );
};

export default TicketHistoryTracking;