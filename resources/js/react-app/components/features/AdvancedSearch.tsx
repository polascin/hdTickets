/**
 * HD Tickets - Advanced Search & Filtering System
 * Inspired by TicketScoutie.com - Sophisticated search with multiple filters
 */

import React, { useState, useEffect, useMemo } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import {
  MagnifyingGlassIcon,
  FunnelIcon,
  XMarkIcon,
  CalendarDaysIcon,
  MapPinIcon,
  CurrencyPoundIcon,
  TagIcon,
  AdjustmentsHorizontalIcon,
  ChevronDownIcon,
  ChevronUpIcon,
  StarIcon,
  ClockIcon,
} from '@heroicons/react/24/outline';
import { CheckIcon } from '@heroicons/react/24/solid';

import { cn, formatCurrency, formatDate } from '../../utils/design';
import Button from '../ui/Button';

interface SearchFilters {
  query: string;
  sports: string[];
  teams: string[];
  venues: string[];
  priceRange: {
    min: number;
    max: number;
  };
  dateRange: {
    start: string;
    end: string;
  };
  leagues: string[];
  platforms: string[];
  availability: string[];
  sections: string[];
  sortBy: 'date' | 'price' | 'popularity' | 'availability';
  sortOrder: 'asc' | 'desc';
  showOnlyDeals: boolean;
  showOnlyFeatured: boolean;
}

interface SearchOption {
  id: string;
  name: string;
  count?: number;
  color?: string;
}

interface AdvancedSearchProps {
  onSearch: (filters: SearchFilters) => void;
  onClose?: () => void;
  initialFilters?: Partial<SearchFilters>;
  isCompact?: boolean;
}

const mockSports: SearchOption[] = [
  { id: 'football', name: 'Football', count: 156, color: '#22c55e' },
  { id: 'rugby', name: 'Rugby', count: 89, color: '#3b82f6' },
  { id: 'cricket', name: 'Cricket', count: 67, color: '#f59e0b' },
  { id: 'tennis', name: 'Tennis', count: 45, color: '#ef4444' },
  { id: 'boxing', name: 'Boxing', count: 23, color: '#8b5cf6' },
  { id: 'racing', name: 'Racing', count: 34, color: '#06b6d4' },
];

const mockTeams: SearchOption[] = [
  { id: 'man-united', name: 'Manchester United', count: 12 },
  { id: 'liverpool', name: 'Liverpool', count: 11 },
  { id: 'arsenal', name: 'Arsenal', count: 10 },
  { id: 'chelsea', name: 'Chelsea', count: 9 },
  { id: 'man-city', name: 'Manchester City', count: 8 },
  { id: 'tottenham', name: 'Tottenham', count: 7 },
];

const mockVenues: SearchOption[] = [
  { id: 'wembley', name: 'Wembley Stadium', count: 25 },
  { id: 'old-trafford', name: 'Old Trafford', count: 12 },
  { id: 'emirates', name: 'Emirates Stadium', count: 10 },
  { id: 'anfield', name: 'Anfield', count: 11 },
  { id: 'stamford-bridge', name: 'Stamford Bridge', count: 9 },
  { id: 'etihad', name: 'Etihad Stadium', count: 8 },
];

const mockLeagues: SearchOption[] = [
  { id: 'premier-league', name: 'Premier League', count: 180 },
  { id: 'championship', name: 'Championship', count: 89 },
  { id: 'league-one', name: 'League One', count: 67 },
  { id: 'fa-cup', name: 'FA Cup', count: 45 },
  { id: 'champions-league', name: 'Champions League', count: 34 },
  { id: 'europa-league', name: 'Europa League', count: 23 },
];

const mockPlatforms: SearchOption[] = [
  { id: 'ticketmaster', name: 'Ticketmaster', count: 245 },
  { id: 'stubhub', name: 'StubHub', count: 198 },
  { id: 'viagogo', name: 'Viagogo', count: 167 },
  { id: 'seatgeek', name: 'SeatGeek', count: 134 },
  { id: 'vivid-seats', name: 'Vivid Seats', count: 112 },
  { id: 'see-tickets', name: 'See Tickets', count: 89 },
];

const availabilityOptions: SearchOption[] = [
  { id: 'available', name: 'Available Now', count: 456 },
  { id: 'limited', name: 'Limited Availability', count: 123 },
  { id: 'high-demand', name: 'High Demand', count: 78 },
  { id: 'price-drops', name: 'Recent Price Drops', count: 34 },
];

const defaultFilters: SearchFilters = {
  query: '',
  sports: [],
  teams: [],
  venues: [],
  priceRange: { min: 0, max: 1000 },
  dateRange: { start: '', end: '' },
  leagues: [],
  platforms: [],
  availability: [],
  sections: [],
  sortBy: 'date',
  sortOrder: 'asc',
  showOnlyDeals: false,
  showOnlyFeatured: false,
};

const AdvancedSearch: React.FC<AdvancedSearchProps> = ({
  onSearch,
  onClose,
  initialFilters = {},
  isCompact = false,
}) => {
  const [filters, setFilters] = useState<SearchFilters>({
    ...defaultFilters,
    ...initialFilters,
  });
  
  const [expandedSections, setExpandedSections] = useState<Record<string, boolean>>({
    sports: true,
    location: false,
    pricing: false,
    timing: false,
    platforms: false,
    availability: false,
  });

  const [showAllOptions, setShowAllOptions] = useState<Record<string, boolean>>({});

  // Calculate active filter count
  const activeFilterCount = useMemo(() => {
    let count = 0;
    if (filters.query) count++;
    if (filters.sports.length) count++;
    if (filters.teams.length) count++;
    if (filters.venues.length) count++;
    if (filters.leagues.length) count++;
    if (filters.platforms.length) count++;
    if (filters.availability.length) count++;
    if (filters.priceRange.min > 0 || filters.priceRange.max < 1000) count++;
    if (filters.dateRange.start || filters.dateRange.end) count++;
    if (filters.showOnlyDeals || filters.showOnlyFeatured) count++;
    return count;
  }, [filters]);

  const updateFilters = (updates: Partial<SearchFilters>) => {
    const newFilters = { ...filters, ...updates };
    setFilters(newFilters);
    onSearch(newFilters);
  };

  const toggleSection = (section: string) => {
    setExpandedSections(prev => ({
      ...prev,
      [section]: !prev[section]
    }));
  };

  const clearAllFilters = () => {
    const clearedFilters = { ...defaultFilters };
    setFilters(clearedFilters);
    onSearch(clearedFilters);
  };

  const toggleArrayFilter = (filterKey: keyof SearchFilters, value: string) => {
    const currentArray = filters[filterKey] as string[];
    const newArray = currentArray.includes(value)
      ? currentArray.filter(item => item !== value)
      : [...currentArray, value];
    
    updateFilters({ [filterKey]: newArray });
  };

  if (isCompact) {
    return (
      <div className="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
        <div className="flex items-center space-x-4">
          {/* Search Input */}
          <div className="flex-1 relative">
            <MagnifyingGlassIcon className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
            <input
              type="text"
              value={filters.query}
              onChange={(e) => updateFilters({ query: e.target.value })}
              placeholder="Search events, teams, venues..."
              className="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500"
            />
          </div>

          {/* Quick Filters */}
          <div className="flex items-center space-x-2">
            <select
              value={filters.sortBy}
              onChange={(e) => updateFilters({ sortBy: e.target.value as any })}
              className="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
            >
              <option value="date">Sort by Date</option>
              <option value="price">Sort by Price</option>
              <option value="popularity">Sort by Popularity</option>
              <option value="availability">Sort by Availability</option>
            </select>

            <Button
              variant="outline"
              size="sm"
              leftIcon={<FunnelIcon className="h-4 w-4" />}
              className={cn(
                activeFilterCount > 0 && "border-blue-500 text-blue-600 bg-blue-50 dark:bg-blue-900/20"
              )}
            >
              Filters {activeFilterCount > 0 && `(${activeFilterCount})`}
            </Button>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div>
          <h2 className="text-2xl font-bold text-gray-900 dark:text-white">
            Advanced Search
          </h2>
          <p className="mt-1 text-gray-600 dark:text-gray-300">
            Find exactly what you're looking for with our comprehensive filters
          </p>
        </div>
        <div className="flex items-center space-x-3">
          {activeFilterCount > 0 && (
            <Button
              variant="outline"
              size="sm"
              onClick={clearAllFilters}
              leftIcon={<XMarkIcon className="h-4 w-4" />}
            >
              Clear All ({activeFilterCount})
            </Button>
          )}
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

      <div className="grid grid-cols-1 lg:grid-cols-4 gap-6">
        {/* Search Input */}
        <div className="lg:col-span-4">
          <div className="relative">
            <MagnifyingGlassIcon className="absolute left-4 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400" />
            <input
              type="text"
              value={filters.query}
              onChange={(e) => updateFilters({ query: e.target.value })}
              placeholder="Search for events, teams, venues, leagues..."
              className="w-full pl-12 pr-4 py-3 text-lg border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>
        </div>

        {/* Sports Filter */}
        <div className="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
          <FilterSection
            title="Sports"
            icon={TagIcon}
            isExpanded={expandedSections.sports}
            onToggle={() => toggleSection('sports')}
            count={filters.sports.length}
          >
            <div className="space-y-2">
              {mockSports.slice(0, showAllOptions.sports ? undefined : 4).map((sport) => (
                <FilterCheckbox
                  key={sport.id}
                  option={sport}
                  checked={filters.sports.includes(sport.id)}
                  onChange={() => toggleArrayFilter('sports', sport.id)}
                />
              ))}
              {mockSports.length > 4 && (
                <button
                  onClick={() => setShowAllOptions(prev => ({ ...prev, sports: !prev.sports }))}
                  className="text-sm text-blue-600 dark:text-blue-400 hover:underline"
                >
                  {showAllOptions.sports ? 'Show Less' : `Show ${mockSports.length - 4} More`}
                </button>
              )}
            </div>
          </FilterSection>
        </div>

        {/* Location Filter */}
        <div className="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
          <FilterSection
            title="Location"
            icon={MapPinIcon}
            isExpanded={expandedSections.location}
            onToggle={() => toggleSection('location')}
            count={filters.teams.length + filters.venues.length}
          >
            <div className="space-y-4">
              {/* Teams */}
              <div>
                <h4 className="text-sm font-medium text-gray-900 dark:text-white mb-2">Teams</h4>
                <div className="space-y-2">
                  {mockTeams.slice(0, showAllOptions.teams ? undefined : 3).map((team) => (
                    <FilterCheckbox
                      key={team.id}
                      option={team}
                      checked={filters.teams.includes(team.id)}
                      onChange={() => toggleArrayFilter('teams', team.id)}
                    />
                  ))}
                  {mockTeams.length > 3 && (
                    <button
                      onClick={() => setShowAllOptions(prev => ({ ...prev, teams: !prev.teams }))}
                      className="text-sm text-blue-600 dark:text-blue-400 hover:underline"
                    >
                      {showAllOptions.teams ? 'Show Less' : `Show ${mockTeams.length - 3} More`}
                    </button>
                  )}
                </div>
              </div>

              {/* Venues */}
              <div>
                <h4 className="text-sm font-medium text-gray-900 dark:text-white mb-2">Venues</h4>
                <div className="space-y-2">
                  {mockVenues.slice(0, showAllOptions.venues ? undefined : 3).map((venue) => (
                    <FilterCheckbox
                      key={venue.id}
                      option={venue}
                      checked={filters.venues.includes(venue.id)}
                      onChange={() => toggleArrayFilter('venues', venue.id)}
                    />
                  ))}
                  {mockVenues.length > 3 && (
                    <button
                      onClick={() => setShowAllOptions(prev => ({ ...prev, venues: !prev.venues }))}
                      className="text-sm text-blue-600 dark:text-blue-400 hover:underline"
                    >
                      {showAllOptions.venues ? 'Show Less' : `Show ${mockVenues.length - 3} More`}
                    </button>
                  )}
                </div>
              </div>
            </div>
          </FilterSection>
        </div>

        {/* Pricing Filter */}
        <div className="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
          <FilterSection
            title="Pricing"
            icon={CurrencyPoundIcon}
            isExpanded={expandedSections.pricing}
            onToggle={() => toggleSection('pricing')}
            count={filters.priceRange.min > 0 || filters.priceRange.max < 1000 ? 1 : 0}
          >
            <div className="space-y-4">
              {/* Price Range */}
              <div>
                <h4 className="text-sm font-medium text-gray-900 dark:text-white mb-3">Price Range</h4>
                <div className="grid grid-cols-2 gap-2">
                  <div>
                    <label className="block text-xs text-gray-600 dark:text-gray-400 mb-1">Min</label>
                    <input
                      type="number"
                      value={filters.priceRange.min}
                      onChange={(e) => updateFilters({
                        priceRange: { ...filters.priceRange, min: Number(e.target.value) }
                      })}
                      className="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                      placeholder="£0"
                    />
                  </div>
                  <div>
                    <label className="block text-xs text-gray-600 dark:text-gray-400 mb-1">Max</label>
                    <input
                      type="number"
                      value={filters.priceRange.max}
                      onChange={(e) => updateFilters({
                        priceRange: { ...filters.priceRange, max: Number(e.target.value) }
                      })}
                      className="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                      placeholder="£1000"
                    />
                  </div>
                </div>
              </div>

              {/* Quick Price Filters */}
              <div>
                <h4 className="text-sm font-medium text-gray-900 dark:text-white mb-2">Quick Filters</h4>
                <div className="space-y-2">
                  <FilterToggle
                    label="Show Only Deals"
                    checked={filters.showOnlyDeals}
                    onChange={(checked) => updateFilters({ showOnlyDeals: checked })}
                  />
                  <FilterToggle
                    label="Featured Events"
                    checked={filters.showOnlyFeatured}
                    onChange={(checked) => updateFilters({ showOnlyFeatured: checked })}
                  />
                </div>
              </div>
            </div>
          </FilterSection>
        </div>

        {/* Date & Time Filter */}
        <div className="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
          <FilterSection
            title="Date & Time"
            icon={CalendarDaysIcon}
            isExpanded={expandedSections.timing}
            onToggle={() => toggleSection('timing')}
            count={filters.dateRange.start || filters.dateRange.end ? 1 : 0}
          >
            <div className="space-y-4">
              {/* Date Range */}
              <div>
                <h4 className="text-sm font-medium text-gray-900 dark:text-white mb-3">Date Range</h4>
                <div className="grid grid-cols-2 gap-2">
                  <div>
                    <label className="block text-xs text-gray-600 dark:text-gray-400 mb-1">From</label>
                    <input
                      type="date"
                      value={filters.dateRange.start}
                      onChange={(e) => updateFilters({
                        dateRange: { ...filters.dateRange, start: e.target.value }
                      })}
                      className="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                    />
                  </div>
                  <div>
                    <label className="block text-xs text-gray-600 dark:text-gray-400 mb-1">To</label>
                    <input
                      type="date"
                      value={filters.dateRange.end}
                      onChange={(e) => updateFilters({
                        dateRange: { ...filters.dateRange, end: e.target.value }
                      })}
                      className="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                    />
                  </div>
                </div>
              </div>

              {/* League Filter */}
              <div>
                <h4 className="text-sm font-medium text-gray-900 dark:text-white mb-2">Leagues</h4>
                <div className="space-y-2">
                  {mockLeagues.slice(0, showAllOptions.leagues ? undefined : 3).map((league) => (
                    <FilterCheckbox
                      key={league.id}
                      option={league}
                      checked={filters.leagues.includes(league.id)}
                      onChange={() => toggleArrayFilter('leagues', league.id)}
                    />
                  ))}
                  {mockLeagues.length > 3 && (
                    <button
                      onClick={() => setShowAllOptions(prev => ({ ...prev, leagues: !prev.leagues }))}
                      className="text-sm text-blue-600 dark:text-blue-400 hover:underline"
                    >
                      {showAllOptions.leagues ? 'Show Less' : `Show ${mockLeagues.length - 3} More`}
                    </button>
                  )}
                </div>
              </div>
            </div>
          </FilterSection>
        </div>

        {/* Platform & Availability Filters */}
        <div className="lg:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6">
          {/* Platforms */}
          <div className="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
            <FilterSection
              title="Platforms"
              icon={AdjustmentsHorizontalIcon}
              isExpanded={expandedSections.platforms}
              onToggle={() => toggleSection('platforms')}
              count={filters.platforms.length}
            >
              <div className="space-y-2">
                {mockPlatforms.slice(0, showAllOptions.platforms ? undefined : 4).map((platform) => (
                  <FilterCheckbox
                    key={platform.id}
                    option={platform}
                    checked={filters.platforms.includes(platform.id)}
                    onChange={() => toggleArrayFilter('platforms', platform.id)}
                  />
                ))}
                {mockPlatforms.length > 4 && (
                  <button
                    onClick={() => setShowAllOptions(prev => ({ ...prev, platforms: !prev.platforms }))}
                    className="text-sm text-blue-600 dark:text-blue-400 hover:underline"
                  >
                    {showAllOptions.platforms ? 'Show Less' : `Show ${mockPlatforms.length - 4} More`}
                  </button>
                )}
              </div>
            </FilterSection>
          </div>

          {/* Availability */}
          <div className="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
            <FilterSection
              title="Availability"
              icon={ClockIcon}
              isExpanded={expandedSections.availability}
              onToggle={() => toggleSection('availability')}
              count={filters.availability.length}
            >
              <div className="space-y-2">
                {availabilityOptions.map((option) => (
                  <FilterCheckbox
                    key={option.id}
                    option={option}
                    checked={filters.availability.includes(option.id)}
                    onChange={() => toggleArrayFilter('availability', option.id)}
                  />
                ))}
              </div>
            </FilterSection>
          </div>
        </div>

        {/* Sort Options */}
        <div className="lg:col-span-2 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
          <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-4">Sort Results</h3>
          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Sort By
              </label>
              <select
                value={filters.sortBy}
                onChange={(e) => updateFilters({ sortBy: e.target.value as any })}
                className="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
              >
                <option value="date">Date</option>
                <option value="price">Price</option>
                <option value="popularity">Popularity</option>
                <option value="availability">Availability</option>
              </select>
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Order
              </label>
              <select
                value={filters.sortOrder}
                onChange={(e) => updateFilters({ sortOrder: e.target.value as any })}
                className="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
              >
                <option value="asc">Ascending</option>
                <option value="desc">Descending</option>
              </select>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

// Filter Section Component
interface FilterSectionProps {
  title: string;
  icon: React.ComponentType<any>;
  isExpanded: boolean;
  onToggle: () => void;
  count?: number;
  children: React.ReactNode;
}

const FilterSection: React.FC<FilterSectionProps> = ({
  title,
  icon: Icon,
  isExpanded,
  onToggle,
  count = 0,
  children,
}) => {
  return (
    <div>
      <button
        onClick={onToggle}
        className="w-full flex items-center justify-between p-4 text-left hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
      >
        <div className="flex items-center space-x-3">
          <Icon className="h-5 w-5 text-gray-400" />
          <span className="font-medium text-gray-900 dark:text-white">{title}</span>
          {count > 0 && (
            <span className="px-2 py-0.5 text-xs bg-blue-100 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 rounded-full">
              {count}
            </span>
          )}
        </div>
        {isExpanded ? (
          <ChevronUpIcon className="h-4 w-4 text-gray-400" />
        ) : (
          <ChevronDownIcon className="h-4 w-4 text-gray-400" />
        )}
      </button>
      
      <AnimatePresence>
        {isExpanded && (
          <motion.div
            initial={{ height: 0, opacity: 0 }}
            animate={{ height: 'auto', opacity: 1 }}
            exit={{ height: 0, opacity: 0 }}
            transition={{ duration: 0.2 }}
            className="overflow-hidden"
          >
            <div className="p-4 pt-0 border-t border-gray-200 dark:border-gray-700">
              {children}
            </div>
          </motion.div>
        )}
      </AnimatePresence>
    </div>
  );
};

// Filter Checkbox Component
interface FilterCheckboxProps {
  option: SearchOption;
  checked: boolean;
  onChange: () => void;
}

const FilterCheckbox: React.FC<FilterCheckboxProps> = ({ option, checked, onChange }) => {
  return (
    <label className="flex items-center justify-between cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 p-2 rounded-lg transition-colors">
      <div className="flex items-center space-x-3">
        <div className="relative">
          <input
            type="checkbox"
            checked={checked}
            onChange={onChange}
            className="sr-only"
          />
          <div className={cn(
            'w-4 h-4 rounded border-2 flex items-center justify-center transition-colors',
            checked 
              ? 'bg-blue-600 border-blue-600' 
              : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700'
          )}>
            {checked && <CheckIcon className="h-3 w-3 text-white" />}
          </div>
        </div>
        <span className="text-sm text-gray-700 dark:text-gray-300">{option.name}</span>
      </div>
      {option.count && (
        <span className="text-xs text-gray-500 dark:text-gray-400">
          {option.count}
        </span>
      )}
    </label>
  );
};

// Filter Toggle Component
interface FilterToggleProps {
  label: string;
  checked: boolean;
  onChange: (checked: boolean) => void;
}

const FilterToggle: React.FC<FilterToggleProps> = ({ label, checked, onChange }) => {
  return (
    <label className="flex items-center justify-between cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 p-2 rounded-lg transition-colors">
      <span className="text-sm text-gray-700 dark:text-gray-300">{label}</span>
      <div className="relative">
        <input
          type="checkbox"
          checked={checked}
          onChange={(e) => onChange(e.target.checked)}
          className="sr-only"
        />
        <div className={cn(
          'w-10 h-6 rounded-full transition-colors',
          checked ? 'bg-blue-600' : 'bg-gray-300 dark:bg-gray-600'
        )}>
          <div className={cn(
            'w-4 h-4 bg-white rounded-full shadow-sm transform transition-transform mt-1',
            checked ? 'translate-x-5' : 'translate-x-1'
          )} />
        </div>
      </div>
    </label>
  );
};

export default AdvancedSearch;