/**
 * HD Tickets - Price Alert System
 * Inspired by TicketScoutie.com - Comprehensive price monitoring and alerting
 */

import React, { useState, useEffect } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import {
  BellIcon,
  PlusIcon,
  XMarkIcon,
  ChartBarIcon,
  TrendingDownIcon,
  TrendingUpIcon,
  ExclamationTriangleIcon,
  CheckCircleIcon,
  ClockIcon,
  CurrencyPoundIcon,
  AdjustmentsHorizontalIcon,
  InformationCircleIcon,
} from '@heroicons/react/24/outline';
import {
  BellIcon as BellSolidIcon,
} from '@heroicons/react/24/solid';

import { cn, formatCurrency, formatDate } from '../../utils/design';
import Button from '../ui/Button';
import LoadingSpinner from '../ui/LoadingSpinner';

interface PriceAlert {
  id: number;
  eventId: number;
  eventTitle: string;
  venue: string;
  date: string;
  targetPrice: number;
  currentPrice: number;
  isActive: boolean;
  alertType: 'price_drop' | 'percentage_drop' | 'availability';
  conditions: {
    targetPrice?: number;
    percentageDrop?: number;
    availabilityThreshold?: number;
  };
  notifications: {
    email: boolean;
    push: boolean;
    sms: boolean;
  };
  createdAt: string;
  triggeredAt?: string;
  triggerCount: number;
  lastTriggered?: string;
}

interface PriceAlertSystemProps {
  eventId?: number;
  onClose?: () => void;
}

const mockAlerts: PriceAlert[] = [
  {
    id: 1,
    eventId: 1,
    eventTitle: 'Manchester United vs Liverpool',
    venue: 'Old Trafford',
    date: '2025-11-15T15:00:00Z',
    targetPrice: 89,
    currentPrice: 125,
    isActive: true,
    alertType: 'price_drop',
    conditions: { targetPrice: 89 },
    notifications: { email: true, push: true, sms: false },
    createdAt: '2025-10-01T10:00:00Z',
    triggerCount: 0,
  },
  {
    id: 2,
    eventId: 2,
    eventTitle: 'Arsenal vs Chelsea',
    venue: 'Emirates Stadium',
    date: '2025-11-22T17:30:00Z',
    targetPrice: 120,
    currentPrice: 125,
    isActive: true,
    alertType: 'price_drop',
    conditions: { targetPrice: 120 },
    notifications: { email: true, push: true, sms: true },
    createdAt: '2025-09-28T14:30:00Z',
    triggeredAt: '2025-10-05T09:15:00Z',
    triggerCount: 1,
    lastTriggered: '2025-10-05T09:15:00Z',
  },
  {
    id: 3,
    eventId: 1,
    eventTitle: 'Manchester United vs Liverpool',
    venue: 'Old Trafford',
    date: '2025-11-15T15:00:00Z',
    targetPrice: 0,
    currentPrice: 125,
    isActive: true,
    alertType: 'percentage_drop',
    conditions: { percentageDrop: 20 },
    notifications: { email: true, push: false, sms: false },
    createdAt: '2025-10-02T16:45:00Z',
    triggerCount: 0,
  },
];

const PriceAlertSystem: React.FC<PriceAlertSystemProps> = ({ eventId, onClose }) => {
  const [alerts, setAlerts] = useState<PriceAlert[]>(mockAlerts);
  const [showCreateForm, setShowCreateForm] = useState(false);
  const [selectedAlert, setSelectedAlert] = useState<PriceAlert | null>(null);
  const [activeTab, setActiveTab] = useState<'active' | 'triggered' | 'all'>('active');
  
  // Filter alerts based on eventId if provided
  const filteredAlerts = eventId 
    ? alerts.filter(alert => alert.eventId === eventId)
    : alerts;

  // Filter by tab
  const tabFilteredAlerts = filteredAlerts.filter(alert => {
    switch (activeTab) {
      case 'active':
        return alert.isActive && alert.triggerCount === 0;
      case 'triggered':
        return alert.triggerCount > 0;
      default:
        return true;
    }
  });

  const toggleAlert = (alertId: number) => {
    setAlerts(alerts => 
      alerts.map(alert =>
        alert.id === alertId 
          ? { ...alert, isActive: !alert.isActive }
          : alert
      )
    );
  };

  const deleteAlert = (alertId: number) => {
    setAlerts(alerts => alerts.filter(alert => alert.id !== alertId));
  };

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div>
          <h2 className="text-2xl font-bold text-gray-900 dark:text-white">
            Price Alerts
          </h2>
          <p className="mt-1 text-gray-600 dark:text-gray-300">
            Get notified when ticket prices drop to your target
          </p>
        </div>
        <div className="flex items-center space-x-3">
          <Button
            variant="outline"
            size="sm"
            leftIcon={<AdjustmentsHorizontalIcon className="h-4 w-4" />}
          >
            Settings
          </Button>
          <Button
            variant="primary"
            size="sm"
            leftIcon={<PlusIcon className="h-4 w-4" />}
            onClick={() => setShowCreateForm(true)}
          >
            New Alert
          </Button>
          {onClose && (
            <Button
              variant="ghost"
              size="sm"
              onClick={onClose}
              leftIcon={<XMarkIcon className="h-4 w-4" />}
            >
              Close
            </Button>
          )}
        </div>
      </div>

      {/* Stats Overview */}
      <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div className="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
          <div className="flex items-center">
            <BellSolidIcon className="h-5 w-5 text-blue-600" />
            <span className="ml-2 text-sm font-medium text-gray-600 dark:text-gray-400">
              Active Alerts
            </span>
          </div>
          <div className="mt-2 text-2xl font-bold text-gray-900 dark:text-white">
            {filteredAlerts.filter(a => a.isActive && a.triggerCount === 0).length}
          </div>
        </div>
        
        <div className="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
          <div className="flex items-center">
            <CheckCircleIcon className="h-5 w-5 text-green-600" />
            <span className="ml-2 text-sm font-medium text-gray-600 dark:text-gray-400">
              Triggered
            </span>
          </div>
          <div className="mt-2 text-2xl font-bold text-gray-900 dark:text-white">
            {filteredAlerts.filter(a => a.triggerCount > 0).length}
          </div>
        </div>
        
        <div className="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
          <div className="flex items-center">
            <TrendingDownIcon className="h-5 w-5 text-purple-600" />
            <span className="ml-2 text-sm font-medium text-gray-600 dark:text-gray-400">
              Avg. Target
            </span>
          </div>
          <div className="mt-2 text-2xl font-bold text-gray-900 dark:text-white">
            {formatCurrency(
              filteredAlerts.reduce((sum, alert) => sum + (alert.conditions.targetPrice || 0), 0) / 
              Math.max(filteredAlerts.filter(a => a.conditions.targetPrice).length, 1)
            )}
          </div>
        </div>
        
        <div className="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
          <div className="flex items-center">
            <ClockIcon className="h-5 w-5 text-yellow-600" />
            <span className="ml-2 text-sm font-medium text-gray-600 dark:text-gray-400">
              This Week
            </span>
          </div>
          <div className="mt-2 text-2xl font-bold text-gray-900 dark:text-white">
            {filteredAlerts.filter(a => 
              a.lastTriggered && 
              new Date(a.lastTriggered) > new Date(Date.now() - 7 * 24 * 60 * 60 * 1000)
            ).length}
          </div>
        </div>
      </div>

      {/* Tabs */}
      <div className="flex space-x-1 bg-gray-100 dark:bg-gray-700 rounded-lg p-1 w-fit">
        {[
          { key: 'active', label: 'Active', count: filteredAlerts.filter(a => a.isActive && a.triggerCount === 0).length },
          { key: 'triggered', label: 'Triggered', count: filteredAlerts.filter(a => a.triggerCount > 0).length },
          { key: 'all', label: 'All', count: filteredAlerts.length },
        ].map((tab) => (
          <button
            key={tab.key}
            onClick={() => setActiveTab(tab.key as any)}
            className={cn(
              'px-4 py-2 text-sm font-medium rounded-md transition-colors',
              activeTab === tab.key
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

      {/* Alerts List */}
      <div className="space-y-4">
        <AnimatePresence>
          {tabFilteredAlerts.map((alert) => (
            <motion.div
              key={alert.id}
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              exit={{ opacity: 0, y: -20 }}
              transition={{ duration: 0.3 }}
            >
              <PriceAlertCard
                alert={alert}
                onToggle={toggleAlert}
                onDelete={deleteAlert}
                onEdit={(alert) => setSelectedAlert(alert)}
              />
            </motion.div>
          ))}
        </AnimatePresence>

        {tabFilteredAlerts.length === 0 && (
          <div className="text-center py-12 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
            <BellIcon className="mx-auto h-12 w-12 text-gray-400" />
            <h3 className="mt-2 text-sm font-medium text-gray-900 dark:text-white">
              No alerts found
            </h3>
            <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">
              {activeTab === 'active' && 'Create your first price alert to get started.'}
              {activeTab === 'triggered' && 'No alerts have been triggered yet.'}
              {activeTab === 'all' && 'Create your first price alert to get started.'}
            </p>
            <div className="mt-6">
              <Button 
                variant="primary" 
                onClick={() => setShowCreateForm(true)}
                leftIcon={<PlusIcon className="h-4 w-4" />}
              >
                Create Alert
              </Button>
            </div>
          </div>
        )}
      </div>

      {/* Create/Edit Alert Modal */}
      <AnimatePresence>
        {(showCreateForm || selectedAlert) && (
          <CreateAlertModal
            alert={selectedAlert}
            eventId={eventId}
            onClose={() => {
              setShowCreateForm(false);
              setSelectedAlert(null);
            }}
            onSave={(alertData) => {
              if (selectedAlert) {
                // Update existing alert
                setAlerts(alerts =>
                  alerts.map(alert =>
                    alert.id === selectedAlert.id
                      ? { ...alert, ...alertData }
                      : alert
                  )
                );
              } else {
                // Create new alert
                const newAlert: PriceAlert = {
                  ...alertData,
                  id: Date.now(),
                  createdAt: new Date().toISOString(),
                  triggerCount: 0,
                  isActive: true,
                };
                setAlerts(alerts => [...alerts, newAlert]);
              }
              setShowCreateForm(false);
              setSelectedAlert(null);
            }}
          />
        )}
      </AnimatePresence>
    </div>
  );
};

// Price Alert Card Component
interface PriceAlertCardProps {
  alert: PriceAlert;
  onToggle: (alertId: number) => void;
  onDelete: (alertId: number) => void;
  onEdit: (alert: PriceAlert) => void;
}

const PriceAlertCard: React.FC<PriceAlertCardProps> = ({ alert, onToggle, onDelete, onEdit }) => {
  const isTriggered = alert.triggerCount > 0;
  const isPriceMet = alert.conditions.targetPrice && alert.currentPrice <= alert.conditions.targetPrice;
  
  const getAlertStatus = () => {
    if (isTriggered) return { text: 'Triggered', color: 'text-green-600 bg-green-100 dark:bg-green-900/20' };
    if (!alert.isActive) return { text: 'Paused', color: 'text-gray-600 bg-gray-100 dark:bg-gray-900/20' };
    if (isPriceMet) return { text: 'Target Met', color: 'text-blue-600 bg-blue-100 dark:bg-blue-900/20' };
    return { text: 'Monitoring', color: 'text-yellow-600 bg-yellow-100 dark:bg-yellow-900/20' };
  };

  const status = getAlertStatus();

  return (
    <div className={cn(
      'bg-white dark:bg-gray-800 rounded-lg border p-6 transition-all duration-200',
      isTriggered 
        ? 'border-green-200 dark:border-green-800 shadow-md' 
        : 'border-gray-200 dark:border-gray-700 hover:shadow-sm'
    )}>
      <div className="flex items-start justify-between mb-4">
        <div className="flex-1">
          <div className="flex items-center space-x-3 mb-2">
            <h3 className="text-lg font-semibold text-gray-900 dark:text-white">
              {alert.eventTitle}
            </h3>
            <span className={cn('px-2 py-1 text-xs font-medium rounded-full', status.color)}>
              {status.text}
            </span>
          </div>
          <div className="flex items-center space-x-4 text-sm text-gray-600 dark:text-gray-400">
            <span>{alert.venue}</span>
            <span>•</span>
            <span>{formatDate(alert.date, 'short')}</span>
          </div>
        </div>
        
        <div className="flex items-center space-x-2">
          <button
            onClick={() => onEdit(alert)}
            className="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
          >
            <AdjustmentsHorizontalIcon className="h-4 w-4" />
          </button>
          <button
            onClick={() => onDelete(alert.id)}
            className="p-2 text-gray-400 hover:text-red-500 transition-colors"
          >
            <XMarkIcon className="h-4 w-4" />
          </button>
        </div>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
        <div className="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3">
          <div className="flex items-center space-x-2 mb-1">
            <span className="text-sm font-medium text-gray-900 dark:text-white">Alert Type</span>
          </div>
          <div className="text-sm text-gray-600 dark:text-gray-400">
            {alert.alertType === 'price_drop' && `Price drops to ${formatCurrency(alert.conditions.targetPrice!)}`}
            {alert.alertType === 'percentage_drop' && `Price drops by ${alert.conditions.percentageDrop}%`}
            {alert.alertType === 'availability' && `Availability reaches ${alert.conditions.availabilityThreshold}`}
          </div>
        </div>

        <div className="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3">
          <div className="flex items-center justify-between mb-1">
            <span className="text-sm font-medium text-gray-900 dark:text-white">Current Price</span>
            {alert.conditions.targetPrice && (
              <div className={cn(
                'flex items-center text-xs',
                alert.currentPrice <= alert.conditions.targetPrice ? 'text-green-600' : 'text-red-600'
              )}>
                {alert.currentPrice <= alert.conditions.targetPrice ? (
                  <TrendingDownIcon className="h-3 w-3 mr-1" />
                ) : (
                  <TrendingUpIcon className="h-3 w-3 mr-1" />
                )}
                {alert.conditions.targetPrice && (
                  <span>
                    {((alert.currentPrice - alert.conditions.targetPrice) / alert.conditions.targetPrice * 100).toFixed(1)}%
                  </span>
                )}
              </div>
            )}
          </div>
          <div className="text-lg font-bold text-gray-900 dark:text-white">
            {formatCurrency(alert.currentPrice)}
          </div>
        </div>

        <div className="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3">
          <span className="text-sm font-medium text-gray-900 dark:text-white">Notifications</span>
          <div className="flex items-center space-x-3 mt-1">
            {alert.notifications.email && (
              <span className="text-xs bg-blue-100 dark:bg-blue-900/20 text-blue-600 px-2 py-1 rounded">
                Email
              </span>
            )}
            {alert.notifications.push && (
              <span className="text-xs bg-green-100 dark:bg-green-900/20 text-green-600 px-2 py-1 rounded">
                Push
              </span>
            )}
            {alert.notifications.sms && (
              <span className="text-xs bg-purple-100 dark:bg-purple-900/20 text-purple-600 px-2 py-1 rounded">
                SMS
              </span>
            )}
          </div>
        </div>
      </div>

      {/* Activity Status */}
      {alert.lastTriggered && (
        <div className="bg-green-50 dark:bg-green-900/10 border border-green-200 dark:border-green-800 rounded-lg p-3 mb-4">
          <div className="flex items-center space-x-2">
            <CheckCircleIcon className="h-4 w-4 text-green-600" />
            <span className="text-sm font-medium text-green-800 dark:text-green-200">
              Alert triggered {alert.triggerCount} time{alert.triggerCount !== 1 ? 's' : ''}
            </span>
          </div>
          <p className="text-xs text-green-600 dark:text-green-400 mt-1">
            Last triggered: {formatDate(alert.lastTriggered, 'long')}
          </p>
        </div>
      )}

      {/* Toggle */}
      <div className="flex items-center justify-between">
        <div className="flex items-center space-x-3">
          <span className="text-sm text-gray-600 dark:text-gray-400">
            Created {formatDate(alert.createdAt, 'short')}
          </span>
        </div>
        
        <label className="flex items-center cursor-pointer">
          <input
            type="checkbox"
            checked={alert.isActive}
            onChange={() => onToggle(alert.id)}
            className="sr-only"
          />
          <div className={cn(
            'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2',
            alert.isActive ? 'bg-blue-600' : 'bg-gray-200 dark:bg-gray-600'
          )}>
            <span className={cn(
              'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
              alert.isActive ? 'translate-x-5' : 'translate-x-0'
            )} />
          </div>
          <span className="ml-3 text-sm font-medium text-gray-900 dark:text-white">
            {alert.isActive ? 'Active' : 'Paused'}
          </span>
        </label>
      </div>
    </div>
  );
};

// Create/Edit Alert Modal Component  
interface CreateAlertModalProps {
  alert?: PriceAlert | null;
  eventId?: number;
  onClose: () => void;
  onSave: (alertData: Partial<PriceAlert>) => void;
}

const CreateAlertModal: React.FC<CreateAlertModalProps> = ({ alert, eventId, onClose, onSave }) => {
  const [formData, setFormData] = useState({
    eventTitle: alert?.eventTitle || '',
    venue: alert?.venue || '',
    date: alert?.date || '',
    alertType: alert?.alertType || 'price_drop' as const,
    targetPrice: alert?.conditions.targetPrice || 0,
    percentageDrop: alert?.conditions.percentageDrop || 10,
    availabilityThreshold: alert?.conditions.availabilityThreshold || 100,
    notifications: alert?.notifications || {
      email: true,
      push: true,
      sms: false,
    },
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    
    const alertData: Partial<PriceAlert> = {
      eventId: eventId || alert?.eventId || 1,
      eventTitle: formData.eventTitle,
      venue: formData.venue,
      date: formData.date,
      alertType: formData.alertType,
      conditions: {
        targetPrice: formData.alertType === 'price_drop' ? formData.targetPrice : undefined,
        percentageDrop: formData.alertType === 'percentage_drop' ? formData.percentageDrop : undefined,
        availabilityThreshold: formData.alertType === 'availability' ? formData.availabilityThreshold : undefined,
      },
      notifications: formData.notifications,
      currentPrice: 125, // Mock current price
    };
    
    onSave(alertData);
  };

  return (
    <motion.div
      initial={{ opacity: 0 }}
      animate={{ opacity: 1 }}
      exit={{ opacity: 0 }}
      className="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4"
    >
      <motion.div
        initial={{ scale: 0.95, opacity: 0 }}
        animate={{ scale: 1, opacity: 1 }}
        exit={{ scale: 0.95, opacity: 0 }}
        className="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto"
      >
        <div className="p-6">
          <div className="flex items-center justify-between mb-6">
            <h3 className="text-lg font-semibold text-gray-900 dark:text-white">
              {alert ? 'Edit Price Alert' : 'Create Price Alert'}
            </h3>
            <button
              onClick={onClose}
              className="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
            >
              <XMarkIcon className="h-5 w-5" />
            </button>
          </div>

          <form onSubmit={handleSubmit} className="space-y-6">
            {/* Event Details */}
            {!eventId && (
              <div className="space-y-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Event
                  </label>
                  <input
                    type="text"
                    value={formData.eventTitle}
                    onChange={(e) => setFormData({ ...formData, eventTitle: e.target.value })}
                    className="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                    placeholder="e.g., Manchester United vs Liverpool"
                    required
                  />
                </div>
                
                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                      Venue
                    </label>
                    <input
                      type="text"
                      value={formData.venue}
                      onChange={(e) => setFormData({ ...formData, venue: e.target.value })}
                      className="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                      placeholder="e.g., Old Trafford"
                      required
                    />
                  </div>
                  
                  <div>
                    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                      Date
                    </label>
                    <input
                      type="datetime-local"
                      value={formData.date ? formData.date.slice(0, 16) : ''}
                      onChange={(e) => setFormData({ ...formData, date: e.target.value + ':00Z' })}
                      className="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                      required
                    />
                  </div>
                </div>
              </div>
            )}

            {/* Alert Type */}
            <div>
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Alert Type
              </label>
              <div className="space-y-3">
                <label className="flex items-center">
                  <input
                    type="radio"
                    value="price_drop"
                    checked={formData.alertType === 'price_drop'}
                    onChange={(e) => setFormData({ ...formData, alertType: e.target.value as any })}
                    className="text-blue-600"
                  />
                  <span className="ml-2 text-sm text-gray-700 dark:text-gray-300">
                    Price drops to specific amount
                  </span>
                </label>
                
                <label className="flex items-center">
                  <input
                    type="radio"
                    value="percentage_drop"
                    checked={formData.alertType === 'percentage_drop'}
                    onChange={(e) => setFormData({ ...formData, alertType: e.target.value as any })}
                    className="text-blue-600"
                  />
                  <span className="ml-2 text-sm text-gray-700 dark:text-gray-300">
                    Price drops by percentage
                  </span>
                </label>
                
                <label className="flex items-center">
                  <input
                    type="radio"
                    value="availability"
                    checked={formData.alertType === 'availability'}
                    onChange={(e) => setFormData({ ...formData, alertType: e.target.value as any })}
                    className="text-blue-600"
                  />
                  <span className="ml-2 text-sm text-gray-700 dark:text-gray-300">
                    Ticket availability changes
                  </span>
                </label>
              </div>
            </div>

            {/* Alert Conditions */}
            <div>
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Alert Condition
              </label>
              {formData.alertType === 'price_drop' && (
                <div className="flex items-center space-x-2">
                  <CurrencyPoundIcon className="h-5 w-5 text-gray-400" />
                  <input
                    type="number"
                    value={formData.targetPrice}
                    onChange={(e) => setFormData({ ...formData, targetPrice: Number(e.target.value) })}
                    className="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                    placeholder="Target price"
                    min="1"
                    required
                  />
                </div>
              )}
              
              {formData.alertType === 'percentage_drop' && (
                <div className="flex items-center space-x-2">
                  <TrendingDownIcon className="h-5 w-5 text-gray-400" />
                  <input
                    type="number"
                    value={formData.percentageDrop}
                    onChange={(e) => setFormData({ ...formData, percentageDrop: Number(e.target.value) })}
                    className="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                    placeholder="Percentage drop"
                    min="1"
                    max="90"
                    required
                  />
                  <span className="text-sm text-gray-500">%</span>
                </div>
              )}
              
              {formData.alertType === 'availability' && (
                <input
                  type="number"
                  value={formData.availabilityThreshold}
                  onChange={(e) => setFormData({ ...formData, availabilityThreshold: Number(e.target.value) })}
                  className="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                  placeholder="Minimum available tickets"
                  min="1"
                  required
                />
              )}
            </div>

            {/* Notification Preferences */}
            <div>
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                Notification Methods
              </label>
              <div className="space-y-2">
                <label className="flex items-center">
                  <input
                    type="checkbox"
                    checked={formData.notifications.email}
                    onChange={(e) => setFormData({
                      ...formData,
                      notifications: { ...formData.notifications, email: e.target.checked }
                    })}
                    className="text-blue-600"
                  />
                  <span className="ml-2 text-sm text-gray-700 dark:text-gray-300">
                    Email notifications
                  </span>
                </label>
                
                <label className="flex items-center">
                  <input
                    type="checkbox"
                    checked={formData.notifications.push}
                    onChange={(e) => setFormData({
                      ...formData,
                      notifications: { ...formData.notifications, push: e.target.checked }
                    })}
                    className="text-blue-600"
                  />
                  <span className="ml-2 text-sm text-gray-700 dark:text-gray-300">
                    Browser push notifications
                  </span>
                </label>
                
                <label className="flex items-center">
                  <input
                    type="checkbox"
                    checked={formData.notifications.sms}
                    onChange={(e) => setFormData({
                      ...formData,
                      notifications: { ...formData.notifications, sms: e.target.checked }
                    })}
                    className="text-blue-600"
                  />
                  <span className="ml-2 text-sm text-gray-700 dark:text-gray-300">
                    SMS notifications
                  </span>
                </label>
              </div>
            </div>

            {/* Actions */}
            <div className="flex space-x-3 pt-4">
              <Button
                type="submit"
                variant="primary"
                className="flex-1"
              >
                {alert ? 'Update Alert' : 'Create Alert'}
              </Button>
              <Button
                type="button"
                variant="outline"
                onClick={onClose}
              >
                Cancel
              </Button>
            </div>
          </form>
        </div>
      </motion.div>
    </motion.div>
  );
};

export default PriceAlertSystem;