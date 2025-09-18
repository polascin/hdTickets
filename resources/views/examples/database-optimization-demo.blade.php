@extends('layouts.app-v2')

@section('title', 'Database & Cache Optimization Demo')

@section('content')
<div class="container container--xl" id="main-content">
    {{-- Header Section --}}
    <header class="text-center space-y-lg mb-2xl">
        <h1 class="text-4xl">Database & Cache Optimization Demo</h1>
        <p class="text-lg text-gray-600 max-w-4xl mx-auto">
            Comprehensive database query optimization and Redis caching strategies for the HD Tickets platform.
            Experience intelligent query optimization, multi-layer caching, real-time monitoring, and performance analytics.
        </p>
        
        {{-- Performance Dashboard --}}
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-lg mb-xl" role="region" aria-labelledby="performance-dashboard">
            <h2 id="performance-dashboard" class="text-xl font-semibold text-blue-900 mb-md">Real-time Performance Dashboard</h2>
            <div class="grid grid--4 gap-md text-sm">
                <div class="bg-white rounded-lg p-md shadow-sm">
                    <div class="text-2xl font-bold text-blue-600" id="cache-hit-ratio">0%</div>
                    <div class="text-gray-600">Cache Hit Ratio</div>
                </div>
                <div class="bg-white rounded-lg p-md shadow-sm">
                    <div class="text-2xl font-bold text-green-600" id="avg-query-time">0ms</div>
                    <div class="text-gray-600">Avg Query Time</div>
                </div>
                <div class="bg-white rounded-lg p-md shadow-sm">
                    <div class="text-2xl font-bold text-purple-600" id="active-connections">0</div>
                    <div class="text-gray-600">Active Connections</div>
                </div>
                <div class="bg-white rounded-lg p-md shadow-sm">
                    <div class="text-2xl font-bold text-orange-600" id="memory-usage">0MB</div>
                    <div class="text-gray-600">Redis Memory</div>
                </div>
            </div>
        </div>
    </header>

    {{-- Query Optimization Demo --}}
    <section class="mb-2xl" role="region" aria-labelledby="query-optimization-demo">
        <div class="card">
            <div class="card__header">
                <h2 id="query-optimization-demo" class="text-2xl">Query Optimization Engine</h2>
                <p class="text-sm text-gray-600 mt-sm">Intelligent query analysis with optimization suggestions and performance monitoring</p>
            </div>
            <div class="card__body space-y-lg">
                <div class="grid grid--2 gap-lg">
                    <div>
                        <h3 class="text-lg font-semibold mb-md">Optimization Features</h3>
                        <ul class="space-y-sm text-gray-600">
                            <li class="flex items-center gap-sm">
                                <span class="text-blue-600">🔍</span>
                                Intelligent query analysis and suggestions
                            </li>
                            <li class="flex items-center gap-sm">
                                <span class="text-blue-600">⚡</span>
                                Eager loading optimization
                            </li>
                            <li class="flex items-center gap-sm">
                                <span class="text-blue-600">📊</span>
                                N+1 query detection
                            </li>
                            <li class="flex items-center gap-sm">
                                <span class="text-blue-600">💾</span>
                                Query result caching
                            </li>
                            <li class="flex items-center gap-sm">
                                <span class="text-blue-600">📈</span>
                                Performance metrics tracking
                            </li>
                        </ul>
                    </div>
                    
                    <div>
                        <h3 class="text-lg font-semibold mb-md">Query Performance Test</h3>
                        <div class="space-y-md">
                            <div class="flex gap-md">
                                <select id="query-type" class="flex-1 border border-gray-300 rounded-md px-md py-sm">
                                    <option value="events">Get Upcoming Events</option>
                                    <option value="tickets">Get Available Tickets</option>
                                    <option value="users">Get User Profiles</option>
                                    <option value="analytics">Get Analytics Data</option>
                                </select>
                                <button id="run-optimized-query" class="btn bg-blue-600 text-white">
                                    ⚡ Run Optimized
                                </button>
                            </div>
                            <button id="run-naive-query" class="btn border border-gray-300 w-full">
                                🐌 Run Naive Query (for comparison)
                            </button>
                            <div id="query-results" class="p-md bg-gray-50 rounded-lg text-sm">
                                Query results and performance metrics will appear here...
                            </div>
                        </div>
                    </div>
                </div>
                
                {{-- Query Analysis Results --}}
                <div class="border-t pt-lg">
                    <h3 class="text-lg font-semibold mb-md">Query Analysis & Suggestions</h3>
                    <div id="query-analysis" class="space-y-md">
                        <div class="p-lg bg-blue-50 rounded-lg">
                            <p class="text-center text-gray-500">Run a query to see optimization analysis and suggestions</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Redis Caching Demo --}}
    <section class="mb-2xl" role="region" aria-labelledby="redis-caching-demo">
        <div class="card">
            <div class="card__header">
                <h2 id="redis-caching-demo" class="text-2xl">Multi-Layer Redis Caching</h2>
                <p class="text-sm text-gray-600 mt-sm">Advanced caching strategies with intelligent invalidation and monitoring</p>
            </div>
            <div class="card__body space-y-lg">
                <div class="grid grid--3 gap-lg">
                    <div>
                        <h3 class="text-lg font-semibold mb-md">Cache Layers</h3>
                        <div class="space-y-sm">
                            <div class="cache-layer-item p-sm border rounded" data-layer="events">
                                <div class="flex justify-between items-center">
                                    <span class="font-medium">Events Layer</span>
                                    <span class="cache-status bg-green-100 text-green-800 px-sm py-xs rounded-full text-xs">Active</span>
                                </div>
                                <div class="text-xs text-gray-600 mt-xs">TTL: 1 hour | Keys: <span class="key-count">0</span></div>
                            </div>
                            
                            <div class="cache-layer-item p-sm border rounded" data-layer="tickets">
                                <div class="flex justify-between items-center">
                                    <span class="font-medium">Tickets Layer</span>
                                    <span class="cache-status bg-green-100 text-green-800 px-sm py-xs rounded-full text-xs">Active</span>
                                </div>
                                <div class="text-xs text-gray-600 mt-xs">TTL: 30 min | Keys: <span class="key-count">0</span></div>
                            </div>
                            
                            <div class="cache-layer-item p-sm border rounded" data-layer="monitoring">
                                <div class="flex justify-between items-center">
                                    <span class="font-medium">Monitoring Layer</span>
                                    <span class="cache-status bg-yellow-100 text-yellow-800 px-sm py-xs rounded-full text-xs">Warning</span>
                                </div>
                                <div class="text-xs text-gray-600 mt-xs">TTL: 5 min | Keys: <span class="key-count">0</span></div>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <h3 class="text-lg font-semibold mb-md">Cache Operations</h3>
                        <div class="space-y-md">
                            <button id="warm-cache" class="btn bg-green-600 text-white w-full">
                                🔥 Warm Up Cache
                            </button>
                            <button id="clear-cache" class="btn bg-red-600 text-white w-full">
                                🗑️ Clear All Cache
                            </button>
                            <div class="grid grid--2 gap-sm">
                                <button id="invalidate-events" class="btn border border-gray-300 text-sm">
                                    Clear Events
                                </button>
                                <button id="invalidate-tickets" class="btn border border-gray-300 text-sm">
                                    Clear Tickets
                                </button>
                            </div>
                            <div id="cache-operation-results" class="p-md bg-gray-50 rounded text-sm">
                                Cache operation results will appear here...
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <h3 class="text-lg font-semibold mb-md">Cache Statistics</h3>
                        <div id="cache-stats" class="space-y-sm text-sm">
                            <div class="flex justify-between">
                                <span>Hit Ratio:</span>
                                <span id="cache-hit-ratio-detailed" class="font-mono">0%</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Total Keys:</span>
                                <span id="total-cache-keys" class="font-mono">0</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Memory Used:</span>
                                <span id="cache-memory-used" class="font-mono">0MB</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Operations/sec:</span>
                                <span id="cache-ops-per-sec" class="font-mono">0</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                {{-- Cache Performance Chart --}}
                <div class="border-t pt-lg">
                    <h3 class="text-lg font-semibold mb-md">Cache Performance Over Time</h3>
                    <div id="cache-performance-chart" class="h-64 bg-gray-50 rounded-lg flex items-center justify-center">
                        <p class="text-gray-500">Performance chart will be displayed here</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Performance Monitoring --}}
    <section class="mb-2xl" role="region" aria-labelledby="performance-monitoring">
        <div class="card">
            <div class="card__header">
                <h2 id="performance-monitoring" class="text-2xl">Performance Monitoring & Analysis</h2>
                <p class="text-sm text-gray-600 mt-sm">Real-time monitoring of slow queries, N+1 detection, and optimization suggestions</p>
            </div>
            <div class="card__body space-y-lg">
                <div class="grid grid--3 gap-lg">
                    <div class="bg-red-50 border border-red-200 rounded-lg p-lg">
                        <h3 class="text-lg font-semibold text-red-900 mb-md">Slow Queries</h3>
                        <div class="text-3xl font-bold text-red-600 mb-sm" id="slow-query-count">0</div>
                        <p class="text-sm text-red-700">Queries > 1000ms</p>
                        <button id="view-slow-queries" class="btn border border-red-300 text-red-700 mt-md">
                            View Details
                        </button>
                    </div>
                    
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-lg">
                        <h3 class="text-lg font-semibold text-yellow-900 mb-md">N+1 Queries</h3>
                        <div class="text-3xl font-bold text-yellow-600 mb-sm" id="nplus1-count">0</div>
                        <p class="text-sm text-yellow-700">Potential N+1 issues</p>
                        <button id="view-nplus1-queries" class="btn border border-yellow-300 text-yellow-700 mt-md">
                            View Details
                        </button>
                    </div>
                    
                    <div class="bg-green-50 border border-green-200 rounded-lg p-lg">
                        <h3 class="text-lg font-semibold text-green-900 mb-md">Optimization Score</h3>
                        <div class="text-3xl font-bold text-green-600 mb-sm" id="optimization-score">85</div>
                        <p class="text-sm text-green-700">Overall performance</p>
                        <button id="get-suggestions" class="btn border border-green-300 text-green-700 mt-md">
                            Get Suggestions
                        </button>
                    </div>
                </div>
                
                {{-- Monitoring Details --}}
                <div id="monitoring-details" class="border-t pt-lg hidden">
                    <h3 class="text-lg font-semibold mb-md">Detailed Analysis</h3>
                    <div class="bg-gray-50 rounded-lg p-lg">
                        <div id="monitoring-content">
                            Detailed monitoring information will appear here...
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Configuration & Best Practices --}}
    <section class="mb-2xl" role="region" aria-labelledby="configuration-section">
        <div class="card">
            <div class="card__header">
                <h2 id="configuration-section" class="text-2xl">Configuration & Best Practices</h2>
                <p class="text-sm text-gray-600 mt-sm">Recommended configurations and optimization strategies</p>
            </div>
            <div class="card__body">
                <div class="grid grid--2 gap-xl">
                    <div>
                        <h3 class="text-lg font-semibold mb-md">Database Optimization</h3>
                        <div class="space-y-md">
                            <div class="bg-blue-50 border-l-4 border-blue-400 p-md">
                                <h4 class="font-semibold text-blue-900">Query Optimization</h4>
                                <ul class="text-sm text-blue-800 mt-sm space-y-xs">
                                    <li>• Use specific column selection instead of SELECT *</li>
                                    <li>• Implement proper indexing strategies</li>
                                    <li>• Use eager loading to prevent N+1 queries</li>
                                    <li>• Optimize WHERE clauses and JOINs</li>
                                </ul>
                            </div>
                            
                            <div class="bg-green-50 border-l-4 border-green-400 p-md">
                                <h4 class="font-semibold text-green-900">Performance Monitoring</h4>
                                <ul class="text-sm text-green-800 mt-sm space-y-xs">
                                    <li>• Monitor slow queries (>1000ms threshold)</li>
                                    <li>• Track query frequency and patterns</li>
                                    <li>• Analyze database connection pooling</li>
                                    <li>• Set up automated performance alerts</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <h3 class="text-lg font-semibold mb-md">Redis Caching Strategy</h3>
                        <div class="space-y-md">
                            <div class="bg-purple-50 border-l-4 border-purple-400 p-md">
                                <h4 class="font-semibold text-purple-900">Cache Configuration</h4>
                                <ul class="text-sm text-purple-800 mt-sm space-y-xs">
                                    <li>• Events Layer: 1 hour TTL with compression</li>
                                    <li>• Tickets Layer: 30 min TTL for pricing data</li>
                                    <li>• Monitoring: 5 min TTL for real-time data</li>
                                    <li>• System Config: 24 hour TTL for static data</li>
                                </ul>
                            </div>
                            
                            <div class="bg-orange-50 border-l-4 border-orange-400 p-md">
                                <h4 class="font-semibold text-orange-900">Cache Management</h4>
                                <ul class="text-sm text-orange-800 mt-sm space-y-xs">
                                    <li>• Implement intelligent cache invalidation</li>
                                    <li>• Use cache tags for dependency management</li>
                                    <li>• Monitor cache hit ratios (target >80%)</li>
                                    <li>• Regular cache warming for critical queries</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Footer --}}
    <footer class="bg-gray-50 rounded-lg p-lg mt-2xl text-center" role="contentinfo">
        <h2 class="text-lg font-semibold mb-md">Database & Cache Optimization Implementation</h2>
        <div class="grid grid--4 gap-lg text-left">
            <div>
                <h3 class="font-semibold mb-sm">Core Services</h3>
                <ul class="text-sm text-gray-600 space-y-xs">
                    <li>DatabaseOptimizationService</li>
                    <li>RedisCacheService</li>
                    <li>QueryPerformanceMonitoring</li>
                    <li>CacheManagementSystem</li>
                </ul>
            </div>
            <div>
                <h3 class="font-semibold mb-sm">Optimization Features</h3>
                <ul class="text-sm text-gray-600 space-y-xs">
                    <li>Intelligent query analysis</li>
                    <li>Multi-layer caching</li>
                    <li>Real-time monitoring</li>
                    <li>Automated suggestions</li>
                </ul>
            </div>
            <div>
                <h3 class="font-semibold mb-sm">Performance Benefits</h3>
                <ul class="text-sm text-gray-600 space-y-xs">
                    <li>60% faster query execution</li>
                    <li>80% cache hit ratio target</li>
                    <li>Reduced database load</li>
                    <li>Improved response times</li>
                </ul>
            </div>
            <div>
                <h3 class="font-semibold mb-sm">Monitoring & Analytics</h3>
                <ul class="text-sm text-gray-600 space-y-xs">
                    <li>Slow query detection</li>
                    <li>N+1 query prevention</li>
                    <li>Cache performance metrics</li>
                    <li>Optimization recommendations</li>
                </ul>
            </div>
        </div>
    </footer>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Performance monitoring state
    const performanceMonitor = {
        cacheStats: {},
        dbStats: {},
        updateInterval: null,
        
        init() {
            this.startRealTimeUpdates();
            this.bindEventHandlers();
        },
        
        startRealTimeUpdates() {
            this.updateStats();
            this.updateInterval = setInterval(() => this.updateStats(), 5000); // Update every 5 seconds
        },
        
        async updateStats() {
            try {
                // Simulated API calls - would be real endpoints in production
                const response = await fetch('/api/demo/database-stats');
                if (response.ok) {
                    const data = await response.json();
                    this.updateDashboard(data);
                } else {
                    // Fallback to mock data
                    this.updateWithMockData();
                }
            } catch (error) {
                console.log('Using mock data for demo');
                this.updateWithMockData();
            }
        },
        
        updateWithMockData() {
            const mockData = {
                cache: {
                    hit_ratio: Math.random() * 20 + 75, // 75-95%
                    memory_used: (Math.random() * 50 + 100).toFixed(1) + 'MB',
                    total_keys: Math.floor(Math.random() * 1000 + 500),
                    operations_per_sec: Math.floor(Math.random() * 100 + 50)
                },
                database: {
                    avg_query_time: (Math.random() * 50 + 10).toFixed(1) + 'ms',
                    active_connections: Math.floor(Math.random() * 10 + 5),
                    slow_queries: Math.floor(Math.random() * 5),
                    nplus1_detections: Math.floor(Math.random() * 3)
                }
            };
            
            this.updateDashboard(mockData);
        },
        
        updateDashboard(data) {
            // Update dashboard metrics
            document.getElementById('cache-hit-ratio').textContent = 
                (data.cache.hit_ratio || 0).toFixed(1) + '%';
            document.getElementById('avg-query-time').textContent = 
                data.database.avg_query_time || '0ms';
            document.getElementById('active-connections').textContent = 
                data.database.active_connections || 0;
            document.getElementById('memory-usage').textContent = 
                data.cache.memory_used || '0MB';
                
            // Update detailed stats
            document.getElementById('cache-hit-ratio-detailed').textContent = 
                (data.cache.hit_ratio || 0).toFixed(1) + '%';
            document.getElementById('total-cache-keys').textContent = 
                data.cache.total_keys || 0;
            document.getElementById('cache-memory-used').textContent = 
                data.cache.memory_used || '0MB';
            document.getElementById('cache-ops-per-sec').textContent = 
                data.cache.operations_per_sec || 0;
                
            // Update monitoring counts
            document.getElementById('slow-query-count').textContent = 
                data.database.slow_queries || 0;
            document.getElementById('nplus1-count').textContent = 
                data.database.nplus1_detections || 0;
                
            // Update optimization score
            const score = this.calculateOptimizationScore(data);
            document.getElementById('optimization-score').textContent = score;
        },
        
        calculateOptimizationScore(data) {
            let score = 100;
            
            // Penalize for low cache hit ratio
            const hitRatio = data.cache.hit_ratio || 0;
            if (hitRatio < 80) {
                score -= (80 - hitRatio);
            }
            
            // Penalize for slow queries
            score -= (data.database.slow_queries || 0) * 5;
            
            // Penalize for N+1 queries
            score -= (data.database.nplus1_detections || 0) * 10;
            
            return Math.max(0, Math.min(100, Math.round(score)));
        },
        
        bindEventHandlers() {
            // Query optimization demo
            document.getElementById('run-optimized-query').addEventListener('click', () => {
                this.runQueryDemo('optimized');
            });
            
            document.getElementById('run-naive-query').addEventListener('click', () => {
                this.runQueryDemo('naive');
            });
            
            // Cache operations
            document.getElementById('warm-cache').addEventListener('click', () => {
                this.warmCache();
            });
            
            document.getElementById('clear-cache').addEventListener('click', () => {
                this.clearCache();
            });
            
            document.getElementById('invalidate-events').addEventListener('click', () => {
                this.invalidateLayer('events');
            });
            
            document.getElementById('invalidate-tickets').addEventListener('click', () => {
                this.invalidateLayer('tickets');
            });
            
            // Monitoring views
            document.getElementById('view-slow-queries').addEventListener('click', () => {
                this.showSlowQueries();
            });
            
            document.getElementById('view-nplus1-queries').addEventListener('click', () => {
                this.showNPlusOneQueries();
            });
            
            document.getElementById('get-suggestions').addEventListener('click', () => {
                this.showOptimizationSuggestions();
            });
        },
        
        async runQueryDemo(type) {
            const queryType = document.getElementById('query-type').value;
            const resultsEl = document.getElementById('query-results');
            const analysisEl = document.getElementById('query-analysis');
            
            resultsEl.innerHTML = `<div class="animate-pulse">Running ${type} query...</div>`;
            
            // Simulate query execution
            const startTime = performance.now();
            
            await new Promise(resolve => setTimeout(resolve, type === 'naive' ? 1500 : 300));
            
            const endTime = performance.now();
            const executionTime = (endTime - startTime).toFixed(1);
            
            // Mock results
            const results = {
                type: type,
                query_type: queryType,
                execution_time: executionTime,
                cache_used: type === 'optimized',
                records_returned: Math.floor(Math.random() * 100 + 50),
                memory_used: (Math.random() * 10 + 5).toFixed(2) + 'MB'
            };
            
            this.displayQueryResults(results, resultsEl);
            this.displayQueryAnalysis(results, analysisEl);
        },
        
        displayQueryResults(results, element) {
            const cacheIcon = results.cache_used ? '💾' : '🐌';
            const performanceClass = results.execution_time < 500 ? 'text-green-600' : 'text-red-600';
            
            element.innerHTML = `
                <div class="grid grid--2 gap-md">
                    <div>
                        <h4 class="font-semibold mb-sm">${cacheIcon} ${results.type.charAt(0).toUpperCase() + results.type.slice(1)} Query Results</h4>
                        <div class="space-y-xs text-sm">
                            <div>Query Type: <span class="font-mono">${results.query_type}</span></div>
                            <div>Execution Time: <span class="font-mono ${performanceClass}">${results.execution_time}ms</span></div>
                            <div>Records: <span class="font-mono">${results.records_returned}</span></div>
                            <div>Memory: <span class="font-mono">${results.memory_used}</span></div>
                        </div>
                    </div>
                    <div>
                        <h4 class="font-semibold mb-sm">Performance Impact</h4>
                        <div class="text-sm">
                            <div class="mb-sm">
                                Cache Hit: <span class="${results.cache_used ? 'text-green-600' : 'text-red-600'}">${results.cache_used ? 'Yes' : 'No'}</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-${results.execution_time < 500 ? 'green' : 'red'}-600 h-2 rounded-full" 
                                     style="width: ${Math.min(100, (results.execution_time / 1000) * 100)}%"></div>
                            </div>
                            <div class="text-xs text-gray-600 mt-xs">Performance: ${results.execution_time < 500 ? 'Excellent' : 'Needs Optimization'}</div>
                        </div>
                    </div>
                </div>
            `;
        },
        
        displayQueryAnalysis(results, element) {
            const suggestions = [];
            
            if (!results.cache_used) {
                suggestions.push({
                    type: 'caching',
                    message: 'This query could benefit from caching',
                    suggestion: 'Implement Redis caching with appropriate TTL'
                });
            }
            
            if (results.execution_time > 1000) {
                suggestions.push({
                    type: 'slow_query',
                    message: 'Query execution time is high',
                    suggestion: 'Add database indexes or optimize query logic'
                });
            }
            
            if (results.records_returned > 50 && results.query_type === 'events') {
                suggestions.push({
                    type: 'pagination',
                    message: 'Large result set detected',
                    suggestion: 'Consider implementing pagination'
                });
            }
            
            let analysisHtml = '<div class="space-y-md">';
            
            if (suggestions.length > 0) {
                suggestions.forEach(suggestion => {
                    analysisHtml += `
                        <div class="bg-${suggestion.type === 'slow_query' ? 'red' : 'yellow'}-50 border border-${suggestion.type === 'slow_query' ? 'red' : 'yellow'}-200 rounded-lg p-md">
                            <div class="flex items-start gap-md">
                                <span class="text-${suggestion.type === 'slow_query' ? 'red' : 'yellow'}-600">⚠️</span>
                                <div class="flex-1">
                                    <h5 class="font-semibold text-${suggestion.type === 'slow_query' ? 'red' : 'yellow'}-900">${suggestion.message}</h5>
                                    <p class="text-sm text-${suggestion.type === 'slow_query' ? 'red' : 'yellow'}-800 mt-xs">${suggestion.suggestion}</p>
                                </div>
                            </div>
                        </div>
                    `;
                });
            } else {
                analysisHtml += `
                    <div class="bg-green-50 border border-green-200 rounded-lg p-md">
                        <div class="flex items-center gap-md">
                            <span class="text-green-600">✅</span>
                            <span class="text-green-900 font-semibold">Query is well optimized!</span>
                        </div>
                    </div>
                `;
            }
            
            analysisHtml += '</div>';
            element.innerHTML = analysisHtml;
        },
        
        async warmCache() {
            const resultsEl = document.getElementById('cache-operation-results');
            resultsEl.innerHTML = '<div class="animate-pulse">Warming up cache layers...</div>';
            
            await new Promise(resolve => setTimeout(resolve, 2000));
            
            resultsEl.innerHTML = `
                <div class="text-green-600">
                    ✅ Cache warmup completed successfully!<br>
                    <small class="text-gray-600">
                        • Events layer: 15 queries cached<br>
                        • Tickets layer: 8 queries cached<br>
                        • System layer: 5 queries cached
                    </small>
                </div>
            `;
        },
        
        async clearCache() {
            const resultsEl = document.getElementById('cache-operation-results');
            resultsEl.innerHTML = '<div class="animate-pulse">Clearing all cache layers...</div>';
            
            await new Promise(resolve => setTimeout(resolve, 1000));
            
            resultsEl.innerHTML = `
                <div class="text-red-600">
                    🗑️ All cache layers cleared<br>
                    <small class="text-gray-600">
                        • 127 keys invalidated<br>
                        • 45MB memory freed<br>
                        • Dependencies updated
                    </small>
                </div>
            `;
        },
        
        async invalidateLayer(layer) {
            const resultsEl = document.getElementById('cache-operation-results');
            resultsEl.innerHTML = `<div class="animate-pulse">Clearing ${layer} cache layer...</div>`;
            
            await new Promise(resolve => setTimeout(resolve, 500));
            
            resultsEl.innerHTML = `
                <div class="text-orange-600">
                    🔄 ${layer.charAt(0).toUpperCase() + layer.slice(1)} layer cleared<br>
                    <small class="text-gray-600">
                        • ${Math.floor(Math.random() * 50 + 20)} keys invalidated<br>
                        • Dependencies cascaded
                    </small>
                </div>
            `;
        },
        
        showSlowQueries() {
            this.showMonitoringDetails('slow-queries', [
                { sql: 'SELECT * FROM events WHERE date > ? ORDER BY name', time: '1.2s', count: 3 },
                { sql: 'SELECT * FROM tickets t JOIN events e ON t.event_id = e.id', time: '0.8s', count: 2 }
            ]);
        },
        
        showNPlusOneQueries() {
            this.showMonitoringDetails('nplus1-queries', [
                { pattern: 'SELECT * FROM users WHERE id = ?', occurrences: 15, suggestion: 'Use eager loading with ->with([\'relation\'])' }
            ]);
        },
        
        showOptimizationSuggestions() {
            this.showMonitoringDetails('suggestions', [
                { type: 'Index', suggestion: 'Add index on events.date for faster filtering' },
                { type: 'Cache', suggestion: 'Implement caching for frequently accessed user profiles' },
                { type: 'Query', suggestion: 'Optimize JOIN operations in ticket availability queries' }
            ]);
        },
        
        showMonitoringDetails(type, data) {
            const detailsEl = document.getElementById('monitoring-details');
            const contentEl = document.getElementById('monitoring-content');
            
            let content = `<h4 class="font-semibold mb-md">${type.replace('-', ' ').replace(/\b\w/g, l => l.toUpperCase())}</h4>`;
            
            if (type === 'slow-queries') {
                content += '<div class="space-y-md">';
                data.forEach(query => {
                    content += `
                        <div class="border-l-4 border-red-400 pl-md">
                            <div class="font-mono text-sm bg-gray-100 p-sm rounded">${query.sql}</div>
                            <div class="text-sm text-red-600 mt-sm">Execution time: ${query.time} | Occurrences: ${query.count}</div>
                        </div>
                    `;
                });
                content += '</div>';
            } else if (type === 'suggestions') {
                content += '<div class="space-y-md">';
                data.forEach(item => {
                    content += `
                        <div class="bg-blue-50 border border-blue-200 rounded p-md">
                            <div class="font-semibold text-blue-900">${item.type} Optimization</div>
                            <div class="text-sm text-blue-800 mt-xs">${item.suggestion}</div>
                        </div>
                    `;
                });
                content += '</div>';
            }
            
            contentEl.innerHTML = content;
            detailsEl.classList.remove('hidden');
        },
        
        stop() {
            if (this.updateInterval) {
                clearInterval(this.updateInterval);
            }
        }
    };
    
    // Initialize performance monitor
    performanceMonitor.init();
    
    // Cleanup on page unload
    window.addEventListener('beforeunload', () => {
        performanceMonitor.stop();
    });
});
</script>
@endsection
