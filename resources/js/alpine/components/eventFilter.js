/**
 * Event Filter Component
 * 
 * Optimized Alpine.js component for filtering sports events with advanced reactivity
 * and performance optimizations for the HD Tickets platform.
 * 
 * @category sports-events
 * @lazy false
 * @dependencies []
 * @props filters: object - Initial filter state
 * @props events: array - Events to filter
 * @events filter-applied: Emitted when filters are applied
 * @events filter-cleared: Emitted when filters are cleared
 */

document.addEventListener('alpine:init', () => {
    Alpine.data('eventFilter', (initialFilters = {}, initialEvents = []) => ({
        // State management
        filters: {
            search: '',
            category: 'all',
            venue: 'all',
            dateRange: 'all',
            priceMin: null,
            priceMax: null,
            availability: 'all',
            platform: 'all',
            ...initialFilters
        },
        
        events: initialEvents,
        filteredEvents: [],
        
        // Performance optimization
        filterTimeout: null,
        filterDelay: 300, // ms
        isFiltering: false,
        
        // UI state
        isExpanded: false,
        showAdvancedFilters: false,
        
        // Cached values for performance
        categories: [],
        venues: [],
        platforms: [],
        dateRanges: [
            { value: 'all', label: 'All Dates' },
            { value: 'today', label: 'Today' },
            { value: 'tomorrow', label: 'Tomorrow' },
            { value: 'this_week', label: 'This Week' },
            { value: 'next_week', label: 'Next Week' },
            { value: 'this_month', label: 'This Month' },
            { value: 'next_month', label: 'Next Month' }
        ],
        
        // Initialization
        init() {
            this.initializeFilters();
            this.setupWatchers();
            this.extractFilterOptions();
            this.applyFilters();
            
            // Setup keyboard shortcuts
            this.setupKeyboardShortcuts();
        },
        
        // Initialize component
        initializeFilters() {
            // Load saved filters from localStorage
            const savedFilters = this.loadSavedFilters();
            if (savedFilters) {
                this.filters = { ...this.filters, ...savedFilters };
            }
            
            // Initialize filtered events
            this.filteredEvents = [...this.events];
        },
        
        // Setup reactive watchers
        setupWatchers() {
            // Watch for filter changes with debouncing
            this.$watch('filters', (newFilters) => {
                this.debouncedFilter();
                this.saveFilters(newFilters);
            }, { deep: true });
            
            // Watch for events data changes
            this.$watch('events', (newEvents) => {
                this.extractFilterOptions();
                this.applyFilters();
            });
        },
        
        // Extract unique filter options from events
        extractFilterOptions() {
            if (!this.events.length) return;
            
            // Extract categories
            this.categories = [...new Set(this.events.map(e => e.sport_category).filter(Boolean))]
                .map(cat => ({ value: cat, label: this.formatCategoryLabel(cat) }));
            
            // Extract venues
            this.venues = [...new Set(this.events.map(e => e.venue).filter(Boolean))]
                .map(venue => ({ value: venue, label: venue }))
                .sort((a, b) => a.label.localeCompare(b.label));
            
            // Extract platforms
            this.platforms = [...new Set(this.events.map(e => e.platform_source).filter(Boolean))]
                .map(platform => ({ 
                    value: platform, 
                    label: this.formatPlatformLabel(platform) 
                }));
        },
        
        // Debounced filtering for performance
        debouncedFilter() {
            this.isFiltering = true;
            
            if (this.filterTimeout) {
                clearTimeout(this.filterTimeout);
            }
            
            this.filterTimeout = setTimeout(() => {
                this.applyFilters();
                this.isFiltering = false;
            }, this.filterDelay);
        },
        
        // Main filtering logic
        applyFilters() {
            let filtered = [...this.events];
            
            // Search filter
            if (this.filters.search) {
                const searchLower = this.filters.search.toLowerCase();
                filtered = filtered.filter(event => 
                    event.name?.toLowerCase().includes(searchLower) ||
                    event.venue?.toLowerCase().includes(searchLower) ||
                    event.description?.toLowerCase().includes(searchLower)
                );
            }
            
            // Category filter
            if (this.filters.category !== 'all') {
                filtered = filtered.filter(event => 
                    event.sport_category === this.filters.category
                );
            }
            
            // Venue filter
            if (this.filters.venue !== 'all') {
                filtered = filtered.filter(event => 
                    event.venue === this.filters.venue
                );
            }
            
            // Date range filter
            if (this.filters.dateRange !== 'all') {
                filtered = this.filterByDateRange(filtered, this.filters.dateRange);
            }
            
            // Price range filter
            if (this.filters.priceMin !== null || this.filters.priceMax !== null) {
                filtered = this.filterByPriceRange(filtered);
            }
            
            // Availability filter
            if (this.filters.availability !== 'all') {
                filtered = filtered.filter(event => 
                    event.availability_status === this.filters.availability
                );
            }
            
            // Platform filter
            if (this.filters.platform !== 'all') {
                filtered = filtered.filter(event => 
                    event.platform_source === this.filters.platform
                );
            }
            
            this.filteredEvents = filtered;
            
            // Emit filter applied event
            this.$dispatch('filter-applied', {
                filters: this.filters,
                resultCount: filtered.length,
                totalCount: this.events.length
            });
        },
        
        // Date range filtering
        filterByDateRange(events, range) {
            const now = new Date();
            const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
            
            return events.filter(event => {
                if (!event.date) return false;
                
                const eventDate = new Date(event.date);
                
                switch (range) {
                    case 'today':
                        return this.isSameDay(eventDate, today);
                    
                    case 'tomorrow':
                        const tomorrow = new Date(today);
                        tomorrow.setDate(tomorrow.getDate() + 1);
                        return this.isSameDay(eventDate, tomorrow);
                    
                    case 'this_week':
                        const weekEnd = new Date(today);
                        weekEnd.setDate(today.getDate() + (7 - today.getDay()));
                        return eventDate >= today && eventDate <= weekEnd;
                    
                    case 'next_week':
                        const nextWeekStart = new Date(today);
                        nextWeekStart.setDate(today.getDate() + (7 - today.getDay() + 1));
                        const nextWeekEnd = new Date(nextWeekStart);
                        nextWeekEnd.setDate(nextWeekEnd.getDate() + 6);
                        return eventDate >= nextWeekStart && eventDate <= nextWeekEnd;
                    
                    case 'this_month':
                        const monthStart = new Date(today.getFullYear(), today.getMonth(), 1);
                        const monthEnd = new Date(today.getFullYear(), today.getMonth() + 1, 0);
                        return eventDate >= monthStart && eventDate <= monthEnd;
                    
                    case 'next_month':
                        const nextMonthStart = new Date(today.getFullYear(), today.getMonth() + 1, 1);
                        const nextMonthEnd = new Date(today.getFullYear(), today.getMonth() + 2, 0);
                        return eventDate >= nextMonthStart && eventDate <= nextMonthEnd;
                    
                    default:
                        return true;
                }
            });
        },
        
        // Price range filtering
        filterByPriceRange(events) {
            return events.filter(event => {
                const price = parseFloat(event.price);
                if (isNaN(price)) return false;
                
                const minMatch = this.filters.priceMin === null || price >= this.filters.priceMin;
                const maxMatch = this.filters.priceMax === null || price <= this.filters.priceMax;
                
                return minMatch && maxMatch;
            });
        },
        
        // Helper functions
        isSameDay(date1, date2) {
            return date1.getFullYear() === date2.getFullYear() &&
                   date1.getMonth() === date2.getMonth() &&
                   date1.getDate() === date2.getDate();
        },
        
        formatCategoryLabel(category) {
            return category.charAt(0).toUpperCase() + category.slice(1).replace('_', ' ');
        },
        
        formatPlatformLabel(platform) {
            const labels = {
                'ticketmaster': 'Ticketmaster',
                'stubhub': 'StubHub',
                'seatgeek': 'SeatGeek',
                'official': 'Official Website'
            };
            return labels[platform] || platform.charAt(0).toUpperCase() + platform.slice(1);
        },
        
        // Filter actions
        clearFilters() {
            this.filters = {
                search: '',
                category: 'all',
                venue: 'all',
                dateRange: 'all',
                priceMin: null,
                priceMax: null,
                availability: 'all',
                platform: 'all'
            };
            
            this.$dispatch('filter-cleared');
        },
        
        resetSearch() {
            this.filters.search = '';
            this.$refs.searchInput?.focus();
        },
        
        toggleAdvancedFilters() {
            this.showAdvancedFilters = !this.showAdvancedFilters;
        },
        
        // Preset filters
        applyPresetFilter(preset) {
            const presets = {
                'popular': {
                    availability: 'available',
                    dateRange: 'this_week'
                },
                'upcoming': {
                    dateRange: 'next_week'
                },
                'affordable': {
                    priceMax: 50,
                    availability: 'available'
                },
                'premium': {
                    priceMin: 100
                }
            };
            
            if (presets[preset]) {
                Object.assign(this.filters, presets[preset]);
            }
        },
        
        // Persistence
        saveFilters(filters) {
            try {
                localStorage.setItem('hd-tickets-event-filters', JSON.stringify(filters));
            } catch (e) {
                console.warn('Failed to save filters to localStorage:', e);
            }
        },
        
        loadSavedFilters() {
            try {
                const saved = localStorage.getItem('hd-tickets-event-filters');
                return saved ? JSON.parse(saved) : null;
            } catch (e) {
                console.warn('Failed to load saved filters:', e);
                return null;
            }
        },
        
        // Keyboard shortcuts
        setupKeyboardShortcuts() {
            document.addEventListener('keydown', (e) => {
                // Only handle if not in input/textarea
                if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
                    return;
                }
                
                switch (e.key) {
                    case '/':
                        e.preventDefault();
                        this.$refs.searchInput?.focus();
                        break;
                    case 'c':
                        if (e.ctrlKey || e.metaKey) {
                            e.preventDefault();
                            this.clearFilters();
                        }
                        break;
                    case 'f':
                        if (e.ctrlKey || e.metaKey) {
                            e.preventDefault();
                            this.toggleAdvancedFilters();
                        }
                        break;
                }
            });
        },
        
        // Computed properties
        get hasActiveFilters() {
            return this.filters.search !== '' ||
                   this.filters.category !== 'all' ||
                   this.filters.venue !== 'all' ||
                   this.filters.dateRange !== 'all' ||
                   this.filters.priceMin !== null ||
                   this.filters.priceMax !== null ||
                   this.filters.availability !== 'all' ||
                   this.filters.platform !== 'all';
        },
        
        get activeFilterCount() {
            let count = 0;
            if (this.filters.search !== '') count++;
            if (this.filters.category !== 'all') count++;
            if (this.filters.venue !== 'all') count++;
            if (this.filters.dateRange !== 'all') count++;
            if (this.filters.priceMin !== null || this.filters.priceMax !== null) count++;
            if (this.filters.availability !== 'all') count++;
            if (this.filters.platform !== 'all') count++;
            return count;
        },
        
        get filterSummary() {
            const active = [];
            if (this.filters.search) active.push(`Search: "${this.filters.search}"`);
            if (this.filters.category !== 'all') active.push(`Category: ${this.formatCategoryLabel(this.filters.category)}`);
            if (this.filters.venue !== 'all') active.push(`Venue: ${this.filters.venue}`);
            if (this.filters.dateRange !== 'all') {
                const range = this.dateRanges.find(r => r.value === this.filters.dateRange);
                active.push(`Date: ${range?.label || this.filters.dateRange}`);
            }
            if (this.filters.priceMin !== null || this.filters.priceMax !== null) {
                const min = this.filters.priceMin || '0';
                const max = this.filters.priceMax || '∞';
                active.push(`Price: £${min} - £${max}`);
            }
            if (this.filters.availability !== 'all') active.push(`Status: ${this.filters.availability}`);
            if (this.filters.platform !== 'all') active.push(`Platform: ${this.formatPlatformLabel(this.filters.platform)}`);
            
            return active.join(', ');
        }
    }));
});

/**
 * Export for manual registration
 */
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        name: 'eventFilter',
        component: 'eventFilter',
        dependencies: [],
        category: 'sports-events',
        description: 'Advanced event filtering component with performance optimization'
    };
}
