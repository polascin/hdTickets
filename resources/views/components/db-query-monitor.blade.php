{{-- Database Query Monitor --}}
{{-- Advanced database performance monitoring for SQL queries, connections, and optimization insights --}}

<div x-data="dbQueryMonitor()" x-init="init()" class="db-query-monitor">
    {{-- Monitor Header --}}
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">🗄️ Database Query Monitor</h1>
                <p class="text-gray-600 mt-1">Real-time SQL performance monitoring and query optimization insights</p>
            </div>
            <div class="flex items-center gap-3">
                {{-- Live Monitoring Toggle --}}
                <label class="flex items-center">
                    <input 
                        type="checkbox" 
                        x-model="liveMonitoring"
                        @change="toggleLiveMonitoring()"
                        class="sr-only"
                    >
                    <div class="relative">
                        <div class="w-10 h-6 bg-gray-200 rounded-full shadow-inner transition-colors duration-200" :class="{ 'bg-green-600': liveMonitoring }"></div>
                        <div class="absolute w-4 h-4 bg-white rounded-full shadow -left-1 -top-1 transition-transform duration-200 transform" :class="{ 'translate-x-6': liveMonitoring }"></div>
                    </div>
                    <span class="ml-2 text-sm text-gray-700">Live Monitor</span>
                </label>
                
                {{-- Clear History --}}
                <button 
                    @click="clearHistory()"
                    class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 flex items-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    Clear
                </button>
            </div>
        </div>
    </div>

    {{-- Performance Metrics Overview --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        {{-- Average Query Time --}}
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-medium text-gray-700">Avg Query Time</h3>
                <div class="w-3 h-3 rounded-full" :class="getAvgTimeStatusColor()"></div>
            </div>
            <div class="text-2xl font-bold mb-1" :class="getAvgTimeTextColor()" x-text="formatDuration(averageQueryTime)"></div>
            <div class="text-xs text-gray-500">Target: < 100ms</div>
        </div>

        {{-- Slow Queries --}}
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-medium text-gray-700">Slow Queries</h3>
                <div class="w-3 h-3 rounded-full" :class="slowQueries.length > 0 ? 'bg-red-500' : 'bg-green-500'"></div>
            </div>
            <div class="text-2xl font-bold mb-1" :class="slowQueries.length > 0 ? 'text-red-600' : 'text-green-600'" x-text="slowQueries.length"></div>
            <div class="text-xs text-gray-500">> 1 second</div>
        </div>

        {{-- Active Connections --}}
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-medium text-gray-700">Active Connections</h3>
                <div class="w-3 h-3 rounded-full" :class="getConnectionStatusColor()"></div>
            </div>
            <div class="text-2xl font-bold mb-1" :class="getConnectionTextColor()" x-text="activeConnections"></div>
            <div class="text-xs text-gray-500">Pool: <span x-text="maxConnections"></span></div>
        </div>

        {{-- Query Cache Hit Rate --}}
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-medium text-gray-700">Cache Hit Rate</h3>
                <div class="w-3 h-3 rounded-full" :class="getCacheHitStatusColor()"></div>
            </div>
            <div class="text-2xl font-bold mb-1" :class="getCacheHitTextColor()" x-text="cacheHitRate + '%'"></div>
            <div class="text-xs text-gray-500">Target: > 95%</div>
        </div>
    </div>

    {{-- Query Timeline Chart --}}
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Query Performance Timeline</h3>
            <div class="flex items-center gap-4">
                <div class="text-sm text-gray-600">
                    Total Queries: <span class="font-medium" x-text="queryHistory.length"></span>
                </div>
                <div class="flex items-center gap-2 text-sm text-gray-600">
                    <div class="flex items-center gap-1">
                        <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                        <span>SELECT</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                        <span>INSERT</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                        <span>UPDATE</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                        <span>DELETE</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="h-64 flex items-end justify-start space-x-1 overflow-x-auto">
            <template x-for="(query, index) in queryHistory.slice(-50)" :key="index">
                <div class="flex flex-col items-center min-w-4">
                    <div 
                        class="w-3 rounded-t transition-all duration-300 hover:w-4"
                        :class="getQueryTypeColor(query.type)"
                        :style="{ height: Math.max((query.duration / maxQueryTime) * 250, 2) + 'px' }"
                        :title="`${query.type}: ${formatDuration(query.duration)} - ${query.table}`"
                    ></div>
                    <div class="text-xs text-gray-500 mt-1 transform -rotate-45 origin-left whitespace-nowrap">
                        <span x-text="formatTimestamp(query.timestamp)"></span>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- Active Queries and Slow Query Analysis --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Active Queries --}}
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Active Queries</h3>
            <div class="space-y-3 max-h-80 overflow-y-auto">
                <template x-for="query in activeQueries" :key="query.id">
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-start justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                                <span class="text-sm font-medium" x-text="query.type"></span>
                                <span class="text-xs text-gray-500">ID: <span x-text="query.id"></span></span>
                            </div>
                            <div class="text-xs text-gray-500" x-text="formatDuration(query.runTime)"></div>
                        </div>
                        <div class="text-sm text-gray-700 mb-2" x-text="query.table"></div>
                        <div class="text-xs text-gray-600 bg-gray-50 rounded p-2 font-mono" x-text="query.sql"></div>
                        <div class="flex items-center gap-4 mt-2 text-xs text-gray-500">
                            <span>User: <span x-text="query.user"></span></span>
                            <span>DB: <span x-text="query.database"></span></span>
                        </div>
                    </div>
                </template>
                <div x-show="activeQueries.length === 0" class="text-center py-8 text-gray-500">
                    <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p>No active queries</p>
                </div>
            </div>
        </div>

        {{-- Slow Query Analysis --}}
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Slow Query Analysis</h3>
            <div class="space-y-3 max-h-80 overflow-y-auto">
                <template x-for="query in slowQueries.slice(0, 5)" :key="query.id">
                    <div class="border border-red-200 rounded-lg p-4 bg-red-50">
                        <div class="flex items-start justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <div class="w-2 h-2 bg-red-500 rounded-full"></div>
                                <span class="text-sm font-medium text-red-800" x-text="query.type"></span>
                                <span class="text-xs bg-red-200 text-red-700 px-2 py-1 rounded">SLOW</span>
                            </div>
                            <div class="text-xs text-red-600 font-semibold" x-text="formatDuration(query.duration)"></div>
                        </div>
                        <div class="text-sm text-red-700 mb-2" x-text="query.table"></div>
                        <div class="text-xs text-red-600 bg-red-100 rounded p-2 font-mono" x-text="query.sql"></div>
                        <div class="mt-2 text-xs text-red-600">
                            <span class="font-medium">Suggestion:</span> <span x-text="query.optimization"></span>
                        </div>
                    </div>
                </template>
                <div x-show="slowQueries.length === 0" class="text-center py-8 text-gray-500">
                    <svg class="w-12 h-12 mx-auto mb-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <p class="text-green-600">No slow queries detected</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Query Statistics by Table and Type --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Table Statistics --}}
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Table Access Statistics</h3>
            <div class="space-y-3">
                <template x-for="table in tableStats" :key="table.name">
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div>
                            <div class="font-medium text-gray-900" x-text="table.name"></div>
                            <div class="text-xs text-gray-500">
                                <span x-text="table.queryCount"></span> queries, 
                                avg: <span x-text="formatDuration(table.avgDuration)"></span>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-semibold" :class="table.avgDuration > 100 ? 'text-red-600' : 'text-green-600'" 
                                 x-text="formatDuration(table.totalDuration)"></div>
                            <div class="text-xs text-gray-500">total time</div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Query Type Distribution --}}
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Query Type Distribution</h3>
            <div class="space-y-4">
                <template x-for="type in queryTypeStats" :key="type.name">
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm font-medium text-gray-700" x-text="type.name"></span>
                            <span class="text-sm text-gray-600" x-text="type.count"></span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div 
                                class="h-2 rounded-full transition-all duration-500"
                                :class="getQueryTypeColor(type.name.toLowerCase())"
                                :style="{ width: (type.percentage) + '%' }"
                            ></div>
                        </div>
                        <div class="text-xs text-gray-500 mt-1">
                            <span x-text="type.percentage.toFixed(1)"></span>% - 
                            Avg: <span x-text="formatDuration(type.avgDuration)"></span>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- Connection Pool Status --}}
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Database Connection Pool Status</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- Connection Pool Visualization --}}
            <div class="md:col-span-1">
                <div class="text-center">
                    <div class="relative w-32 h-32 mx-auto mb-4">
                        {{-- Pool Circle --}}
                        <svg class="w-32 h-32 transform -rotate-90" viewBox="0 0 100 100">
                            <circle cx="50" cy="50" r="40" stroke="currentColor" stroke-width="8" fill="transparent" class="text-gray-200"></circle>
                            <circle 
                                cx="50" cy="50" r="40" 
                                stroke="currentColor" 
                                stroke-width="8" 
                                fill="transparent"
                                :class="getConnectionPoolColor()"
                                stroke-dasharray="251.2"
                                :stroke-dashoffset="251.2 - ((activeConnections / maxConnections) * 251.2)"
                                class="transition-all duration-1000 ease-out"
                            ></circle>
                        </svg>
                        
                        {{-- Connection Numbers --}}
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="text-center">
                                <div class="text-xl font-bold" :class="getConnectionTextColor()" x-text="activeConnections"></div>
                                <div class="text-xs text-gray-500">/ <span x-text="maxConnections"></span></div>
                            </div>
                        </div>
                    </div>
                    <div class="text-sm text-gray-600">Active Connections</div>
                </div>
            </div>

            {{-- Connection Details --}}
            <div class="md:col-span-2">
                <div class="grid grid-cols-2 gap-4">
                    <div class="text-center p-4 bg-gray-50 rounded-lg">
                        <div class="text-2xl font-bold text-blue-600" x-text="idleConnections"></div>
                        <div class="text-sm text-gray-600">Idle Connections</div>
                    </div>
                    <div class="text-center p-4 bg-gray-50 rounded-lg">
                        <div class="text-2xl font-bold text-green-600" x-text="connectionWaitTime + 'ms'"></div>
                        <div class="text-sm text-gray-600">Avg Wait Time</div>
                    </div>
                    <div class="text-center p-4 bg-gray-50 rounded-lg">
                        <div class="text-2xl font-bold text-purple-600" x-text="totalConnectionsCreated"></div>
                        <div class="text-sm text-gray-600">Total Created</div>
                    </div>
                    <div class="text-center p-4 bg-gray-50 rounded-lg">
                        <div class="text-2xl font-bold text-orange-600" x-text="failedConnections"></div>
                        <div class="text-sm text-gray-600">Failed Connections</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function dbQueryMonitor() {
    return {
        // State
        liveMonitoring: false,
        monitoringInterval: null,
        
        // Core Metrics
        averageQueryTime: 0,
        activeConnections: 0,
        maxConnections: 100,
        idleConnections: 0,
        cacheHitRate: 0,
        connectionWaitTime: 0,
        totalConnectionsCreated: 0,
        failedConnections: 0,
        
        // Query Data
        queryHistory: [],
        activeQueries: [],
        slowQueries: [],
        maxQueryTime: 1000, // For timeline scaling
        
        // Statistics
        tableStats: [],
        queryTypeStats: [],
        
        init() {
            this.loadInitialData();
            this.generateSampleData();
            this.startLiveMonitoring();
            
            console.log('[DBMonitor] Database query monitor initialized');
        },
        
        loadInitialData() {
            // Initialize connection pool data
            this.activeConnections = Math.floor(Math.random() * 30) + 10;
            this.idleConnections = Math.floor(Math.random() * 20) + 5;
            this.cacheHitRate = Math.floor(Math.random() * 10) + 90; // 90-100%
            this.connectionWaitTime = Math.floor(Math.random() * 50) + 5; // 5-55ms
            this.totalConnectionsCreated = Math.floor(Math.random() * 1000) + 500;
            this.failedConnections = Math.floor(Math.random() * 5);
        },
        
        generateSampleData() {
            // Clear existing data
            this.queryHistory = [];
            this.activeQueries = [];
            this.slowQueries = [];
            
            // Generate historical query data (last hour)
            const now = Date.now();
            const queryTypes = ['select', 'insert', 'update', 'delete'];
            const tables = ['tickets', 'events', 'users', 'venues', 'purchases', 'categories'];
            
            // Generate 100 sample queries
            for (let i = 0; i < 100; i++) {
                const timestamp = now - (Math.random() * 3600000); // Last hour
                const type = queryTypes[Math.floor(Math.random() * queryTypes.length)];
                const table = tables[Math.floor(Math.random() * tables.length)];
                const duration = this.generateQueryDuration(type);
                
                const query = {
                    id: `q_${i}`,
                    timestamp,
                    type,
                    table,
                    duration,
                    sql: this.generateSampleSQL(type, table),
                    user: `user_${Math.floor(Math.random() * 50) + 1}`,
                    database: 'hdtickets_production'
                };
                
                this.queryHistory.push(query);
                
                // Add to slow queries if > 1 second
                if (duration > 1000) {
                    this.slowQueries.push({
                        ...query,
                        optimization: this.getOptimizationSuggestion(type, table, duration)
                    });
                }
                
                // Update max query time for scaling
                this.maxQueryTime = Math.max(this.maxQueryTime, duration);
            }
            
            // Generate active queries (2-5 currently running)
            const activeCount = Math.floor(Math.random() * 4) + 2;
            for (let i = 0; i < activeCount; i++) {
                const type = queryTypes[Math.floor(Math.random() * queryTypes.length)];
                const table = tables[Math.floor(Math.random() * tables.length)];
                
                this.activeQueries.push({
                    id: `active_${i}`,
                    type,
                    table,
                    runTime: Math.floor(Math.random() * 5000) + 100, // 100ms - 5s
                    sql: this.generateSampleSQL(type, table),
                    user: `user_${Math.floor(Math.random() * 50) + 1}`,
                    database: 'hdtickets_production'
                });
            }
            
            // Calculate statistics
            this.calculateStatistics();
        },
        
        generateQueryDuration(type) {
            // Different query types have different typical durations
            const baseTime = {
                select: Math.random() * 200 + 50,    // 50-250ms
                insert: Math.random() * 100 + 20,    // 20-120ms
                update: Math.random() * 150 + 30,    // 30-180ms
                delete: Math.random() * 200 + 40     // 40-240ms
            };
            
            let duration = baseTime[type] || 100;
            
            // 10% chance of being a slow query
            if (Math.random() < 0.1) {
                duration += Math.random() * 3000 + 1000; // Add 1-4 seconds
            }
            
            return Math.round(duration);
        },
        
        generateSampleSQL(type, table) {
            const samples = {
                select: [
                    `SELECT * FROM ${table} WHERE id = ?`,
                    `SELECT COUNT(*) FROM ${table} WHERE status = 'active'`,
                    `SELECT ${table}.*, related.name FROM ${table} JOIN related ON ${table}.id = related.${table}_id`
                ],
                insert: [
                    `INSERT INTO ${table} (name, status, created_at) VALUES (?, 'active', NOW())`,
                    `INSERT INTO ${table} SET data = ?`
                ],
                update: [
                    `UPDATE ${table} SET status = 'updated', updated_at = NOW() WHERE id = ?`,
                    `UPDATE ${table} SET view_count = view_count + 1 WHERE slug = ?`
                ],
                delete: [
                    `DELETE FROM ${table} WHERE id = ? AND status = 'deleted'`,
                    `DELETE FROM ${table} WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 YEAR)`
                ]
            };
            
            const typeQueries = samples[type] || samples.select;
            return typeQueries[Math.floor(Math.random() * typeQueries.length)];
        },
        
        getOptimizationSuggestion(type, table, duration) {
            const suggestions = [
                "Add index on WHERE clause columns",
                "Optimize JOIN conditions",
                "Use LIMIT to reduce result set",
                "Consider query caching",
                "Review table structure",
                "Use covering indexes",
                "Avoid SELECT * in production",
                "Consider partitioning large tables"
            ];
            
            return suggestions[Math.floor(Math.random() * suggestions.length)];
        },
        
        calculateStatistics() {
            // Calculate average query time
            const totalTime = this.queryHistory.reduce((sum, q) => sum + q.duration, 0);
            this.averageQueryTime = totalTime / this.queryHistory.length || 0;
            
            // Calculate table statistics
            const tableData = {};
            this.queryHistory.forEach(query => {
                if (!tableData[query.table]) {
                    tableData[query.table] = {
                        name: query.table,
                        queryCount: 0,
                        totalDuration: 0,
                        avgDuration: 0
                    };
                }
                tableData[query.table].queryCount++;
                tableData[query.table].totalDuration += query.duration;
            });
            
            this.tableStats = Object.values(tableData).map(table => ({
                ...table,
                avgDuration: table.totalDuration / table.queryCount
            })).sort((a, b) => b.totalDuration - a.totalDuration);
            
            // Calculate query type statistics
            const typeData = {};
            this.queryHistory.forEach(query => {
                if (!typeData[query.type]) {
                    typeData[query.type] = {
                        name: query.type.toUpperCase(),
                        count: 0,
                        totalDuration: 0,
                        avgDuration: 0,
                        percentage: 0
                    };
                }
                typeData[query.type].count++;
                typeData[query.type].totalDuration += query.duration;
            });
            
            const totalQueries = this.queryHistory.length;
            this.queryTypeStats = Object.values(typeData).map(type => ({
                ...type,
                avgDuration: type.totalDuration / type.count,
                percentage: (type.count / totalQueries) * 100
            })).sort((a, b) => b.count - a.count);
        },
        
        toggleLiveMonitoring() {
            if (this.liveMonitoring) {
                this.startLiveMonitoring();
            } else {
                this.stopLiveMonitoring();
            }
        },
        
        startLiveMonitoring() {
            this.stopLiveMonitoring();
            this.liveMonitoring = true;
            
            // Update every 2 seconds when live monitoring is on
            this.monitoringInterval = setInterval(() => {
                this.updateLiveData();
            }, 2000);
        },
        
        stopLiveMonitoring() {
            this.liveMonitoring = false;
            if (this.monitoringInterval) {
                clearInterval(this.monitoringInterval);
                this.monitoringInterval = null;
            }
        },
        
        updateLiveData() {
            // Simulate new queries coming in
            if (Math.random() < 0.7) { // 70% chance of new query
                const queryTypes = ['select', 'insert', 'update', 'delete'];
                const tables = ['tickets', 'events', 'users', 'venues', 'purchases', 'categories'];
                const type = queryTypes[Math.floor(Math.random() * queryTypes.length)];
                const table = tables[Math.floor(Math.random() * tables.length)];
                const duration = this.generateQueryDuration(type);
                
                const newQuery = {
                    id: `live_${Date.now()}`,
                    timestamp: Date.now(),
                    type,
                    table,
                    duration,
                    sql: this.generateSampleSQL(type, table),
                    user: `user_${Math.floor(Math.random() * 50) + 1}`,
                    database: 'hdtickets_production'
                };
                
                this.queryHistory.push(newQuery);
                
                // Keep only last 200 queries
                if (this.queryHistory.length > 200) {
                    this.queryHistory.shift();
                }
                
                // Check if it's a slow query
                if (duration > 1000) {
                    this.slowQueries.push({
                        ...newQuery,
                        optimization: this.getOptimizationSuggestion(type, table, duration)
                    });
                    
                    // Keep only last 10 slow queries
                    if (this.slowQueries.length > 10) {
                        this.slowQueries.shift();
                    }
                }
                
                // Update max query time
                this.maxQueryTime = Math.max(this.maxQueryTime, duration);
                
                // Recalculate statistics
                this.calculateStatistics();
            }
            
            // Update active queries (simulate completion)
            this.activeQueries = this.activeQueries.filter(() => Math.random() < 0.8); // 20% chance of completion
            
            // Update connection pool metrics
            this.activeConnections = Math.max(5, this.activeConnections + Math.floor(Math.random() * 6) - 3);
            this.idleConnections = this.maxConnections - this.activeConnections;
        },
        
        clearHistory() {
            this.queryHistory = [];
            this.slowQueries = [];
            this.tableStats = [];
            this.queryTypeStats = [];
            this.averageQueryTime = 0;
            
            console.log('[DBMonitor] History cleared');
        },
        
        // Utility Methods
        formatDuration(ms) {
            if (ms < 1000) {
                return Math.round(ms) + 'ms';
            } else {
                return (ms / 1000).toFixed(1) + 's';
            }
        },
        
        formatTimestamp(timestamp) {
            return new Date(timestamp).toLocaleTimeString();
        },
        
        // Color helper methods
        getAvgTimeStatusColor() {
            if (this.averageQueryTime <= 100) return 'bg-green-500';
            if (this.averageQueryTime <= 500) return 'bg-yellow-500';
            return 'bg-red-500';
        },
        
        getAvgTimeTextColor() {
            if (this.averageQueryTime <= 100) return 'text-green-600';
            if (this.averageQueryTime <= 500) return 'text-yellow-600';
            return 'text-red-600';
        },
        
        getConnectionStatusColor() {
            const usage = this.activeConnections / this.maxConnections;
            if (usage <= 0.7) return 'bg-green-500';
            if (usage <= 0.9) return 'bg-yellow-500';
            return 'bg-red-500';
        },
        
        getConnectionTextColor() {
            const usage = this.activeConnections / this.maxConnections;
            if (usage <= 0.7) return 'text-green-600';
            if (usage <= 0.9) return 'text-yellow-600';
            return 'text-red-600';
        },
        
        getConnectionPoolColor() {
            const usage = this.activeConnections / this.maxConnections;
            if (usage <= 0.7) return 'text-green-500';
            if (usage <= 0.9) return 'text-yellow-500';
            return 'text-red-500';
        },
        
        getCacheHitStatusColor() {
            if (this.cacheHitRate >= 95) return 'bg-green-500';
            if (this.cacheHitRate >= 90) return 'bg-yellow-500';
            return 'bg-red-500';
        },
        
        getCacheHitTextColor() {
            if (this.cacheHitRate >= 95) return 'text-green-600';
            if (this.cacheHitRate >= 90) return 'text-yellow-600';
            return 'text-red-600';
        },
        
        getQueryTypeColor(type) {
            const colors = {
                select: 'bg-blue-500',
                insert: 'bg-green-500',
                update: 'bg-yellow-500',
                delete: 'bg-red-500'
            };
            return colors[type.toLowerCase()] || 'bg-gray-500';
        }
    };
}
</script>