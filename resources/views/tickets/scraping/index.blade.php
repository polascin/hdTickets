@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4">
    <div class="flex justify-between items-center mb-4">
        <h1>Sports Ticket Monitor <span class="badge bg-primary ms-2" id="total-tickets">{{ $tickets->total() ?? 0 }}</span></h1>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-success" id="refresh-tickets">
                <i class="fas fa-sync-alt me-1"></i>Refresh
            </button>
            <button class="btn btn-outline-secondary" data-clear-filters>
                <i class="fas fa-eraser me-1"></i>Clear Filters
            </button>
        </div>
    </div>

    <!-- Advanced Search & Filters -->
    <div class="card mb-4 shadow-sm mx-auto">
        <div class="card-header bg-gradient-primary text-white">
            <h5 class="mb-0">
                <i class="fas fa-filter me-2"></i>Advanced Search & Filters
            </h5>
        </div>
        <div class="card-body">
            <form id="filters-form">
<div class="flex flex-wrap -mx-3">
                    <!-- Search Bar with Autocomplete -->
                    <div class="col-md-6">
                        <label for="keywords" class="form-label">Search Tickets</label>
                        <div class="position-relative autocomplete-container">
                            <input type="text" 
                                   class="form-control" 
                                   id="search-input" 
                                   name="keywords" 
                                   value="{{ request('keywords') }}" 
                                   placeholder="Search by event, team, venue...">
                            <i class="fas fa-search position-absolute top-50 end-0 translate-middle-y text-muted me-3"></i>
                        </div>
                    </div>
                    
                    <!-- Platform Filter -->
                    <div class="col-md-3">
                        <label for="platform" class="form-label">Platform</label>
                        <select class="form-select" name="platform">
                            <option value="">All Platforms</option>
                            <option value="ticketmaster" {{ request('platform') == 'ticketmaster' ? 'selected' : '' }}>Ticketmaster</option>
                            <option value="stubhub" {{ request('platform') == 'stubhub' ? 'selected' : '' }}>StubHub</option>
                            <option value="viagogo" {{ request('platform') == 'viagogo' ? 'selected' : '' }}>Viagogo</option>
                            <option value="seatgeek" {{ request('platform') == 'seatgeek' ? 'selected' : '' }}>SeatGeek</option>
                        </select>
                    </div>
                    
                    <!-- Availability Filter -->
                    <div class="col-md-3">
                        <label for="availability" class="form-label">Availability</label>
                        <select class="form-select" name="availability">
                            <option value="">All Tickets</option>
                            <option value="1" {{ request('availability') == '1' ? 'selected' : '' }}>Available Only</option>
                            <option value="0" {{ request('availability') == '0' ? 'selected' : '' }}>Sold Out</option>
                        </select>
                    </div>
                </div>
                
                <div class="row g-3 mt-2">
                    <!-- Price Range -->
                    <div class="col-md-3">
                        <label for="min_price" class="form-label">Min Price</label>
                        <input type="number" class="form-control" name="min_price" value="{{ request('min_price') }}" placeholder="0">
                    </div>
                    
                    <div class="col-md-3">
                        <label for="max_price" class="form-label">Max Price</label>
                        <input type="number" class="form-control" name="max_price" value="{{ request('max_price') }}" placeholder="1000">
                    </div>
                    
                    <!-- Date Range -->
                    <div class="col-md-3">
                        <label for="date_from" class="form-label">Date From</label>
                        <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
                    </div>
                    
                    <div class="col-md-3">
                        <label for="date_to" class="form-label">Date To</label>
                        <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="high_demand" value="1" 
                                   {{ request('high_demand') ? 'checked' : '' }} id="high_demand">
                            <label class="form-check-label" for="high_demand">
                                <i class="fas fa-fire text-warning me-1"></i>High Demand Only
                            </label>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Loading Indicator -->
    <div id="loading-indicator" class="text-center py-4" style="display: none;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2 text-muted">Loading tickets...</p>
    </div>

    <!-- Tickets Container -->
    <div id="tickets-container" class="row">
        <!-- Tickets will be loaded here dynamically -->
    </div>

    <!-- Empty State -->
<div id="empty-state" class="text-center py-5 mx-auto" style="display: none;">
        <i class="fas fa-ticket-alt fa-3x text-muted mb-3"></i>
        <h4 class="text-muted">No tickets found</h4>
        <p class="text-muted">Try adjusting your search criteria or check back later for new tickets.</p>
        <button class="btn btn-primary" data-clear-filters>
            <i class="fas fa-eraser me-1"></i>Clear All Filters
        </button>
    </div>

    <!-- Load More Button -->
    <div class="text-center mt-4">
        <button id="load-more-btn" class="btn btn-outline-primary btn-lg" style="display: none;">
            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true" style="display: none;"></span>
            <span class="btn-text">Load More Tickets</span>
        </button>
    </div>

    <!-- Statistics Panel -->
    <div class="mt-5">
    <div class="flex justify-center -mx-3">
            <div class="col-md-3">
                <div class="card text-center bg-primary text-white">
                    <div class="card-body">
                        <h2 class="card-title" id="stat-total">0</h2>
                        <p class="card-text">Total Tickets</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center bg-success text-white">
                    <div class="card-body">
                        <h2 class="card-title" id="stat-available">0</h2>
                        <p class="card-text">Available</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center bg-warning text-white">
                    <div class="card-body">
                        <h2 class="card-title" id="stat-high-demand">0</h2>
                        <p class="card-text">High Demand</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center bg-info text-white">
                    <div class="card-body">
                        <h2 class="card-title" id="stat-platforms">0</h2>
                        <p class="card-text">Platforms</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('styles')
<style>
.hover-shadow {
    transition: box-shadow 0.15s ease-in-out;
}

.hover-shadow:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.bg-gradient-primary {
    background: linear-gradient(45deg, #007bff, #0056b3);
}

.autocomplete-dropdown .autocomplete-item:hover {
    background-color: #f8f9fa;
}

.ticket-card {
    transition: transform 0.2s ease-in-out;
}

.ticket-card:hover {
    transform: translateY(-2px);
}

.animate__animated {
    animation-duration: 0.5s;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translate3d(0, 40px, 0);
    }
    to {
        opacity: 1;
        transform: translate3d(0, 0, 0);
    }
}

@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translate3d(0, -40px, 0);
    }
    to {
        opacity: 1;
        transform: translate3d(0, 0, 0);
    }
}

.animate__fadeInUp {
    animation-name: fadeInUp;
}

.animate__fadeInDown {
    animation-name: fadeInDown;
}

.cursor-pointer {
    cursor: pointer;
}

.hover-bg-light:hover {
    background-color: #f8f9fa;
}
</style>
@endpush

@push('scripts')
<script src="{{ asset('js/modules/LazyTicketLoader.js') }}"></script>
<script>
$(document).ready(function() {
    // Initialize the lazy ticket loader
    const ticketLoader = new LazyTicketLoader({
        container: '#tickets-container',
        loadMoreBtn: '#load-more-btn',
        searchForm: '#ticket-search-form',
        searchInput: '#search-input',
        filtersForm: '#filters-form',
        loadingIndicator: '#loading-indicator',
        emptyState: '#empty-state',
        perPage: 24,
        enableInfiniteScroll: true,
        enableAutoComplete: true,
        cacheEnabled: true,
        debounceDelay: 300
    });

    // Refresh button functionality
    $('#refresh-tickets').on('click', function() {
        const $btn = $(this);
        const originalHtml = $btn.html();
        
        $btn.html('<i class="fas fa-spinner fa-spin me-1"></i>Refreshing...')
            .prop('disabled', true);
            
        ticketLoader.resetAndSearch().finally(() => {
            setTimeout(() => {
                $btn.html(originalHtml).prop('disabled', false);
            }, 1000);
        });
    });

    // Load dashboard statistics
    function loadStats() {
        axios.get('/ajax/dashboard/stats')
            .then(response => {
                const stats = response.data.stats;
                
                // Update statistics with animation
                animateCountUp('#stat-total', stats.scraped_tickets);
                animateCountUp('#stat-available', stats.available_tickets);
                animateCountUp('#stat-high-demand', stats.high_demand_tickets);
                animateCountUp('#stat-platforms', stats.platforms_monitored);
                
                // Update total tickets badge
                $('#total-tickets').text(stats.scraped_tickets.toLocaleString());
            })
            .catch(error => {
                console.error('Failed to load stats:', error);
            });
    }

    // Animate count up effect
    function animateCountUp(selector, target) {
        const element = $(selector);
        const current = parseInt(element.text()) || 0;
        const increment = Math.ceil((target - current) / 20);
        
        if (current !== target) {
            const timer = setInterval(() => {
                const newValue = current + increment;
                if (newValue >= target) {
                    element.text(target.toLocaleString());
                    clearInterval(timer);
                } else {
                    element.text(newValue.toLocaleString());
                }
            }, 50);
        }
    }

    // Load initial stats
    loadStats();

    // Refresh stats every 30 seconds
    setInterval(loadStats, 30000);

    // Real-time notifications
    if (typeof window.Echo !== 'undefined') {
        // Listen for ticket updates
        window.Echo.channel('ticket-updates')
            .listen('TicketScraped', (e) => {
                // Show toast notification
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'info',
                    title: 'New Ticket Found!',
                    text: e.ticket.title,
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
                
                // Refresh stats
                loadStats();
            })
            .listen('TicketAvailabilityUpdated', (e) => {
                // Update ticket availability in real-time
                const ticketCard = $(`.ticket-card[data-ticket-id="${e.ticket.id}"]`);
                if (ticketCard.length) {
                    const badge = e.ticket.is_available ? 
                        '<span class="badge bg-success">Available</span>' :
                        '<span class="badge bg-secondary">Sold Out</span>';
                    
                    ticketCard.find('.badge').first().replaceWith(badge);
                    
                    // Add visual feedback
                    ticketCard.addClass('animate__animated animate__pulse');
                    setTimeout(() => {
                        ticketCard.removeClass('animate__animated animate__pulse');
                    }, 1000);
                }
            });
    }

    // Add smooth scroll behavior
    $('html').css('scroll-behavior', 'smooth');

    // Performance monitoring
    window.addEventListener('load', function() {
        const loadTime = performance.timing.loadEventEnd - performance.timing.navigationStart;
        console.log('Page loaded in:', loadTime + 'ms');
    });
});
</script>
@endpush
