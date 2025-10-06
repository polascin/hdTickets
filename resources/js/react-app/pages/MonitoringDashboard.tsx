/**
 * HD Tickets - Smart Monitoring Dashboard
 * Inspired by TicketScoutie.com - Advanced ticket monitoring and alerting system
 */

import React, { useState, useEffect } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import {
  BellIcon,
  EyeIcon,
  ChartBarIcon,
  CalendarDaysIcon,
  ClockIcon,
  TrendingUpIcon,
  TrendingDownIcon,
  FireIcon,
  StarIcon,
  MapPinIcon,
  UsersIcon,
  ExclamationTriangleIcon,
  CheckCircleIcon,
} from '@heroicons/react/24/outline';
import {
  BellIcon as BellSolidIcon,
  EyeIcon as EyeSolidIcon,
  StarIcon as StarSolidIcon,
} from '@heroicons/react/24/solid';

import { useAppSelector, useAppDispatch } from '../store/store';
import { cn, formatCurrency, formatDate, formatTime } from '../utils/design';
import Button from '../components/ui/Button';
import LoadingSpinner from '../components/ui/LoadingSpinner';

// Mock data - replace with real API calls
const mockMonitoredEvents = [
  {
    id: 1,
    title: 'Manchester United vs Liverpool',
    venue: 'Old Trafford',
    date: '2025-11-15T15:00:00Z',
    sport: 'Football',
    league: 'Premier League',
    isFollowing: true,
    hasAlerts: true,
    alertCount: 3,
    status: 'monitoring',
    ticketStats: {
      lowestPrice: 89,
      averagePrice: 165,
      highestPrice: 450,
      availableCount: 847,
      platforms: 5,
      priceChange: -12.5,
      demandLevel: 'high',
    },
    recentActivity: [
      { type: 'price_drop', message: 'Price dropped to £89 on StubHub', time: '2 min ago' },
      { type: 'new_listing', message: '15 new tickets added on Viagogo', time: '5 min ago' },
      { type: 'selling_fast', message: '50+ tickets sold in last hour', time: '12 min ago' },
    ],
  },
  {
    id: 2,
    title: 'Arsenal vs Chelsea',
    venue: 'Emirates Stadium',
    date: '2025-11-22T17:30:00Z',
    sport: 'Football',
    league: 'Premier League',
    isFollowing: false,
    hasAlerts: true,
    alertCount: 1,
    status: 'limited',
    ticketStats: {
      lowestPrice: 125,
      averagePrice: 220,
      highestPrice: 680,
      availableCount: 234,
      platforms: 4,
      priceChange: 8.3,
      demandLevel: 'very_high',
    },
    recentActivity: [
      { type: 'alert_triggered', message: 'Price alert triggered - £125 target reached', time: '15 min ago' },
      { type: 'low_stock', message: 'Only 234 tickets remaining', time: '1 hour ago' },
    ],
  },
  {
    id: 3,
    title: 'Tottenham vs Manchester City',
    venue: 'Tottenham Hotspur Stadium',
    date: '2025-12-01T16:00:00Z',
    sport: 'Football',
    league: 'Premier League',
    isFollowing: true,
    hasAlerts: false,
    alertCount: 0,
    status: 'abundant',
    ticketStats: {
      lowestPrice: 67,
      averagePrice: 145,
      highestPrice: 380,
      availableCount: 1205,
      platforms: 6,
      priceChange: -5.2,
      demandLevel: 'medium',
    },
    recentActivity: [
      { type: 'price_stable', message: 'Prices stable for 2 hours', time: '2 hours ago' },
    ],
  },
];

const MonitoringDashboard: React.FC = () => {
  const [selectedTab, setSelectedTab] = useState<'all' | 'following' | 'alerts'>('all');
  const [sortBy, setSortBy] = useState<'date' | 'price' | 'demand'>('date');
  const [viewMode, setViewMode] = useState<'grid' | 'list'>('grid');
  const [monitoredEvents, setMonitoredEvents] = useState(mockMonitoredEvents);
  const [isLoading, setIsLoading] = useState(false);

  // Filter events based on selected tab
  const filteredEvents = monitoredEvents.filter(event => {
    switch (selectedTab) {
      case 'following':
        return event.isFollowing;
      case 'alerts':
        return event.hasAlerts;
      default:
        return true;
    }
  });

  // Sort events
  const sortedEvents = [...filteredEvents].sort((a, b) => {
    switch (sortBy) {
      case 'price':
        return a.ticketStats.lowestPrice - b.ticketStats.lowestPrice;
      case 'demand':
        const demandOrder = { 'very_high': 4, 'high': 3, 'medium': 2, 'low': 1 };
        return demandOrder[b.ticketStats.demandLevel as keyof typeof demandOrder] - demandOrder[a.ticketStats.demandLevel as keyof typeof demandOrder];
      default:
        return new Date(a.date).getTime() - new Date(b.date).getTime();
    }
  });

  const toggleFollow = (eventId: number) => {
    setMonitoredEvents(events =>
      events.map(event =>
        event.id === eventId
          ? { ...event, isFollowing: !event.isFollowing }
          : event
      )
    );
  };

  return (
    <div className="min-h-screen bg-gray-50 dark:bg-gray-900">
      {/* Header */}
      <div className="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
          <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div className="mb-4 sm:mb-0">
              <h1 className="text-3xl font-bold text-gray-900 dark:text-white">
                Ticket Monitor
              </h1>
              <p className="mt-1 text-gray-600 dark:text-gray-300">
                Smart monitoring for football tickets across multiple platforms
              </p>
            </div>
            
            <div className="flex items-center space-x-3">
              <Button
                variant="outline"
                size="sm"
                leftIcon={<BellIcon className="h-4 w-4" />}
              >
                Manage Alerts
              </Button>
              <Button
                variant="primary"
                size="sm"
                leftIcon={<EyeIcon className="h-4 w-4" />}
              >
                Add Event
              </Button>
            </div>
          </div>
        </div>
      </div>

      {/* Stats Overview */}
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div className="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
          <StatsCard
            title="Monitored Events"
            value={monitoredEvents.length.toString()}
            icon={EyeIcon}
            color="blue"
            change="+2 this week"
          />
          <StatsCard
            title="Active Alerts"
            value={monitoredEvents.filter(e => e.hasAlerts).length.toString()}
            icon={BellIcon}
            color="yellow"
            change="3 triggered today"
          />
          <StatsCard
            title="Following"
            value={monitoredEvents.filter(e => e.isFollowing).length.toString()}
            icon={StarIcon}
            color="green"
            change="Recently updated"
          />
          <StatsCard
            title="Avg. Savings"
            value="£42"
            icon={TrendingDownIcon}
            color="purple"
            change="vs face value"
          />
        </div>

        {/* Controls */}
        <div className="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 mb-6">
          <div className="p-6">
            <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0">
              {/* Tabs */}
              <div className="flex space-x-1 bg-gray-100 dark:bg-gray-700 rounded-lg p-1">
                {[
                  { key: 'all', label: 'All Events', count: monitoredEvents.length },
                  { key: 'following', label: 'Following', count: monitoredEvents.filter(e => e.isFollowing).length },
                  { key: 'alerts', label: 'Alerts', count: monitoredEvents.filter(e => e.hasAlerts).length },
                ].map((tab) => (
                  <button
                    key={tab.key}
                    onClick={() => setSelectedTab(tab.key as any)}
                    className={cn(
                      'px-4 py-2 text-sm font-medium rounded-md transition-colors',
                      selectedTab === tab.key
                        ? 'bg-white dark:bg-gray-800 text-blue-600 dark:text-blue-400 shadow-sm'
                        : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white'
                    )}
                  >
                    {tab.label} {tab.count > 0 && (
                      <span className="ml-2 px-2 py-0.5 text-xs bg-gray-200 dark:bg-gray-600 rounded-full">
                        {tab.count}
                      </span>
                    )}
                  </button>
                ))}
              </div>

              {/* Controls */}
              <div className="flex items-center space-x-3">
                <select
                  value={sortBy}
                  onChange={(e) => setSortBy(e.target.value as any)}
                  className="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                >
                  <option value="date">Sort by Date</option>
                  <option value="price">Sort by Price</option>
                  <option value="demand">Sort by Demand</option>
                </select>

                <div className="flex space-x-1 bg-gray-100 dark:bg-gray-700 rounded-lg p-1">
                  <button
                    onClick={() => setViewMode('grid')}
                    className={cn(
                      'p-2 rounded text-sm',
                      viewMode === 'grid'
                        ? 'bg-white dark:bg-gray-800 text-blue-600 shadow-sm'
                        : 'text-gray-600 hover:text-gray-900'
                    )}
                  >
                    Grid
                  </button>
                  <button
                    onClick={() => setViewMode('list')}
                    className={cn(
                      'p-2 rounded text-sm',
                      viewMode === 'list'
                        ? 'bg-white dark:bg-gray-800 text-blue-600 shadow-sm'
                        : 'text-gray-600 hover:text-gray-900'
                    )}
                  >
                    List
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* Events List */}
        {isLoading ? (
          <div className="flex justify-center py-12">
            <LoadingSpinner size="lg" text="Loading monitored events..." />
          </div>
        ) : sortedEvents.length === 0 ? (
          <div className="text-center py-12">
            <EyeIcon className="mx-auto h-12 w-12 text-gray-400" />
            <h3 className="mt-2 text-sm font-medium text-gray-900 dark:text-white">
              No events found
            </h3>
            <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">
              Start monitoring events to see them here.
            </p>
            <div className="mt-6">
              <Button variant="primary">Add Your First Event</Button>
            </div>
          </div>
        ) : (
          <div className={cn(
            viewMode === 'grid' 
              ? 'grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6'
              : 'space-y-4'
          )}>
            <AnimatePresence>
              {sortedEvents.map((event) => (
                <motion.div
                  key={event.id}
                  initial={{ opacity: 0, y: 20 }}
                  animate={{ opacity: 1, y: 0 }}
                  exit={{ opacity: 0, y: -20 }}
                  transition={{ duration: 0.3 }}
                >
                  {viewMode === 'grid' ? (
                    <EventMonitorCard event={event} onToggleFollow={toggleFollow} />
                  ) : (
                    <EventMonitorListItem event={event} onToggleFollow={toggleFollow} />
                  )}
                </motion.div>
              ))}
            </AnimatePresence>
          </div>
        )}
      </div>
    </div>
  );
};

// Stats Card Component
interface StatsCardProps {
  title: string;
  value: string;
  icon: React.ComponentType<any>;
  color: 'blue' | 'yellow' | 'green' | 'purple';
  change: string;
}

const StatsCard: React.FC<StatsCardProps> = ({ title, value, icon: Icon, color, change }) => {
  const colorClasses = {
    blue: 'text-blue-600 bg-blue-100 dark:bg-blue-900/20',
    yellow: 'text-yellow-600 bg-yellow-100 dark:bg-yellow-900/20',
    green: 'text-green-600 bg-green-100 dark:bg-green-900/20',
    purple: 'text-purple-600 bg-purple-100 dark:bg-purple-900/20',
  };

  return (
    <div className="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
      <div className="flex items-center">
        <div className={cn('p-3 rounded-lg', colorClasses[color])}>
          <Icon className="h-6 w-6" />
        </div>
        <div className="ml-4">
          <p className="text-sm font-medium text-gray-600 dark:text-gray-400">{title}</p>
          <p className="text-2xl font-bold text-gray-900 dark:text-white">{value}</p>
        </div>
      </div>
      <div className="mt-4">
        <p className="text-sm text-gray-500 dark:text-gray-400">{change}</p>
      </div>
    </div>
  );
};

// Event Monitor Card Component
interface EventMonitorCardProps {
  event: typeof mockMonitoredEvents[0];
  onToggleFollow: (eventId: number) => void;
}

const EventMonitorCard: React.FC<EventMonitorCardProps> = ({ event, onToggleFollow }) => {
  const getDemandColor = (level: string) => {
    switch (level) {
      case 'very_high': return 'text-red-600 bg-red-100 dark:bg-red-900/20';
      case 'high': return 'text-orange-600 bg-orange-100 dark:bg-orange-900/20';
      case 'medium': return 'text-yellow-600 bg-yellow-100 dark:bg-yellow-900/20';
      default: return 'text-green-600 bg-green-100 dark:bg-green-900/20';
    }
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'monitoring': return 'text-blue-600 bg-blue-100 dark:bg-blue-900/20';
      case 'limited': return 'text-orange-600 bg-orange-100 dark:bg-orange-900/20';
      case 'abundant': return 'text-green-600 bg-green-100 dark:bg-green-900/20';
      default: return 'text-gray-600 bg-gray-100 dark:bg-gray-900/20';
    }
  };

  return (
    <div className="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow">
      {/* Header */}
      <div className="flex items-start justify-between mb-4">
        <div className="flex-1">
          <div className="flex items-center space-x-2 mb-1">
            <h3 className="text-lg font-semibold text-gray-900 dark:text-white">
              {event.title}
            </h3>
            {event.hasAlerts && (
              <div className="flex items-center">
                <BellSolidIcon className="h-4 w-4 text-yellow-500" />
                <span className="ml-1 text-xs font-medium text-yellow-600 dark:text-yellow-400">
                  {event.alertCount}
                </span>
              </div>
            )}
          </div>
          <div className="flex items-center space-x-4 text-sm text-gray-600 dark:text-gray-400">
            <div className="flex items-center">
              <MapPinIcon className="h-4 w-4 mr-1" />
              {event.venue}
            </div>
            <div className="flex items-center">
              <CalendarDaysIcon className="h-4 w-4 mr-1" />
              {formatDate(event.date, 'short')}
            </div>
          </div>
        </div>
        
        <div className="flex items-center space-x-2">
          <span className={cn('px-2 py-1 text-xs font-medium rounded-full', getStatusColor(event.status))}>
            {event.status}
          </span>
          <button
            onClick={() => onToggleFollow(event.id)}
            className="p-1 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
          >
            {event.isFollowing ? (
              <StarSolidIcon className="h-5 w-5 text-yellow-500" />
            ) : (
              <StarIcon className="h-5 w-5 text-gray-400" />
            )}
          </button>
        </div>
      </div>

      {/* Stats */}
      <div className="grid grid-cols-2 gap-4 mb-4">
        <div className="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3">
          <div className="flex items-center justify-between">
            <span className="text-sm text-gray-600 dark:text-gray-400">Lowest Price</span>
            {event.ticketStats.priceChange !== 0 && (
              <div className={cn(
                'flex items-center text-xs',
                event.ticketStats.priceChange > 0 ? 'text-red-600' : 'text-green-600'
              )}>
                {event.ticketStats.priceChange > 0 ? (
                  <TrendingUpIcon className="h-3 w-3 mr-1" />
                ) : (
                  <TrendingDownIcon className="h-3 w-3 mr-1" />
                )}
                {Math.abs(event.ticketStats.priceChange)}%
              </div>
            )}
          </div>
          <div className="text-xl font-bold text-gray-900 dark:text-white">
            {formatCurrency(event.ticketStats.lowestPrice, 'GBP')}
          </div>
        </div>
        
        <div className="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3">
          <span className="text-sm text-gray-600 dark:text-gray-400">Available</span>
          <div className="text-xl font-bold text-gray-900 dark:text-white">
            {event.ticketStats.availableCount.toLocaleString()}
          </div>
          <div className="text-xs text-gray-500 dark:text-gray-400">
            on {event.ticketStats.platforms} platforms
          </div>
        </div>
      </div>

      {/* Demand Level */}
      <div className="flex items-center justify-between mb-4">
        <span className="text-sm text-gray-600 dark:text-gray-400">Demand Level</span>
        <span className={cn('px-2 py-1 text-xs font-medium rounded-full capitalize', getDemandColor(event.ticketStats.demandLevel))}>
          {event.ticketStats.demandLevel.replace('_', ' ')}
        </span>
      </div>

      {/* Recent Activity */}
      {event.recentActivity.length > 0 && (
        <div className="border-t border-gray-200 dark:border-gray-700 pt-4">
          <h4 className="text-sm font-medium text-gray-900 dark:text-white mb-2">Recent Activity</h4>
          <div className="space-y-2">
            {event.recentActivity.slice(0, 2).map((activity, index) => (
              <div key={index} className="flex items-start space-x-2">
                <div className={cn(
                  'w-2 h-2 rounded-full mt-1.5 flex-shrink-0',
                  activity.type === 'price_drop' ? 'bg-green-500' :
                  activity.type === 'alert_triggered' ? 'bg-yellow-500' :
                  activity.type === 'selling_fast' ? 'bg-red-500' :
                  activity.type === 'low_stock' ? 'bg-orange-500' :
                  'bg-blue-500'
                )} />
                <div>
                  <p className="text-xs text-gray-900 dark:text-white">{activity.message}</p>
                  <p className="text-xs text-gray-500 dark:text-gray-400">{activity.time}</p>
                </div>
              </div>
            ))}
          </div>
        </div>
      )}

      {/* Actions */}
      <div className="flex space-x-2 mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
        <Button variant="primary" size="sm" className="flex-1">
          View Details
        </Button>
        <Button variant="outline" size="sm" leftIcon={<BellIcon className="h-4 w-4" />}>
          Alert
        </Button>
      </div>
    </div>
  );
};

// Event Monitor List Item Component
const EventMonitorListItem: React.FC<EventMonitorCardProps> = ({ event, onToggleFollow }) => {
  // Similar implementation but in list format
  return (
    <div className="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
      <div className="flex items-center justify-between">
        <div className="flex items-center space-x-4">
          <div>
            <h3 className="text-lg font-semibold text-gray-900 dark:text-white">
              {event.title}
            </h3>
            <p className="text-sm text-gray-600 dark:text-gray-400">
              {event.venue} • {formatDate(event.date, 'short')} • {formatTime(event.date)}
            </p>
          </div>
        </div>
        
        <div className="flex items-center space-x-6">
          <div className="text-right">
            <div className="text-lg font-bold text-gray-900 dark:text-white">
              {formatCurrency(event.ticketStats.lowestPrice, 'GBP')}
            </div>
            <div className="text-sm text-gray-500 dark:text-gray-400">
              {event.ticketStats.availableCount} available
            </div>
          </div>
          
          <div className="flex items-center space-x-2">
            {event.hasAlerts && (
              <BellSolidIcon className="h-5 w-5 text-yellow-500" />
            )}
            <button
              onClick={() => onToggleFollow(event.id)}
              className="p-1 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
            >
              {event.isFollowing ? (
                <StarSolidIcon className="h-5 w-5 text-yellow-500" />
              ) : (
                <StarIcon className="h-5 w-5 text-gray-400" />
              )}
            </button>
          </div>
        </div>
      </div>
    </div>
  );
};

export default MonitoringDashboard;