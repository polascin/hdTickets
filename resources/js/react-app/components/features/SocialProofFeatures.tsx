/**
 * HD Tickets - Social Proof Features
 * Demand indicators, trending events, and social signals to help users make informed decisions
 */

import React, { useState, useMemo } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import {
  FireIcon,
  TrendingUpIcon,
  EyeIcon,
  UserGroupIcon,
  ClockIcon,
  HeartIcon,
  ChatBubbleLeftEllipsisIcon,
  StarIcon,
  BoltIcon,
  SignalIcon,
  ChartBarIcon,
  ExclamationTriangleIcon,
  CheckCircleIcon,
  ArrowTrendingUpIcon,
  ArrowTrendingDownIcon,
  CalendarDaysIcon,
  MapPinIcon,
  CurrencyPoundIcon,
  AdjustmentsHorizontalIcon,
  FunnelIcon,
} from '@heroicons/react/24/outline';
import {
  FireIcon as FireSolidIcon,
  HeartIcon as HeartSolidIcon,
  StarIcon as StarSolidIcon,
  EyeIcon as EyeSolidIcon,
} from '@heroicons/react/24/solid';

import { cn, formatCurrency, formatDate } from '../../utils/design';
import Button from '../ui/Button';

interface SocialSignal {
  type: 'views' | 'follows' | 'alerts' | 'purchases' | 'searches';
  count: number;
  change24h: number;
  changePct24h: number;
}

interface DemandIndicator {
  level: 'low' | 'medium' | 'high' | 'very_high' | 'extreme';
  score: number;
  factors: string[];
  prediction: 'stable' | 'increasing' | 'decreasing';
}

interface TrendingEvent {
  id: string;
  name: string;
  venue: string;
  date: string;
  sport: string;
  image?: string;
  trendingReason: string;
  demandIndicator: DemandIndicator;
  socialSignals: Record<string, SocialSignal>;
  priceRange: {
    min: number;
    max: number;
    average: number;
  };
  availability: {
    total: number;
    remaining: number;
    percentage: number;
  };
  recentActivity: {
    type: 'price_drop' | 'new_tickets' | 'selling_fast' | 'high_demand';
    message: string;
    timestamp: string;
    impact: 'positive' | 'neutral' | 'negative';
  }[];
  userEngagement: {
    likes: number;
    comments: number;
    shares: number;
    bookmarks: number;
  };
  isHot: boolean;
  isTrending: boolean;
  isRising: boolean;
}

interface SocialProofFeaturesProps {
  trendingEvents?: TrendingEvent[];
  onViewEvent?: (eventId: string) => void;
  onFollowEvent?: (eventId: string) => void;
  onCreateAlert?: (eventId: string) => void;
  onLikeEvent?: (eventId: string) => void;
  onShareEvent?: (eventId: string) => void;
}

// Mock data
const mockTrendingEvents: TrendingEvent[] = [
  {
    id: 'trending-1',
    name: 'Manchester United vs Liverpool',
    venue: 'Old Trafford',
    date: '2024-03-15T15:00:00Z',
    sport: 'Football',
    trendingReason: 'Massive price drop - 25% off all tickets!',
    demandIndicator: {
      level: 'very_high',
      score: 87,
      factors: ['High search volume', 'Rapid price changes', 'Limited availability'],
      prediction: 'increasing',
    },
    socialSignals: {
      views: { type: 'views', count: 15420, change24h: 2340, changePct24h: 17.9 },
      follows: { type: 'follows', count: 892, change24h: 127, changePct24h: 16.6 },
      alerts: { type: 'alerts', count: 1456, change24h: 234, changePct24h: 19.1 },
      purchases: { type: 'purchases', count: 89, change24h: 23, changePct24h: 34.8 },
      searches: { type: 'searches', count: 3420, change24h: 890, changePct24h: 35.2 },
    },
    priceRange: { min: 85, max: 450, average: 167 },
    availability: { total: 2500, remaining: 342, percentage: 13.7 },
    recentActivity: [
      {
        type: 'price_drop',
        message: 'Prices dropped by 25% in the last hour',
        timestamp: '2024-01-15T14:30:00Z',
        impact: 'positive',
      },
      {
        type: 'selling_fast',
        message: '89 tickets sold in the last hour',
        timestamp: '2024-01-15T13:45:00Z',
        impact: 'neutral',
      },
    ],
    userEngagement: { likes: 1234, comments: 89, shares: 156, bookmarks: 445 },
    isHot: true,
    isTrending: true,
    isRising: true,
  },
  {
    id: 'trending-2',
    name: 'Anthony Joshua vs Francis Ngannou',
    venue: 'Wembley Stadium',
    date: '2024-04-12T19:00:00Z',
    sport: 'Boxing',
    trendingReason: 'Highest demand event this week',
    demandIndicator: {
      level: 'extreme',
      score: 94,
      factors: ['Celebrity endorsements', 'Media coverage surge', 'Limited VIP availability'],
      prediction: 'increasing',
    },
    socialSignals: {
      views: { type: 'views', count: 28450, change24h: 4120, changePct24h: 16.9 },
      follows: { type: 'follows', count: 1689, change24h: 267, changePct24h: 18.8 },
      alerts: { type: 'alerts', count: 2890, change24h: 445, changePct24h: 18.2 },
      purchases: { type: 'purchases', count: 156, change24h: 34, changePct24h: 27.9 },
      searches: { type: 'searches', count: 8920, change24h: 1560, changePct24h: 21.2 },
    },
    priceRange: { min: 195, max: 1200, average: 425 },
    availability: { total: 1800, remaining: 89, percentage: 4.9 },
    recentActivity: [
      {
        type: 'high_demand',
        message: 'Demand increased by 400% after celebrity endorsement',
        timestamp: '2024-01-15T12:00:00Z',
        impact: 'positive',
      },
      {
        type: 'selling_fast',
        message: 'Only 89 tickets remaining',
        timestamp: '2024-01-15T11:30:00Z',
        impact: 'negative',
      },
    ],
    userEngagement: { likes: 2890, comments: 234, shares: 567, bookmarks: 1123 },
    isHot: true,
    isTrending: true,
    isRising: true,
  },
  {
    id: 'trending-3',
    name: 'Arsenal vs Chelsea',
    venue: 'Emirates Stadium',
    date: '2024-03-20T17:30:00Z',
    sport: 'Football',
    trendingReason: 'London Derby - High local interest',
    demandIndicator: {
      level: 'high',
      score: 76,
      factors: ['Local rivalry', 'Weekend match', 'Good weather forecast'],
      prediction: 'stable',
    },
    socialSignals: {
      views: { type: 'views', count: 9820, change24h: 890, changePct24h: 10.0 },
      follows: { type: 'follows', count: 456, change24h: 67, changePct24h: 17.2 },
      alerts: { type: 'alerts', count: 789, change24h: 89, changePct24h: 12.8 },
      purchases: { type: 'purchases', count: 45, change24h: 12, changePct24h: 36.4 },
      searches: { type: 'searches', count: 2340, change24h: 234, changePct24h: 11.1 },
    },
    priceRange: { min: 45, max: 250, average: 89 },
    availability: { total: 3200, remaining: 1890, percentage: 59.1 },
    recentActivity: [
      {
        type: 'new_tickets',
        message: 'New premium seats released',
        timestamp: '2024-01-15T10:00:00Z',
        impact: 'positive',
      },
    ],
    userEngagement: { likes: 567, comments: 45, shares: 89, bookmarks: 234 },
    isHot: false,
    isTrending: true,
    isRising: false,
  },
];

const SocialProofFeatures: React.FC<SocialProofFeaturesProps> = ({
  trendingEvents = mockTrendingEvents,
  onViewEvent,
  onFollowEvent,
  onCreateAlert,
  onLikeEvent,
  onShareEvent,
}) => {
  const [sortBy, setSortBy] = useState<'trending' | 'demand' | 'activity' | 'price'>('trending');
  const [filterLevel, setFilterLevel] = useState<string>('all');
  const [showFilters, setShowFilters] = useState(false);

  // Sort events
  const sortedEvents = useMemo(() => {
    return [...trendingEvents].sort((a, b) => {
      switch (sortBy) {
        case 'trending':
          return (Number(b.isTrending) * 2 + Number(b.isHot)) - (Number(a.isTrending) * 2 + Number(a.isHot));
        case 'demand':
          return b.demandIndicator.score - a.demandIndicator.score;
        case 'activity':
          return b.socialSignals.views.count - a.socialSignals.views.count;
        case 'price':
          return a.priceRange.min - b.priceRange.min;
        default:
          return 0;
      }
    });
  }, [trendingEvents, sortBy]);

  // Filter events
  const filteredEvents = useMemo(() => {
    if (filterLevel === 'all') return sortedEvents;
    return sortedEvents.filter(event => event.demandIndicator.level === filterLevel);
  }, [sortedEvents, filterLevel]);

  const getDemandColor = (level: string) => {
    switch (level) {
      case 'extreme':
        return 'text-purple-600 bg-purple-50 dark:bg-purple-900/20';
      case 'very_high':
        return 'text-red-600 bg-red-50 dark:bg-red-900/20';
      case 'high':
        return 'text-orange-600 bg-orange-50 dark:bg-orange-900/20';
      case 'medium':
        return 'text-yellow-600 bg-yellow-50 dark:bg-yellow-900/20';
      case 'low':
        return 'text-gray-600 bg-gray-50 dark:bg-gray-900/20';
      default:
        return 'text-gray-600 bg-gray-50 dark:bg-gray-900/20';
    }
  };

  const getDemandIcon = (level: string) => {
    switch (level) {
      case 'extreme':
        return <BoltIcon className="h-4 w-4 text-purple-600" />;
      case 'very_high':
        return <FireSolidIcon className="h-4 w-4 text-red-600" />;
      case 'high':
        return <TrendingUpIcon className="h-4 w-4 text-orange-600" />;
      case 'medium':
        return <SignalIcon className="h-4 w-4 text-yellow-600" />;
      case 'low':
        return <ChartBarIcon className="h-4 w-4 text-gray-600" />;
      default:
        return <ChartBarIcon className="h-4 w-4 text-gray-600" />;
    }
  };

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div>
          <h2 className="text-2xl font-bold text-gray-900 dark:text-white">
            Trending Events
          </h2>
          <p className="mt-1 text-gray-600 dark:text-gray-300">
            Discover what's hot right now based on real user activity and demand
          </p>
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
            <option value="trending">Sort by Trending</option>
            <option value="demand">Sort by Demand</option>
            <option value="activity">Sort by Activity</option>
            <option value="price">Sort by Price</option>
          </select>
        </div>

        {/* Quick Stats */}
        <div className="flex items-center space-x-4 text-sm text-gray-600 dark:text-gray-300">
          <div className="flex items-center space-x-1">
            <FireSolidIcon className="h-4 w-4 text-red-500" />
            <span>{trendingEvents.filter(e => e.isHot).length} Hot</span>
          </div>
          <div className="flex items-center space-x-1">
            <TrendingUpIcon className="h-4 w-4 text-blue-500" />
            <span>{trendingEvents.filter(e => e.isTrending).length} Trending</span>
          </div>
          <div className="flex items-center space-x-1">
            <ArrowTrendingUpIcon className="h-4 w-4 text-green-500" />
            <span>{trendingEvents.filter(e => e.isRising).length} Rising</span>
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
            <div className="flex items-center space-x-4">
              <span className="text-sm font-medium text-gray-700 dark:text-gray-300">
                Demand Level:
              </span>
              <div className="flex items-center space-x-2">
                {['all', 'extreme', 'very_high', 'high', 'medium', 'low'].map((level) => (
                  <button
                    key={level}
                    onClick={() => setFilterLevel(level)}
                    className={cn(
                      'px-3 py-1 text-sm rounded-full transition-colors capitalize',
                      filterLevel === level
                        ? 'bg-blue-100 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300'
                        : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'
                    )}
                  >
                    {level === 'all' ? 'All' : level.replace('_', ' ')}
                  </button>
                ))}
              </div>
            </div>
          </motion.div>
        )}
      </AnimatePresence>

      {/* Trending Events */}
      <div className="space-y-4">
        {filteredEvents.length === 0 ? (
          <div className="text-center py-12">
            <TrendingUpIcon className="mx-auto h-12 w-12 text-gray-400" />
            <h3 className="mt-4 text-lg font-medium text-gray-900 dark:text-white">
              No trending events found
            </h3>
            <p className="mt-1 text-gray-600 dark:text-gray-300">
              Try adjusting your filters to see more events.
            </p>
          </div>
        ) : (
          filteredEvents.map((event, index) => (
            <TrendingEventCard
              key={event.id}
              event={event}
              index={index}
              onViewEvent={onViewEvent}
              onFollowEvent={onFollowEvent}
              onCreateAlert={onCreateAlert}
              onLikeEvent={onLikeEvent}
              onShareEvent={onShareEvent}
              getDemandColor={getDemandColor}
              getDemandIcon={getDemandIcon}
            />
          ))
        )}
      </div>
    </div>
  );
};

// Trending Event Card Component
interface TrendingEventCardProps {
  event: TrendingEvent;
  index: number;
  onViewEvent?: (eventId: string) => void;
  onFollowEvent?: (eventId: string) => void;
  onCreateAlert?: (eventId: string) => void;
  onLikeEvent?: (eventId: string) => void;
  onShareEvent?: (eventId: string) => void;
  getDemandColor: (level: string) => string;
  getDemandIcon: (level: string) => JSX.Element;
}

const TrendingEventCard: React.FC<TrendingEventCardProps> = ({
  event,
  index,
  onViewEvent,
  onFollowEvent,
  onCreateAlert,
  onLikeEvent,
  onShareEvent,
  getDemandColor,
  getDemandIcon,
}) => {
  const [liked, setLiked] = useState(false);

  const handleLike = () => {
    setLiked(!liked);
    onLikeEvent?.(event.id);
  };

  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ delay: index * 0.1 }}
      className="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-lg transition-shadow"
    >
      {/* Header with badges */}
      <div className="relative p-6 pb-4">
        <div className="absolute top-4 right-4 flex items-center space-x-2">
          {event.isHot && (
            <span className="px-2 py-1 text-xs bg-red-100 dark:bg-red-900/20 text-red-700 dark:text-red-400 rounded-full font-medium flex items-center space-x-1">
              <FireSolidIcon className="h-3 w-3" />
              <span>Hot</span>
            </span>
          )}
          {event.isTrending && (
            <span className="px-2 py-1 text-xs bg-blue-100 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 rounded-full font-medium flex items-center space-x-1">
              <TrendingUpIcon className="h-3 w-3" />
              <span>Trending</span>
            </span>
          )}
          {event.isRising && (
            <span className="px-2 py-1 text-xs bg-green-100 dark:bg-green-900/20 text-green-700 dark:text-green-400 rounded-full font-medium flex items-center space-x-1">
              <ArrowTrendingUpIcon className="h-3 w-3" />
              <span>Rising</span>
            </span>
          )}
        </div>

        <div className="pr-32">
          <h3 className="text-xl font-bold text-gray-900 dark:text-white">
            {event.name}
          </h3>
          <div className="flex items-center space-x-4 mt-2 text-sm text-gray-500 dark:text-gray-400">
            <div className="flex items-center space-x-1">
              <MapPinIcon className="h-4 w-4" />
              <span>{event.venue}</span>
            </div>
            <div className="flex items-center space-x-1">
              <CalendarDaysIcon className="h-4 w-4" />
              <span>{formatDate(event.date)}</span>
            </div>
          </div>
        </div>

        <div className="mt-4 p-3 bg-gradient-to-r from-blue-50 to-purple-50 dark:from-blue-900/20 dark:to-purple-900/20 rounded-lg">
          <div className="flex items-center space-x-2">
            {getDemandIcon(event.demandIndicator.level)}
            <span className="font-medium text-gray-900 dark:text-white">
              {event.trendingReason}
            </span>
          </div>
        </div>
      </div>

      {/* Demand Indicator */}
      <div className="px-6 pb-4">
        <div className="flex items-center justify-between mb-3">
          <h4 className="text-sm font-medium text-gray-900 dark:text-white">
            Demand Level
          </h4>
          <span className={cn(
            'px-2 py-1 text-xs rounded-full font-medium capitalize',
            getDemandColor(event.demandIndicator.level)
          )}>
            {event.demandIndicator.level.replace('_', ' ')} ({event.demandIndicator.score}%)
          </span>
        </div>

        {/* Demand Bar */}
        <div className="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 mb-2">
          <div 
            className={cn(
              'h-2 rounded-full',
              event.demandIndicator.level === 'extreme' ? 'bg-purple-600' :
              event.demandIndicator.level === 'very_high' ? 'bg-red-600' :
              event.demandIndicator.level === 'high' ? 'bg-orange-600' :
              event.demandIndicator.level === 'medium' ? 'bg-yellow-600' : 'bg-gray-600'
            )}
            style={{ width: `${event.demandIndicator.score}%` }}
          />
        </div>

        <div className="flex flex-wrap gap-1 text-xs text-gray-500 dark:text-gray-400">
          {event.demandIndicator.factors.map((factor, idx) => (
            <span key={idx} className="bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">
              {factor}
            </span>
          ))}
        </div>
      </div>

      {/* Social Signals */}
      <div className="px-6 pb-4">
        <h4 className="text-sm font-medium text-gray-900 dark:text-white mb-3">
          Real-time Activity
        </h4>
        <div className="grid grid-cols-2 md:grid-cols-5 gap-3">
          {Object.entries(event.socialSignals).map(([key, signal]) => (
            <div key={key} className="text-center">
              <div className="text-lg font-bold text-gray-900 dark:text-white">
                {signal.count.toLocaleString()}
              </div>
              <div className="text-xs text-gray-500 dark:text-gray-400 capitalize">
                {key}
              </div>
              <div className={cn(
                'text-xs font-medium',
                signal.changePct24h > 0 
                  ? 'text-green-600 dark:text-green-400' 
                  : signal.changePct24h < 0 
                    ? 'text-red-600 dark:text-red-400'
                    : 'text-gray-500 dark:text-gray-400'
              )}>
                {signal.changePct24h > 0 ? '+' : ''}{signal.changePct24h.toFixed(1)}%
              </div>
            </div>
          ))}
        </div>
      </div>

      {/* Price and Availability */}
      <div className="px-6 pb-4">
        <div className="grid grid-cols-2 gap-6">
          <div>
            <h4 className="text-sm font-medium text-gray-900 dark:text-white mb-2">
              Price Range
            </h4>
            <div className="flex items-baseline space-x-2">
              <span className="text-lg font-bold text-gray-900 dark:text-white">
                {formatCurrency(event.priceRange.min)}
              </span>
              <span className="text-sm text-gray-500 dark:text-gray-400">to</span>
              <span className="text-lg font-bold text-gray-900 dark:text-white">
                {formatCurrency(event.priceRange.max)}
              </span>
            </div>
            <div className="text-sm text-gray-500 dark:text-gray-400">
              Avg: {formatCurrency(event.priceRange.average)}
            </div>
          </div>

          <div>
            <h4 className="text-sm font-medium text-gray-900 dark:text-white mb-2">
              Availability
            </h4>
            <div className="flex items-center space-x-2">
              <div className="flex-1">
                <div className="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                  <div 
                    className={cn(
                      'h-2 rounded-full',
                      event.availability.percentage > 50 ? 'bg-green-600' :
                      event.availability.percentage > 20 ? 'bg-yellow-600' : 'bg-red-600'
                    )}
                    style={{ width: `${event.availability.percentage}%` }}
                  />
                </div>
              </div>
              <span className="text-sm text-gray-600 dark:text-gray-300 whitespace-nowrap">
                {event.availability.remaining.toLocaleString()} left
              </span>
            </div>
            <div className="text-sm text-gray-500 dark:text-gray-400 mt-1">
              {event.availability.percentage.toFixed(1)}% remaining
            </div>
          </div>
        </div>
      </div>

      {/* Recent Activity */}
      {event.recentActivity.length > 0 && (
        <div className="px-6 pb-4 border-t border-gray-200 dark:border-gray-700 pt-4">
          <h4 className="text-sm font-medium text-gray-900 dark:text-white mb-3">
            Recent Activity
          </h4>
          <div className="space-y-2">
            {event.recentActivity.slice(0, 2).map((activity, idx) => (
              <div key={idx} className="flex items-start space-x-2">
                <div className={cn(
                  'w-2 h-2 rounded-full mt-2',
                  activity.impact === 'positive' ? 'bg-green-500' :
                  activity.impact === 'negative' ? 'bg-red-500' : 'bg-blue-500'
                )} />
                <div className="flex-1 min-w-0">
                  <p className="text-sm text-gray-600 dark:text-gray-300">
                    {activity.message}
                  </p>
                  <p className="text-xs text-gray-500 dark:text-gray-400">
                    {formatDate(activity.timestamp, { relative: true })}
                  </p>
                </div>
              </div>
            ))}
          </div>
        </div>
      )}

      {/* User Engagement */}
      <div className="px-6 pb-4 border-t border-gray-200 dark:border-gray-700 pt-4">
        <div className="flex items-center justify-between">
          <div className="flex items-center space-x-4 text-sm text-gray-500 dark:text-gray-400">
            <div className="flex items-center space-x-1">
              <button
                onClick={handleLike}
                className={cn(
                  'flex items-center space-x-1 transition-colors',
                  liked ? 'text-red-500' : 'hover:text-red-500'
                )}
              >
                {liked ? (
                  <HeartSolidIcon className="h-4 w-4" />
                ) : (
                  <HeartIcon className="h-4 w-4" />
                )}
                <span>{event.userEngagement.likes + (liked ? 1 : 0)}</span>
              </button>
            </div>
            <div className="flex items-center space-x-1">
              <ChatBubbleLeftEllipsisIcon className="h-4 w-4" />
              <span>{event.userEngagement.comments}</span>
            </div>
            <div className="flex items-center space-x-1">
              <EyeSolidIcon className="h-4 w-4" />
              <span>{event.userEngagement.shares}</span>
            </div>
          </div>

          <div className="flex items-center space-x-2">
            <Button
              size="sm"
              variant="outline"
              onClick={() => onFollowEvent?.(event.id)}
              leftIcon={<HeartIcon className="h-4 w-4" />}
            >
              Follow
            </Button>
            <Button
              size="sm"
              onClick={() => onViewEvent?.(event.id)}
              leftIcon={<EyeIcon className="h-4 w-4" />}
            >
              View Tickets
            </Button>
          </div>
        </div>
      </div>
    </motion.div>
  );
};

export default SocialProofFeatures;