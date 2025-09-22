{{-- Data Visualization Component --}}
{{-- Interactive charts and analytics using Chart.js --}}

<div x-data="dataVisualization()" x-init="init()" class="data-visualization">
    {{-- Chart Container --}}
    <div class="relative">
        <canvas 
            :id="chartId" 
            class="max-w-full h-auto"
            :style="{ height: chartHeight + 'px' }"
        ></canvas>
        
        {{-- Loading Overlay --}}
        <div 
            x-show="isLoading"
            x-transition:enter="transition-opacity duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center rounded-lg"
        >
            <div class="text-center">
                <div class="animate-spin rounded-full h-8 w-8 border-4 border-blue-600 border-t-transparent mx-auto mb-2"></div>
                <p class="text-sm text-gray-600">Loading chart data...</p>
            </div>
        </div>
        
        {{-- Error State --}}
        <div 
            x-show="hasError"
            x-transition
            class="absolute inset-0 bg-gray-50 flex items-center justify-center rounded-lg"
        >
            <div class="text-center">
                <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 18.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Failed to Load Chart</h3>
                <p class="text-sm text-gray-600 mb-4" x-text="errorMessage"></p>
                <button 
                    @click="retryLoad()"
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors"
                >
                    Try Again
                </button>
            </div>
        </div>
        
        {{-- No Data State --}}
        <div 
            x-show="noData"
            x-transition
            class="absolute inset-0 bg-gray-50 flex items-center justify-center rounded-lg"
        >
            <div class="text-center">
                <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">No Data Available</h3>
                <p class="text-sm text-gray-600">There's no data to display for the selected time period.</p>
            </div>
        </div>
    </div>
    
    {{-- Chart Controls --}}
    <div x-show="showControls" class="mt-4 flex items-center justify-between flex-wrap gap-4">
        {{-- Time Range Selector --}}
        <div x-show="showTimeRange" class="flex items-center space-x-2">
            <label class="text-sm font-medium text-gray-700">Time Range:</label>
            <select 
                x-model="selectedTimeRange" 
                @change="updateTimeRange()"
                class="text-sm border border-gray-300 rounded-md px-3 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
                <template x-for="range in timeRanges" :key="range.value">
                    <option :value="range.value" x-text="range.label"></option>
                </template>
            </select>
        </div>
        
        {{-- Chart Type Selector --}}
        <div x-show="showChartTypes" class="flex items-center space-x-2">
            <label class="text-sm font-medium text-gray-700">Chart Type:</label>
            <div class="flex rounded-lg border border-gray-300">
                <template x-for="(type, index) in availableChartTypes" :key="type.value">
                    <button
                        @click="changeChartType(type.value)"
                        :class="{
                            'bg-blue-600 text-white': chartType === type.value,
                            'bg-white text-gray-700 hover:bg-gray-50': chartType !== type.value,
                            'rounded-l-md': index === 0,
                            'rounded-r-md': index === availableChartTypes.length - 1,
                            'border-r border-gray-300': index < availableChartTypes.length - 1
                        }"
                        class="px-3 py-1 text-sm transition-colors"
                        :title="type.label"
                    >
                        <span x-html="type.icon"></span>
                    </button>
                </template>
            </div>
        </div>
        
        {{-- Export Options --}}
        <div x-show="showExport" class="flex items-center space-x-2">
            <button
                @click="exportChart('png')"
                class="text-sm text-gray-600 hover:text-gray-900 flex items-center space-x-1"
                data-tooltip="Export as PNG"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path>
                </svg>
                <span>PNG</span>
            </button>
            
            <button
                @click="exportChart('csv')"
                class="text-sm text-gray-600 hover:text-gray-900 flex items-center space-x-1"
                data-tooltip="Export data as CSV"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span>CSV</span>
            </button>
        </div>
    </div>
</div>

<style>
    /* Chart container styles */
    .data-visualization {
        @apply relative w-full;
    }
    
    .chart-animation-enter {
        animation: chartFadeIn 0.5s ease-out;
    }
    
    @keyframes chartFadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* Custom chart tooltip styles */
    .chart-tooltip {
        @apply bg-gray-900 text-white text-sm rounded-lg px-3 py-2 shadow-lg;
    }
    
    .chart-tooltip::after {
        content: '';
        position: absolute;
        top: 100%;
        left: 50%;
        margin-left: -5px;
        border-width: 5px;
        border-style: solid;
        border-color: #1f2937 transparent transparent transparent;
    }
    
    /* Real-time indicator */
    .realtime-indicator {
        @apply absolute top-2 right-2 w-3 h-3 bg-green-500 rounded-full animate-pulse;
    }
    
    .realtime-indicator::after {
        content: '';
        @apply absolute inset-0 bg-green-500 rounded-full animate-ping;
    }
</style>

<script>
function dataVisualization() {
    return {
        // Configuration
        chartId: 'chart-' + Math.random().toString(36).substr(2, 9),
        chartInstance: null,
        chartType: 'line',
        chartHeight: 400,
        
        // Data
        chartData: null,
        rawData: null,
        
        // State
        isLoading: true,
        hasError: false,
        noData: false,
        errorMessage: '',
        
        // Controls
        showControls: true,
        showTimeRange: true,
        showChartTypes: true,
        showExport: true,
        
        // Time range
        selectedTimeRange: '7d',
        timeRanges: [
            { value: '1d', label: 'Last 24 Hours' },
            { value: '7d', label: 'Last 7 Days' },
            { value: '30d', label: 'Last 30 Days' },
            { value: '90d', label: 'Last 3 Months' },
            { value: '1y', label: 'Last Year' },
            { value: 'custom', label: 'Custom Range' }
        ],
        
        // Chart types
        availableChartTypes: [
            { value: 'line', label: 'Line Chart', icon: '📈' },
            { value: 'bar', label: 'Bar Chart', icon: '📊' },
            { value: 'doughnut', label: 'Doughnut Chart', icon: '🍩' },
            { value: 'radar', label: 'Radar Chart', icon: '🎯' },
            { value: 'area', label: 'Area Chart', icon: '📊' }
        ],
        
        // Real-time updates
        realTimeEnabled: false,
        updateInterval: null,
        
        init() {
            this.loadChartLibrary().then(() => {
                this.initializeChart();
            }).catch((error) => {
                this.handleError('Failed to load Chart.js library', error);
            });
            
            console.log('[Charts] Data visualization component initialized');
        },
        
        async loadChartLibrary() {
            // Check if Chart.js is already loaded
            if (typeof Chart !== 'undefined') {
                return Promise.resolve();
            }
            
            // Load Chart.js dynamically
            return new Promise((resolve, reject) => {
                const script = document.createElement('script');
                script.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js';
                script.onload = resolve;
                script.onerror = reject;
                document.head.appendChild(script);
            });
        },
        
        async initializeChart() {
            try {
                this.isLoading = true;
                this.hasError = false;
                
                // Load data
                await this.loadData();
                
                if (!this.chartData || this.chartData.datasets.length === 0) {
                    this.noData = true;
                    this.isLoading = false;
                    return;
                }
                
                // Create chart
                this.createChart();
                
                this.isLoading = false;
                this.noData = false;
                
                // Setup real-time updates if enabled
                if (this.realTimeEnabled) {
                    this.setupRealTimeUpdates();
                }
                
            } catch (error) {
                this.handleError('Failed to initialize chart', error);
            }
        },
        
        async loadData() {
            // Get chart configuration from element attributes or props
            const chartConfig = this.getChartConfig();
            
            if (chartConfig.staticData) {
                // Use static data if provided
                this.chartData = chartConfig.staticData;
                this.rawData = chartConfig.rawData || chartConfig.staticData;
                return;
            }
            
            if (!chartConfig.dataUrl) {
                throw new Error('No data source specified');
            }
            
            // Fetch data from API
            const response = await fetch(chartConfig.dataUrl, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            this.rawData = data;
            this.chartData = this.processData(data, chartConfig);
        },
        
        getChartConfig() {
            const element = document.getElementById(this.chartId)?.closest('[data-chart-config]');
            if (!element) {
                return this.getDefaultConfig();
            }
            
            try {
                return JSON.parse(element.dataset.chartConfig);
            } catch (error) {
                console.warn('[Charts] Invalid chart config, using defaults');
                return this.getDefaultConfig();
            }
        },
        
        getDefaultConfig() {
            return {
                type: 'revenue',
                title: 'Revenue Analytics',
                dataUrl: '/api/analytics/revenue',
                timeRange: '7d'
            };
        },
        
        processData(rawData, config) {
            const processors = {
                revenue: this.processRevenueData,
                tickets: this.processTicketData,
                users: this.processUserData,
                performance: this.processPerformanceData,
                alerts: this.processAlertData
            };
            
            const processor = processors[config.type] || processors.revenue;
            return processor.call(this, rawData, config);
        },
        
        processRevenueData(data, config) {
            return {
                labels: data.dates || data.labels,
                datasets: [{
                    label: 'Revenue',
                    data: data.revenue || data.values,
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
                    borderWidth: 2,
                    fill: this.chartType === 'area',
                    tension: 0.4
                }]
            };
        },
        
        processTicketData(data, config) {
            const colors = ['#2563eb', '#059669', '#dc2626', '#ea580c', '#7c3aed'];
            
            return {
                labels: data.labels,
                datasets: data.datasets?.map((dataset, index) => ({
                    label: dataset.label,
                    data: dataset.data,
                    borderColor: colors[index % colors.length],
                    backgroundColor: colors[index % colors.length] + '20',
                    borderWidth: 2,
                    fill: false
                })) || [{
                    label: 'Tickets',
                    data: data.values,
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
                    borderWidth: 2
                }]
            };
        },
        
        processUserData(data, config) {
            return {
                labels: data.labels,
                datasets: [{
                    label: 'Active Users',
                    data: data.users,
                    borderColor: '#059669',
                    backgroundColor: 'rgba(5, 150, 105, 0.1)',
                    borderWidth: 2,
                    fill: true
                }]
            };
        },
        
        processPerformanceData(data, config) {
            return {
                labels: data.metrics.map(m => m.name),
                datasets: [{
                    label: 'Performance Score',
                    data: data.metrics.map(m => m.score),
                    borderColor: '#7c3aed',
                    backgroundColor: 'rgba(124, 58, 237, 0.1)',
                    borderWidth: 2,
                    fill: true
                }]
            };
        },
        
        processAlertData(data, config) {
            return {
                labels: data.categories,
                datasets: [{
                    label: 'Active Alerts',
                    data: data.counts,
                    backgroundColor: [
                        '#dc2626',
                        '#ea580c',
                        '#d97706',
                        '#059669'
                    ],
                    borderWidth: 0
                }]
            };
        },
        
        createChart() {
            const canvas = document.getElementById(this.chartId);
            if (!canvas) {
                throw new Error('Chart canvas element not found');
            }
            
            // Destroy existing chart if it exists
            if (this.chartInstance) {
                this.chartInstance.destroy();
            }
            
            const ctx = canvas.getContext('2d');
            
            this.chartInstance = new Chart(ctx, {
                type: this.chartType === 'area' ? 'line' : this.chartType,
                data: this.chartData,
                options: this.getChartOptions()
            });
            
            // Add animation class
            setTimeout(() => {
                canvas.classList.add('chart-animation-enter');
            }, 100);
        },
        
        getChartOptions() {
            const baseOptions = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 20
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: 'rgba(31, 41, 55, 0.9)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: '#6b7280',
                        borderWidth: 1,
                        cornerRadius: 8,
                        displayColors: true,
                        callbacks: {
                            label: (context) => {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                
                                // Format value based on chart type
                                const value = context.parsed.y;
                                if (this.isMoneyValue(context.dataset.label)) {
                                    label += this.formatMoney(value);
                                } else if (this.isPercentageValue(context.dataset.label)) {
                                    label += this.formatPercentage(value);
                                } else {
                                    label += this.formatNumber(value);
                                }
                                
                                return label;
                            }
                        }
                    }
                },
                scales: this.getScaleOptions(),
                interaction: {
                    mode: 'nearest',
                    axis: 'x',
                    intersect: false
                },
                animation: {
                    duration: 1000,
                    easing: 'easeInOutQuart'
                }
            };
            
            // Chart-specific options
            if (this.chartType === 'doughnut') {
                baseOptions.scales = {};
                baseOptions.plugins.legend.position = 'right';
            }
            
            return baseOptions;
        },
        
        getScaleOptions() {
            if (this.chartType === 'doughnut' || this.chartType === 'radar') {
                return {};
            }
            
            return {
                x: {
                    display: true,
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: '#6b7280'
                    }
                },
                y: {
                    display: true,
                    grid: {
                        color: 'rgba(107, 114, 128, 0.1)'
                    },
                    ticks: {
                        color: '#6b7280',
                        callback: (value) => {
                            if (this.isMoneyValue()) {
                                return this.formatMoney(value);
                            }
                            return this.formatNumber(value);
                        }
                    }
                }
            };
        },
        
        async updateTimeRange() {
            this.isLoading = true;
            
            try {
                await this.loadData();
                this.chartInstance.data = this.chartData;
                this.chartInstance.update('active');
                this.isLoading = false;
            } catch (error) {
                this.handleError('Failed to update time range', error);
            }
        },
        
        changeChartType(newType) {
            if (this.chartType === newType) return;
            
            this.chartType = newType;
            
            // Recreate chart with new type
            this.createChart();
        },
        
        setupRealTimeUpdates() {
            if (this.updateInterval) {
                clearInterval(this.updateInterval);
            }
            
            this.updateInterval = setInterval(async () => {
                try {
                    await this.loadData();
                    this.chartInstance.data = this.chartData;
                    this.chartInstance.update('none'); // No animation for real-time updates
                } catch (error) {
                    console.error('[Charts] Real-time update failed:', error);
                }
            }, 30000); // Update every 30 seconds
            
            // Add real-time indicator
            const container = document.getElementById(this.chartId).parentElement;
            if (!container.querySelector('.realtime-indicator')) {
                const indicator = document.createElement('div');
                indicator.className = 'realtime-indicator';
                indicator.title = 'Real-time updates enabled';
                container.appendChild(indicator);
            }
        },
        
        exportChart(format) {
            if (!this.chartInstance) return;
            
            if (format === 'png') {
                const url = this.chartInstance.toBase64Image();
                const link = document.createElement('a');
                link.download = `chart-${Date.now()}.png`;
                link.href = url;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                
                this.showToast('Chart exported as PNG', 'success');
            } else if (format === 'csv') {
                this.exportDataAsCSV();
            }
        },
        
        exportDataAsCSV() {
            if (!this.rawData) return;
            
            let csv = '';
            
            // Add headers
            if (this.chartData.labels && this.chartData.datasets) {
                csv += 'Date,' + this.chartData.datasets.map(d => d.label).join(',') + '\n';
                
                // Add data rows
                this.chartData.labels.forEach((label, index) => {
                    csv += label + ',' + this.chartData.datasets.map(d => d.data[index] || '').join(',') + '\n';
                });
            }
            
            // Download CSV
            const blob = new Blob([csv], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.download = `chart-data-${Date.now()}.csv`;
            link.href = url;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            window.URL.revokeObjectURL(url);
            
            this.showToast('Data exported as CSV', 'success');
        },
        
        retryLoad() {
            this.initializeChart();
        },
        
        handleError(message, error) {
            console.error('[Charts]', message, error);
            this.hasError = true;
            this.isLoading = false;
            this.errorMessage = message;
        },
        
        // Utility methods
        isMoneyValue(label) {
            const moneyKeywords = ['revenue', 'sales', 'price', 'cost', 'profit', 'earnings'];
            return moneyKeywords.some(keyword => 
                (label || '').toLowerCase().includes(keyword)
            );
        },
        
        isPercentageValue(label) {
            const percentKeywords = ['rate', 'percent', 'ratio', 'conversion'];
            return percentKeywords.some(keyword => 
                (label || '').toLowerCase().includes(keyword)
            );
        },
        
        formatMoney(value) {
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(value);
        },
        
        formatPercentage(value) {
            return new Intl.NumberFormat('en-US', {
                style: 'percent',
                minimumFractionDigits: 1,
                maximumFractionDigits: 1
            }).format(value / 100);
        },
        
        formatNumber(value) {
            return new Intl.NumberFormat('en-US', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(value);
        },
        
        showToast(message, type = 'info') {
            this.$dispatch('showtoast', {
                message,
                type,
                duration: 3000
            });
        },
        
        // Cleanup
        destroy() {
            if (this.updateInterval) {
                clearInterval(this.updateInterval);
            }
            
            if (this.chartInstance) {
                this.chartInstance.destroy();
            }
        }
    };
}

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    document.querySelectorAll('[x-data*="dataVisualization"]').forEach(el => {
        if (el._x_dataStack && el._x_dataStack[0].destroy) {
            el._x_dataStack[0].destroy();
        }
    });
});
</script>