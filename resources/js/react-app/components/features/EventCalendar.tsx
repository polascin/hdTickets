/**
 * HD Tickets - Interactive Event Calendar
 * Calendar view with ticket availability indicators and monitoring features
 */

import React, { useState, useMemo } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import {
  ChevronLeftIcon,
  ChevronRightIcon,
  CalendarDaysIcon,
  ClockIcon,
  MapPinIcon,
  UserGroupIcon,
  EyeIcon,
  BellIcon,
  HeartIcon,
  FunnelIcon,
  AdjustmentsHorizontalIcon,
  MagnifyingGlassIcon,
  ViewColumnsIcon,
  ListBulletIcon,
  TagIcon,
} from '@heroicons/react/24/outline';
import { 
  HeartIcon as HeartSolidIcon, 
  BellIcon as BellSolidIcon,
  CalendarDaysIcon as CalendarSolidIcon,
} from '@heroicons/react/24/solid';

import { cn, formatCurrency, formatDate } from '../../utils/design';
import Button from '../ui/Button';

interface CalendarEvent {
  id: string;
  title: string;
  sport: string;
  venue: string;
  date: string;
  time: string;
  homeTeam?: string;
  awayTeam?: string;
  league?: string;
  ticketAvailability: {
    status: 'high' | 'medium' | 'low' | 'sold_out' | 'on_sale_soon';
    totalTickets: number;
    cheapestPrice: number;
    averagePrice: number;
    platforms: string[];
  };
  isFollowing: boolean;
  hasAlerts: boolean;
  isFeatured: boolean;
  image?: string;
  category: string;
}

interface EventCalendarProps {
  events?: CalendarEvent[];
  onEventClick?: (event: CalendarEvent) => void;
  onToggleFollow?: (eventId: string) => void;
  onToggleAlert?: (eventId: string) => void;
  onCreateAlert?: (eventId: string) => void;
  onViewTickets?: (eventId: string) => void;
}

// Mock events data
const mockEvents: CalendarEvent[] = [
  {
    id: '1',
    title: 'Manchester United vs Liverpool',
    sport: 'Football',
    venue: 'Old Trafford',
    date: '2024-01-20',
    time: '15:00',
    homeTeam: 'Manchester United',
    awayTeam: 'Liverpool',
    league: 'Premier League',
    ticketAvailability: {
      status: 'medium',
      totalTickets: 1250,
      cheapestPrice: 85,
      averagePrice: 165,
      platforms: ['Ticketmaster', 'StubHub', 'Viagogo'],
    },
    isFollowing: true,
    hasAlerts: true,
    isFeatured: true,
    category: 'Football',
  },
  {
    id: '2',
    title: 'Arsenal vs Chelsea',
    sport: 'Football',
    venue: 'Emirates Stadium',
    date: '2024-01-21',
    time: '17:30',
    homeTeam: 'Arsenal',
    awayTeam: 'Chelsea',
    league: 'Premier League',
    ticketAvailability: {
      status: 'high',
      totalTickets: 2150,
      cheapestPrice: 65,
      averagePrice: 125,
      platforms: ['Ticketmaster', 'See Tickets'],
    },
    isFollowing: false,
    hasAlerts: false,
    isFeatured: false,
    category: 'Football',
  },
  {
    id: '3',
    title: 'Anthony Joshua vs Francis Ngannou',
    sport: 'Boxing',
    venue: 'Wembley Stadium',
    date: '2024-01-25',
    time: '19:00',
    league: 'Heavyweight Championship',
    ticketAvailability: {
      status: 'low',
      totalTickets: 89,
      cheapestPrice: 195,
      averagePrice: 450,
      platforms: ['Ticketmaster', 'StubHub'],
    },
    isFollowing: true,
    hasAlerts: false,
    isFeatured: true,
    category: 'Boxing',
  },
  {
    id: '4',
    title: 'England vs Wales',
    sport: 'Rugby',
    venue: 'Twickenham',
    date: '2024-01-28',
    time: '14:30',
    homeTeam: 'England',
    awayTeam: 'Wales',
    league: 'Six Nations',
    ticketAvailability: {
      status: 'sold_out',
      totalTickets: 0,
      cheapestPrice: 0,
      averagePrice: 0,
      platforms: [],
    },
    isFollowing: true,
    hasAlerts: true,
    isFeatured: false,
    category: 'Rugby',
  },
];

const EventCalendar: React.FC<EventCalendarProps> = ({
  events = mockEvents,
  onEventClick,
  onToggleFollow,
  onToggleAlert,
  onCreateAlert,
  onViewTickets,
}) => {
  const [currentDate, setCurrentDate] = useState(new Date());
  const [viewMode, setViewMode] = useState<'month' | 'week' | 'list'>('month');
  const [selectedSport, setSelectedSport] = useState<string>('all');
  const [selectedStatus, setSelectedStatus] = useState<string>('all');
  const [searchQuery, setSearchQuery] = useState('');
  const [showFilters, setShowFilters] = useState(false);

  // Get unique sports for filter
  const availableSports = useMemo(() => {
    const sports = Array.from(new Set(events.map(event => event.sport)));
    return sports.sort();
  }, [events]);

  // Filter events
  const filteredEvents = useMemo(() => {
    return events.filter(event => {
      const matchesSport = selectedSport === 'all' || event.sport === selectedSport;
      const matchesStatus = selectedStatus === 'all' || event.ticketAvailability.status === selectedStatus;
      const matchesSearch = searchQuery === '' || 
        event.title.toLowerCase().includes(searchQuery.toLowerCase()) ||
        event.venue.toLowerCase().includes(searchQuery.toLowerCase()) ||
        (event.league && event.league.toLowerCase().includes(searchQuery.toLowerCase()));
      
      return matchesSport && matchesStatus && matchesSearch;
    });
  }, [events, selectedSport, selectedStatus, searchQuery]);

  // Calendar navigation
  const navigateMonth = (direction: 'prev' | 'next') => {
    setCurrentDate(prev => {
      const newDate = new Date(prev);
      if (direction === 'prev') {
        newDate.setMonth(prev.getMonth() - 1);
      } else {
        newDate.setMonth(prev.getMonth() + 1);
      }
      return newDate;
    });
  };

  // Get calendar days
  const getCalendarDays = () => {
    const year = currentDate.getFullYear();
    const month = currentDate.getMonth();
    
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const startDate = new Date(firstDay);
    startDate.setDate(startDate.getDate() - firstDay.getDay());
    
    const days = [];
    const currentDay = new Date(startDate);
    
    for (let i = 0; i < 42; i++) { // 6 weeks
      days.push(new Date(currentDay));
      currentDay.setDate(currentDay.getDate() + 1);
    }
    
    return days;
  };

  // Get events for specific date
  const getEventsForDate = (date: Date) => {
    const dateString = date.toISOString().split('T')[0];
    return filteredEvents.filter(event => event.date === dateString);
  };

  const getAvailabilityColor = (status: string) => {
    switch (status) {
      case 'high':
        return 'bg-green-500';
      case 'medium':
        return 'bg-yellow-500';
      case 'low':
        return 'bg-orange-500';
      case 'sold_out':
        return 'bg-red-500';
      case 'on_sale_soon':
        return 'bg-blue-500';
      default:
        return 'bg-gray-500';
    }
  };

  const monthNames = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
  ];

  const weekDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
          <h2 className="text-2xl font-bold text-gray-900 dark:text-white">
            Event Calendar
          </h2>
          <p className="mt-1 text-gray-600 dark:text-gray-300">
            View upcoming sports events with ticket availability
          </p>
        </div>
        
        <div className="flex items-center space-x-2">
          {/* View Mode Toggle */}
          <div className="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-1">
            <button
              onClick={() => setViewMode('month')}
              className={cn(
                'px-3 py-1.5 text-sm rounded-md transition-colors',
                viewMode === 'month'
                  ? 'bg-blue-100 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300'
                  : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'
              )}
            >
              <CalendarDaysIcon className="h-4 w-4 inline mr-1" />
              Month
            </button>
            <button
              onClick={() => setViewMode('week')}
              className={cn(
                'px-3 py-1.5 text-sm rounded-md transition-colors',
                viewMode === 'week'
                  ? 'bg-blue-100 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300'
                  : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'
              )}
            >
              <ViewColumnsIcon className="h-4 w-4 inline mr-1" />
              Week
            </button>
            <button
              onClick={() => setViewMode('list')}
              className={cn(
                'px-3 py-1.5 text-sm rounded-md transition-colors',
                viewMode === 'list'
                  ? 'bg-blue-100 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300'
                  : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'
              )}
            >
              <ListBulletIcon className="h-4 w-4 inline mr-1" />
              List
            </button>
          </div>

          <Button
            variant="outline"
            size="sm"
            leftIcon={<FunnelIcon className="h-4 w-4" />}
            onClick={() => setShowFilters(!showFilters)}
          >
            Filters
          </Button>
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
            <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
              {/* Search */}
              <div className="relative">
                <MagnifyingGlassIcon className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                <input
                  type="text"
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  placeholder="Search events..."
                  className="w-full pl-10 pr-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                />
              </div>

              {/* Sport Filter */}
              <select
                value={selectedSport}
                onChange={(e) => setSelectedSport(e.target.value)}
                className="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
              >
                <option value="all">All Sports</option>
                {availableSports.map(sport => (
                  <option key={sport} value={sport}>{sport}</option>
                ))}
              </select>

              {/* Status Filter */}
              <select
                value={selectedStatus}
                onChange={(e) => setSelectedStatus(e.target.value)}
                className="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
              >
                <option value="all">All Availability</option>
                <option value="high">High Availability</option>
                <option value="medium">Medium Availability</option>
                <option value="low">Low Availability</option>
                <option value="sold_out">Sold Out</option>
                <option value="on_sale_soon">On Sale Soon</option>
              </select>

              {/* Clear Filters */}
              <Button
                variant="outline"
                size="sm"
                onClick={() => {
                  setSearchQuery('');
                  setSelectedSport('all');
                  setSelectedStatus('all');
                }}
              >
                Clear Filters
              </Button>
            </div>
          </motion.div>
        )}
      </AnimatePresence>

      {/* Calendar Navigation */}
      {viewMode !== 'list' && (
        <div className="flex items-center justify-between">
          <button
            onClick={() => navigateMonth('prev')}
            className="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
          >
            <ChevronLeftIcon className="h-5 w-5 text-gray-600 dark:text-gray-400" />
          </button>
          
          <h3 className="text-lg font-semibold text-gray-900 dark:text-white">
            {monthNames[currentDate.getMonth()]} {currentDate.getFullYear()}
          </h3>
          
          <button
            onClick={() => navigateMonth('next')}
            className="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
          >
            <ChevronRightIcon className="h-5 w-5 text-gray-600 dark:text-gray-400" />
          </button>
        </div>
      )}

      {/* Calendar View */}
      <div className="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        {viewMode === 'month' && (
          <MonthView
            days={getCalendarDays()}
            currentMonth={currentDate.getMonth()}
            getEventsForDate={getEventsForDate}
            getAvailabilityColor={getAvailabilityColor}
            onEventClick={onEventClick}
            onToggleFollow={onToggleFollow}
            onToggleAlert={onToggleAlert}
            onViewTickets={onViewTickets}
          />
        )}
        
        {viewMode === 'week' && (
          <WeekView
            currentDate={currentDate}
            filteredEvents={filteredEvents}
            getAvailabilityColor={getAvailabilityColor}
            onEventClick={onEventClick}
          />
        )}
        
        {viewMode === 'list' && (
          <ListView
            events={filteredEvents}
            getAvailabilityColor={getAvailabilityColor}
            onEventClick={onEventClick}
            onToggleFollow={onToggleFollow}
            onToggleAlert={onToggleAlert}
            onViewTickets={onViewTickets}
          />
        )}
      </div>

      {/* Legend */}
      <div className="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
        <h4 className="text-sm font-medium text-gray-900 dark:text-white mb-3">
          Ticket Availability Legend
        </h4>
        <div className="flex flex-wrap gap-4">
          <div className="flex items-center space-x-2">
            <div className="w-3 h-3 rounded bg-green-500"></div>
            <span className="text-sm text-gray-600 dark:text-gray-300">High Availability</span>
          </div>
          <div className="flex items-center space-x-2">
            <div className="w-3 h-3 rounded bg-yellow-500"></div>
            <span className="text-sm text-gray-600 dark:text-gray-300">Medium Availability</span>
          </div>
          <div className="flex items-center space-x-2">
            <div className="w-3 h-3 rounded bg-orange-500"></div>
            <span className="text-sm text-gray-600 dark:text-gray-300">Low Availability</span>
          </div>
          <div className="flex items-center space-x-2">
            <div className="w-3 h-3 rounded bg-red-500"></div>
            <span className="text-sm text-gray-600 dark:text-gray-300">Sold Out</span>
          </div>
          <div className="flex items-center space-x-2">
            <div className="w-3 h-3 rounded bg-blue-500"></div>
            <span className="text-sm text-gray-600 dark:text-gray-300">On Sale Soon</span>
          </div>
        </div>
      </div>
    </div>
  );
};

// Month View Component
interface MonthViewProps {
  days: Date[];
  currentMonth: number;
  getEventsForDate: (date: Date) => CalendarEvent[];
  getAvailabilityColor: (status: string) => string;
  onEventClick?: (event: CalendarEvent) => void;
  onToggleFollow?: (eventId: string) => void;
  onToggleAlert?: (eventId: string) => void;
  onViewTickets?: (eventId: string) => void;
}

const MonthView: React.FC<MonthViewProps> = ({
  days,
  currentMonth,
  getEventsForDate,
  getAvailabilityColor,
  onEventClick,
  onToggleFollow,
  onToggleAlert,
  onViewTickets,
}) => {
  const weekDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

  return (
    <div>
      {/* Week day headers */}
      <div className="grid grid-cols-7 gap-px bg-gray-200 dark:bg-gray-600">
        {weekDays.map((day) => (
          <div key={day} className="bg-gray-50 dark:bg-gray-700 px-3 py-2 text-center">
            <span className="text-xs font-medium text-gray-900 dark:text-white">
              {day}
            </span>
          </div>
        ))}
      </div>

      {/* Calendar grid */}
      <div className="grid grid-cols-7 gap-px bg-gray-200 dark:bg-gray-600">
        {days.map((day, index) => {
          const events = getEventsForDate(day);
          const isCurrentMonth = day.getMonth() === currentMonth;
          const isToday = day.toDateString() === new Date().toDateString();

          return (
            <div
              key={index}
              className={cn(
                'bg-white dark:bg-gray-800 min-h-[120px] p-2',
                !isCurrentMonth && 'bg-gray-50 dark:bg-gray-700 opacity-60'
              )}
            >
              <div className={cn(
                'text-sm font-medium mb-1',
                isToday
                  ? 'text-blue-600 dark:text-blue-400'
                  : isCurrentMonth
                    ? 'text-gray-900 dark:text-white'
                    : 'text-gray-500 dark:text-gray-400'
              )}>
                {day.getDate()}
              </div>
              
              <div className="space-y-1">
                {events.slice(0, 2).map((event) => (
                  <div
                    key={event.id}
                    className="relative group cursor-pointer"
                    onClick={() => onEventClick?.(event)}
                  >
                    <div className="text-xs p-1 rounded bg-gray-100 dark:bg-gray-600 hover:bg-gray-200 dark:hover:bg-gray-500 transition-colors">
                      <div className="flex items-center space-x-1">
                        <div className={cn(
                          'w-2 h-2 rounded-full flex-shrink-0',
                          getAvailabilityColor(event.ticketAvailability.status)
                        )} />
                        <span className="text-gray-900 dark:text-white truncate">
                          {event.title}
                        </span>
                      </div>
                    </div>
                    
                    {/* Event tooltip */}
                    <div className="absolute z-10 invisible group-hover:visible top-full left-0 mt-1 w-64 p-3 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg">
                      <EventTooltip
                        event={event}
                        onToggleFollow={onToggleFollow}
                        onToggleAlert={onToggleAlert}
                        onViewTickets={onViewTickets}
                      />
                    </div>
                  </div>
                ))}
                
                {events.length > 2 && (
                  <div className="text-xs text-gray-500 dark:text-gray-400">
                    +{events.length - 2} more
                  </div>
                )}
              </div>
            </div>
          );
        })}
      </div>
    </div>
  );
};

// Week View Component (simplified for brevity)
interface WeekViewProps {
  currentDate: Date;
  filteredEvents: CalendarEvent[];
  getAvailabilityColor: (status: string) => string;
  onEventClick?: (event: CalendarEvent) => void;
}

const WeekView: React.FC<WeekViewProps> = ({
  currentDate,
  filteredEvents,
  getAvailabilityColor,
  onEventClick,
}) => {
  return (
    <div className="p-4">
      <div className="text-center text-gray-500 dark:text-gray-400">
        Week view - Coming soon
      </div>
    </div>
  );
};

// List View Component
interface ListViewProps {
  events: CalendarEvent[];
  getAvailabilityColor: (status: string) => string;
  onEventClick?: (event: CalendarEvent) => void;
  onToggleFollow?: (eventId: string) => void;
  onToggleAlert?: (eventId: string) => void;
  onViewTickets?: (eventId: string) => void;
}

const ListView: React.FC<ListViewProps> = ({
  events,
  getAvailabilityColor,
  onEventClick,
  onToggleFollow,
  onToggleAlert,
  onViewTickets,
}) => {
  return (
    <div className="divide-y divide-gray-200 dark:divide-gray-700">
      {events.map((event) => (
        <div key={event.id} className="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50">
          <div className="flex items-center justify-between">
            <div className="flex items-center space-x-4">
              <div className={cn(
                'w-3 h-3 rounded-full',
                getAvailabilityColor(event.ticketAvailability.status)
              )} />
              <div>
                <h3 className="font-medium text-gray-900 dark:text-white">
                  {event.title}
                </h3>
                <div className="flex items-center space-x-4 text-sm text-gray-500 dark:text-gray-400">
                  <div className="flex items-center space-x-1">
                    <CalendarDaysIcon className="h-4 w-4" />
                    <span>{formatDate(event.date)}</span>
                  </div>
                  <div className="flex items-center space-x-1">
                    <ClockIcon className="h-4 w-4" />
                    <span>{event.time}</span>
                  </div>
                  <div className="flex items-center space-x-1">
                    <MapPinIcon className="h-4 w-4" />
                    <span>{event.venue}</span>
                  </div>
                </div>
              </div>
            </div>
            
            <div className="flex items-center space-x-2">
              <span className="text-sm font-medium text-gray-900 dark:text-white">
                From {formatCurrency(event.ticketAvailability.cheapestPrice)}
              </span>
              <Button
                size="sm"
                onClick={() => onViewTickets?.(event.id)}
                leftIcon={<EyeIcon className="h-4 w-4" />}
              >
                View Tickets
              </Button>
            </div>
          </div>
        </div>
      ))}
      
      {events.length === 0 && (
        <div className="p-8 text-center">
          <CalendarDaysIcon className="mx-auto h-12 w-12 text-gray-400" />
          <h3 className="mt-4 text-lg font-medium text-gray-900 dark:text-white">
            No events found
          </h3>
          <p className="mt-1 text-gray-500 dark:text-gray-400">
            Try adjusting your filters to see more events.
          </p>
        </div>
      )}
    </div>
  );
};

// Event Tooltip Component
interface EventTooltipProps {
  event: CalendarEvent;
  onToggleFollow?: (eventId: string) => void;
  onToggleAlert?: (eventId: string) => void;
  onViewTickets?: (eventId: string) => void;
}

const EventTooltip: React.FC<EventTooltipProps> = ({
  event,
  onToggleFollow,
  onToggleAlert,
  onViewTickets,
}) => {
  return (
    <div className="space-y-2">
      <div>
        <h4 className="font-medium text-gray-900 dark:text-white">
          {event.title}
        </h4>
        <p className="text-sm text-gray-600 dark:text-gray-300">
          {event.time} • {event.venue}
        </p>
      </div>
      
      <div className="text-sm text-gray-600 dark:text-gray-300">
        <div>From {formatCurrency(event.ticketAvailability.cheapestPrice)}</div>
        <div>{event.ticketAvailability.totalTickets} tickets available</div>
      </div>
      
      <div className="flex items-center space-x-1 pt-2">
        <Button
          size="xs"
          variant="outline"
          onClick={() => onViewTickets?.(event.id)}
        >
          View
        </Button>
        <Button
          size="xs"
          variant="outline"
          onClick={() => onToggleFollow?.(event.id)}
          leftIcon={event.isFollowing ? <HeartSolidIcon className="h-3 w-3" /> : <HeartIcon className="h-3 w-3" />}
        >
          {event.isFollowing ? 'Following' : 'Follow'}
        </Button>
        <Button
          size="xs"
          variant="outline"
          onClick={() => onToggleAlert?.(event.id)}
          leftIcon={event.hasAlerts ? <BellSolidIcon className="h-3 w-3" /> : <BellIcon className="h-3 w-3" />}
        >
          Alert
        </Button>
      </div>
    </div>
  );
};

export default EventCalendar;