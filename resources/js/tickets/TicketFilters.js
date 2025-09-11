/**
 * Advanced Ticket Filtering Component
 * 
 * Handles complex filtering with URL state management, debounced requests,
 * and seamless user experience for sports ticket browsing.
 */
class TicketFilters {
    constructor(options = {}) {
        this.options = {
            formSelector: '#filters-form',
            resultsSelector: '#main-content',
            loadingSelector: '#loading-indicator',
            debounceMs: 300,
            maxRetries: 3,
            cacheEnabled: true,
            enableUrlSync: true,
            ...options
        };
        
        this.form = document.querySelector(this.options.formSelector);
        this.resultsContainer = document.querySelector(this.options.resultsSelector);
        this.loadingIndicator = document.querySelector(this.options.loadingSelector);
        
        this.cache = new Map();
        this.debounceTimer = null;
        this.requestController = null;
        this.retryCount = 0;
        
        this.init();
    }
    
    init() {
        if (!this.form) {
            console.warn('TicketFilters: Form not found');
            return;
        }
        
        this.setupEventListeners();
        this.setupUrlSync();
        this.setupKeyboardShortcuts();
        this.restoreFiltersFromUrl();
        
        console.log('TicketFilters initialized');
    }
    
    setupEventListeners() {
        // Auto-submit on form changes
        this.form.addEventListener('change', (e) => {
            this.handleFilterChange(e);
        });
        
        // Handle search input with debouncing
        const searchInput = this.form.querySelector('#keywords');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                this.handleSearchInput(e);
            });
            
            searchInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    this.clearDebounce();
                    this.submitFilters();
                }
            });
        }
        
        // Clear filters functionality
        document.querySelectorAll('[data-clear-filters]').forEach(button => {
            button.addEventListener('click', () => {
                this.clearAllFilters();
            });
        });
        
        // Advanced filters toggle
        const advancedToggle = document.getElementById('advanced-filters-toggle');
        if (advancedToggle) {
            advancedToggle.addEventListener('click', () => {
                this.toggleAdvancedFilters();
            });
        }
        
        // Per page selector
        const perPageSelect = document.getElementById('per-page-select');
        if (perPageSelect) {
            perPageSelect.addEventListener('change', () => {
                this.handleFilterChange();
            });
        }
    }
    
    setupUrlSync() {
        if (!this.options.enableUrlSync) return;
        
        // Listen for browser back/forward buttons
        window.addEventListener('popstate', (e) => {
            if (e.state && e.state.filters) {
                this.restoreFilters(e.state.filters);
                this.submitFilters(false); // Don't update URL again
            }
        });
    }
    
    setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // Ctrl/Cmd + K for search focus
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                const searchInput = document.getElementById('keywords');
                if (searchInput) {
                    searchInput.focus();
                    searchInput.select();
                }
            }
            
            // Ctrl/Cmd + Enter to apply filters
            if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                e.preventDefault();
                this.clearDebounce();
                this.submitFilters();
            }
            
            // Escape to clear search
            if (e.key === 'Escape') {
                const searchInput = document.getElementById('keywords');
                if (searchInput && document.activeElement === searchInput) {
                    searchInput.value = '';
                    this.handleSearchInput();
                }
            }
        });
    }
    
    handleFilterChange(event = null) {
        this.clearDebounce();
        
        // Immediate update for certain controls
        const immediateControls = ['sort_by', 'sort_dir', 'view', 'per_page'];
        if (event && immediateControls.includes(event.target.name)) {
            this.submitFilters();
            return;
        }
        
        // Debounced update for other controls
        this.debounceTimer = setTimeout(() => {
            this.submitFilters();
        }, this.options.debounceMs);
    }
    
    handleSearchInput(_event = null) {
        this.clearDebounce();
        
        const searchInput = document.getElementById('keywords');
        if (!searchInput) return;
        
        const query = searchInput.value.trim();
        
        // Show/hide clear button
        const clearButton = document.getElementById('clear-search');
        if (clearButton) {
            if (query.length > 0) {
                clearButton.classList.remove('hidden');
            } else {
                clearButton.classList.add('hidden');
            }
        }
        
        // Debounced filter update
        this.debounceTimer = setTimeout(() => {
            this.submitFilters();
            
            // Load suggestions if query is long enough
            if (query.length >= 2) {
                this.loadSearchSuggestions(query);
            } else {
                this.hideSuggestions();
            }
        }, this.options.debounceMs);
    }
    
    async loadSearchSuggestions(query) {
        try {
            const response = await fetch(`/tickets/scraping/search-suggestions?term=${encodeURIComponent(query)}`);
            const data = await response.json();
            
            if (data.success && data.suggestions.length > 0) {
                this.displaySuggestions(data.suggestions);
            } else {
                this.hideSuggestions();
            }
        } catch (error) {
            console.warn('Failed to load search suggestions:', error);
            this.hideSuggestions();
        }
    }
    
    displaySuggestions(suggestions) {
        const suggestionsContainer = document.getElementById('search-suggestions');
        const suggestionsList = document.getElementById('suggestions-list');
        
        if (!suggestionsContainer || !suggestionsList) return;
        
        suggestionsList.innerHTML = suggestions.map(suggestion => `
            <div class="px-3 py-2 hover:bg-gray-100 cursor-pointer suggestion-item flex items-center"
                 data-value="${this.escapeHtml(suggestion.value)}"
                 data-type="${this.escapeHtml(suggestion.type)}">
                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <span class="flex-1">${this.escapeHtml(suggestion.value)}</span>
                <span class="text-xs text-gray-500 capitalize">${this.escapeHtml(suggestion.type)}</span>
            </div>
        `).join('');
        
        // Add click handlers
        suggestionsList.querySelectorAll('.suggestion-item').forEach(item => {
            item.addEventListener('click', () => {
                this.applySuggestion(item.dataset.value);
            });
        });
        
        suggestionsContainer.classList.remove('hidden');
    }
    
    hideSuggestions() {
        const suggestionsContainer = document.getElementById('search-suggestions');
        if (suggestionsContainer) {
            suggestionsContainer.classList.add('hidden');
        }
    }
    
    applySuggestion(value) {
        const searchInput = document.getElementById('keywords');
        if (searchInput) {
            searchInput.value = value;
            this.hideSuggestions();
            this.clearDebounce();
            this.submitFilters();
        }
    }
    
    async submitFilters(updateUrl = true) {
        const formData = new FormData(this.form);
        const filters = this.formDataToObject(formData);
        
        // Check cache first
        const cacheKey = this.generateCacheKey(filters);
        if (this.options.cacheEnabled && this.cache.has(cacheKey)) {
            const cachedData = this.cache.get(cacheKey);
            this.updateResults(cachedData);
            return;
        }
        
        // Cancel any existing request
        if (this.requestController) {
            this.requestController.abort();
        }
        
        this.requestController = new AbortController();
        
        // Update URL if enabled
        if (updateUrl && this.options.enableUrlSync) {
            const url = this.buildUrlWithFilters(filters);
            const state = { filters };
            history.pushState(state, '', url);
        }
        
        // Show loading state
        this.showLoading();
        
        try {
            const response = await fetch('/tickets/scraping/ajax-filter', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                signal: this.requestController.signal
            });
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            
            if (data.success) {
                // Cache successful response
                if (this.options.cacheEnabled) {
                    this.cache.set(cacheKey, data);
                    
                    // Limit cache size
                    if (this.cache.size > 50) {
                        const firstKey = this.cache.keys().next().value;
                        this.cache.delete(firstKey);
                    }
                }
                
                this.updateResults(data);
                this.retryCount = 0;
            } else {
                throw new Error(data.message || 'Filter request failed');
            }
        } catch (error) {
            if (error.name === 'AbortError') {
                return; // Request was cancelled
            }
            
            console.error('Filter error:', error);
            
            // Retry logic
            if (this.retryCount < this.options.maxRetries) {
                this.retryCount++;
                console.log(`Retrying filter request (${this.retryCount}/${this.options.maxRetries})`);
                setTimeout(() => this.submitFilters(updateUrl), 1000 * this.retryCount);
                return;
            }
            
            this.showError('Failed to load tickets. Please try again.');
        } finally {
            this.hideLoading();
            this.requestController = null;
        }
    }
    
    updateResults(data) {
        if (!this.resultsContainer) return;
        
        // Update main content
        if (data.html) {
            this.resultsContainer.innerHTML = data.html;
        }
        
        // Update statistics
        this.updateStatistics(data.stats);
        
        // Update active filters
        this.updateActiveFilters(data.applied_filters);
        
        // Update pagination
        this.updatePagination(data.pagination);
        
        // Trigger custom event
        this.dispatchEvent('filtersApplied', { data });
    }
    
    updateStatistics(stats) {
        if (!stats) return;
        
        // Update ticket count
        const countElements = document.querySelectorAll('[data-stat="total-count"]');
        countElements.forEach(el => {
            el.textContent = stats.total_count ? stats.total_count.toLocaleString() : '0';
        });
        
        // Update average price
        const avgPriceElements = document.querySelectorAll('[data-stat="avg-price"]');
        avgPriceElements.forEach(el => {
            el.textContent = stats.avg_price ? `$${stats.avg_price.toFixed(2)}` : 'N/A';
        });
        
        // Update available count
        const availableElements = document.querySelectorAll('[data-stat="available-count"]');
        availableElements.forEach(el => {
            el.textContent = stats.available_count ? stats.available_count.toLocaleString() : '0';
        });
    }
    
    updateActiveFilters(appliedFilters) {
        const container = document.getElementById('active-filters-list');
        if (!container || !appliedFilters) return;
        
        container.innerHTML = Object.entries(appliedFilters)
            .filter(([key, value]) => value && value !== '' && value !== false)
            .map(([key, value]) => `
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                      data-filter="${key}">
                    ${this.formatFilterLabel(key)}: ${this.formatFilterValue(value)}
                    <button type="button" class="ml-1 text-blue-400 hover:text-blue-600"
                            onclick="ticketFilters.removeFilter('${key}')">×</button>
                </span>
            `).join('');
    }
    
    updatePagination(pagination) {
        if (!pagination) return;
        
        // Update pagination controls (implementation depends on UI structure)
        const paginationElements = document.querySelectorAll('[data-pagination]');
        paginationElements.forEach(el => {
            // Update pagination state
        });
    }
    
    clearAllFilters() {
        this.form.reset();
        this.hideSuggestions();
        
        // Clear URL
        if (this.options.enableUrlSync) {
            history.pushState({}, '', window.location.pathname);
        }
        
        this.submitFilters();
    }
    
    removeFilter(filterKey) {
        const input = this.form.querySelector(`[name="${filterKey}"]`);
        if (input) {
            if (input.type === 'checkbox') {
                input.checked = false;
            } else {
                input.value = '';
            }
            this.submitFilters();
        }
    }
    
    toggleAdvancedFilters() {
        const panel = document.getElementById('advanced-filters');
        const icon = document.getElementById('advanced-icon');
        const toggle = document.getElementById('advanced-filters-toggle');
        
        if (!panel || !icon || !toggle) return;
        
        const isExpanded = toggle.getAttribute('aria-expanded') === 'true';
        
        panel.classList.toggle('hidden');
        icon.style.transform = isExpanded ? 'rotate(0deg)' : 'rotate(180deg)';
        toggle.setAttribute('aria-expanded', !isExpanded);
    }
    
    showLoading() {
        if (this.loadingIndicator) {
            this.loadingIndicator.classList.remove('hidden');
        }
        if (this.resultsContainer) {
            this.resultsContainer.style.opacity = '0.5';
        }
    }
    
    hideLoading() {
        if (this.loadingIndicator) {
            this.loadingIndicator.classList.add('hidden');
        }
        if (this.resultsContainer) {
            this.resultsContainer.style.opacity = '1';
        }
    }
    
    showError(message) {
        // Create error notification
        const errorDiv = document.createElement('div');
        errorDiv.className = 'fixed top-4 right-4 z-50 px-4 py-2 bg-red-500 text-white rounded-lg shadow-lg transition-opacity duration-300';
        errorDiv.textContent = message;
        
        document.body.appendChild(errorDiv);
        
        setTimeout(() => {
            errorDiv.style.opacity = '0';
            setTimeout(() => document.body.removeChild(errorDiv), 300);
        }, 5000);
    }
    
    // Utility methods
    
    clearDebounce() {
        if (this.debounceTimer) {
            clearTimeout(this.debounceTimer);
            this.debounceTimer = null;
        }
    }
    
    formDataToObject(formData) {
        const obj = {};
        for (const [key, value] of formData.entries()) {
            obj[key] = value;
        }
        return obj;
    }
    
    generateCacheKey(filters) {
        return btoa(JSON.stringify(filters));
    }
    
    buildUrlWithFilters(filters) {
        const url = new URL(window.location.href);
        url.search = '';
        
        Object.entries(filters).forEach(([key, value]) => {
            if (value && value !== '' && value !== 'false') {
                url.searchParams.set(key, value);
            }
        });
        
        return url.toString();
    }
    
    restoreFiltersFromUrl() {
        const urlParams = new URLSearchParams(window.location.search);
        const filters = Object.fromEntries(urlParams.entries());
        
        if (Object.keys(filters).length > 0) {
            this.restoreFilters(filters);
        }
    }
    
    restoreFilters(filters) {
        Object.entries(filters).forEach(([key, value]) => {
            const input = this.form.querySelector(`[name="${key}"]`);
            if (input) {
                if (input.type === 'checkbox') {
                    input.checked = value === '1' || value === 'true';
                } else {
                    input.value = value;
                }
            }
        });
    }
    
    formatFilterLabel(key) {
        return key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    }
    
    formatFilterValue(value) {
        if (typeof value === 'boolean') {
            return value ? 'Yes' : 'No';
        }
        return value;
    }
    
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    dispatchEvent(eventName, detail = {}) {
        const event = new CustomEvent(eventName, { detail });
        document.dispatchEvent(event);
    }
    
    // Public API methods
    
    getFilters() {
        const formData = new FormData(this.form);
        return this.formDataToObject(formData);
    }
    
    setFilter(key, value) {
        const input = this.form.querySelector(`[name="${key}"]`);
        if (input) {
            if (input.type === 'checkbox') {
                input.checked = Boolean(value);
            } else {
                input.value = value;
            }
            this.submitFilters();
        }
    }
    
    clearCache() {
        this.cache.clear();
        console.log('Filter cache cleared');
    }
    
    destroy() {
        this.clearDebounce();
        if (this.requestController) {
            this.requestController.abort();
        }
        this.cache.clear();
    }
}

// Global instance
window.TicketFilters = TicketFilters;

// Auto-initialize if DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.ticketFilters = new TicketFilters();
    });
} else {
    window.ticketFilters = new TicketFilters();
}
