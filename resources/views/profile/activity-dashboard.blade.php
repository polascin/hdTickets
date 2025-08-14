@extends('layouts.app')

@section('title', 'Activity Dashboard')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}?v={{ config('app.css_timestamp', time()) }}">
<style>
    .activity-dashboard {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: calc(100vh - 60px);
        padding: 2rem 0;
    }
    
    .dashboard-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 1rem;
    }
    
    .dashboard-header {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 16px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 8px 32px rgba(31, 38, 135, 0.37);
        border: 1px solid rgba(255, 255, 255, 0.18);
    }
    
    .widget-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .widget-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 8px 32px rgba(31, 38, 135, 0.37);
        border: 1px solid rgba(255, 255, 255, 0.18);
        transition: all 0.3s ease;
    }
    
    .widget-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 16px 48px rgba(31, 38, 135, 0.5);
    }
    
    .widget-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #f1f5f9;
    }
    
    .widget-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1e293b;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .widget-icon {
        width: 24px;
        height: 24px;
        color: #6366f1;
    }
    
    .stat-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
        margin-bottom: 1rem;
    }
    
    .stat-item {
        text-align: center;
        padding: 1rem;
        background: linear-gradient(135deg, #f8fafc, #e2e8f0);
        border-radius: 12px;
    }
    
    .stat-value {
        font-size: 2rem;
        font-weight: bold;
        color: #1e293b;
        margin-bottom: 0.25rem;
    }
    
    .stat-label {
        font-size: 0.875rem;
        color: #64748b;
        font-weight: 500;
    }
    
    .chart-container {
        position: relative;
        height: 300px;
        margin-top: 1rem;
    }
    
    .filters-bar {
        display: flex;
        gap: 1rem;
        align-items: center;
        flex-wrap: wrap;
    }
    
    .filter-select {
        padding: 0.5rem 1rem;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        background: white;
        font-size: 0.875rem;
    }
    
    .trend-indicator {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.25rem 0.5rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .trend-up {
        background: #dcfce7;
        color: #16a34a;
    }
    
    .trend-down {
        background: #fef2f2;
        color: #dc2626;
    }
    
    .trend-stable {
        background: #f1f5f9;
        color: #64748b;
    }
    
    .recent-items {
        max-height: 200px;
        overflow-y: auto;
    }
    
    .recent-item {
        display: flex;
        justify-content: between;
        align-items: center;
        padding: 0.75rem;
        margin-bottom: 0.5rem;
        background: #f8fafc;
        border-radius: 8px;
        border-left: 4px solid #6366f1;
    }
    
    .pagination-controls {
        display: flex;
        justify-content: center;
        gap: 0.5rem;
        margin-top: 1rem;
    }
    
    .pagination-btn {
        padding: 0.5rem 1rem;
        border: 1px solid #d1d5db;
        background: white;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .pagination-btn:hover {
        background: #f3f4f6;
    }
    
    .pagination-btn.active {
        background: #6366f1;
        color: white;
        border-color: #6366f1;
    }
    
    .export-btn {
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .export-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(16, 185, 129, 0.3);
    }
    
    .loading-spinner {
        display: inline-block;
        width: 20px;
        height: 20px;
        border: 3px solid #f3f3f3;
        border-top: 3px solid #6366f1;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .alert-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem;
        background: #f8fafc;
        border-radius: 8px;
        margin-bottom: 0.5rem;
        border-left: 4px solid;
    }
    
    .alert-active {
        border-left-color: #10b981;
    }
    
    .alert-paused {
        border-left-color: #f59e0b;
    }
    
    @media (max-width: 768px) {
        .widget-grid {
            grid-template-columns: 1fr;
        }
        
        .filters-bar {
            flex-direction: column;
            align-items: stretch;
        }
        
        .stat-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="activity-dashboard">
    <div class="dashboard-container">
        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <div class="flex justify-between items-center flex-wrap gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Activity Dashboard</h1>
                    <p class="text-gray-600">Track your ticket alerts, purchases, and platform activity</p>
                </div>
                
                <div class="filters-bar">
                    <select id="dateRangeFilter" class="filter-select">
                        <option value="7" {{ $dateRange == '7' ? 'selected' : '' }}>Last 7 Days</option>
                        <option value="30" {{ $dateRange == '30' ? 'selected' : '' }}>Last 30 Days</option>
                        <option value="90" {{ $dateRange == '90' ? 'selected' : '' }}>Last 90 Days</option>
                        <option value="365" {{ $dateRange == '365' ? 'selected' : '' }}>Last Year</option>
                    </select>
                    
                    <button id="exportBtn" class="export-btn">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Export Data
                    </button>
                </div>
            </div>
        </div>

        <!-- Widgets Grid -->
        <div class="widget-grid">
            <!-- Ticket Alerts Widget -->
            <div class="widget-card" id="alertsWidget">
                <div class="widget-header">
                    <h3 class="widget-title">
                        <svg class="widget-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4 17h5l-5 5v-5zM12 12l8-8a3 3 0 00-3-3l-8 8-8-8a3 3 0 00-3 3l8 8z"></path>
                        </svg>
                        Ticket Alerts
                    </h3>
                    <div class="loading-spinner" id="alertsLoading" style="display: none;"></div>
                </div>
                
                <div class="stat-grid">
                    <div class="stat-item">
                        <div class="stat-value" id="totalAlerts">{{ $alertsData['total_alerts'] }}</div>
                        <div class="stat-label">Total Alerts</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value" id="activeAlerts">{{ $alertsData['active_alerts'] }}</div>
                        <div class="stat-label">Active</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value" id="totalTriggers">{{ $alertsData['total_triggers'] }}</div>
                        <div class="stat-label">Total Triggers</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value" id="avgMatches">{{ $alertsData['avg_matches_per_alert'] }}</div>
                        <div class="stat-label">Avg Matches</div>
                    </div>
                </div>

                <div id="recentTriggers">
                    <h4 class="font-semibold mb-2 text-gray-700">Recent Triggers</h4>
                    <div class="recent-items">
                        @forelse($alertsData['recent_triggers'] as $trigger)
                            <div class="alert-item alert-{{ $trigger->status }}">
                                <div>
                                    <div class="font-medium">{{ $trigger->alert_name }}</div>
                                    <div class="text-sm text-gray-600">{{ $trigger->matches_found }} matches</div>
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ $trigger->triggered_at->diffForHumans() }}
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-500 text-sm">No recent triggers found</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Purchase History Widget -->
            <div class="widget-card" id="purchaseWidget">
                <div class="widget-header">
                    <h3 class="widget-title">
                        <svg class="widget-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.68 8.32M7 13h10m-10 0l-1.68 8.32M16 16h8"></path>
                        </svg>
                        Purchase Analytics
                    </h3>
                    <div class="loading-spinner" id="purchaseLoading" style="display: none;"></div>
                </div>
                
                <div class="stat-grid">
                    <div class="stat-item">
                        <div class="stat-value" id="totalSpent">${{ number_format($purchaseData['total_spent'], 2) }}</div>
                        <div class="stat-label">Total Spent</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value" id="successfulPurchases">{{ $purchaseData['successful_purchases'] }}</div>
                        <div class="stat-label">Successful</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value" id="avgTicketPrice">${{ number_format($purchaseData['average_ticket_price'], 2) }}</div>
                        <div class="stat-label">Avg Price</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value" id="successRate">{{ $purchaseData['success_rate'] }}%</div>
                        <div class="stat-label">Success Rate</div>
                    </div>
                </div>

                <!-- Platform Breakdown -->
                <div id="platformBreakdown">
                    <h4 class="font-semibold mb-2 text-gray-700">Platform Breakdown</h4>
                    <div class="recent-items">
                        @forelse($purchaseData['platform_breakdown'] as $platform => $data)
                            <div class="recent-item">
                                <div>
                                    <div class="font-medium">{{ ucfirst($platform) }}</div>
                                    <div class="text-sm text-gray-600">{{ $data['count'] }} purchases</div>
                                </div>
                                <div class="text-right">
                                    <div class="font-medium">${{ number_format($data['total'], 2) }}</div>
                                    <div class="text-xs text-gray-500">Avg: ${{ number_format($data['avg'], 2) }}</div>
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-500 text-sm">No purchase data available</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Saved Searches Widget -->
            <div class="widget-card" id="searchWidget">
                <div class="widget-header">
                    <h3 class="widget-title">
                        <svg class="widget-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        Saved Searches
                    </h3>
                    <div class="loading-spinner" id="searchLoading" style="display: none;"></div>
                </div>
                
                <div class="stat-grid">
                    <div class="stat-item">
                        <div class="stat-value" id="totalSearches">{{ $searchData['total_saved_searches'] }}</div>
                        <div class="stat-label">Total Saved</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value" id="teamSearches">{{ $searchData['saved_team_searches']->count() }}</div>
                        <div class="stat-label">Team Searches</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value" id="venueSearches">{{ $searchData['saved_venue_searches']->count() }}</div>
                        <div class="stat-label">Venue Searches</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value" id="frequentQueries">{{ $searchData['frequent_queries']->count() }}</div>
                        <div class="stat-label">Frequent Queries</div>
                    </div>
                </div>

                <!-- Frequent Queries -->
                <div id="frequentQueriesList">
                    <h4 class="font-semibold mb-2 text-gray-700">Most Frequent Searches</h4>
                    <div class="recent-items">
                        @forelse($searchData['frequent_queries'] as $query)
                            <div class="recent-item">
                                <div>
                                    <div class="font-medium">{{ $query->alert_name }}</div>
                                    <div class="text-sm text-gray-600">Search frequency</div>
                                </div>
                                <div class="text-right">
                                    <span class="trend-indicator trend-stable">{{ $query->frequency }}x</span>
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-500 text-sm">No frequent queries found</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Watchlist Widget -->
            <div class="widget-card" id="watchlistWidget">
                <div class="widget-header">
                    <h3 class="widget-title">
                        <svg class="widget-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                        Watchlist & Trends
                    </h3>
                    <div class="loading-spinner" id="watchlistLoading" style="display: none;"></div>
                </div>
                
                <div class="stat-grid">
                    <div class="stat-item">
                        <div class="stat-value" id="watchlistItems">{{ $watchlistData['total_watchlist_items'] }}</div>
                        <div class="stat-label">Watchlist Items</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value" id="favoriteTeams">{{ $watchlistData['favorite_teams']->count() }}</div>
                        <div class="stat-label">Favorite Teams</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value" id="favoriteVenues">{{ $watchlistData['favorite_venues']->count() }}</div>
                        <div class="stat-label">Favorite Venues</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value" id="avgPrice">${{ number_format($watchlistData['average_price'] ?? 0, 2) }}</div>
                        <div class="stat-label">Avg Price</div>
                    </div>
                </div>

                <!-- Price Trends -->
                <div id="priceTrendsList">
                    <h4 class="font-semibold mb-2 text-gray-700">Price Trends</h4>
                    <div class="recent-items">
                        @forelse($watchlistData['price_trends'] as $eventName => $trend)
                            <div class="recent-item">
                                <div>
                                    <div class="font-medium text-sm">{{ Str::limit($eventName, 30) }}</div>
                                    <div class="text-xs text-gray-600">${{ number_format($trend['first_price'], 2) }} → ${{ number_format($trend['last_price'], 2) }}</div>
                                </div>
                                <div class="text-right">
                                    <span class="trend-indicator trend-{{ $trend['trend'] }}">
                                        {{ $trend['percent_change'] > 0 ? '+' : '' }}{{ $trend['percent_change'] }}%
                                    </span>
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-500 text-sm">No price trends available</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="widget-card">
            <div class="widget-header">
                <h3 class="widget-title">
                    <svg class="widget-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    Activity Analytics
                </h3>
                <div class="loading-spinner" id="chartsLoading" style="display: none;"></div>
            </div>
            
            <!-- Chart Tabs -->
            <div class="flex space-x-4 mb-4 border-b">
                <button class="chart-tab active" data-chart="alerts">Alert Triggers</button>
                <button class="chart-tab" data-chart="purchases">Purchase Activity</button>
                <button class="chart-tab" data-chart="logins">Login Activity</button>
            </div>
            
            <div class="chart-container">
                <canvas id="activityChart"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@3.0.0/dist/chartjs-adapter-date-fns.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let activityChart = null;
    let currentChartType = 'alerts';
    
    // Chart.js configuration
    const chartConfig = {
        type: 'line',
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                }
            },
            scales: {
                x: {
                    type: 'time',
                    time: {
                        unit: 'day',
                        displayFormats: {
                            day: 'MMM dd'
                        }
                    },
                    title: {
                        display: true,
                        text: 'Date'
                    }
                },
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Count'
                    }
                }
            },
            interaction: {
                mode: 'nearest',
                axis: 'x',
                intersect: false
            }
        }
    };

    // Initialize chart with alerts data
    initializeChart('alerts');

    // Chart data from backend
    const chartData = @json($chartData);

    function initializeChart(type) {
        const ctx = document.getElementById('activityChart').getContext('2d');
        
        if (activityChart) {
            activityChart.destroy();
        }

        let data = getChartData(type);
        
        activityChart = new Chart(ctx, {
            ...chartConfig,
            data: data
        });

        currentChartType = type;
    }

    function getChartData(type) {
        switch(type) {
            case 'alerts':
                return {
                    labels: chartData.alert_triggers.map(item => item.date),
                    datasets: [{
                        label: 'Alert Triggers',
                        data: chartData.alert_triggers.map(item => ({
                            x: item.date,
                            y: item.triggers
                        })),
                        borderColor: 'rgb(99, 102, 241)',
                        backgroundColor: 'rgba(99, 102, 241, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                };
                
            case 'purchases':
                return {
                    labels: chartData.purchase_attempts.map(item => item.date),
                    datasets: [
                        {
                            label: 'Purchase Attempts',
                            data: chartData.purchase_attempts.map(item => ({
                                x: item.date,
                                y: item.attempts
                            })),
                            borderColor: 'rgb(16, 185, 129)',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            tension: 0.4,
                            fill: true,
                            yAxisID: 'y'
                        },
                        {
                            label: 'Amount Spent ($)',
                            data: chartData.purchase_attempts.map(item => ({
                                x: item.date,
                                y: parseFloat(item.spent)
                            })),
                            borderColor: 'rgb(245, 158, 11)',
                            backgroundColor: 'rgba(245, 158, 11, 0.1)',
                            tension: 0.4,
                            fill: true,
                            yAxisID: 'y1'
                        }
                    ]
                };
                
            case 'logins':
                return {
                    labels: chartData.login_activity.map(item => item.date),
                    datasets: [{
                        label: 'Login Activity',
                        data: chartData.login_activity.map(item => ({
                            x: item.date,
                            y: item.logins
                        })),
                        borderColor: 'rgb(168, 85, 247)',
                        backgroundColor: 'rgba(168, 85, 247, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                };
                
            default:
                return { labels: [], datasets: [] };
        }
    }

    // Chart tab switching
    document.querySelectorAll('.chart-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            document.querySelectorAll('.chart-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            const chartType = this.dataset.chart;
            initializeChart(chartType);
        });
    });

    // Date range filter
    document.getElementById('dateRangeFilter').addEventListener('change', function() {
        const dateRange = this.value;
        updateDashboard(dateRange);
    });

    // Export functionality
    document.getElementById('exportBtn').addEventListener('click', function() {
        const dateRange = document.getElementById('dateRangeFilter').value;
        const exportUrl = `{{ route('profile.activity.export') }}?date_range=${dateRange}`;
        
        // Create a temporary link and click it to download
        const link = document.createElement('a');
        link.href = exportUrl;
        link.download = `activity-dashboard-${new Date().toISOString().split('T')[0]}.json`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });

    // Update dashboard with new date range
    function updateDashboard(dateRange) {
        showLoadingSpinners();
        
        fetch(`{{ route('profile.activity.widget-data') }}?date_range=${dateRange}&widget=all`)
            .then(response => response.json())
            .then(data => {
                updateWidgets(data);
                hideLoadingSpinners();
            })
            .catch(error => {
                console.error('Error updating dashboard:', error);
                hideLoadingSpinners();
            });
    }

    function showLoadingSpinners() {
        document.querySelectorAll('.loading-spinner').forEach(spinner => {
            spinner.style.display = 'inline-block';
        });
    }

    function hideLoadingSpinners() {
        document.querySelectorAll('.loading-spinner').forEach(spinner => {
            spinner.style.display = 'none';
        });
    }

    function updateWidgets(data) {
        // Update alerts widget if data provided
        if (data.alerts) {
            document.getElementById('totalAlerts').textContent = data.alerts.total_alerts;
            document.getElementById('activeAlerts').textContent = data.alerts.active_alerts;
            document.getElementById('totalTriggers').textContent = data.alerts.total_triggers;
            document.getElementById('avgMatches').textContent = data.alerts.avg_matches_per_alert;
        }

        // Update purchases widget if data provided
        if (data.purchases) {
            document.getElementById('totalSpent').textContent = '$' + parseFloat(data.purchases.total_spent).toFixed(2);
            document.getElementById('successfulPurchases').textContent = data.purchases.successful_purchases;
            document.getElementById('avgTicketPrice').textContent = '$' + parseFloat(data.purchases.average_ticket_price).toFixed(2);
            document.getElementById('successRate').textContent = data.purchases.success_rate + '%';
        }

        // Update chart if new data provided
        if (data.charts) {
            initializeChart(currentChartType);
        }
    }

    // Add CSS for chart tabs
    const style = document.createElement('style');
    style.textContent = `
        .chart-tab {
            padding: 0.5rem 1rem;
            border: none;
            background: transparent;
            color: #64748b;
            font-weight: 500;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: all 0.2s;
        }
        
        .chart-tab:hover {
            color: #475569;
        }
        
        .chart-tab.active {
            color: #6366f1;
            border-bottom-color: #6366f1;
        }
    `;
    document.head.appendChild(style);
});
</script>
@endpush
