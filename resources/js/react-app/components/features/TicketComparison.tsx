/**
 * HD Tickets - Ticket Comparison Engine
 * Side-by-side comparison tool for tickets from different platforms
 */

import React, { useState, useEffect, useMemo } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import {
  ArrowsUpDownIcon,
  XMarkIcon,
  CheckIcon,
  ExclamationTriangleIcon,
  StarIcon,
  ShieldCheckIcon,
  ClockIcon,
  CurrencyPoundIcon,
  TruckIcon,
  UserGroupIcon,
  MapPinIcon,
  CalendarDaysIcon,
  AdjustmentsHorizontalIcon,
  ArrowTopRightOnSquareIcon,
  HeartIcon,
  ShareIcon,
  PrinterIcon,
  EyeIcon,
} from '@heroicons/react/24/outline';
import { 
  StarIcon as StarSolidIcon, 
  HeartIcon as HeartSolidIcon,
  CheckCircleIcon,
  ExclamationCircleIcon,
} from '@heroicons/react/24/solid';

import { cn, formatCurrency, formatDate } from '../../utils/design';
import Button from '../ui/Button';

interface TicketListing {
  id: string;
  platform: string;
  platformLogo?: string;
  platformRating: number;
  section: string;
  row?: string;
  seats?: string;
  quantity: number;
  price: number;
  fees: number;
  totalPrice: number;
  delivery: {
    method: 'instant' | 'email' | 'mobile' | 'postal' | 'collection';
    cost: number;
    timeframe: string;
  };
  seller: {
    name: string;
    rating: number;
    reviews: number;
    verified: boolean;
    professional: boolean;
  };
  features: {
    guarantee: boolean;
    refundable: boolean;
    transferable: boolean;
    restricted: boolean;
  };
  availability: {
    status: 'available' | 'limited' | 'selling_fast' | 'last_chance';
    quantity: number;
    updated: string;
  };
  notes?: string;
  originalPrice?: number;
  discount?: number;
  lastUpdate: string;
  trusted: boolean;
}

interface ComparisonEvent {
  id: string;
  name: string;
  venue: string;
  date: string;
  sport: string;
  image?: string;
}

interface TicketComparisonProps {
  event: ComparisonEvent;
  tickets: TicketListing[];
  onAddToWatchlist?: (ticketId: string) => void;
  onPurchase?: (ticketId: string) => void;
  onRemoveTicket?: (ticketId: string) => void;
  maxCompare?: number;
}

// Mock data
const mockTickets: TicketListing[] = [
  {
    id: 'tm-1',
    platform: 'Ticketmaster',
    platformRating: 4.2,
    section: 'Lower Tier Block 134',
    row: '12',
    seats: '15-16',
    quantity: 2,
    price: 95,
    fees: 12.50,
    totalPrice: 215,
    delivery: {
      method: 'mobile',
      cost: 0,
      timeframe: 'Instant',
    },
    seller: {
      name: 'Ticketmaster Official',
      rating: 4.8,
      reviews: 50000,
      verified: true,
      professional: true,
    },
    features: {
      guarantee: true,
      refundable: false,
      transferable: true,
      restricted: false,
    },
    availability: {
      status: 'available',
      quantity: 4,
      updated: '2024-01-15T10:30:00Z',
    },
    trusted: true,
    lastUpdate: '2024-01-15T10:30:00Z',
  },
  {
    id: 'sh-1',
    platform: 'StubHub',
    platformRating: 4.0,
    section: 'Lower Tier Block 135',
    row: '8',
    seats: '21-22',
    quantity: 2,
    price: 85,
    fees: 18,
    totalPrice: 188,
    delivery: {
      method: 'email',
      cost: 0,
      timeframe: '1-2 hours',
    },
    seller: {
      name: 'ProTicketSeller',
      rating: 4.6,
      reviews: 1250,
      verified: true,
      professional: true,
    },
    features: {
      guarantee: true,
      refundable: true,
      transferable: true,
      restricted: false,
    },
    availability: {
      status: 'limited',
      quantity: 2,
      updated: '2024-01-15T09:45:00Z',
    },
    originalPrice: 100,
    discount: 15,
    trusted: true,
    lastUpdate: '2024-01-15T09:45:00Z',
  },
  {
    id: 'vg-1',
    platform: 'Viagogo',
    platformRating: 3.8,
    section: 'Lower Tier Block 133',
    row: '15',
    seats: '8-9',
    quantity: 2,
    price: 78,
    fees: 25,
    totalPrice: 181,
    delivery: {
      method: 'email',
      cost: 5,
      timeframe: '24-48 hours',
    },
    seller: {
      name: 'TicketFan2024',
      rating: 4.2,
      reviews: 89,
      verified: false,
      professional: false,
    },
    features: {
      guarantee: true,
      refundable: false,
      transferable: false,
      restricted: true,
    },
    availability: {
      status: 'selling_fast',
      quantity: 1,
      updated: '2024-01-15T08:20:00Z',
    },
    notes: 'Restricted view - partial obstruction possible',
    trusted: false,
    lastUpdate: '2024-01-15T08:20:00Z',
  },
];

const mockEvent: ComparisonEvent = {
  id: 'match-1',
  name: 'Manchester United vs Liverpool',
  venue: 'Old Trafford',
  date: '2024-03-15T15:00:00Z',
  sport: 'Football',
};

const TicketComparison: React.FC<TicketComparisonProps> = ({
  event = mockEvent,
  tickets = mockTickets,
  onAddToWatchlist,
  onPurchase,
  onRemoveTicket,
  maxCompare = 4,
}) => {
  const [sortBy, setSortBy] = useState<'price' | 'total' | 'rating' | 'section'>('total');
  const [selectedTickets, setSelectedTickets] = useState<string[]>([]);
  const [showFeatures, setShowFeatures] = useState(false);

  // Sort tickets
  const sortedTickets = useMemo(() => {
    return [...tickets].sort((a, b) => {
      switch (sortBy) {
        case 'price':
          return a.price - b.price;
        case 'total':
          return a.totalPrice - b.totalPrice;
        case 'rating':
          return b.seller.rating - a.seller.rating;
        case 'section':
          return a.section.localeCompare(b.section);
        default:
          return 0;
      }
    });
  }, [tickets, sortBy]);

  const handleToggleSelect = (ticketId: string) => {
    setSelectedTickets(prev => 
      prev.includes(ticketId) 
        ? prev.filter(id => id !== ticketId)
        : prev.length < maxCompare 
          ? [...prev, ticketId] 
          : prev
    );
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'available':
        return 'text-green-600 bg-green-50 dark:bg-green-900/20';
      case 'limited':
        return 'text-yellow-600 bg-yellow-50 dark:bg-yellow-900/20';
      case 'selling_fast':
        return 'text-orange-600 bg-orange-50 dark:bg-orange-900/20';
      case 'last_chance':
        return 'text-red-600 bg-red-50 dark:bg-red-900/20';
      default:
        return 'text-gray-600 bg-gray-50 dark:bg-gray-900/20';
    }
  };

  const getDeliveryIcon = (method: string) => {
    switch (method) {
      case 'instant':
        return <ClockIcon className="h-4 w-4" />;
      case 'mobile':
        return <ClockIcon className="h-4 w-4" />;
      case 'email':
        return <ClockIcon className="h-4 w-4" />;
      case 'postal':
        return <TruckIcon className="h-4 w-4" />;
      case 'collection':
        return <MapPinIcon className="h-4 w-4" />;
      default:
        return <ClockIcon className="h-4 w-4" />;
    }
  };

  const getBestValue = () => {
    return sortedTickets.reduce((best, ticket) => 
      ticket.totalPrice < best.totalPrice ? ticket : best
    );
  };

  const bestValue = getBestValue();

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div>
          <h2 className="text-2xl font-bold text-gray-900 dark:text-white">
            Compare Tickets
          </h2>
          <div className="mt-1 space-y-1">
            <h3 className="text-lg text-gray-700 dark:text-gray-300">{event.name}</h3>
            <div className="flex items-center space-x-4 text-sm text-gray-500 dark:text-gray-400">
              <div className="flex items-center space-x-1">
                <CalendarDaysIcon className="h-4 w-4" />
                <span>{formatDate(event.date)}</span>
              </div>
              <div className="flex items-center space-x-1">
                <MapPinIcon className="h-4 w-4" />
                <span>{event.venue}</span>
              </div>
            </div>
          </div>
        </div>
        <div className="flex items-center space-x-2">
          <Button
            variant="outline"
            size="sm"
            leftIcon={<AdjustmentsHorizontalIcon className="h-4 w-4" />}
            onClick={() => setShowFeatures(!showFeatures)}
          >
            {showFeatures ? 'Hide' : 'Show'} Features
          </Button>
          <select
            value={sortBy}
            onChange={(e) => setSortBy(e.target.value as any)}
            className="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
          >
            <option value="total">Sort by Total Price</option>
            <option value="price">Sort by Ticket Price</option>
            <option value="rating">Sort by Seller Rating</option>
            <option value="section">Sort by Section</option>
          </select>
        </div>
      </div>

      {/* Summary Stats */}
      <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div className="bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
          <div className="text-sm text-gray-500 dark:text-gray-400">Cheapest</div>
          <div className="text-lg font-semibold text-gray-900 dark:text-white">
            {formatCurrency(Math.min(...tickets.map(t => t.totalPrice)))}
          </div>
        </div>
        <div className="bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
          <div className="text-sm text-gray-500 dark:text-gray-400">Most Expensive</div>
          <div className="text-lg font-semibold text-gray-900 dark:text-white">
            {formatCurrency(Math.max(...tickets.map(t => t.totalPrice)))}
          </div>
        </div>
        <div className="bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
          <div className="text-sm text-gray-500 dark:text-gray-400">Average Price</div>
          <div className="text-lg font-semibold text-gray-900 dark:text-white">
            {formatCurrency(tickets.reduce((sum, t) => sum + t.totalPrice, 0) / tickets.length)}
          </div>
        </div>
        <div className="bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
          <div className="text-sm text-gray-500 dark:text-gray-400">Platforms</div>
          <div className="text-lg font-semibold text-gray-900 dark:text-white">
            {new Set(tickets.map(t => t.platform)).size}
          </div>
        </div>
      </div>

      {/* Ticket Listings */}
      <div className="space-y-4">
        {sortedTickets.map((ticket, index) => (
          <TicketComparisonCard
            key={ticket.id}
            ticket={ticket}
            isBestValue={ticket.id === bestValue.id}
            isSelected={selectedTickets.includes(ticket.id)}
            onToggleSelect={() => handleToggleSelect(ticket.id)}
            onAddToWatchlist={onAddToWatchlist}
            onPurchase={onPurchase}
            onRemove={onRemoveTicket}
            showFeatures={showFeatures}
            rank={index + 1}
          />
        ))}
      </div>

      {/* Comparison Table */}
      {selectedTickets.length > 1 && (
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          className="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden"
        >
          <div className="p-4 border-b border-gray-200 dark:border-gray-700">
            <h3 className="text-lg font-semibold text-gray-900 dark:text-white">
              Side-by-Side Comparison ({selectedTickets.length} selected)
            </h3>
          </div>
          <div className="overflow-x-auto">
            <ComparisonTable 
              tickets={tickets.filter(t => selectedTickets.includes(t.id))}
              onPurchase={onPurchase}
            />
          </div>
        </motion.div>
      )}
    </div>
  );
};

// Ticket Comparison Card Component
interface TicketComparisonCardProps {
  ticket: TicketListing;
  isBestValue: boolean;
  isSelected: boolean;
  onToggleSelect: () => void;
  onAddToWatchlist?: (ticketId: string) => void;
  onPurchase?: (ticketId: string) => void;
  onRemove?: (ticketId: string) => void;
  showFeatures: boolean;
  rank: number;
}

const TicketComparisonCard: React.FC<TicketComparisonCardProps> = ({
  ticket,
  isBestValue,
  isSelected,
  onToggleSelect,
  onAddToWatchlist,
  onPurchase,
  onRemove,
  showFeatures,
  rank,
}) => {
  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ delay: rank * 0.1 }}
      className={cn(
        'bg-white dark:bg-gray-800 rounded-lg border overflow-hidden transition-all hover:shadow-lg',
        isSelected 
          ? 'border-blue-500 ring-2 ring-blue-500/20' 
          : 'border-gray-200 dark:border-gray-700',
        isBestValue && 'ring-2 ring-green-500/20 border-green-500'
      )}
    >
      {/* Header */}
      <div className="p-4 pb-3">
        <div className="flex items-start justify-between">
          <div className="flex items-center space-x-3">
            <div className="relative">
              <input
                type="checkbox"
                checked={isSelected}
                onChange={onToggleSelect}
                className="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
              />
            </div>
            <div className="flex items-center space-x-2">
              <div className="w-8 h-8 bg-gray-100 dark:bg-gray-700 rounded flex items-center justify-center text-xs font-medium text-gray-600 dark:text-gray-300">
                {rank}
              </div>
              <div>
                <div className="flex items-center space-x-2">
                  <span className="font-semibold text-gray-900 dark:text-white">
                    {ticket.platform}
                  </span>
                  <div className="flex items-center space-x-1">
                    <StarSolidIcon className="h-4 w-4 text-yellow-500" />
                    <span className="text-sm text-gray-600 dark:text-gray-300">
                      {ticket.platformRating}
                    </span>
                  </div>
                  {ticket.trusted && (
                    <ShieldCheckIcon className="h-4 w-4 text-green-500" title="Trusted seller" />
                  )}
                </div>
                <div className="text-xs text-gray-500 dark:text-gray-400">
                  Updated {formatDate(ticket.lastUpdate, { relative: true })}
                </div>
              </div>
            </div>
          </div>
          
          {/* Badges */}
          <div className="flex items-center space-x-2">
            {isBestValue && (
              <span className="px-2 py-1 text-xs bg-green-100 dark:bg-green-900/20 text-green-700 dark:text-green-400 rounded-full font-medium">
                Best Value
              </span>
            )}
            {ticket.discount && (
              <span className="px-2 py-1 text-xs bg-red-100 dark:bg-red-900/20 text-red-700 dark:text-red-400 rounded-full font-medium">
                {ticket.discount}% Off
              </span>
            )}
            <span className={cn(
              'px-2 py-1 text-xs rounded-full font-medium',
              'text-green-600 bg-green-50 dark:bg-green-900/20'
            )}>
              {ticket.availability.status.replace('_', ' ')}
            </span>
          </div>
        </div>
      </div>

      {/* Main Content */}
      <div className="px-4 pb-4">
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          {/* Seat Details */}
          <div className="space-y-3">
            <h4 className="font-medium text-gray-900 dark:text-white">Seat Details</h4>
            <div className="space-y-2">
              <div className="flex items-center justify-between">
                <span className="text-sm text-gray-500 dark:text-gray-400">Section:</span>
                <span className="text-sm font-medium text-gray-900 dark:text-white">
                  {ticket.section}
                </span>
              </div>
              {ticket.row && (
                <div className="flex items-center justify-between">
                  <span className="text-sm text-gray-500 dark:text-gray-400">Row:</span>
                  <span className="text-sm font-medium text-gray-900 dark:text-white">
                    {ticket.row}
                  </span>
                </div>
              )}
              {ticket.seats && (
                <div className="flex items-center justify-between">
                  <span className="text-sm text-gray-500 dark:text-gray-400">Seats:</span>
                  <span className="text-sm font-medium text-gray-900 dark:text-white">
                    {ticket.seats}
                  </span>
                </div>
              )}
              <div className="flex items-center justify-between">
                <span className="text-sm text-gray-500 dark:text-gray-400">Quantity:</span>
                <span className="text-sm font-medium text-gray-900 dark:text-white">
                  {ticket.quantity}
                </span>
              </div>
            </div>
          </div>

          {/* Pricing */}
          <div className="space-y-3">
            <h4 className="font-medium text-gray-900 dark:text-white">Pricing</h4>
            <div className="space-y-2">
              <div className="flex items-center justify-between">
                <span className="text-sm text-gray-500 dark:text-gray-400">Ticket Price:</span>
                <span className="text-sm font-medium text-gray-900 dark:text-white">
                  {formatCurrency(ticket.price)} × {ticket.quantity}
                </span>
              </div>
              <div className="flex items-center justify-between">
                <span className="text-sm text-gray-500 dark:text-gray-400">Fees:</span>
                <span className="text-sm font-medium text-gray-900 dark:text-white">
                  {formatCurrency(ticket.fees)}
                </span>
              </div>
              <div className="flex items-center justify-between">
                <span className="text-sm text-gray-500 dark:text-gray-400">Delivery:</span>
                <span className="text-sm font-medium text-gray-900 dark:text-white">
                  {ticket.delivery.cost > 0 ? formatCurrency(ticket.delivery.cost) : 'Free'}
                </span>
              </div>
              <div className="border-t border-gray-200 dark:border-gray-700 pt-2">
                <div className="flex items-center justify-between">
                  <span className="font-medium text-gray-900 dark:text-white">Total:</span>
                  <span className="text-lg font-bold text-gray-900 dark:text-white">
                    {formatCurrency(ticket.totalPrice)}
                  </span>
                </div>
              </div>
            </div>
          </div>

          {/* Seller & Delivery */}
          <div className="space-y-3">
            <h4 className="font-medium text-gray-900 dark:text-white">Seller & Delivery</h4>
            <div className="space-y-2">
              <div className="flex items-center justify-between">
                <span className="text-sm text-gray-500 dark:text-gray-400">Seller:</span>
                <div className="text-right">
                  <div className="text-sm font-medium text-gray-900 dark:text-white">
                    {ticket.seller.name}
                    {ticket.seller.verified && (
                      <CheckCircleIcon className="inline h-3 w-3 text-green-500 ml-1" />
                    )}
                  </div>
                  <div className="flex items-center space-x-1">
                    <StarSolidIcon className="h-3 w-3 text-yellow-500" />
                    <span className="text-xs text-gray-500">
                      {ticket.seller.rating} ({ticket.seller.reviews})
                    </span>
                  </div>
                </div>
              </div>
              <div className="flex items-center justify-between">
                <span className="text-sm text-gray-500 dark:text-gray-400">Delivery:</span>
                <div className="text-right">
                  <div className="text-sm font-medium text-gray-900 dark:text-white capitalize">
                    {ticket.delivery.method}
                  </div>
                  <div className="text-xs text-gray-500 dark:text-gray-400">
                    {ticket.delivery.timeframe}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* Features */}
        {showFeatures && (
          <div className="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
            <h4 className="font-medium text-gray-900 dark:text-white mb-3">Features</h4>
            <div className="grid grid-cols-2 md:grid-cols-4 gap-3">
              <div className="flex items-center space-x-2">
                {ticket.features.guarantee ? (
                  <CheckCircleIcon className="h-4 w-4 text-green-500" />
                ) : (
                  <ExclamationCircleIcon className="h-4 w-4 text-gray-400" />
                )}
                <span className="text-sm text-gray-600 dark:text-gray-300">Guarantee</span>
              </div>
              <div className="flex items-center space-x-2">
                {ticket.features.refundable ? (
                  <CheckCircleIcon className="h-4 w-4 text-green-500" />
                ) : (
                  <ExclamationCircleIcon className="h-4 w-4 text-gray-400" />
                )}
                <span className="text-sm text-gray-600 dark:text-gray-300">Refundable</span>
              </div>
              <div className="flex items-center space-x-2">
                {ticket.features.transferable ? (
                  <CheckCircleIcon className="h-4 w-4 text-green-500" />
                ) : (
                  <ExclamationCircleIcon className="h-4 w-4 text-gray-400" />
                )}
                <span className="text-sm text-gray-600 dark:text-gray-300">Transferable</span>
              </div>
              <div className="flex items-center space-x-2">
                {ticket.features.restricted ? (
                  <ExclamationTriangleIcon className="h-4 w-4 text-orange-500" />
                ) : (
                  <CheckCircleIcon className="h-4 w-4 text-green-500" />
                )}
                <span className="text-sm text-gray-600 dark:text-gray-300">
                  {ticket.features.restricted ? 'Restricted' : 'Unrestricted'}
                </span>
              </div>
            </div>
          </div>
        )}

        {/* Notes */}
        {ticket.notes && (
          <div className="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
            <div className="flex items-start space-x-2">
              <ExclamationTriangleIcon className="h-4 w-4 text-orange-500 mt-0.5 flex-shrink-0" />
              <p className="text-sm text-gray-600 dark:text-gray-300">{ticket.notes}</p>
            </div>
          </div>
        )}

        {/* Actions */}
        <div className="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
          <div className="flex items-center space-x-3">
            <Button
              className="flex-1"
              onClick={() => onPurchase?.(ticket.id)}
            >
              Buy Now - {formatCurrency(ticket.totalPrice)}
            </Button>
            <Button
              variant="outline"
              size="sm"
              onClick={() => onAddToWatchlist?.(ticket.id)}
              leftIcon={<HeartIcon className="h-4 w-4" />}
            >
              Watch
            </Button>
            <Button
              variant="outline"
              size="sm"
              leftIcon={<ArrowTopRightOnSquareIcon className="h-4 w-4" />}
            >
              View
            </Button>
            {onRemove && (
              <Button
                variant="ghost"
                size="sm"
                onClick={() => onRemove(ticket.id)}
                leftIcon={<XMarkIcon className="h-4 w-4" />}
                className="text-gray-500 hover:text-red-600"
              />
            )}
          </div>
        </div>
      </div>
    </motion.div>
  );
};

// Comparison Table Component
interface ComparisonTableProps {
  tickets: TicketListing[];
  onPurchase?: (ticketId: string) => void;
}

const ComparisonTable: React.FC<ComparisonTableProps> = ({ tickets, onPurchase }) => {
  return (
    <table className="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
      <thead className="bg-gray-50 dark:bg-gray-700">
        <tr>
          <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
            Platform
          </th>
          {tickets.map((ticket) => (
            <th key={ticket.id} className="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
              {ticket.platform}
            </th>
          ))}
        </tr>
      </thead>
      <tbody className="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
        {/* Price Rows */}
        <tr>
          <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
            Total Price
          </td>
          {tickets.map((ticket) => (
            <td key={ticket.id} className="px-6 py-4 whitespace-nowrap text-center">
              <span className="text-lg font-bold text-gray-900 dark:text-white">
                {formatCurrency(ticket.totalPrice)}
              </span>
            </td>
          ))}
        </tr>
        <tr>
          <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
            Section
          </td>
          {tickets.map((ticket) => (
            <td key={ticket.id} className="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-600 dark:text-gray-300">
              {ticket.section}
            </td>
          ))}
        </tr>
        <tr>
          <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
            Seller Rating
          </td>
          {tickets.map((ticket) => (
            <td key={ticket.id} className="px-6 py-4 whitespace-nowrap text-center">
              <div className="flex items-center justify-center space-x-1">
                <StarSolidIcon className="h-4 w-4 text-yellow-500" />
                <span className="text-sm text-gray-600 dark:text-gray-300">
                  {ticket.seller.rating}
                </span>
              </div>
            </td>
          ))}
        </tr>
        <tr>
          <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
            Delivery
          </td>
          {tickets.map((ticket) => (
            <td key={ticket.id} className="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-600 dark:text-gray-300 capitalize">
              {ticket.delivery.method}
              <div className="text-xs text-gray-500 dark:text-gray-400">
                {ticket.delivery.timeframe}
              </div>
            </td>
          ))}
        </tr>
        <tr>
          <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
            Action
          </td>
          {tickets.map((ticket) => (
            <td key={ticket.id} className="px-6 py-4 whitespace-nowrap text-center">
              <Button
                size="sm"
                onClick={() => onPurchase?.(ticket.id)}
                className="w-full"
              >
                Buy Now
              </Button>
            </td>
          ))}
        </tr>
      </tbody>
    </table>
  );
};

export default TicketComparison;