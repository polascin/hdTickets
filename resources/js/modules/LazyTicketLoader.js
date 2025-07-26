/**
 * LazyTicketLoader - Handles lazy loading of tickets with AJAX and infinite scroll
 */
class LazyTicketLoader {
    constructor(options = {}) {
        this.options = {
            container: '#tickets-container',
            loadMoreBtn: '#load-more-btn',
            searchForm: '#ticket-search-form',
            searchInput: '#search-input',
            filtersForm: '#filters-form',
            loadingIndicator: '#loading-indicator',
            emptyState: '#empty-state',
            perPage: 20,
            enableInfiniteScroll: true,
            enableAutoComplete: true,
            cacheEnabled: true,
            debounceDelay: 300,
            ...options
        };

        this.currentPage = 1;
        this.totalPages = 1;
        this.isLoading = false;
        this.hasMorePages = true;
        this.lastId = 0;
        this.searchTimeout = null;
        this.cache = new Map();

        this.init();
    }

    init() {
        this.bindEvents();
        this.loadInitialTickets();
        
        if (this.options.enableInfiniteScroll) {
            this.setupInfiniteScroll();
        }
        
        if (this.options.enableAutoComplete) {
            this.setupAutoComplete();
        }

        // Real-time updates
        this.setupRealTimeUpdates();
    }

    bindEvents() {
        // Load more button
        $(document).on('click', this.options.loadMoreBtn, (e) => {
            e.preventDefault();
            this.loadMoreTickets();
        });

        // Search form
        $(document).on('submit', this.options.searchForm, (e) => {
            e.preventDefault();
            this.resetAndSearch();
        });

        // Filters form
        $(document).on('change', `${this.options.filtersForm} input, ${this.options.filtersForm} select`, 
            this.debounce(() => this.resetAndSearch(), this.options.debounceDelay)
        );

        // Search input with debounce
        $(document).on('input', this.options.searchInput,
            this.debounce(() => this.performSearch(), this.options.debounceDelay)
        );

        // Clear filters
        $(document).on('click', '[data-clear-filters]', () => {
            this.clearFilters();
        });
    }

    async loadInitialTickets() {
        this.showLoading();
        this.currentPage = 1;
        
        try {
            const data = await this.fetchTickets({
                page: 1,
                per_page: this.options.perPage
            });
            
            this.renderTickets(data.data, true);
            this.updatePagination(data.pagination);
            this.hideLoading();
        } catch (error) {
            this.handleError(error);
        }
    }

    async loadMoreTickets() {
        if (this.isLoading || !this.hasMorePages) return;

        this.showLoading();
        this.currentPage++;

        try {
            const filters = this.getFilters();
            const data = await this.fetchTickets({
                ...filters,
                page: this.currentPage,
                per_page: this.options.perPage
            });

            this.renderTickets(data.data, false);
            this.updatePagination(data.pagination);
            this.hideLoading();
        } catch (error) {
            this.currentPage--; // Rollback on error
            this.handleError(error);
        }
    }

    async fetchTickets(params = {}) {
        const cacheKey = this.options.cacheEnabled ? JSON.stringify(params) : null;
        
        if (cacheKey && this.cache.has(cacheKey)) {
            return this.cache.get(cacheKey);
        }

        const response = await axios.get('/ajax/tickets/load', { params });
        
        if (cacheKey) {
            this.cache.set(cacheKey, response.data);
            // Clear cache after 2 minutes
            setTimeout(() => this.cache.delete(cacheKey), 120000);
        }

        return response.data;
    }

    renderTickets(tickets, replace = false) {
        const container = $(this.options.container);
        
        if (replace) {
            container.empty();
        }

        if (tickets.length === 0 && replace) {
            this.showEmptyState();
            return;
        }

        tickets.forEach(ticket => {
            const ticketHtml = this.renderTicketCard(ticket);
            container.append(ticketHtml);
        });

        this.hideEmptyState();
        this.animateNewTickets();
    }

    renderTicketCard(ticket) {
        const dateDisplay = ticket.event_date ? 
            new Date(ticket.event_date).toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric' 
            }) : 'TBD';

        const priceDisplay = this.formatPrice(ticket);
        const availabilityBadge = ticket.is_available ? 
            '<span class="badge bg-success">Available</span>' :
            '<span class="badge bg-secondary">Sold Out</span>';
        
        const highDemandBadge = ticket.is_high_demand ? 
            '<span class="badge bg-warning">High Demand</span>' : '';

        return `
            <div class="col-md-6 col-lg-4 mb-4 ticket-card" data-ticket-id="${ticket.id}">
                <div class="card h-100 shadow-sm hover-shadow">
                    <div class="card-body">
                        <h5 class="card-title">${this.escapeHtml(ticket.title)}</h5>
                        <p class="card-text text-muted small">
                            <i class="fas fa-calendar me-1"></i>${dateDisplay}
                        </p>
                        <p class="card-text">
                            <strong class="text-primary">${priceDisplay}</strong>
                        </p>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                ${availabilityBadge}
                                ${highDemandBadge}
                            </div>
                            <small class="text-muted">${this.escapeHtml(ticket.platform)}</small>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent">
                        <div class="d-flex gap-2">
                            <a href="/tickets/scraping/${ticket.id}" class="btn btn-outline-primary btn-sm flex-fill">
                                <i class="fas fa-eye me-1"></i>View
                            </a>
                            ${ticket.ticket_url ? 
                                `<a href="${ticket.ticket_url}" target="_blank" class="btn btn-success btn-sm flex-fill">
                                    <i class="fas fa-external-link-alt me-1"></i>Buy
                                </a>` : ''
                            }
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    formatPrice(ticket) {
        if (ticket.min_price && ticket.max_price) {
            return `${ticket.currency} ${this.formatCurrency(ticket.min_price)} - ${this.formatCurrency(ticket.max_price)}`;
        } else if (ticket.max_price) {
            return `${ticket.currency} ${this.formatCurrency(ticket.max_price)}`;
        } else if (ticket.min_price) {
            return `${ticket.currency} ${this.formatCurrency(ticket.min_price)}`;
        }
        return 'Price on request';
    }

    formatCurrency(amount) {
        return new Intl.NumberFormat('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(amount);
    }

    setupInfiniteScroll() {
        let isNearBottom = false;
        
        $(window).on('scroll', this.throttle(() => {
            const scrollTop = $(window).scrollTop();
            const windowHeight = $(window).height();
            const documentHeight = $(document).height();
            
            isNearBottom = scrollTop + windowHeight >= documentHeight - 200;
            
            if (isNearBottom && this.hasMorePages && !this.isLoading) {
                this.loadMoreTickets();
            }
        }, 100));
    }

    setupAutoComplete() {
        const searchInput = $(this.options.searchInput);
        let dropdown = null;

        searchInput.on('input', this.debounce(async (e) => {
            const query = e.target.value.trim();
            
            if (query.length < 2) {
                this.hideAutoComplete();
                return;
            }

            try {
                const response = await axios.get('/ajax/tickets/search', {
                    params: { q: query, limit: 10 }
                });

                this.showAutoComplete(response.data.results, searchInput);
            } catch (error) {
                console.error('Autocomplete error:', error);
            }
        }, 300));

        // Hide autocomplete when clicking outside
        $(document).on('click', (e) => {
            if (!$(e.target).closest('.autocomplete-container').length) {
                this.hideAutoComplete();
            }
        });
    }

    showAutoComplete(results, input) {
        this.hideAutoComplete();

        if (results.length === 0) return;

        const dropdown = $(`
            <div class="autocomplete-dropdown position-absolute bg-white border rounded shadow-lg" style="z-index: 1000; max-height: 300px; overflow-y: auto;">
                ${results.map(result => `
                    <div class="autocomplete-item p-2 border-bottom hover-bg-light cursor-pointer" data-url="${result.url}">
                        <div class="fw-bold">${this.escapeHtml(result.title)}</div>
                        <small class="text-muted">${this.escapeHtml(result.venue)} • ${result.date} • ${result.price}</small>
                    </div>
                `).join('')}
            </div>
        `);

        input.parent().addClass('position-relative').append(dropdown);

        dropdown.on('click', '.autocomplete-item', function() {
            window.location.href = $(this).data('url');
        });
    }

    hideAutoComplete() {
        $('.autocomplete-dropdown').remove();
    }

    setupRealTimeUpdates() {
        if (typeof window.Echo !== 'undefined') {
            // Listen for ticket availability updates
            window.Echo.channel('tickets')
                .listen('TicketAvailabilityUpdated', (e) => {
                    this.updateTicketAvailability(e.ticket);
                });

            // Listen for new tickets
            window.Echo.channel('tickets')
                .listen('TicketScraped', (e) => {
                    this.handleNewTicket(e.ticket);
                });
        }
    }

    updateTicketAvailability(ticket) {
        const ticketCard = $(`.ticket-card[data-ticket-id="${ticket.id}"]`);
        if (ticketCard.length) {
            const badge = ticket.is_available ? 
                '<span class="badge bg-success">Available</span>' :
                '<span class="badge bg-secondary">Sold Out</span>';
            
            ticketCard.find('.badge').first().replaceWith(badge);
            
            // Show notification
            this.showNotification('Ticket availability updated', 'info');
        }
    }

    handleNewTicket(ticket) {
        // Check if ticket matches current filters
        const filters = this.getFilters();
        if (this.ticketMatchesFilters(ticket, filters)) {
            // Prepend new ticket to the list
            const ticketHtml = this.renderTicketCard(ticket);
            $(this.options.container).prepend(ticketHtml);
            
            // Animate the new ticket
            $(this.options.container).find('.ticket-card').first()
                .addClass('animate__animated animate__fadeInDown');
            
            this.showNotification('New ticket found!', 'success');
        }
    }

    ticketMatchesFilters(ticket, filters) {
        // Simple filter matching logic
        if (filters.platform && !ticket.platform.toLowerCase().includes(filters.platform.toLowerCase())) {
            return false;
        }
        
        if (filters.keywords && !ticket.title.toLowerCase().includes(filters.keywords.toLowerCase())) {
            return false;
        }
        
        if (filters.max_price && ticket.min_price > parseFloat(filters.max_price)) {
            return false;
        }
        
        return true;
    }

    getFilters() {
        const form = $(this.options.filtersForm);
        const filters = {};
        
        form.find('input, select').each(function() {
            const name = $(this).attr('name');
            const value = $(this).val();
            
            if (name && value) {
                filters[name] = value;
            }
        });

        return filters;
    }

    clearFilters() {
        $(this.options.filtersForm)[0].reset();
        $(this.options.searchInput).val('');
        this.resetAndSearch();
    }

    async resetAndSearch() {
        this.currentPage = 1;
        this.hasMorePages = true;
        this.cache.clear();
        
        await this.loadInitialTickets();
    }

    async performSearch() {
        await this.resetAndSearch();
    }

    updatePagination(pagination) {
        this.totalPages = pagination.last_page;
        this.hasMorePages = pagination.has_more;
        
        const loadMoreBtn = $(this.options.loadMoreBtn);
        if (this.hasMorePages) {
            loadMoreBtn.show().find('.btn-text').text(`Load More (${pagination.total - (pagination.current_page * pagination.per_page)} remaining)`);
        } else {
            loadMoreBtn.hide();
        }
    }

    animateNewTickets() {
        $(this.options.container).find('.ticket-card:not(.animated)')
            .addClass('animated animate__animated animate__fadeInUp')
            .on('animationend', function() {
                $(this).removeClass('animate__animated animate__fadeInUp');
            });
    }

    showLoading() {
        this.isLoading = true;
        $(this.options.loadingIndicator).show();
        $(this.options.loadMoreBtn).prop('disabled', true).find('.spinner').show();
    }

    hideLoading() {
        this.isLoading = false;
        $(this.options.loadingIndicator).hide();
        $(this.options.loadMoreBtn).prop('disabled', false).find('.spinner').hide();
    }

    showEmptyState() {
        $(this.options.emptyState).show();
    }

    hideEmptyState() {
        $(this.options.emptyState).hide();
    }

    showNotification(message, type = 'info') {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: type,
            title: message,
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
    }

    handleError(error) {
        this.hideLoading();
        console.error('LazyTicketLoader error:', error);
        
        const message = error.response?.data?.message || 'Failed to load tickets. Please try again.';
        this.showNotification(message, 'error');
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    throttle(func, limit) {
        let inThrottle;
        return function() {
            const args = arguments;
            const context = this;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    }
}

// Export for use in other modules
window.LazyTicketLoader = LazyTicketLoader;

export default LazyTicketLoader;
