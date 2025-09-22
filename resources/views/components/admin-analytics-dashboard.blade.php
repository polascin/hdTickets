{{-- Admin Analytics Dashboard --}}
{{-- Comprehensive analytics and reporting interface for platform performance insights --}}

<div x-data="adminAnalytics()" x-init="init()" class="admin-analytics">
    {{-- Header with Date Range Selector --}}
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                    <svg class="w-7 h-7 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"></path>
                    </svg>
                    Analytics Dashboard
                </h1>
                <p class="text-gray-600 mt-1">Platform performance and insights</p>
            </div>
            <div class="flex items-center gap-4">
                {{-- Date Range Picker --}}
                <div class="flex items-center gap-2">
                    <label class="text-sm font-medium text-gray-700">Period:</label>
                    <select 
                        x-model="selectedPeriod" 
                        @change="loadAnalytics()"
                        class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                        <option value="7d">Last 7 Days</option>
                        <option value="30d">Last 30 Days</option>
                        <option value="90d">Last 90 Days</option>
                        <option value="1y">Last Year</option>
                    </select>
                </div>
                <button 
                    @click="exportReport()"
                    class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 flex items-center gap-2"
                >
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                    Export Report
                </button>
            </div>
        </div>
    </div>

    {{-- Key Metrics Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        {{-- Total Revenue --}}
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Revenue</p>
                    <p class="text-2xl font-bold text-gray-900" x-text="formatCurrency(metrics.revenue.total)"></p>
                    <div class="flex items-center mt-2">
                        <svg 
                            :class="metrics.revenue.change >= 0 ? 'text-green-500' : 'text-red-500'"
                            class="w-4 h-4 mr-1" 
                            fill="currentColor" 
                            viewBox="0 0 20 20"
                        >
                            <path 
                                :d="metrics.revenue.change >= 0 ? 'M10 17a1 1 0 01-1-1V6a1 1 0 112 0v10a1 1 0 01-1 1z M8.293 7.293a1 1 0 011.414 0L10 7.586l.293-.293a1 1 0 011.414 1.414l-1 1a1 1 0 01-1.414 0l-1-1a1 1 0 010-1.414z' : 'M10 3a1 1 0 01-1 1v10a1 1 0 11-2 0V4a1 1 0 01-1-1z M11.707 12.707a1 1 0 01-1.414 0L10 12.414l-.293.293a1 1 0 01-1.414-1.414l1-1a1 1 0 011.414 0l1 1a1 1 0 010 1.414z'"
                            ></path>
                        </svg>
                        <span 
                            :class="metrics.revenue.change >= 0 ? 'text-green-700' : 'text-red-700'"
                            class="text-sm font-medium"
                            x-text="Math.abs(metrics.revenue.change) + '%'"
                        ></span>
                        <span class="text-sm text-gray-500 ml-1">vs last period</span>
                    </div>
                </div>
                <div class="p-3 bg-green-100 rounded-full">
                    <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zM14 6a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2V8a2 2 0 012-2h6zM4 14a2 2 0 002 2h8a2 2 0 002-2v-2H4v2z"></path>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Total Users --}}
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Users</p>
                    <p class="text-2xl font-bold text-gray-900" x-text="metrics.users.total.toLocaleString()"></p>
                    <div class="flex items-center mt-2">
                        <svg 
                            :class="metrics.users.change >= 0 ? 'text-green-500' : 'text-red-500'"
                            class="w-4 h-4 mr-1" 
                            fill="currentColor" 
                            viewBox="0 0 20 20"
                        >
                            <path 
                                :d="metrics.users.change >= 0 ? 'M10 17a1 1 0 01-1-1V6a1 1 0 112 0v10a1 1 0 01-1 1z M8.293 7.293a1 1 0 011.414 0L10 7.586l.293-.293a1 1 0 011.414 1.414l-1 1a1 1 0 01-1.414 0l-1-1a1 1 0 010-1.414z' : 'M10 3a1 1 0 01-1 1v10a1 1 0 11-2 0V4a1 1 0 01-1-1z M11.707 12.707a1 1 0 01-1.414 0L10 12.414l-.293.293a1 1 0 01-1.414-1.414l1-1a1 1 0 011.414 0l1 1a1 1 0 010 1.414z'"
                            ></path>
                        </svg>
                        <span 
                            :class="metrics.users.change >= 0 ? 'text-green-700' : 'text-red-700'"
                            class="text-sm font-medium"
                            x-text="Math.abs(metrics.users.change) + '%'"
                        ></span>
                        <span class="text-sm text-gray-500 ml-1">vs last period</span>
                    </div>
                </div>
                <div class="p-3 bg-blue-100 rounded-full">
                    <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"></path>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Tickets Sold --}}
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Tickets Sold</p>
                    <p class="text-2xl font-bold text-gray-900" x-text="metrics.tickets.sold.toLocaleString()"></p>
                    <div class="flex items-center mt-2">
                        <svg 
                            :class="metrics.tickets.change >= 0 ? 'text-green-500' : 'text-red-500'"
                            class="w-4 h-4 mr-1" 
                            fill="currentColor" 
                            viewBox="0 0 20 20"
                        >
                            <path 
                                :d="metrics.tickets.change >= 0 ? 'M10 17a1 1 0 01-1-1V6a1 1 0 112 0v10a1 1 0 01-1 1z M8.293 7.293a1 1 0 011.414 0L10 7.586l.293-.293a1 1 0 011.414 1.414l-1 1a1 1 0 01-1.414 0l-1-1a1 1 0 010-1.414z' : 'M10 3a1 1 0 01-1 1v10a1 1 0 11-2 0V4a1 1 0 01-1-1z M11.707 12.707a1 1 0 01-1.414 0L10 12.414l-.293.293a1 1 0 01-1.414-1.414l1-1a1 1 0 011.414 0l1 1a1 1 0 010 1.414z'"
                            ></path>
                        </svg>
                        <span 
                            :class="metrics.tickets.change >= 0 ? 'text-green-700' : 'text-red-700'"
                            class="text-sm font-medium"
                            x-text="Math.abs(metrics.tickets.change) + '%'"
                        ></span>
                        <span class="text-sm text-gray-500 ml-1">vs last period</span>
                    </div>
                </div>
                <div class="p-3 bg-purple-100 rounded-full">
                    <svg class="w-6 h-6 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Conversion Rate --}}
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Conversion Rate</p>
                    <p class="text-2xl font-bold text-gray-900" x-text="metrics.conversion.rate + '%'"></p>
                    <div class="flex items-center mt-2">
                        <svg 
                            :class="metrics.conversion.change >= 0 ? 'text-green-500' : 'text-red-500'"
                            class="w-4 h-4 mr-1" 
                            fill="currentColor" 
                            viewBox="0 0 20 20"
                        >
                            <path 
                                :d="metrics.conversion.change >= 0 ? 'M10 17a1 1 0 01-1-1V6a1 1 0 112 0v10a1 1 0 01-1 1z M8.293 7.293a1 1 0 011.414 0L10 7.586l.293-.293a1 1 0 011.414 1.414l-1 1a1 1 0 01-1.414 0l-1-1a1 1 0 010-1.414z' : 'M10 3a1 1 0 01-1 1v10a1 1 0 11-2 0V4a1 1 0 01-1-1z M11.707 12.707a1 1 0 01-1.414 0L10 12.414l-.293.293a1 1 0 01-1.414-1.414l1-1a1 1 0 011.414 0l1 1a1 1 0 010 1.414z'"
                            ></path>
                        </svg>
                        <span 
                            :class="metrics.conversion.change >= 0 ? 'text-green-700' : 'text-red-700'"
                            class="text-sm font-medium"
                            x-text="Math.abs(metrics.conversion.change) + '%'"
                        ></span>
                        <span class="text-sm text-gray-500 ml-1">vs last period</span>
                    </div>
                </div>
                <div class="p-3 bg-orange-100 rounded-full">
                    <svg class="w-6 h-6 text-orange-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Revenue Trend Chart --}}
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Revenue Trend</h3>
                <select 
                    x-model="revenueChartType"
                    @change="updateRevenueChart()"
                    class="px-2 py-1 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-blue-500"
                >
                    <option value="daily">Daily</option>
                    <option value="weekly">Weekly</option>
                    <option value="monthly">Monthly</option>
                </select>
            </div>
            <div class="h-80">
                <canvas id="revenueChart" class="w-full h-full"></canvas>
            </div>
        </div>

        {{-- User Activity Chart --}}
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">User Activity</h3>
            <div class="h-80">
                <canvas id="userChart" class="w-full h-full"></canvas>
            </div>
        </div>
    </div>

    {{-- Analytics Tables Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        {{-- Top Events --}}
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Top Events</h3>
            <div class="space-y-3">
                <template x-for="event in topEvents" :key="event.id">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="font-medium text-gray-900" x-text="event.name"></p>
                            <p class="text-sm text-gray-500" x-text="event.venue"></p>
                        </div>
                        <div class="text-right">
                            <p class="font-medium text-gray-900" x-text="event.tickets_sold"></p>
                            <p class="text-sm text-gray-500" x-text="formatCurrency(event.revenue)"></p>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Popular Categories --}}
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Popular Categories</h3>
            <div class="space-y-3">
                <template x-for="category in topCategories" :key="category.name">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div 
                                class="w-3 h-3 rounded-full mr-3"
                                :style="'background-color: ' + category.color"
                            ></div>
                            <span class="font-medium text-gray-900" x-text="category.name"></span>
                        </div>
                        <div class="text-right">
                            <span class="font-medium text-gray-900" x-text="category.percentage + '%'"></span>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Traffic Sources --}}
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Traffic Sources</h3>
            <div class="space-y-3">
                <template x-for="source in trafficSources" :key="source.name">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path x-show="source.type === 'search'" fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                                <path x-show="source.type === 'social'" d="M15.545 6.558a9.42 9.42 0 01.139 1.626c0 2.434-.87 4.492-2.384 5.885h.002C11.978 15.292 10.158 16 8 16A8 8 0 118 0a7.689 7.689 0 015.352 2.082l-2.284 2.284A4.347 4.347 0 008 3.166c-2.087 0-3.86 1.408-4.492 3.304a4.792 4.792 0 000 3.063c.631 1.893 2.405 3.301 4.492 3.301 1.078 0 2.004-.276 2.722-.764h-.003a3.702 3.702 0 001.599-2.431H8v-3.08h7.545z"></path>
                                <path x-show="source.type === 'direct'" d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                            </svg>
                            <span class="font-medium text-gray-900" x-text="source.name"></span>
                        </div>
                        <div class="text-right">
                            <span class="font-medium text-gray-900" x-text="source.visitors.toLocaleString()"></span>
                            <div class="w-full bg-gray-200 rounded-full h-1 mt-1">
                                <div 
                                    class="bg-blue-600 h-1 rounded-full"
                                    :style="'width: ' + source.percentage + '%'"
                                ></div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- Recent Activity & System Health --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Recent Activity --}}
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Activity</h3>
            <div class="space-y-4 max-h-96 overflow-y-auto">
                <template x-for="activity in recentActivity" :key="activity.id">
                    <div class="flex items-start space-x-3">
                        <div 
                            class="flex-shrink-0 w-2 h-2 rounded-full mt-2"
                            :class="{
                                'bg-green-400': activity.type === 'purchase',
                                'bg-blue-400': activity.type === 'registration',
                                'bg-yellow-400': activity.type === 'alert',
                                'bg-red-400': activity.type === 'error'
                            }"
                        ></div>
                        <div class="flex-1">
                            <p class="text-sm text-gray-900" x-text="activity.description"></p>
                            <p class="text-xs text-gray-500" x-text="activity.timestamp"></p>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- System Health --}}
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">System Health</h3>
            <div class="space-y-4">
                <template x-for="service in systemHealth" :key="service.name">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div 
                                class="w-3 h-3 rounded-full mr-3"
                                :class="{
                                    'bg-green-400': service.status === 'healthy',
                                    'bg-yellow-400': service.status === 'warning',
                                    'bg-red-400': service.status === 'error'
                                }"
                            ></div>
                            <div>
                                <p class="font-medium text-gray-900" x-text="service.name"></p>
                                <p class="text-sm text-gray-500" x-text="service.description"></p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium" 
                               :class="{
                                    'text-green-600': service.status === 'healthy',
                                    'text-yellow-600': service.status === 'warning',
                                    'text-red-600': service.status === 'error'
                                }"
                               x-text="service.uptime"></p>
                            <p class="text-xs text-gray-500">uptime</p>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- Loading Overlay --}}
    <div x-show="isLoading" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 flex items-center gap-3">
            <svg class="animate-spin w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-gray-700">Loading analytics data...</span>
        </div>
    </div>
</div>

{{-- Chart.js CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
function adminAnalytics() {
    return {
        // UI State
        selectedPeriod: '30d',
        revenueChartType: 'daily',
        isLoading: false,
        
        // Chart instances
        revenueChart: null,
        userChart: null,
        
        // Analytics Data
        metrics: {
            revenue: { total: 0, change: 0 },
            users: { total: 0, change: 0 },
            tickets: { sold: 0, change: 0 },
            conversion: { rate: 0, change: 0 }
        },
        
        topEvents: [],
        topCategories: [],
        trafficSources: [],
        recentActivity: [],
        systemHealth: [],
        
        init() {
            this.loadAnalytics();
            console.log('[AdminAnalytics] Initialized');
        },
        
        async loadAnalytics() {
            this.isLoading = true;
            
            try {
                const response = await fetch(`/api/admin/analytics?period=${this.selectedPeriod}`);
                const data = await response.json();
                
                if (data.success) {
                    this.updateData(data);
                    this.initializeCharts();
                }
            } catch (error) {
                console.error('[AdminAnalytics] Failed to load analytics:', error);
                this.loadSampleData();
            } finally {
                this.isLoading = false;
            }
        },
        
        updateData(data) {
            this.metrics = data.metrics || this.getSampleMetrics();
            this.topEvents = data.topEvents || this.getSampleEvents();
            this.topCategories = data.topCategories || this.getSampleCategories();
            this.trafficSources = data.trafficSources || this.getSampleTrafficSources();
            this.recentActivity = data.recentActivity || this.getSampleActivity();
            this.systemHealth = data.systemHealth || this.getSampleSystemHealth();
        },
        
        loadSampleData() {
            this.metrics = this.getSampleMetrics();
            this.topEvents = this.getSampleEvents();
            this.topCategories = this.getSampleCategories();
            this.trafficSources = this.getSampleTrafficSources();
            this.recentActivity = this.getSampleActivity();
            this.systemHealth = this.getSampleSystemHealth();
        },
        
        getSampleMetrics() {
            return {
                revenue: { total: 547892.50, change: 12.5 },
                users: { total: 24789, change: 8.2 },
                tickets: { sold: 15432, change: 15.7 },
                conversion: { rate: 3.2, change: 0.8 }
            };
        },
        
        getSampleEvents() {
            return [
                { id: 1, name: 'Lakers vs Warriors', venue: 'Staples Center', tickets_sold: 2543, revenue: 382750 },
                { id: 2, name: 'Taylor Swift Concert', venue: 'Madison Square Garden', tickets_sold: 1892, revenue: 567600 },
                { id: 3, name: 'NFL Championship', venue: 'MetLife Stadium', tickets_sold: 1654, revenue: 496200 },
                { id: 4, name: 'Yankees vs Red Sox', venue: 'Yankee Stadium', tickets_sold: 1234, revenue: 185100 },
                { id: 5, name: 'Broadway Musical', venue: 'Theater District', tickets_sold: 987, revenue: 148050 }
            ];
        },
        
        getSampleCategories() {
            return [
                { name: 'Sports', percentage: 45, color: '#10B981' },
                { name: 'Concerts', percentage: 30, color: '#3B82F6' },
                { name: 'Theater', percentage: 15, color: '#8B5CF6' },
                { name: 'Comedy', percentage: 7, color: '#F59E0B' },
                { name: 'Other', percentage: 3, color: '#6B7280' }
            ];
        },
        
        getSampleTrafficSources() {
            return [
                { name: 'Google Search', type: 'search', visitors: 12543, percentage: 45 },
                { name: 'Facebook', type: 'social', visitors: 8321, percentage: 30 },
                { name: 'Direct', type: 'direct', visitors: 4562, percentage: 16 },
                { name: 'Instagram', type: 'social', visitors: 2134, percentage: 8 },
                { name: 'Other', type: 'other', visitors: 287, percentage: 1 }
            ];
        },
        
        getSampleActivity() {
            return [
                { id: 1, type: 'purchase', description: 'User John Doe purchased Lakers vs Warriors tickets', timestamp: '2 minutes ago' },
                { id: 2, type: 'registration', description: 'New user registered: jane.smith@email.com', timestamp: '5 minutes ago' },
                { id: 3, type: 'alert', description: 'Price alert triggered for Taylor Swift concert', timestamp: '12 minutes ago' },
                { id: 4, type: 'purchase', description: 'User Mike Johnson purchased Yankees tickets', timestamp: '18 minutes ago' },
                { id: 5, type: 'registration', description: 'New user registered: bob.wilson@email.com', timestamp: '22 minutes ago' },
                { id: 6, type: 'error', description: 'Payment processing error for order #12345', timestamp: '35 minutes ago' }
            ];
        },
        
        getSampleSystemHealth() {
            return [
                { name: 'Web Server', description: 'Apache/Nginx', status: 'healthy', uptime: '99.9%' },
                { name: 'Database', description: 'MySQL/PostgreSQL', status: 'healthy', uptime: '99.8%' },
                { name: 'Cache System', description: 'Redis/Memcached', status: 'warning', uptime: '98.5%' },
                { name: 'Email Service', description: 'SendGrid/Mailgun', status: 'healthy', uptime: '99.7%' },
                { name: 'Payment Gateway', description: 'Stripe/PayPal', status: 'healthy', uptime: '99.9%' }
            ];
        },
        
        initializeCharts() {
            this.initRevenueChart();
            this.initUserChart();
        },
        
        initRevenueChart() {
            const ctx = document.getElementById('revenueChart');
            if (!ctx) return;
            
            if (this.revenueChart) {
                this.revenueChart.destroy();
            }
            
            const labels = this.getChartLabels();
            const revenueData = this.getRevenueChartData();
            
            this.revenueChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Revenue',
                        data: revenueData,
                        borderColor: '#10B981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '$' + value.toLocaleString();
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        },
        
        initUserChart() {
            const ctx = document.getElementById('userChart');
            if (!ctx) return;
            
            if (this.userChart) {
                this.userChart.destroy();
            }
            
            const labels = this.getChartLabels();
            const userData = this.getUserChartData();
            
            this.userChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Active Users',
                        data: userData,
                        backgroundColor: '#3B82F6',
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        },
        
        getChartLabels() {
            const labels = [];
            const days = this.selectedPeriod === '7d' ? 7 : this.selectedPeriod === '30d' ? 30 : 90;
            
            for (let i = days - 1; i >= 0; i--) {
                const date = new Date();
                date.setDate(date.getDate() - i);
                labels.push(date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }));
            }
            
            return labels;
        },
        
        getRevenueChartData() {
            const days = this.selectedPeriod === '7d' ? 7 : this.selectedPeriod === '30d' ? 30 : 90;
            const data = [];
            
            for (let i = 0; i < days; i++) {
                data.push(Math.floor(Math.random() * 50000) + 10000);
            }
            
            return data;
        },
        
        getUserChartData() {
            const days = this.selectedPeriod === '7d' ? 7 : this.selectedPeriod === '30d' ? 30 : 90;
            const data = [];
            
            for (let i = 0; i < days; i++) {
                data.push(Math.floor(Math.random() * 2000) + 500);
            }
            
            return data;
        },
        
        updateRevenueChart() {
            this.initRevenueChart();
        },
        
        formatCurrency(amount) {
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD'
            }).format(amount);
        },
        
        async exportReport() {
            try {
                const response = await fetch(`/api/admin/analytics/export?period=${this.selectedPeriod}`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/pdf',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                
                if (response.ok) {
                    const blob = await response.blob();
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `analytics-report-${this.selectedPeriod}-${new Date().toISOString().split('T')[0]}.pdf`;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);
                } else {
                    throw new Error('Export failed');
                }
            } catch (error) {
                console.error('[AdminAnalytics] Export failed:', error);
                alert('Failed to export report. Please try again.');
            }
        }
    };
}
</script>