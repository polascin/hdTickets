/**
 * HD Tickets - Team & Venue Following System
 * Inspired by TicketScoutie.com - Follow teams and venues for personalised updates
 */

import React, { useState, useEffect, useMemo } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import {
  HeartIcon,
  BellIcon,
  MapPinIcon,
  UserGroupIcon,
  CalendarDaysIcon,
  MagnifyingGlassIcon,
  XMarkIcon,
  PlusIcon,
  CheckIcon,
  AdjustmentsHorizontalIcon,
  StarIcon,
  TrophyIcon,
  BuildingOfficeIcon,
  EyeIcon,
  FireIcon,
  ClockIcon,
} from '@heroicons/react/24/outline';
import { HeartIcon as HeartSolidIcon, BellIcon as BellSolidIcon } from '@heroicons/react/24/solid';

import { cn, formatDate, formatCurrency } from '../../utils/design';
import Button from '../ui/Button';

interface Following {
  id: string;
  type: 'team' | 'venue';
  name: string;
  description?: string;
  logo?: string;
  location?: string;
  category?: string;
  isFollowing: boolean;
  notificationsEnabled: boolean;
  upcomingEvents: number;
  lastEventDate?: string;
  totalEvents: number;
  averagePrice: number;
  popularity: number;
  recentActivity?: {
    type: 'new_event' | 'price_drop' | 'sold_out' | 'back_in_stock';
    message: string;
    timestamp: string;
  }[];
}

interface FollowingSystemProps {
  onFollow: (id: string, type: 'team' | 'venue') => void;
  onUnfollow: (id: string, type: 'team' | 'venue') => void;
  onToggleNotifications: (id: string, enabled: boolean) => void;
  onViewEvents: (id: string, type: 'team' | 'venue') => void;
  followedItems?: Following[];
  popularSuggestions?: Following[];
}

// Mock data
const mockTeams: Following[] = [
  {
    id: 'man-united',
    type: 'team',
    name: 'Manchester United',
    description: 'English Premier League football club',
    logo: '/images/teams/man-united.png',
    location: 'Manchester, England',
    category: 'Football',
    isFollowing: true,
    notificationsEnabled: true,
    upcomingEvents: 8,
    lastEventDate: '2024-03-15T19:30:00Z',
    totalEvents: 45,
    averagePrice: 125,
    popularity: 95,
    recentActivity: [
      {
        type: 'new_event',
        message: 'New match added: Man United vs Liverpool',
        timestamp: '2024-01-10T10:00:00Z',
      },
      {
        type: 'price_drop',
        message: 'Ticket prices dropped by 15% for upcoming match',
        timestamp: '2024-01-08T15:30:00Z',
      },
    ],
  },
  {
    id: 'arsenal',
    type: 'team',
    name: 'Arsenal',
    description: 'English Premier League football club',
    logo: '/images/teams/arsenal.png',
    location: 'London, England',
    category: 'Football',
    isFollowing: false,
    notificationsEnabled: false,
    upcomingEvents: 6,
    lastEventDate: '2024-03-18T16:00:00Z',
    totalEvents: 38,
    averagePrice: 98,
    popularity: 88,
    recentActivity: [
      {
        type: 'back_in_stock',
        message: 'Premium seats now available',
        timestamp: '2024-01-09T12:00:00Z',
      },
    ],
  },
  {
    id: 'liverpool',
    type: 'team',
    name: 'Liverpool',
    description: 'English Premier League football club',
    logo: '/images/teams/liverpool.png',
    location: 'Liverpool, England',
    category: 'Football',
    isFollowing: true,
    notificationsEnabled: false,
    upcomingEvents: 7,
    lastEventDate: '2024-03-20T20:00:00Z',
    totalEvents: 42,
    averagePrice: 115,
    popularity: 92,
    recentActivity: [],
  },
];

const mockVenues: Following[] = [
  {
    id: 'wembley',
    type: 'venue',
    name: 'Wembley Stadium',
    description: 'The home of football - London\'s premier stadium',
    logo: '/images/venues/wembley.png',
    location: 'London, England',
    category: 'Football Stadium',
    isFollowing: true,
    notificationsEnabled: true,
    upcomingEvents: 12,
    lastEventDate: '2024-03-22T19:30:00Z',
    totalEvents: 89,
    averagePrice: 156,
    popularity: 98,
    recentActivity: [
      {
        type: 'new_event',
        message: 'FA Cup Final tickets now on sale',
        timestamp: '2024-01-11T09:00:00Z',
      },
      {
        type: 'sold_out',
        message: 'Champions League Final sold out',
        timestamp: '2024-01-07T14:20:00Z',
      },
    ],
  },
  {
    id: 'old-trafford',
    type: 'venue',
    name: 'Old Trafford',
    description: 'The Theatre of Dreams - Manchester United\'s home ground',
    logo: '/images/venues/old-trafford.png',
    location: 'Manchester, England',
    category: 'Football Stadium',
    isFollowing: false,
    notificationsEnabled: false,
    upcomingEvents: 9,
    lastEventDate: '2024-03-25T16:30:00Z',
    totalEvents: 67,
    averagePrice: 134,
    popularity: 89,
    recentActivity: [],
  },
  {
    id: 'emirates',
    type: 'venue',
    name: 'Emirates Stadium',
    description: 'Arsenal\'s modern home ground in North London',
    logo: '/images/venues/emirates.png',
    location: 'London, England',
    category: 'Football Stadium',
    isFollowing: false,
    notificationsEnabled: false,
    upcomingEvents: 5,
    lastEventDate: '2024-03-28T19:00:00Z',
    totalEvents: 45,
    averagePrice: 89,
    popularity: 78,
    recentActivity: [
      {
        type: 'price_drop',
        message: 'Season ticket holders get 20% discount',
        timestamp: '2024-01-06T11:15:00Z',
      },
    ],
  },
];

const FollowingSystem: React.FC<FollowingSystemProps> = ({
  onFollow,
  onUnfollow,
  onToggleNotifications,
  onViewEvents,
  followedItems = [...mockTeams.filter(t => t.isFollowing), ...mockVenues.filter(v => v.isFollowing)],
  popularSuggestions = [...mockTeams, ...mockVenues].filter(item => !item.isFollowing),
}) => {
  const [activeTab, setActiveTab] = useState<'following' | 'discover'>('following');
  const [searchQuery, setSearchQuery] = useState('');
  const [filterType, setFilterType] = useState<'all' | 'team' | 'venue'>('all');
  const [sortBy, setSortBy] = useState<'name' | 'popularity' | 'events' | 'price'>('popularity');

  // Filter and sort logic
  const filteredFollowing = useMemo(() => {
    let filtered = followedItems.filter(item => {
      const matchesSearch = item.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
                          (item.description && item.description.toLowerCase().includes(searchQuery.toLowerCase()));
      const matchesType = filterType === 'all' || item.type === filterType;
      return matchesSearch && matchesType;
    });

    return filtered.sort((a, b) => {
      switch (sortBy) {
        case 'name':
          return a.name.localeCompare(b.name);
        case 'popularity':
          return b.popularity - a.popularity;
        case 'events':
          return b.upcomingEvents - a.upcomingEvents;
        case 'price':
          return a.averagePrice - b.averagePrice;
        default:
          return 0;
      }
    });
  }, [followedItems, searchQuery, filterType, sortBy]);

  const filteredSuggestions = useMemo(() => {
    let filtered = popularSuggestions.filter(item => {
      const matchesSearch = item.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
                          (item.description && item.description.toLowerCase().includes(searchQuery.toLowerCase()));
      const matchesType = filterType === 'all' || item.type === filterType;
      return matchesSearch && matchesType;
    });

    return filtered.sort((a, b) => {
      switch (sortBy) {
        case 'name':
          return a.name.localeCompare(b.name);
        case 'popularity':
          return b.popularity - a.popularity;
        case 'events':
          return b.upcomingEvents - a.upcomingEvents;
        case 'price':
          return a.averagePrice - b.averagePrice;
        default:
          return 0;
      }
    }).slice(0, 8);
  }, [popularSuggestions, searchQuery, filterType, sortBy]);

  const handleFollow = (item: Following) => {
    onFollow(item.id, item.type);
  };

  const handleUnfollow = (item: Following) => {
    onUnfollow(item.id, item.type);
  };

  const handleToggleNotifications = (item: Following) => {
    onToggleNotifications(item.id, !item.notificationsEnabled);
  };

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div>
          <h2 className="text-2xl font-bold text-gray-900 dark:text-white">
            Following
          </h2>
          <p className="mt-1 text-gray-600 dark:text-gray-300">
            Stay updated with your favourite teams and venues
          </p>
        </div>
        <div className="flex items-center space-x-2">
          <span className="px-3 py-1 text-sm bg-blue-100 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 rounded-full">
            {followedItems.length} Following
          </span>
        </div>
      </div>

      {/* Tabs */}
      <div className="border-b border-gray-200 dark:border-gray-700">
        <nav className="-mb-px flex space-x-8">
          <button
            onClick={() => setActiveTab('following')}
            className={cn(
              'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm',
              activeTab === 'following'
                ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'
            )}
          >
            Following ({followedItems.length})
          </button>
          <button
            onClick={() => setActiveTab('discover')}
            className={cn(
              'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm',
              activeTab === 'discover'
                ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'
            )}
          >
            Discover
          </button>
        </nav>
      </div>

      {/* Search and Filters */}
      <div className="flex flex-col sm:flex-row gap-4">
        <div className="flex-1 relative">
          <MagnifyingGlassIcon className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
          <input
            type="text"
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
            placeholder="Search teams and venues..."
            className="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500"
          />
        </div>
        <select
          value={filterType}
          onChange={(e) => setFilterType(e.target.value as any)}
          className="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
        >
          <option value="all">All Types</option>
          <option value="team">Teams Only</option>
          <option value="venue">Venues Only</option>
        </select>
        <select
          value={sortBy}
          onChange={(e) => setSortBy(e.target.value as any)}
          className="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
        >
          <option value="popularity">Sort by Popularity</option>
          <option value="name">Sort by Name</option>
          <option value="events">Sort by Events</option>
          <option value="price">Sort by Price</option>
        </select>
      </div>

      {/* Content */}
      <AnimatePresence mode="wait">
        {activeTab === 'following' ? (
          <motion.div
            key="following"
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            exit={{ opacity: 0, y: -20 }}
            transition={{ duration: 0.2 }}
            className="space-y-4"
          >
            {filteredFollowing.length === 0 ? (
              <div className="text-center py-12">
                <HeartIcon className="mx-auto h-12 w-12 text-gray-400" />
                <h3 className="mt-4 text-lg font-medium text-gray-900 dark:text-white">
                  {followedItems.length === 0 ? 'No teams or venues followed yet' : 'No matches found'}
                </h3>
                <p className="mt-1 text-gray-600 dark:text-gray-300">
                  {followedItems.length === 0 
                    ? 'Start following your favourite teams and venues to get personalised updates.'
                    : 'Try adjusting your search or filters.'}
                </p>
                {followedItems.length === 0 && (
                  <Button
                    className="mt-4"
                    onClick={() => setActiveTab('discover')}
                    leftIcon={<PlusIcon className="h-4 w-4" />}
                  >
                    Discover Teams & Venues
                  </Button>
                )}
              </div>
            ) : (
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                {filteredFollowing.map((item) => (
                  <FollowingCard
                    key={item.id}
                    item={item}
                    onToggleFollow={() => handleUnfollow(item)}
                    onToggleNotifications={() => handleToggleNotifications(item)}
                    onViewEvents={() => onViewEvents(item.id, item.type)}
                    isFollowing={true}
                  />
                ))}
              </div>
            )}
          </motion.div>
        ) : (
          <motion.div
            key="discover"
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            exit={{ opacity: 0, y: -20 }}
            transition={{ duration: 0.2 }}
            className="space-y-4"
          >
            {filteredSuggestions.length === 0 ? (
              <div className="text-center py-12">
                <MagnifyingGlassIcon className="mx-auto h-12 w-12 text-gray-400" />
                <h3 className="mt-4 text-lg font-medium text-gray-900 dark:text-white">
                  No suggestions found
                </h3>
                <p className="mt-1 text-gray-600 dark:text-gray-300">
                  Try adjusting your search or filters.
                </p>
              </div>
            ) : (
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                {filteredSuggestions.map((item) => (
                  <FollowingCard
                    key={item.id}
                    item={item}
                    onToggleFollow={() => handleFollow(item)}
                    onToggleNotifications={() => handleToggleNotifications(item)}
                    onViewEvents={() => onViewEvents(item.id, item.type)}
                    isFollowing={false}
                  />
                ))}
              </div>
            )}
          </motion.div>
        )}
      </AnimatePresence>
    </div>
  );
};

// Following Card Component
interface FollowingCardProps {
  item: Following;
  isFollowing: boolean;
  onToggleFollow: () => void;
  onToggleNotifications: () => void;
  onViewEvents: () => void;
}

const FollowingCard: React.FC<FollowingCardProps> = ({
  item,
  isFollowing,
  onToggleFollow,
  onToggleNotifications,
  onViewEvents,
}) => {
  const getActivityIcon = (type: string) => {
    switch (type) {
      case 'new_event':
        return <CalendarDaysIcon className="h-4 w-4 text-green-500" />;
      case 'price_drop':
        return <FireIcon className="h-4 w-4 text-red-500" />;
      case 'sold_out':
        return <XMarkIcon className="h-4 w-4 text-orange-500" />;
      case 'back_in_stock':
        return <CheckIcon className="h-4 w-4 text-blue-500" />;
      default:
        return <ClockIcon className="h-4 w-4 text-gray-400" />;
    }
  };

  return (
    <motion.div
      initial={{ opacity: 0, scale: 0.95 }}
      animate={{ opacity: 1, scale: 1 }}
      transition={{ duration: 0.2 }}
      className="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-lg transition-shadow"
    >
      {/* Header */}
      <div className="p-6 pb-4">
        <div className="flex items-start justify-between">
          <div className="flex items-center space-x-3">
            <div className="w-12 h-12 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
              {item.type === 'team' ? (
                <UserGroupIcon className="h-6 w-6 text-gray-600 dark:text-gray-300" />
              ) : (
                <BuildingOfficeIcon className="h-6 w-6 text-gray-600 dark:text-gray-300" />
              )}
            </div>
            <div className="flex-1">
              <h3 className="font-semibold text-gray-900 dark:text-white">
                {item.name}
              </h3>
              <div className="flex items-center space-x-2 mt-1">
                <span className="text-xs px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-full">
                  {item.category}
                </span>
                <div className="flex items-center space-x-1">
                  <StarIcon className="h-3 w-3 text-yellow-500" />
                  <span className="text-xs text-gray-500 dark:text-gray-400">
                    {item.popularity}%
                  </span>
                </div>
              </div>
            </div>
          </div>
          <div className="flex items-center space-x-1">
            <button
              onClick={onToggleFollow}
              className={cn(
                'p-2 rounded-lg transition-colors',
                isFollowing
                  ? 'text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20'
                  : 'text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20'
              )}
              title={isFollowing ? 'Unfollow' : 'Follow'}
            >
              {isFollowing ? (
                <HeartSolidIcon className="h-5 w-5" />
              ) : (
                <HeartIcon className="h-5 w-5" />
              )}
            </button>
            {isFollowing && (
              <button
                onClick={onToggleNotifications}
                className={cn(
                  'p-2 rounded-lg transition-colors',
                  item.notificationsEnabled
                    ? 'text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20'
                    : 'text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20'
                )}
                title={item.notificationsEnabled ? 'Disable notifications' : 'Enable notifications'}
              >
                {item.notificationsEnabled ? (
                  <BellSolidIcon className="h-5 w-5" />
                ) : (
                  <BellIcon className="h-5 w-5" />
                )}
              </button>
            )}
          </div>
        </div>

        {item.description && (
          <p className="text-sm text-gray-600 dark:text-gray-300 mt-2">
            {item.description}
          </p>
        )}

        {item.location && (
          <div className="flex items-center space-x-1 mt-2">
            <MapPinIcon className="h-4 w-4 text-gray-400" />
            <span className="text-sm text-gray-500 dark:text-gray-400">
              {item.location}
            </span>
          </div>
        )}
      </div>

      {/* Stats */}
      <div className="px-6 pb-4">
        <div className="grid grid-cols-3 gap-4">
          <div className="text-center">
            <div className="text-lg font-semibold text-gray-900 dark:text-white">
              {item.upcomingEvents}
            </div>
            <div className="text-xs text-gray-500 dark:text-gray-400">
              Upcoming
            </div>
          </div>
          <div className="text-center">
            <div className="text-lg font-semibold text-gray-900 dark:text-white">
              {formatCurrency(item.averagePrice)}
            </div>
            <div className="text-xs text-gray-500 dark:text-gray-400">
              Avg Price
            </div>
          </div>
          <div className="text-center">
            <div className="text-lg font-semibold text-gray-900 dark:text-white">
              {item.totalEvents}
            </div>
            <div className="text-xs text-gray-500 dark:text-gray-400">
              Total Events
            </div>
          </div>
        </div>
      </div>

      {/* Recent Activity */}
      {item.recentActivity && item.recentActivity.length > 0 && (
        <div className="px-6 pb-4 border-t border-gray-200 dark:border-gray-700 pt-4">
          <h4 className="text-sm font-medium text-gray-900 dark:text-white mb-2">
            Recent Activity
          </h4>
          <div className="space-y-2">
            {item.recentActivity.slice(0, 2).map((activity, index) => (
              <div key={index} className="flex items-start space-x-2">
                {getActivityIcon(activity.type)}
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

      {/* Actions */}
      <div className="px-6 pb-6">
        <Button
          variant="outline"
          size="sm"
          className="w-full"
          onClick={onViewEvents}
          leftIcon={<EyeIcon className="h-4 w-4" />}
        >
          View Events
        </Button>
      </div>
    </motion.div>
  );
};

export default FollowingSystem;