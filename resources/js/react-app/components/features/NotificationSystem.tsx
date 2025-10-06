/**
 * HD Tickets - User Notification System
 * Comprehensive notification management with email, push, SMS, and in-app alerts
 */

import React, { useState, useEffect, useMemo } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import {
  BellIcon,
  EnvelopeIcon,
  DevicePhoneMobileIcon,
  ComputerDesktopIcon,
  CheckCircleIcon,
  ExclamationCircleIcon,
  InformationCircleIcon,
  XCircleIcon,
  Cog6ToothIcon,
  AdjustmentsHorizontalIcon,
  EyeIcon,
  TrashIcon,
  XMarkIcon,
  ClockIcon,
  FireIcon,
  CurrencyPoundIcon,
  CalendarDaysIcon,
  MapPinIcon,
  UserGroupIcon,
  FunnelIcon,
  MagnifyingGlassIcon,
  BellAlertIcon,
  EllipsisVerticalIcon,
} from '@heroicons/react/24/outline';
import { 
  BellIcon as BellSolidIcon,
  CheckCircleIcon as CheckSolidIcon,
  ExclamationTriangleIcon,
} from '@heroicons/react/24/solid';

import { cn, formatCurrency, formatDate } from '../../utils/design';
import Button from '../ui/Button';

interface NotificationPreferences {
  email: {
    enabled: boolean;
    priceDrops: boolean;
    newEvents: boolean;
    availabilityChanges: boolean;
    followedTeams: boolean;
    systemUpdates: boolean;
    frequency: 'instant' | 'hourly' | 'daily' | 'weekly';
  };
  push: {
    enabled: boolean;
    priceDrops: boolean;
    newEvents: boolean;
    availabilityChanges: boolean;
    followedTeams: boolean;
    systemUpdates: boolean;
  };
  sms: {
    enabled: boolean;
    priceDrops: boolean;
    urgentOnly: boolean;
    phoneNumber?: string;
  };
  inApp: {
    enabled: boolean;
    showBadge: boolean;
    sound: boolean;
    desktop: boolean;
  };
}

interface Notification {
  id: string;
  type: 'price_drop' | 'new_event' | 'availability_change' | 'followed_team' | 'system' | 'alert_triggered';
  title: string;
  message: string;
  timestamp: string;
  read: boolean;
  priority: 'low' | 'medium' | 'high' | 'urgent';
  eventId?: string;
  eventName?: string;
  venue?: string;
  originalPrice?: number;
  newPrice?: number;
  discount?: number;
  actionUrl?: string;
  metadata?: Record<string, any>;
}

interface NotificationSystemProps {
  notifications?: Notification[];
  preferences?: NotificationPreferences;
  onUpdatePreferences?: (preferences: NotificationPreferences) => void;
  onMarkAsRead?: (notificationId: string) => void;
  onMarkAllAsRead?: () => void;
  onDeleteNotification?: (notificationId: string) => void;
  onClearAll?: () => void;
  onNotificationAction?: (notification: Notification) => void;
}

// Mock data
const mockNotifications: Notification[] = [
  {
    id: '1',
    type: 'price_drop',
    title: 'Price Drop Alert',
    message: 'Manchester United vs Liverpool tickets dropped by 20%',
    timestamp: '2024-01-15T10:30:00Z',
    read: false,
    priority: 'high',
    eventId: 'match-1',
    eventName: 'Manchester United vs Liverpool',
    venue: 'Old Trafford',
    originalPrice: 150,
    newPrice: 120,
    discount: 20,
    actionUrl: '/events/match-1',
  },
  {
    id: '2',
    type: 'availability_change',
    title: 'Tickets Back in Stock',
    message: 'Arsenal vs Chelsea premium seats are now available',
    timestamp: '2024-01-15T09:15:00Z',
    read: false,
    priority: 'medium',
    eventId: 'match-2',
    eventName: 'Arsenal vs Chelsea',
    venue: 'Emirates Stadium',
    actionUrl: '/events/match-2',
  },
  {
    id: '3',
    type: 'new_event',
    title: 'New Event Added',
    message: 'Anthony Joshua vs Francis Ngannou announced for Wembley',
    timestamp: '2024-01-15T08:45:00Z',
    read: true,
    priority: 'medium',
    eventId: 'fight-1',
    eventName: 'Anthony Joshua vs Francis Ngannou',
    venue: 'Wembley Stadium',
    actionUrl: '/events/fight-1',
  },
  {
    id: '4',
    type: 'followed_team',
    title: 'Team Update',
    message: 'Liverpool has 3 new upcoming matches added',
    timestamp: '2024-01-14T16:20:00Z',
    read: true,
    priority: 'low',
    metadata: { teamId: 'liverpool', matchCount: 3 },
  },
  {
    id: '5',
    type: 'system',
    title: 'System Maintenance',
    message: 'Scheduled maintenance tonight from 2-4 AM GMT',
    timestamp: '2024-01-14T12:00:00Z',
    read: true,
    priority: 'low',
  },
];

const defaultPreferences: NotificationPreferences = {
  email: {
    enabled: true,
    priceDrops: true,
    newEvents: true,
    availabilityChanges: true,
    followedTeams: true,
    systemUpdates: false,
    frequency: 'instant',
  },
  push: {
    enabled: true,
    priceDrops: true,
    newEvents: false,
    availabilityChanges: true,
    followedTeams: true,
    systemUpdates: false,
  },
  sms: {
    enabled: false,
    priceDrops: true,
    urgentOnly: true,
    phoneNumber: '',
  },
  inApp: {
    enabled: true,
    showBadge: true,
    sound: true,
    desktop: true,
  },
};

const NotificationSystem: React.FC<NotificationSystemProps> = ({
  notifications = mockNotifications,
  preferences = defaultPreferences,
  onUpdatePreferences,
  onMarkAsRead,
  onMarkAllAsRead,
  onDeleteNotification,
  onClearAll,
  onNotificationAction,
}) => {
  const [activeTab, setActiveTab] = useState<'notifications' | 'preferences'>('notifications');
  const [filterType, setFilterType] = useState<string>('all');
  const [filterRead, setFilterRead] = useState<'all' | 'unread' | 'read'>('all');
  const [searchQuery, setSearchQuery] = useState('');
  const [showFilters, setShowFilters] = useState(false);

  // Filter notifications
  const filteredNotifications = useMemo(() => {
    return notifications.filter(notification => {
      const matchesType = filterType === 'all' || notification.type === filterType;
      const matchesRead = filterRead === 'all' || 
        (filterRead === 'unread' && !notification.read) ||
        (filterRead === 'read' && notification.read);
      const matchesSearch = searchQuery === '' ||
        notification.title.toLowerCase().includes(searchQuery.toLowerCase()) ||
        notification.message.toLowerCase().includes(searchQuery.toLowerCase()) ||
        (notification.eventName && notification.eventName.toLowerCase().includes(searchQuery.toLowerCase()));
      
      return matchesType && matchesRead && matchesSearch;
    });
  }, [notifications, filterType, filterRead, searchQuery]);

  // Count unread notifications
  const unreadCount = notifications.filter(n => !n.read).length;

  const getNotificationIcon = (type: string, priority: string) => {
    const iconClass = priority === 'urgent' ? 'text-red-500' : 
                     priority === 'high' ? 'text-orange-500' :
                     priority === 'medium' ? 'text-blue-500' : 'text-gray-500';

    switch (type) {
      case 'price_drop':
        return <FireIcon className={cn('h-5 w-5', iconClass)} />;
      case 'new_event':
        return <CalendarDaysIcon className={cn('h-5 w-5', iconClass)} />;
      case 'availability_change':
        return <ExclamationCircleIcon className={cn('h-5 w-5', iconClass)} />;
      case 'followed_team':
        return <UserGroupIcon className={cn('h-5 w-5', iconClass)} />;
      case 'system':
        return <InformationCircleIcon className={cn('h-5 w-5', iconClass)} />;
      case 'alert_triggered':
        return <BellAlertIcon className={cn('h-5 w-5', iconClass)} />;
      default:
        return <BellIcon className={cn('h-5 w-5', iconClass)} />;
    }
  };

  const getPriorityBadge = (priority: string) => {
    const colors = {
      urgent: 'bg-red-100 dark:bg-red-900/20 text-red-700 dark:text-red-400',
      high: 'bg-orange-100 dark:bg-orange-900/20 text-orange-700 dark:text-orange-400',
      medium: 'bg-blue-100 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400',
      low: 'bg-gray-100 dark:bg-gray-900/20 text-gray-700 dark:text-gray-400',
    };

    return (
      <span className={cn(
        'px-2 py-0.5 text-xs rounded-full font-medium capitalize',
        colors[priority as keyof typeof colors] || colors.low
      )}>
        {priority}
      </span>
    );
  };

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div>
          <h2 className="text-2xl font-bold text-gray-900 dark:text-white">
            Notifications
          </h2>
          <p className="mt-1 text-gray-600 dark:text-gray-300">
            Manage your notification preferences and view alerts
          </p>
        </div>
        <div className="flex items-center space-x-2">
          {unreadCount > 0 && (
            <span className="px-3 py-1 text-sm bg-red-100 dark:bg-red-900/20 text-red-600 dark:text-red-400 rounded-full">
              {unreadCount} Unread
            </span>
          )}
        </div>
      </div>

      {/* Tabs */}
      <div className="border-b border-gray-200 dark:border-gray-700">
        <nav className="-mb-px flex space-x-8">
          <button
            onClick={() => setActiveTab('notifications')}
            className={cn(
              'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm',
              activeTab === 'notifications'
                ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'
            )}
          >
            <BellIcon className="h-4 w-4 inline mr-2" />
            Notifications ({notifications.length})
          </button>
          <button
            onClick={() => setActiveTab('preferences')}
            className={cn(
              'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm',
              activeTab === 'preferences'
                ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'
            )}
          >
            <Cog6ToothIcon className="h-4 w-4 inline mr-2" />
            Preferences
          </button>
        </nav>
      </div>

      {/* Content */}
      <AnimatePresence mode="wait">
        {activeTab === 'notifications' ? (
          <motion.div
            key="notifications"
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            exit={{ opacity: 0, y: -20 }}
            transition={{ duration: 0.2 }}
            className="space-y-4"
          >
            {/* Notification Controls */}
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
                {unreadCount > 0 && (
                  <Button
                    variant="outline"
                    size="sm"
                    onClick={onMarkAllAsRead}
                  >
                    Mark All Read
                  </Button>
                )}
                <Button
                  variant="outline"
                  size="sm"
                  onClick={onClearAll}
                  className="text-red-600 hover:text-red-700"
                >
                  Clear All
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
                        placeholder="Search notifications..."
                        className="w-full pl-10 pr-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                      />
                    </div>

                    {/* Type Filter */}
                    <select
                      value={filterType}
                      onChange={(e) => setFilterType(e.target.value)}
                      className="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                    >
                      <option value="all">All Types</option>
                      <option value="price_drop">Price Drops</option>
                      <option value="new_event">New Events</option>
                      <option value="availability_change">Availability Changes</option>
                      <option value="followed_team">Team Updates</option>
                      <option value="system">System</option>
                    </select>

                    {/* Read Status Filter */}
                    <select
                      value={filterRead}
                      onChange={(e) => setFilterRead(e.target.value as any)}
                      className="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                    >
                      <option value="all">All Notifications</option>
                      <option value="unread">Unread Only</option>
                      <option value="read">Read Only</option>
                    </select>

                    {/* Clear Filters */}
                    <Button
                      variant="outline"
                      size="sm"
                      onClick={() => {
                        setSearchQuery('');
                        setFilterType('all');
                        setFilterRead('all');
                      }}
                    >
                      Clear Filters
                    </Button>
                  </div>
                </motion.div>
              )}
            </AnimatePresence>

            {/* Notifications List */}
            <div className="space-y-2">
              {filteredNotifications.length === 0 ? (
                <div className="text-center py-12">
                  <BellIcon className="mx-auto h-12 w-12 text-gray-400" />
                  <h3 className="mt-4 text-lg font-medium text-gray-900 dark:text-white">
                    No notifications found
                  </h3>
                  <p className="mt-1 text-gray-600 dark:text-gray-300">
                    {notifications.length === 0 
                      ? 'You\'re all caught up! New notifications will appear here.'
                      : 'Try adjusting your filters to see more notifications.'}
                  </p>
                </div>
              ) : (
                filteredNotifications.map((notification, index) => (
                  <NotificationCard
                    key={notification.id}
                    notification={notification}
                    onMarkAsRead={onMarkAsRead}
                    onDelete={onDeleteNotification}
                    onAction={onNotificationAction}
                    index={index}
                  />
                ))
              )}
            </div>
          </motion.div>
        ) : (
          <motion.div
            key="preferences"
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            exit={{ opacity: 0, y: -20 }}
            transition={{ duration: 0.2 }}
          >
            <NotificationPreferencesPanel
              preferences={preferences}
              onUpdate={onUpdatePreferences}
            />
          </motion.div>
        )}
      </AnimatePresence>
    </div>
  );
};

// Notification Card Component
interface NotificationCardProps {
  notification: Notification;
  onMarkAsRead?: (id: string) => void;
  onDelete?: (id: string) => void;
  onAction?: (notification: Notification) => void;
  index: number;
}

const NotificationCard: React.FC<NotificationCardProps> = ({
  notification,
  onMarkAsRead,
  onDelete,
  onAction,
  index,
}) => {
  const [showActions, setShowActions] = useState(false);

  const getNotificationIcon = (type: string, priority: string) => {
    const iconClass = priority === 'urgent' ? 'text-red-500' : 
                     priority === 'high' ? 'text-orange-500' :
                     priority === 'medium' ? 'text-blue-500' : 'text-gray-500';

    switch (type) {
      case 'price_drop':
        return <FireIcon className={cn('h-5 w-5', iconClass)} />;
      case 'new_event':
        return <CalendarDaysIcon className={cn('h-5 w-5', iconClass)} />;
      case 'availability_change':
        return <ExclamationCircleIcon className={cn('h-5 w-5', iconClass)} />;
      case 'followed_team':
        return <UserGroupIcon className={cn('h-5 w-5', iconClass)} />;
      case 'system':
        return <InformationCircleIcon className={cn('h-5 w-5', iconClass)} />;
      default:
        return <BellIcon className={cn('h-5 w-5', iconClass)} />;
    }
  };

  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ delay: index * 0.05 }}
      className={cn(
        'bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 transition-all hover:shadow-md',
        !notification.read && 'ring-2 ring-blue-500/20 border-blue-500/50'
      )}
    >
      <div className="flex items-start space-x-3">
        {/* Icon */}
        <div className="flex-shrink-0 mt-0.5">
          {getNotificationIcon(notification.type, notification.priority)}
        </div>

        {/* Content */}
        <div className="flex-1 min-w-0">
          <div className="flex items-start justify-between">
            <div className="flex-1">
              <div className="flex items-center space-x-2">
                <h3 className={cn(
                  'text-sm font-medium',
                  notification.read 
                    ? 'text-gray-600 dark:text-gray-300' 
                    : 'text-gray-900 dark:text-white'
                )}>
                  {notification.title}
                </h3>
                {!notification.read && (
                  <div className="w-2 h-2 bg-blue-500 rounded-full" />
                )}
              </div>
              
              <p className={cn(
                'mt-1 text-sm',
                notification.read
                  ? 'text-gray-500 dark:text-gray-400'
                  : 'text-gray-700 dark:text-gray-300'
              )}>
                {notification.message}
              </p>

              {/* Event Details */}
              {notification.eventName && (
                <div className="mt-2 flex items-center space-x-4 text-xs text-gray-500 dark:text-gray-400">
                  {notification.venue && (
                    <div className="flex items-center space-x-1">
                      <MapPinIcon className="h-3 w-3" />
                      <span>{notification.venue}</span>
                    </div>
                  )}
                  {notification.originalPrice && notification.newPrice && (
                    <div className="flex items-center space-x-1">
                      <CurrencyPoundIcon className="h-3 w-3" />
                      <span className="line-through">{formatCurrency(notification.originalPrice)}</span>
                      <span className="font-medium text-green-600">
                        {formatCurrency(notification.newPrice)}
                      </span>
                      {notification.discount && (
                        <span className="text-green-600">(-{notification.discount}%)</span>
                      )}
                    </div>
                  )}
                </div>
              )}

              <div className="mt-2 flex items-center justify-between">
                <div className="flex items-center space-x-2">
                  <span className="text-xs text-gray-500 dark:text-gray-400">
                    {formatDate(notification.timestamp, { relative: true })}
                  </span>
                  {/* Priority badge for high/urgent */}
                  {(notification.priority === 'high' || notification.priority === 'urgent') && (
                    <span className={cn(
                      'px-2 py-0.5 text-xs rounded-full font-medium',
                      notification.priority === 'urgent' 
                        ? 'bg-red-100 dark:bg-red-900/20 text-red-700 dark:text-red-400'
                        : 'bg-orange-100 dark:bg-orange-900/20 text-orange-700 dark:text-orange-400'
                    )}>
                      {notification.priority}
                    </span>
                  )}
                </div>
                
                {/* Actions */}
                <div className="flex items-center space-x-1">
                  {!notification.read && onMarkAsRead && (
                    <Button
                      size="xs"
                      variant="ghost"
                      onClick={() => onMarkAsRead(notification.id)}
                      className="text-blue-600 hover:text-blue-700"
                    >
                      Mark Read
                    </Button>
                  )}
                  
                  {notification.actionUrl && onAction && (
                    <Button
                      size="xs"
                      variant="outline"
                      onClick={() => onAction(notification)}
                      leftIcon={<EyeIcon className="h-3 w-3" />}
                    >
                      View
                    </Button>
                  )}
                  
                  {onDelete && (
                    <Button
                      size="xs"
                      variant="ghost"
                      onClick={() => onDelete(notification.id)}
                      className="text-gray-500 hover:text-red-600"
                      leftIcon={<TrashIcon className="h-3 w-3" />}
                    />
                  )}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </motion.div>
  );
};

// Notification Preferences Panel Component
interface NotificationPreferencesPanelProps {
  preferences: NotificationPreferences;
  onUpdate?: (preferences: NotificationPreferences) => void;
}

const NotificationPreferencesPanel: React.FC<NotificationPreferencesPanelProps> = ({
  preferences,
  onUpdate,
}) => {
  const [localPreferences, setLocalPreferences] = useState(preferences);
  const [hasChanges, setHasChanges] = useState(false);

  useEffect(() => {
    const hasChanged = JSON.stringify(preferences) !== JSON.stringify(localPreferences);
    setHasChanges(hasChanged);
  }, [preferences, localPreferences]);

  const handleSave = () => {
    onUpdate?.(localPreferences);
    setHasChanges(false);
  };

  const handleReset = () => {
    setLocalPreferences(preferences);
    setHasChanges(false);
  };

  const updatePreference = (section: keyof NotificationPreferences, key: string, value: any) => {
    setLocalPreferences(prev => ({
      ...prev,
      [section]: {
        ...prev[section],
        [key]: value,
      },
    }));
  };

  return (
    <div className="space-y-6">
      {/* Save/Reset Actions */}
      {hasChanges && (
        <motion.div
          initial={{ opacity: 0, y: -20 }}
          animate={{ opacity: 1, y: 0 }}
          className="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4"
        >
          <div className="flex items-center justify-between">
            <div className="flex items-center space-x-2">
              <InformationCircleIcon className="h-5 w-5 text-blue-600 dark:text-blue-400" />
              <span className="text-sm text-blue-800 dark:text-blue-200">
                You have unsaved changes
              </span>
            </div>
            <div className="flex items-center space-x-2">
              <Button variant="outline" size="sm" onClick={handleReset}>
                Reset
              </Button>
              <Button size="sm" onClick={handleSave}>
                Save Changes
              </Button>
            </div>
          </div>
        </motion.div>
      )}

      {/* Email Preferences */}
      <div className="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
        <div className="flex items-center space-x-3 mb-4">
          <EnvelopeIcon className="h-5 w-5 text-gray-600 dark:text-gray-400" />
          <h3 className="text-lg font-medium text-gray-900 dark:text-white">
            Email Notifications
          </h3>
        </div>

        <div className="space-y-4">
          <div className="flex items-center justify-between">
            <span className="text-sm text-gray-700 dark:text-gray-300">
              Enable email notifications
            </span>
            <label className="relative inline-flex items-center cursor-pointer">
              <input
                type="checkbox"
                checked={localPreferences.email.enabled}
                onChange={(e) => updatePreference('email', 'enabled', e.target.checked)}
                className="sr-only"
              />
              <div className={cn(
                'w-11 h-6 rounded-full transition-colors',
                localPreferences.email.enabled ? 'bg-blue-600' : 'bg-gray-300 dark:bg-gray-600'
              )}>
                <div className={cn(
                  'w-5 h-5 bg-white rounded-full shadow transform transition-transform',
                  localPreferences.email.enabled ? 'translate-x-5' : 'translate-x-0'
                )} />
              </div>
            </label>
          </div>

          {localPreferences.email.enabled && (
            <div className="space-y-3 pl-4 border-l-2 border-gray-200 dark:border-gray-700">
              {Object.entries({
                priceDrops: 'Price drops',
                newEvents: 'New events',
                availabilityChanges: 'Availability changes',
                followedTeams: 'Followed teams updates',
                systemUpdates: 'System updates',
              }).map(([key, label]) => (
                <div key={key} className="flex items-center justify-between">
                  <span className="text-sm text-gray-600 dark:text-gray-400">{label}</span>
                  <input
                    type="checkbox"
                    checked={localPreferences.email[key as keyof typeof localPreferences.email] as boolean}
                    onChange={(e) => updatePreference('email', key, e.target.checked)}
                    className="h-4 w-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500"
                  />
                </div>
              ))}
              
              <div className="flex items-center justify-between">
                <span className="text-sm text-gray-600 dark:text-gray-400">Frequency</span>
                <select
                  value={localPreferences.email.frequency}
                  onChange={(e) => updatePreference('email', 'frequency', e.target.value)}
                  className="text-sm border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                >
                  <option value="instant">Instant</option>
                  <option value="hourly">Hourly</option>
                  <option value="daily">Daily</option>
                  <option value="weekly">Weekly</option>
                </select>
              </div>
            </div>
          )}
        </div>
      </div>

      {/* Push Notifications */}
      <div className="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
        <div className="flex items-center space-x-3 mb-4">
          <ComputerDesktopIcon className="h-5 w-5 text-gray-600 dark:text-gray-400" />
          <h3 className="text-lg font-medium text-gray-900 dark:text-white">
            Push Notifications
          </h3>
        </div>

        <div className="space-y-4">
          <div className="flex items-center justify-between">
            <span className="text-sm text-gray-700 dark:text-gray-300">
              Enable push notifications
            </span>
            <label className="relative inline-flex items-center cursor-pointer">
              <input
                type="checkbox"
                checked={localPreferences.push.enabled}
                onChange={(e) => updatePreference('push', 'enabled', e.target.checked)}
                className="sr-only"
              />
              <div className={cn(
                'w-11 h-6 rounded-full transition-colors',
                localPreferences.push.enabled ? 'bg-blue-600' : 'bg-gray-300 dark:bg-gray-600'
              )}>
                <div className={cn(
                  'w-5 h-5 bg-white rounded-full shadow transform transition-transform',
                  localPreferences.push.enabled ? 'translate-x-5' : 'translate-x-0'
                )} />
              </div>
            </label>
          </div>

          {localPreferences.push.enabled && (
            <div className="space-y-3 pl-4 border-l-2 border-gray-200 dark:border-gray-700">
              {Object.entries({
                priceDrops: 'Price drops',
                newEvents: 'New events',
                availabilityChanges: 'Availability changes',
                followedTeams: 'Followed teams updates',
                systemUpdates: 'System updates',
              }).map(([key, label]) => (
                <div key={key} className="flex items-center justify-between">
                  <span className="text-sm text-gray-600 dark:text-gray-400">{label}</span>
                  <input
                    type="checkbox"
                    checked={localPreferences.push[key as keyof typeof localPreferences.push] as boolean}
                    onChange={(e) => updatePreference('push', key, e.target.checked)}
                    className="h-4 w-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500"
                  />
                </div>
              ))}
            </div>
          )}
        </div>
      </div>

      {/* SMS Notifications */}
      <div className="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
        <div className="flex items-center space-x-3 mb-4">
          <DevicePhoneMobileIcon className="h-5 w-5 text-gray-600 dark:text-gray-400" />
          <h3 className="text-lg font-medium text-gray-900 dark:text-white">
            SMS Notifications
          </h3>
        </div>

        <div className="space-y-4">
          <div className="flex items-center justify-between">
            <span className="text-sm text-gray-700 dark:text-gray-300">
              Enable SMS notifications
            </span>
            <label className="relative inline-flex items-center cursor-pointer">
              <input
                type="checkbox"
                checked={localPreferences.sms.enabled}
                onChange={(e) => updatePreference('sms', 'enabled', e.target.checked)}
                className="sr-only"
              />
              <div className={cn(
                'w-11 h-6 rounded-full transition-colors',
                localPreferences.sms.enabled ? 'bg-blue-600' : 'bg-gray-300 dark:bg-gray-600'
              )}>
                <div className={cn(
                  'w-5 h-5 bg-white rounded-full shadow transform transition-transform',
                  localPreferences.sms.enabled ? 'translate-x-5' : 'translate-x-0'
                )} />
              </div>
            </label>
          </div>

          {localPreferences.sms.enabled && (
            <div className="space-y-3 pl-4 border-l-2 border-gray-200 dark:border-gray-700">
              <div>
                <label className="block text-sm text-gray-600 dark:text-gray-400 mb-2">
                  Phone Number
                </label>
                <input
                  type="tel"
                  value={localPreferences.sms.phoneNumber || ''}
                  onChange={(e) => updatePreference('sms', 'phoneNumber', e.target.value)}
                  placeholder="+44 7XXX XXXXXX"
                  className="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                />
              </div>
              
              <div className="flex items-center justify-between">
                <span className="text-sm text-gray-600 dark:text-gray-400">Price drops only</span>
                <input
                  type="checkbox"
                  checked={localPreferences.sms.priceDrops}
                  onChange={(e) => updatePreference('sms', 'priceDrops', e.target.checked)}
                  className="h-4 w-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500"
                />
              </div>
              
              <div className="flex items-center justify-between">
                <span className="text-sm text-gray-600 dark:text-gray-400">Urgent notifications only</span>
                <input
                  type="checkbox"
                  checked={localPreferences.sms.urgentOnly}
                  onChange={(e) => updatePreference('sms', 'urgentOnly', e.target.checked)}
                  className="h-4 w-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500"
                />
              </div>
            </div>
          )}
        </div>
      </div>

      {/* In-App Preferences */}
      <div className="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
        <div className="flex items-center space-x-3 mb-4">
          <BellIcon className="h-5 w-5 text-gray-600 dark:text-gray-400" />
          <h3 className="text-lg font-medium text-gray-900 dark:text-white">
            In-App Notifications
          </h3>
        </div>

        <div className="space-y-4">
          {Object.entries({
            enabled: 'Enable in-app notifications',
            showBadge: 'Show notification badge',
            sound: 'Play notification sound',
            desktop: 'Show desktop notifications',
          }).map(([key, label]) => (
            <div key={key} className="flex items-center justify-between">
              <span className="text-sm text-gray-700 dark:text-gray-300">{label}</span>
              <label className="relative inline-flex items-center cursor-pointer">
                <input
                  type="checkbox"
                  checked={localPreferences.inApp[key as keyof typeof localPreferences.inApp] as boolean}
                  onChange={(e) => updatePreference('inApp', key, e.target.checked)}
                  className="sr-only"
                />
                <div className={cn(
                  'w-11 h-6 rounded-full transition-colors',
                  localPreferences.inApp[key as keyof typeof localPreferences.inApp] ? 'bg-blue-600' : 'bg-gray-300 dark:bg-gray-600'
                )}>
                  <div className={cn(
                    'w-5 h-5 bg-white rounded-full shadow transform transition-transform',
                    localPreferences.inApp[key as keyof typeof localPreferences.inApp] ? 'translate-x-5' : 'translate-x-0'
                  )} />
                </div>
              </label>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
};

export default NotificationSystem;