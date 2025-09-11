@extends('layouts.modern')
@section('title', 'Analytics Dashboard - HD Tickets')

@push('styles')
    <meta name="analytics-config" content="{{ json_encode($config) }}">
    <meta name="analytics-filters" content="{{ json_encode($filters) }}">
@endpush

@push('styles')
    <!-- D3.js will be loaded via CDN in scripts -->
    <style>
        .analytics-dashboard {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .dashboard-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        
        .widget-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .widget-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        
        .metric-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
            position: relative;
            overflow: hidden;
        }
        
        .metric-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            transform: translate(30px, -30px);
        }
        
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }
        
        .chart-container.large {
            height: 400px;
        }
        
        .chart-container.small {
            height: 200px;
        }
        
        .filters-panel {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .alert-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #e74c3c;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            z-index: 10;
        }
        
        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .severity-critical { color: #e74c3c; }
        .severity-high { color: #f39c12; }
        .severity-medium { color: #f1c40f; }
        .severity-low { color: #3498db; }
        
        .trend-up { color: #2ecc71; }
        .trend-down { color: #e74c3c; }
        .trend-neutral { color: #95a5a6; }
        
        .filter-chip {
            background: #667eea;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            margin: 2px;
            display: inline-block;
        }
        
        .export-buttons .btn {
            margin: 0 5px 10px 0;
        }
        
        .refresh-indicator {
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .refresh-indicator.active {
            opacity: 1;
        }
    </style>
@endpush

@section('content')
<div class="analytics-dashboard">
    <div class="container-fluid py-4">
        <div class="dashboard-container p-4">
            <!-- Dashboard Header -->
            <div class="row mb-4">
                <div class="col-md-8">
                    <h1 class="h2 text-primary mb-1">
                        <i class="fas fa-chart-line me-2"></i>
                        Analytics Dashboard
                    </h1>
                    <p class="text-muted mb-0">
                        Sports Event Ticket Analytics & Performance Insights
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="refresh-indicator" id="refreshIndicator">
                        <i class="fas fa-sync-alt"></i>
                        <span class="ms-1">Updating...</span>
                    </div>
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-outline-primary" id="refreshBtn">
                            <i class="fas fa-sync-alt"></i>
                            Refresh
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="exportBtn">
                            <i class="fas fa-download"></i>
                            Export
                        </button>
                        @if(auth()->user()->role === 'admin')
                        <button type="button" class="btn btn-outline-warning" id="clearCacheBtn">
                            <i class="fas fa-trash-alt"></i>
                            Clear Cache
                        </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Filters Panel -->
            <div class="filters-panel p-3 mb-4">
                <form id="filtersForm" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Time Range</label>
                        <select name="days" class="form-select" id="timeRangeSelect">
                            <option value="7" {{ ($filters['days'] ?? 30) == 7 ? 'selected' : '' }}>Last 7 Days</option>
                            <option value="14" {{ ($filters['days'] ?? 30) == 14 ? 'selected' : '' }}>Last 2 Weeks</option>
                            <option value="30" {{ ($filters['days'] ?? 30) == 30 ? 'selected' : '' }}>Last 30 Days</option>
                            <option value="60" {{ ($filters['days'] ?? 30) == 60 ? 'selected' : '' }}>Last 2 Months</option>
                            <option value="90" {{ ($filters['days'] ?? 30) == 90 ? 'selected' : '' }}>Last 3 Months</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Platform</label>
                        <select name="platform" class="form-select" id="platformSelect">
                            <option value="">All Platforms</option>
                            <!-- Options will be populated via JavaScript -->
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Sport Category</label>
                        <select name="sport_category" class="form-select" id="categorySelect">
                            <option value="">All Categories</option>
                            <!-- Options will be populated via JavaScript -->
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-filter"></i>
                            Apply Filters
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="resetFiltersBtn">
                            Reset
                        </button>
                    </div>
                </form>
            </div>

            <!-- Overview Metrics Cards -->
            <div class="row mb-4" id="overviewMetrics">
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="metric-card p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="h4 mb-1" id="totalEvents">{{ number_format($initialData['overview_metrics']['total_events'] ?? 0) }}</h3>
                                <p class="mb-0">Total Events</p>
                            </div>
                            <i class="fas fa-calendar-alt fa-2x opacity-75"></i>
                        </div>
                        <div class="mt-2">
                            <span class="trend-indicator" id="eventsGrowth">
                                <!-- Growth indicator will be populated via JavaScript -->
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="metric-card p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="h4 mb-1" id="totalTickets">{{ number_format($initialData['overview_metrics']['total_tickets'] ?? 0) }}</h3>
                                <p class="mb-0">Total Tickets</p>
                            </div>
                            <i class="fas fa-ticket-alt fa-2x opacity-75"></i>
                        </div>
                        <div class="mt-2">
                            <span class="trend-indicator" id="ticketsGrowth">
                                <!-- Growth indicator will be populated via JavaScript -->
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="metric-card p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="h4 mb-1" id="avgPrice">${{ number_format($initialData['overview_metrics']['avg_ticket_price'] ?? 0, 2) }}</h3>
                                <p class="mb-0">Average Price</p>
                            </div>
                            <i class="fas fa-dollar-sign fa-2x opacity-75"></i>
                        </div>
                        <div class="mt-2">
                            <span class="trend-indicator" id="priceGrowth">
                                <!-- Growth indicator will be populated via JavaScript -->
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="metric-card p-4 position-relative">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="h4 mb-1" id="anomalyCount">{{ count($anomalies['price_anomalies']['anomalies'] ?? []) }}</h3>
                                <p class="mb-0">Anomalies</p>
                            </div>
                            <i class="fas fa-exclamation-triangle fa-2x opacity-75"></i>
                        </div>
                        @if(count($anomalies['price_anomalies']['anomalies'] ?? []) > 0)
                        <span class="alert-badge">{{ count($anomalies['price_anomalies']['anomalies']) }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Charts Row 1 -->
            <div class="row mb-4">
                <!-- Platform Performance Chart -->
                <div class="col-lg-8 mb-3">
                    <div class="widget-card p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-chart-bar text-primary me-2"></i>
                                Platform Performance
                            </h5>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-secondary active" data-chart="bar">Bar</button>
                                <button type="button" class="btn btn-outline-secondary" data-chart="radar">Radar</button>
                            </div>
                        </div>
                        <div class="chart-container large" id="platformPerformanceContainer">
                            <canvas id="platformPerformanceChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Real-time Alerts -->
                <div class="col-lg-4 mb-3">
                    <div class="widget-card p-4">
                        <h5 class="card-title mb-3">
                            <i class="fas fa-bell text-warning me-2"></i>
                            Real-time Alerts
                        </h5>
                        <div id="alertsList" class="alerts-container" style="max-height: 350px; overflow-y: auto;">
                            <!-- Alerts will be populated via JavaScript -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row 2 -->
            <div class="row mb-4">
                <!-- Pricing Trends Chart -->
                <div class="col-lg-6 mb-3">
                    <div class="widget-card p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-chart-line text-success me-2"></i>
                                Pricing Trends
                            </h5>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-secondary active" data-period="daily">Daily</button>
                                <button type="button" class="btn btn-outline-secondary" data-period="weekly">Weekly</button>
                                <button type="button" class="btn btn-outline-secondary" data-period="monthly">Monthly</button>
                            </div>
                        </div>
                        <div class="chart-container" id="pricingTrendsContainer">
                            <canvas id="pricingTrendsChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Event Popularity Chart -->
                <div class="col-lg-6 mb-3">
                    <div class="widget-card p-4">
                        <h5 class="card-title mb-3">
                            <i class="fas fa-fire text-danger me-2"></i>
                            Event Popularity
                        </h5>
                        <div class="chart-container" id="eventPopularityContainer">
                            <canvas id="eventPopularityChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Analytics Tables Row -->
            <div class="row">
                <!-- Trending Events Table -->
                <div class="col-lg-6 mb-3">
                    <div class="widget-card p-4">
                        <h5 class="card-title mb-3">
                            <i class="fas fa-trophy text-warning me-2"></i>
                            Trending Events
                        </h5>
                        <div class="table-responsive">
                            <table class="table table-sm" id="trendingEventsTable">
                                <thead>
                                    <tr>
                                        <th>Event</th>
                                        <th>Category</th>
                                        <th>Tickets</th>
                                        <th>Avg Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data will be populated via JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Platform Rankings -->
                <div class="col-lg-6 mb-3">
                    <div class="widget-card p-4">
                        <h5 class="card-title mb-3">
                            <i class="fas fa-medal text-info me-2"></i>
                            Platform Rankings
                        </h5>
                        <div class="table-responsive">
                            <table class="table table-sm" id="platformRankingsTable">
                                <thead>
                                    <tr>
                                        <th>Platform</th>
                                        <th>Market Share</th>
                                        <th>Avg Price</th>
                                        <th>Quality Score</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data will be populated via JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Export Modal -->
<div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exportModalLabel">Export Analytics Data</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="exportForm">
                    <div class="mb-3">
                        <label class="form-label">Export Format</label>
                        <select name="format" class="form-select" required>
                            <option value="pdf">PDF Report</option>
                            <option value="xlsx">Excel Spreadsheet</option>
                            <option value="csv">CSV Data</option>
                            <option value="json">JSON Data</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Include Sections</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="sections[]" value="overview" checked>
                            <label class="form-check-label">Overview Metrics</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="sections[]" value="platform_performance" checked>
                            <label class="form-check-label">Platform Performance</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="sections[]" value="pricing_trends">
                            <label class="form-check-label">Pricing Trends</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="sections[]" value="event_popularity">
                            <label class="form-check-label">Event Popularity</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="sections[]" value="anomalies">
                            <label class="form-check-label">Anomalies</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="startExportBtn">
                    <i class="fas fa-download me-1"></i>
                    Export Data
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@vite('resources/js/vendor/chart.js')
@vite('resources/js/vendor/d3.js')

<script>
// Global analytics dashboard object
const AnalyticsDashboard = {
    config: @json($config),
    initialData: @json($initialData),
    anomalies: @json($anomalies),
    charts: {},
    refreshInterval: null,
    
    init() {
        this.setupEventListeners();
        this.initializeCharts();
        this.loadFilterOptions();
        this.updateOverviewMetrics();
        this.updateAnomaliesWidget();
        this.updateTables();
        this.startAutoRefresh();
        
        console.log('Analytics Dashboard initialized');
    },
    
    setupEventListeners() {
        // Refresh button
        document.getElementById('refreshBtn').addEventListener('click', () => {
            this.refreshDashboard();
        });
        
        // Export button
        document.getElementById('exportBtn').addEventListener('click', () => {
            const exportModal = new bootstrap.Modal(document.getElementById('exportModal'));
            exportModal.show();
        });
        
        // Export form submission
        document.getElementById('startExportBtn').addEventListener('click', () => {
            this.exportData();
        });
        
        // Clear cache button (admin only)
        const clearCacheBtn = document.getElementById('clearCacheBtn');
        if (clearCacheBtn) {
            clearCacheBtn.addEventListener('click', () => {
                this.clearCache();
            });
        }
        
        // Filters form
        document.getElementById('filtersForm').addEventListener('submit', (e) => {
            e.preventDefault();
            this.applyFilters();
        });
        
        // Reset filters
        document.getElementById('resetFiltersBtn').addEventListener('click', () => {
            this.resetFilters();
        });
        
        // Chart type toggles
        document.querySelectorAll('[data-chart]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const chartType = e.target.dataset.chart;
                this.switchChartType('platformPerformance', chartType);
            });
        });
        
        // Period toggles
        document.querySelectorAll('[data-period]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const period = e.target.dataset.period;
                this.switchPeriod(period);
            });
        });
    },
    
    initializeCharts() {
        // Platform Performance Chart
        this.charts.platformPerformance = this.createPlatformPerformanceChart();
        
        // Pricing Trends Chart
        this.charts.pricingTrends = this.createPricingTrendsChart();
        
        // Event Popularity Chart
        this.charts.eventPopularity = this.createEventPopularityChart();
    },
    
    createPlatformPerformanceChart() {
        const ctx = document.getElementById('platformPerformanceChart').getContext('2d');
        const data = this.initialData.platform_performance?.platforms || [];
        
        return new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.map(p => p.platform),
                datasets: [{
                    label: 'Total Tickets',
                    data: data.map(p => p.performance.total_tickets),
                    backgroundColor: this.config.charts.platform_performance.colors,
                    borderRadius: 8,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            afterLabel: (context) => {
                                const platform = data[context.dataIndex];
                                return [
                                    `Events: ${platform.performance.unique_events}`,
                                    `Avg Price: $${platform.performance.avg_price}`,
                                ];
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    },
    
    createPricingTrendsChart() {
        const ctx = document.getElementById('pricingTrendsChart').getContext('2d');
        
        // Generate sample time-series data for demo
        const dates = [];
        const prices = [];
        for (let i = 30; i >= 0; i--) {
            const date = new Date();
            date.setDate(date.getDate() - i);
            dates.push(date);
            prices.push(Math.random() * 100 + 50 + Math.sin(i / 5) * 20);
        }
        
        return new Chart(ctx, {
            type: 'line',
            data: {
                labels: dates,
                datasets: [{
                    label: 'Average Price',
                    data: prices,
                    borderColor: this.config.charts.price_trends.colors[0],
                    backgroundColor: this.config.charts.price_trends.colors[0] + '20',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 0,
                    pointHoverRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index',
                },
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        type: 'time',
                        time: {
                            unit: 'day'
                        },
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        },
                        ticks: {
                            callback: (value) => '$' + value
                        }
                    }
                }
            }
        });
    },
    
    createEventPopularityChart() {
        const ctx = document.getElementById('eventPopularityChart').getContext('2d');
        const data = this.initialData.event_popularity?.trending_events?.slice(0, 6) || [];
        
        return new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: data.map(e => e.name?.substring(0, 20) + '...'),
                datasets: [{
                    data: data.map(e => e.ticket_count),
                    backgroundColor: this.config.charts.event_popularity.colors,
                    borderWidth: 2,
                    borderColor: '#fff',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                        }
                    }
                }
            }
        });
    },
    
    async loadFilterOptions() {
        try {
            const response = await fetch('/analytics/filter-options');
            const result = await response.json();
            
            if (result.success) {
                this.populateFilterOptions(result.data);
            }
        } catch (error) {
            console.error('Failed to load filter options:', error);
        }
    },
    
    populateFilterOptions(options) {
        // Populate platform options
        const platformSelect = document.getElementById('platformSelect');
        options.platforms.forEach(platform => {
            const option = new Option(platform.label, platform.value);
            platformSelect.add(option);
        });
        
        // Populate category options
        const categorySelect = document.getElementById('categorySelect');
        options.categories.forEach(category => {
            const option = new Option(category.label, category.value);
            categorySelect.add(option);
        });
    },
    
    updateOverviewMetrics() {
        const metrics = this.initialData.overview_metrics || {};
        const growth = metrics.growth_metrics || {};
        
        // Update metric values
        document.getElementById('totalEvents').textContent = this.formatNumber(metrics.total_events || 0);
        document.getElementById('totalTickets').textContent = this.formatNumber(metrics.total_tickets || 0);
        document.getElementById('avgPrice').textContent = '$' + this.formatNumber(metrics.avg_ticket_price || 0, 2);
        
        // Update growth indicators
        this.updateGrowthIndicator('eventsGrowth', growth.events_growth || 0);
        this.updateGrowthIndicator('ticketsGrowth', growth.tickets_growth || 0);
        this.updateGrowthIndicator('priceGrowth', growth.price_growth || 0);
    },
    
    updateGrowthIndicator(elementId, value) {
        const element = document.getElementById(elementId);
        const isPositive = value > 0;
        const isNegative = value < 0;
        
        element.className = `trend-indicator ${isPositive ? 'trend-up' : isNegative ? 'trend-down' : 'trend-neutral'}`;
        element.innerHTML = `
            <i class="fas fa-arrow-${isPositive ? 'up' : isNegative ? 'down' : 'right'} me-1"></i>
            ${Math.abs(value).toFixed(1)}%
        `;
    },
    
    updateAnomaliesWidget() {
        const alertsList = document.getElementById('alertsList');
        const anomalies = this.anomalies.price_anomalies?.anomalies || [];
        
        if (anomalies.length === 0) {
            alertsList.innerHTML = '<p class="text-muted text-center">No recent anomalies detected</p>';
            return;
        }
        
        alertsList.innerHTML = anomalies.slice(0, 10).map(anomaly => `
            <div class="alert alert-sm alert-${this.getSeverityClass(anomaly.severity)} mb-2">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <strong>${anomaly.type.replace('_', ' ')}</strong>
                        <br>
                        <small>Price: $${anomaly.price} (Z-score: ${anomaly.z_score?.toFixed(2)})</small>
                        <br>
                        <small class="text-muted">${anomaly.platform}</small>
                    </div>
                    <span class="badge bg-${this.getSeverityClass(anomaly.severity)}">
                        ${anomaly.severity}
                    </span>
                </div>
            </div>
        `).join('');
    },
    
    updateTables() {
        this.updateTrendingEventsTable();
        this.updatePlatformRankingsTable();
    },
    
    updateTrendingEventsTable() {
        const tbody = document.querySelector('#trendingEventsTable tbody');
        const events = this.initialData.event_popularity?.trending_events || [];
        
        tbody.innerHTML = events.slice(0, 10).map(event => `
            <tr>
                <td>
                    <div class="text-truncate" style="max-width: 150px;" title="${event.name}">
                        ${event.name}
                    </div>
                </td>
                <td><span class="badge bg-secondary">${event.category}</span></td>
                <td>${this.formatNumber(event.ticket_count)}</td>
                <td>$${this.formatNumber(event.avg_price, 2)}</td>
            </tr>
        `).join('');
    },
    
    updatePlatformRankingsTable() {
        const tbody = document.querySelector('#platformRankingsTable tbody');
        const platforms = this.initialData.platform_performance?.platforms || [];
        const marketShare = this.initialData.platform_performance?.market_share || [];
        
        tbody.innerHTML = platforms.slice(0, 10).map(platform => {
            const share = marketShare.find(s => s.platform === platform.platform);
            return `
                <tr>
                    <td><strong>${platform.platform}</strong></td>
                    <td>
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar" role="progressbar" 
                                 style="width: ${share?.market_share || 0}%;">
                                ${(share?.market_share || 0).toFixed(1)}%
                            </div>
                        </div>
                    </td>
                    <td>$${this.formatNumber(platform.performance.avg_price, 2)}</td>
                    <td>
                        <span class="badge bg-${this.getQualityBadgeClass(platform.quality_metrics?.accuracy_score || 0)}">
                            ${((platform.quality_metrics?.accuracy_score || 0) * 100).toFixed(0)}%
                        </span>
                    </td>
                </tr>
            `;
        }).join('');
    },
    
    async refreshDashboard() {
        this.showRefreshIndicator();
        
        try {
            const filters = this.getCurrentFilters();
            const response = await fetch('/analytics/dashboard-data?' + new URLSearchParams(filters));
            const result = await response.json();
            
            if (result.success) {
                this.initialData = result.data;
                this.updateAllComponents();
            } else {
                this.showError('Failed to refresh dashboard data');
            }
        } catch (error) {
            console.error('Dashboard refresh failed:', error);
            this.showError('Connection error. Please try again.');
        } finally {
            this.hideRefreshIndicator();
        }
    },
    
    updateAllComponents() {
        this.updateOverviewMetrics();
        this.updateChartsData();
        this.updateTables();
        this.updateAnomaliesWidget();
    },
    
    updateChartsData() {
        // Update platform performance chart
        const platformData = this.initialData.platform_performance?.platforms || [];
        this.charts.platformPerformance.data.labels = platformData.map(p => p.platform);
        this.charts.platformPerformance.data.datasets[0].data = platformData.map(p => p.performance.total_tickets);
        this.charts.platformPerformance.update();
        
        // Update other charts similarly...
    },
    
    async applyFilters() {
        const filters = this.getCurrentFilters();
        
        // Update URL without reloading
        const url = new URL(window.location);
        Object.keys(filters).forEach(key => {
            if (filters[key]) {
                url.searchParams.set(key, filters[key]);
            } else {
                url.searchParams.delete(key);
            }
        });
        window.history.pushState({}, '', url);
        
        // Refresh with new filters
        await this.refreshDashboard();
    },
    
    resetFilters() {
        document.getElementById('filtersForm').reset();
        document.getElementById('timeRangeSelect').value = '30';
        this.applyFilters();
    },
    
    getCurrentFilters() {
        const formData = new FormData(document.getElementById('filtersForm'));
        const filters = {};
        
        for (let [key, value] of formData.entries()) {
            if (value) {
                filters[key] = value;
            }
        }
        
        return filters;
    },
    
    async exportData() {
        const form = document.getElementById('exportForm');
        const formData = new FormData(form);
        
        // Add current filters to export
        const filters = this.getCurrentFilters();
        Object.keys(filters).forEach(key => {
            formData.append(key, filters[key]);
        });
        
        try {
            const response = await fetch('/analytics/export', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Create download link
                const link = document.createElement('a');
                link.href = result.download_url;
                link.download = result.filename;
                link.click();
                
                bootstrap.Modal.getInstance(document.getElementById('exportModal')).hide();
                this.showSuccess('Export completed successfully!');
            } else {
                this.showError('Export failed: ' + result.error);
            }
        } catch (error) {
            console.error('Export failed:', error);
            this.showError('Export failed. Please try again.');
        }
    },
    
    async clearCache() {
        if (!confirm('Are you sure you want to clear the analytics cache?')) {
            return;
        }
        
        try {
            const response = await fetch('/analytics/clear-cache', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                }
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showSuccess('Cache cleared successfully!');
                await this.refreshDashboard();
            } else {
                this.showError('Failed to clear cache');
            }
        } catch (error) {
            console.error('Cache clear failed:', error);
            this.showError('Failed to clear cache');
        }
    },
    
    startAutoRefresh() {
        if (this.config.auto_refresh) {
            this.refreshInterval = setInterval(() => {
                this.refreshDashboard();
            }, (this.config.refresh_interval || 30) * 1000);
        }
    },
    
    stopAutoRefresh() {
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
            this.refreshInterval = null;
        }
    },
    
    showRefreshIndicator() {
        document.getElementById('refreshIndicator').classList.add('active');
    },
    
    hideRefreshIndicator() {
        document.getElementById('refreshIndicator').classList.remove('active');
    },
    
    showError(message) {
        // Create toast or alert for error
        console.error(message);
        alert('Error: ' + message);
    },
    
    showSuccess(message) {
        // Create toast or alert for success
        console.log(message);
        alert(message);
    },
    
    // Utility methods
    formatNumber(num, decimals = 0) {
        if (typeof num !== 'number') return '0';
        return num.toLocaleString('en-US', { 
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals 
        });
    },
    
    getSeverityClass(severity) {
        const classes = {
            'critical': 'danger',
            'high': 'warning',
            'medium': 'info',
            'low': 'secondary'
        };
        return classes[severity] || 'secondary';
    },
    
    getQualityBadgeClass(score) {
        if (score >= 0.9) return 'success';
        if (score >= 0.75) return 'primary';
        if (score >= 0.5) return 'warning';
        return 'danger';
    }
};

// Initialize dashboard when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    AnalyticsDashboard.init();
});

// Cleanup on page unload
window.addEventListener('beforeunload', function() {
    AnalyticsDashboard.stopAutoRefresh();
});
</script>
@endpush
