@extends('layouts.app')

@section('title', 'High-Demand Sports Tickets')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">🏟️ High-Demand Sports Tickets</h1>
            <p class="mb-0 text-muted">Live results from all supported ticket platforms</p>
        </div>
        
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#filtersModal">
                <i class="fas fa-filter"></i> Filters
            </button>
            <button type="button" class="btn btn-outline-success" onclick="refreshResults()">
                <i class="fas fa-sync"></i> Refresh
            </button>
        </div>
    </div>

    <!-- Supported Platforms -->
    <div class="card shadow mb-4">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">📡 Supported Platforms</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-2 text-center mb-3">
                    <div class="platform-badge">
                        <i class="fas fa-music text-info fa-2x mb-2"></i>
                        <div class="fw-bold">Ticketmaster</div>
                        <small class="text-success">95% uptime</small>
                    </div>
                </div>
                <div class="col-md-2 text-center mb-3">
                    <div class="platform-badge">
                        <i class="fas fa-ticket-alt text-primary fa-2x mb-2"></i>
                        <div class="fw-bold">StubHub</div>
                        <small class="text-success">98% uptime</small>
                    </div>
                </div>
                <div class="col-md-2 text-center mb-3">
                    <div class="platform-badge">
                        <i class="fas fa-globe text-warning fa-2x mb-2"></i>
                        <div class="fw-bold">Viagogo</div>
                        <small class="text-success">92% uptime</small>
                    </div>
                </div>
                <div class="col-md-2 text-center mb-3">
                    <div class="platform-badge">
                        <i class="fas fa-chair text-secondary fa-2x mb-2"></i>
                        <div class="fw-bold">SeatGeek</div>
                        <small class="text-success">96% uptime</small>
                    </div>
                </div>
                <div class="col-md-2 text-center mb-3">
                    <div class="platform-badge">
                        <i class="fas fa-tags text-success fa-2x mb-2"></i>
                        <div class="fw-bold">TickPick</div>
                        <small class="text-success">97% uptime</small>
                    </div>
                </div>
                <div class="col-md-2 text-center mb-3">
                    <div class="platform-badge">
                        <i class="fas fa-gamepad text-danger fa-2x mb-2"></i>
                        <div class="fw-bold">FunZone</div>
                        <small class="text-success">94% uptime</small>
                    </div>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-3 text-center mb-3">
                    <div class="platform-badge">
                        <i class="fas fa-calendar text-orange fa-2x mb-2"></i>
                        <div class="fw-bold">Eventbrite</div>
                        <small class="text-success">99% uptime</small>
                    </div>
                </div>
                <div class="col-md-3 text-center mb-3">
                    <div class="platform-badge">
                        <i class="fas fa-headphones text-purple fa-2x mb-2"></i>
                        <div class="fw-bold">Bandsintown</div>
                        <small class="text-success">96% uptime</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Results -->
    <div class="row" id="search-results">
        <!-- Results will be loaded here dynamically -->
    </div>

    <!-- Search Statistics -->
    <div class="card shadow mt-4">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">📈 Search Statistics</h6>
        </div>
        <div class="card-body">
            <div class="row" id="search-stats">
                <div class="col-md-2 text-center">
                    <div class="stat-box">
                        <div class="stat-number text-primary" id="total-events">--</div>
                        <div class="stat-label">Total Events</div>
                    </div>
                </div>
                <div class="col-md-2 text-center">
                    <div class="stat-box">
                        <div class="stat-number text-info" id="platforms-searched">6</div>
                        <div class="stat-label">Platforms</div>
                    </div>
                </div>
                <div class="col-md-2 text-center">
                    <div class="stat-box">
                        <div class="stat-number text-success" id="avg-min-price">--</div>
                        <div class="stat-label">Avg Min Price</div>
                    </div>
                </div>
                <div class="col-md-2 text-center">
                    <div class="stat-box">
                        <div class="stat-number text-warning" id="avg-max-price">--</div>
                        <div class="stat-label">Avg Max Price</div>
                    </div>
                </div>
                <div class="col-md-2 text-center">
                    <div class="stat-box">
                        <div class="stat-number text-danger" id="high-demand-count">--</div>
                        <div class="stat-label">High Demand</div>
                    </div>
                </div>
                <div class="col-md-2 text-center">
                    <div class="stat-box">
                        <div class="stat-number text-muted" id="search-time">~2.3s</div>
                        <div class="stat-label">Search Time</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters Modal -->
<div class="modal fade" id="filtersModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Search Filters</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="filters-form">
                    <div class="mb-3">
                        <label for="max_price" class="form-label">Maximum Price ($)</label>
                        <input type="number" class="form-control" id="max_price" name="max_price" value="600" min="0">
                    </div>
                    <div class="mb-3">
                        <label for="currency" class="form-label">Currency</label>
                        <select class="form-control" id="currency" name="currency">
                            <option value="USD">USD ($)</option>
                            <option value="EUR">EUR (€)</option>
                            <option value="GBP">GBP (£)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="date_range" class="form-label">Date Range</label>
                        <select class="form-control" id="date_range" name="date_range">
                            <option value="">All dates</option>
                            <option value="this_week">This Week</option>
                            <option value="next_week">Next Week</option>
                            <option value="this_month">This Month</option>
                            <option value="next_month">Next Month</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="venue" class="form-label">Venue (optional)</label>
                        <input type="text" class="form-control" id="venue" name="venue" placeholder="e.g., Old Trafford, Wembley">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="applyFilters()">Apply Filters</button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.platform-badge {
    padding: 15px;
    border: 1px solid #e3e6f0;
    border-radius: 10px;
    transition: all 0.3s ease;
}

.platform-badge:hover {
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    transform: translateY(-2px);
}

.stat-box {
    padding: 10px;
}

.stat-number {
    font-size: 1.5rem;
    font-weight: bold;
    margin-bottom: 5px;
}

.stat-label {
    font-size: 0.8rem;
    color: #858796;
    text-transform: uppercase;
}

.demand-badge {
    font-size: 0.75rem;
    padding: 3px 8px;
    border-radius: 10px;
    font-weight: bold;
}

.demand-maximum {
    background-color: #e74a3b;
    color: white;
}

.demand-extremely-high {
    background-color: #f39c12;
    color: white;
}

.demand-very-high {
    background-color: #3498db;
    color: white;
}

.demand-high {
    background-color: #2ecc71;
    color: white;
}

.ticket-card {
    transition: all 0.3s ease;
    border: 1px solid #e3e6f0;
}

.ticket-card:hover {
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    transform: translateY(-2px);
}

.price-range {
    font-size: 1.2rem;
    font-weight: bold;
    color: #2e59d9;
}

.availability-indicator {
    font-size: 0.8rem;
    padding: 2px 6px;
    border-radius: 12px;
    background-color: #f8f9fc;
    border: 1px solid #e3e6f0;
}

.loading-spinner {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 3px solid #f3f3f3;
    border-top: 3px solid #3498db;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
@endpush

@push('scripts')
<script>
let currentFilters = {
    max_price: 600,
    currency: 'USD',
    date_range: '',
    venue: ''
};

// Load initial results
document.addEventListener('DOMContentLoaded', function() {
    loadHighDemandSports();
});

function loadHighDemandSports() {
    showLoading();
    
    // Simulate API call with sample data
    setTimeout(() => {
        const sampleData = {
            success: true,
            results: {
                tickets: [
                    {
                        title: 'Manchester United vs Liverpool',
                        venue: 'Old Trafford',
                        date: '2025-03-15',
                        min_price: 125,
                        max_price: 850,
                        platform: 'StubHub',
                        availability: 'Limited - Only 47 tickets left',
                        demand_level: 'EXTREMELY HIGH',
                        url: '#'
                    },
                    {
                        title: 'El Clasico: FC Barcelona vs Real Madrid',
                        venue: 'Camp Nou',
                        date: '2025-04-20',
                        min_price: 200,
                        max_price: 1200,
                        platform: 'Viagogo',
                        availability: 'High demand - 156 watching',
                        demand_level: 'EXTREMELY HIGH',
                        url: '#'
                    },
                    {
                        title: 'Champions League Final',
                        venue: 'Wembley Stadium',
                        date: '2025-05-28',
                        min_price: 300,
                        max_price: 2500,
                        platform: 'Ticketmaster',
                        availability: 'Sold out - Resale only',
                        demand_level: 'MAXIMUM',
                        url: '#'
                    },
                    {
                        title: 'Arsenal vs Manchester City',
                        venue: 'Emirates Stadium',
                        date: '2025-02-28',
                        min_price: 95,
                        max_price: 450,
                        platform: 'TickPick',
                        availability: 'Limited seats available',
                        demand_level: 'VERY HIGH',
                        url: '#'
                    },
                    {
                        title: 'Chelsea vs Tottenham (London Derby)',
                        venue: 'Stamford Bridge',
                        date: '2025-03-08',
                        min_price: 110,
                        max_price: 520,
                        platform: 'SeatGeek',
                        availability: '89 tickets available',
                        demand_level: 'HIGH',
                        url: '#'
                    },
                    {
                        title: 'Liverpool vs Manchester City',
                        venue: 'Anfield',
                        date: '2025-04-12',
                        min_price: 140,
                        max_price: 680,
                        platform: 'FunZone',
                        availability: 'High demand - 234 users watching',
                        demand_level: 'VERY HIGH',
                        url: '#'
                    }
                ],
                total_found: 6
            }
        };
        
        displayResults(sampleData.results);
        updateStatistics(sampleData.results);
    }, 1500);
}

function showLoading() {
    document.getElementById('search-results').innerHTML = `
        <div class="col-12 text-center py-5">
            <div class="loading-spinner"></div>
            <p class="mt-3 text-muted">Searching across all platforms...</p>
        </div>
    `;
}

function displayResults(results) {
    const container = document.getElementById('search-results');
    let html = '';
    
    const filteredTickets = results.tickets.filter(ticket => 
        ticket.min_price <= currentFilters.max_price
    );
    
    filteredTickets.forEach((ticket, index) => {
        const demandClass = getDemandClass(ticket.demand_level);
        const formattedDate = new Date(ticket.date).toLocaleDateString('en-US', {
            weekday: 'short',
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
        
        html += `
            <div class="col-lg-6 col-xl-4 mb-4">
                <div class="card ticket-card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 font-weight-bold">${ticket.title}</h6>
                        <span class="demand-badge ${demandClass}">${ticket.demand_level}</span>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <i class="fas fa-map-marker-alt text-muted"></i>
                            <span class="ms-1">${ticket.venue}</span>
                        </div>
                        <div class="mb-2">
                            <i class="fas fa-calendar text-muted"></i>
                            <span class="ms-1">${formattedDate}</span>
                        </div>
                        <div class="mb-2">
                            <i class="fas fa-globe text-muted"></i>
                            <span class="ms-1">${ticket.platform}</span>
                        </div>
                        <div class="mb-3">
                            <div class="price-range">$${ticket.min_price} - $${ticket.max_price}</div>
                        </div>
                        <div class="mb-3">
                            <div class="availability-indicator">
                                <i class="fas fa-info-circle"></i> ${ticket.availability}
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="${ticket.url}" class="btn btn-primary btn-sm w-100" target="_blank">
                            <i class="fas fa-external-link-alt"></i> View on ${ticket.platform}
                        </a>
                    </div>
                </div>
            </div>
        `;
    });
    
    if (filteredTickets.length === 0) {
        html = `
            <div class="col-12 text-center py-5">
                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No tickets found</h5>
                <p class="text-muted">Try adjusting your filters or check back later.</p>
            </div>
        `;
    }
    
    container.innerHTML = html;
}

function getDemandClass(demandLevel) {
    switch(demandLevel) {
        case 'MAXIMUM': return 'demand-maximum';
        case 'EXTREMELY HIGH': return 'demand-extremely-high';
        case 'VERY HIGH': return 'demand-very-high';
        case 'HIGH': return 'demand-high';
        default: return 'demand-high';
    }
}

function updateStatistics(results) {
    const tickets = results.tickets.filter(ticket => ticket.min_price <= currentFilters.max_price);
    
    document.getElementById('total-events').textContent = tickets.length;
    
    if (tickets.length > 0) {
        const avgMinPrice = Math.round(tickets.reduce((sum, t) => sum + t.min_price, 0) / tickets.length);
        const avgMaxPrice = Math.round(tickets.reduce((sum, t) => sum + t.max_price, 0) / tickets.length);
        const highDemandCount = tickets.filter(t => 
            ['HIGH', 'VERY HIGH', 'EXTREMELY HIGH', 'MAXIMUM'].includes(t.demand_level)
        ).length;
        
        document.getElementById('avg-min-price').textContent = '$' + avgMinPrice;
        document.getElementById('avg-max-price').textContent = '$' + avgMaxPrice;
        document.getElementById('high-demand-count').textContent = highDemandCount;
    }
}

function refreshResults() {
    loadHighDemandSports();
}

function applyFilters() {
    currentFilters.max_price = parseInt(document.getElementById('max_price').value) || 600;
    currentFilters.currency = document.getElementById('currency').value;
    currentFilters.date_range = document.getElementById('date_range').value;
    currentFilters.venue = document.getElementById('venue').value;
    
    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('filtersModal'));
    modal.hide();
    
    // Reload results with new filters
    loadHighDemandSports();
}
</script>
@endpush
@endsection
