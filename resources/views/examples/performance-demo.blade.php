@extends('layouts.app')

@section('title', 'Performance Optimization Demo')

@section('content')
{{-- Include Performance Scripts --}}
<script src="{{ asset('js/lazy-loading.js') }}" defer></script>
<script src="{{ asset('js/virtual-scrolling.js') }}" defer></script>
<script src="{{ asset('js/debounce-utils.js') }}" defer></script>

<div class="container container--xl" id="main-content">
    {{-- Header Section --}}
    <header class="text-center space-y-lg mb-2xl">
        <h1 class="text-4xl">Performance Optimization Demo</h1>
        <p class="text-lg text-gray-600 max-w-3xl mx-auto">
            Comprehensive performance optimizations for the HD Tickets platform including
            lazy loading, virtual scrolling, debouncing, and advanced caching strategies.
        </p>
        
        {{-- Performance Metrics Panel --}}
        <div class="bg-green-50 border border-green-200 rounded-lg p-lg mb-xl" role="region" aria-labelledby="performance-metrics">
            <h2 id="performance-metrics" class="text-lg font-semibold text-green-900 mb-md">Real-time Performance Metrics</h2>
            <div class="grid grid--4 gap-md text-sm">
                <div class="bg-white rounded p-md">
                    <strong>Load Time:</strong>
                    <span id="load-time" class="font-mono">Measuring...</span>
                </div>
                <div class="bg-white rounded p-md">
                    <strong>DOM Elements:</strong>
                    <span id="dom-elements" class="font-mono">Counting...</span>
                </div>
                <div class="bg-white rounded p-md">
                    <strong>Memory Usage:</strong>
                    <span id="memory-usage" class="font-mono">Calculating...</span>
                </div>
                <div class="bg-white rounded p-md">
                    <strong>FPS:</strong>
                    <span id="fps-counter" class="font-mono">0</span>
                </div>
            </div>
        </div>
    </header>

    {{-- Lazy Loading Demo --}}
    <section class="mb-2xl" role="region" aria-labelledby="lazy-loading-demo">
        <div class="card">
            <div class="card__header">
                <h2 id="lazy-loading-demo" class="text-2xl">Lazy Loading Implementation</h2>
                <p class="text-sm text-gray-600 mt-sm">Images and content loaded only when needed to improve initial page load</p>
            </div>
            <div class="card__body space-y-lg">
                <div class="grid grid--2 gap-lg">
                    <div>
                        <h3 class="text-lg font-semibold mb-md">Lazy Loading Features</h3>
                        <ul class="space-y-sm text-gray-600">
                            <li class="flex items-center gap-sm">
                                <span class="text-green-600">🖼️</span>
                                Image lazy loading with intersection observer
                            </li>
                            <li class="flex items-center gap-sm">
                                <span class="text-green-600">🎨</span>
                                Background image lazy loading
                            </li>
                            <li class="flex items-center gap-sm">
                                <span class="text-green-600">📱</span>
                                Iframe lazy loading for embeds
                            </li>
                            <li class="flex items-center gap-sm">
                                <span class="text-green-600">📄</span>
                                Dynamic content loading via AJAX
                            </li>
                            <li class="flex items-center gap-sm">
                                <span class="text-green-600">🔄</span>
                                Automatic retry on failed loads
                            </li>
                        </ul>
                    </div>
                    
                    <div>
                        <h3 class="text-lg font-semibold mb-md">Lazy Loading Controls</h3>
                        <div class="space-y-md">
                            <button id="load-images" class="btn bg-blue-600 text-white w-full">
                                📸 Load All Images Now
                            </button>
                            <button id="check-lazy-stats" class="btn border border-gray-300 w-full">
                                📊 Check Lazy Loading Stats
                            </button>
                            <div id="lazy-loading-info" class="p-md bg-gray-50 rounded text-sm text-gray-600">
                                Lazy loading statistics will appear here...
                            </div>
                        </div>
                    </div>
                </div>
                
                {{-- Lazy Loading Examples --}}
                <div class="border-t pt-lg">
                    <h3 class="text-lg font-semibold mb-md">Lazy Loading Examples</h3>
                    <div class="grid grid--3 gap-md">
                        <div class="text-center">
                            <img data-lazy-src="https://via.placeholder.com/300x200/3B82F6/FFFFFF?text=Lazy+Image+1"
                                 data-lazy-placeholder="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='300' height='200'%3E%3Crect width='300' height='200' fill='%23f3f4f6'/%3E%3Ctext x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' fill='%236b7280'%3ELoading...%3C/text%3E%3C/svg%3E"
                                 alt="Lazy loaded image example"
                                 class="w-full h-48 object-cover rounded-lg mb-md">
                            <h4 class="font-semibold">Lazy Loaded Image</h4>
                            <p class="text-sm text-gray-600">Loads when scrolled into view</p>
                        </div>
                        
                        <div class="text-center">
                            <div class="w-full h-48 bg-gray-200 rounded-lg mb-md"
                                 data-lazy-background="https://via.placeholder.com/300x200/10B981/FFFFFF?text=Background+Image"
                                 style="display: flex; align-items: center; justify-content: center; color: #6b7280;">
                                Background Loading...
                            </div>
                            <h4 class="font-semibold">Lazy Background</h4>
                            <p class="text-sm text-gray-600">Background image lazy loading</p>
                        </div>
                        
                        <div class="text-center">
                            <div class="w-full h-48 border-2 border-dashed border-gray-300 rounded-lg mb-md flex items-center justify-center"
                                 data-lazy-content="/api/demo/sample-content">
                                <span class="text-gray-500">Content loads when visible</span>
                            </div>
                            <h4 class="font-semibold">Dynamic Content</h4>
                            <p class="text-sm text-gray-600">AJAX content lazy loading</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Virtual Scrolling Demo --}}
    <section class="mb-2xl" role="region" aria-labelledby="virtual-scrolling-demo">
        <div class="card">
            <div class="card__header">
                <h2 id="virtual-scrolling-demo" class="text-2xl">Virtual Scrolling</h2>
                <p class="text-sm text-gray-600 mt-sm">Efficiently handle large lists with minimal DOM elements</p>
            </div>
            <div class="card__body space-y-lg">
                <div class="grid grid--2 gap-lg">
                    <div>
                        <h3 class="text-lg font-semibold mb-md">Virtual Scrolling Benefits</h3>
                        <ul class="space-y-sm text-gray-600">
                            <li class="flex items-center gap-sm">
                                <span class="text-purple-600">⚡</span>
                                Handles 10,000+ items smoothly
                            </li>
                            <li class="flex items-center gap-sm">
                                <span class="text-purple-600">🎯</span>
                                Only renders visible items
                            </li>
                            <li class="flex items-center gap-sm">
                                <span class="text-purple-600">📱</span>
                                Mobile optimized scrolling
                            </li>
                            <li class="flex items-center gap-sm">
                                <span class="text-purple-600">🔍</span>
                                Dynamic height estimation
                            </li>
                            <li class="flex items-center gap-sm">
                                <span class="text-purple-600">🎨</span>
                                Customizable item rendering
                            </li>
                        </ul>
                    </div>
                    
                    <div>
                        <h3 class="text-lg font-semibold mb-md">Virtual Scroll Controls</h3>
                        <div class="space-y-md">
                            <div class="flex gap-md">
                                <input type="number" id="item-count" value="1000" min="100" max="50000" 
                                       class="flex-1 border border-gray-300 rounded-md px-md py-sm">
                                <button id="generate-items" class="btn bg-purple-600 text-white">
                                    Generate Items
                                </button>
                            </div>
                            <button id="scroll-to-middle" class="btn border border-gray-300 w-full">
                                📍 Scroll to Middle
                            </button>
                            <button id="get-virtual-stats" class="btn border border-gray-300 w-full">
                                📊 Get Virtual Scroll Stats
                            </button>
                            <div id="virtual-scroll-info" class="p-md bg-gray-50 rounded text-sm text-gray-600">
                                Virtual scroll statistics will appear here...
                            </div>
                        </div>
                    </div>
                </div>
                
                {{-- Virtual Scroll Container --}}
                <div class="border-t pt-lg">
                    <h3 class="text-lg font-semibold mb-md">Virtual Scroll Demo</h3>
                    <div id="virtual-scroll-container" class="border border-gray-300 rounded-lg" 
                         style="height: 400px; background: #f9fafb;">
                        <div class="p-lg text-center text-gray-500">
                            Click "Generate Items" to see virtual scrolling in action
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Debouncing Demo --}}
    <section class="mb-2xl" role="region" aria-labelledby="debouncing-demo">
        <div class="card">
            <div class="card__header">
                <h2 id="debouncing-demo" class="text-2xl">Debouncing & Search Optimization</h2>
                <p class="text-sm text-gray-600 mt-sm">Optimize user input and API calls with smart debouncing</p>
            </div>
            <div class="card__body space-y-lg">
                <div class="grid grid--2 gap-lg">
                    <div>
                        <h3 class="text-lg font-semibold mb-md">Search Features</h3>
                        <ul class="space-y-sm text-gray-600">
                            <li class="flex items-center gap-sm">
                                <span class="text-indigo-600">🔍</span>
                                Debounced search input (300ms delay)
                            </li>
                            <li class="flex items-center gap-sm">
                                <span class="text-indigo-600">💾</span>
                                Intelligent result caching
                            </li>
                            <li class="flex items-center gap-sm">
                                <span class="text-indigo-600">⌨️</span>
                                Keyboard navigation support
                            </li>
                            <li class="flex items-center gap-sm">
                                <span class="text-indigo-600">🚫</span>
                                Request cancellation for fast typing
                            </li>
                            <li class="flex items-center gap-sm">
                                <span class="text-indigo-600">📊</span>
                                Performance metrics tracking
                            </li>
                        </ul>
                    </div>
                    
                    <div>
                        <h3 class="text-lg font-semibold mb-md">Search Performance Test</h3>
                        <div class="space-y-md">
                            <div>
                                <label for="search-input" class="block text-sm font-medium text-gray-700 mb-sm">
                                    Search Sports Events (try typing fast)
                                </label>
                                <input type="text" id="search-input" placeholder="Type to search events, teams, venues..." 
                                       class="w-full border border-gray-300 rounded-md px-md py-sm">
                            </div>
                            
                            <div class="space-y-sm">
                                <div class="flex justify-between text-sm">
                                    <span>Search calls made:</span>
                                    <span id="search-count" class="font-mono">0</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span>Cache hits:</span>
                                    <span id="cache-hits" class="font-mono">0</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span>Average response time:</span>
                                    <span id="avg-response-time" class="font-mono">0ms</span>
                                </div>
                            </div>
                            
                            <button id="clear-search-cache" class="btn border border-gray-300 w-full">
                                🗑️ Clear Search Cache
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Performance Monitoring Demo --}}
    <section class="mb-2xl" role="region" aria-labelledby="performance-monitoring-demo">
        <div class="card">
            <div class="card__header">
                <h2 id="performance-monitoring-demo" class="text-2xl">Performance Monitoring</h2>
                <p class="text-sm text-gray-600 mt-sm">Real-time performance metrics and optimization insights</p>
            </div>
            <div class="card__body space-y-lg">
                <div class="grid grid--3 gap-lg">
                    <div class="text-center p-lg border rounded-lg">
                        <div class="text-3xl font-bold text-blue-600" id="page-load-time">0ms</div>
                        <div class="text-sm text-gray-600">Page Load Time</div>
                    </div>
                    <div class="text-center p-lg border rounded-lg">
                        <div class="text-3xl font-bold text-green-600" id="dom-ready-time">0ms</div>
                        <div class="text-sm text-gray-600">DOM Ready Time</div>
                    </div>
                    <div class="text-center p-lg border rounded-lg">
                        <div class="text-3xl font-bold text-purple-600" id="resource-count">0</div>
                        <div class="text-sm text-gray-600">Resources Loaded</div>
                    </div>
                </div>
                
                {{-- Performance Tests --}}
                <div class="border-t pt-lg">
                    <h3 class="text-lg font-semibold mb-md">Performance Tests</h3>
                    <div class="grid grid--2 gap-lg">
                        <div class="space-y-md">
                            <h4 class="font-semibold">DOM Manipulation Test</h4>
                            <button id="dom-test" class="btn bg-orange-600 text-white w-full">
                                🔧 Run DOM Performance Test
                            </button>
                            <div id="dom-test-results" class="p-md bg-gray-50 rounded text-sm">
                                Results will appear here...
                            </div>
                        </div>
                        
                        <div class="space-y-md">
                            <h4 class="font-semibold">Scroll Performance Test</h4>
                            <button id="scroll-test" class="btn bg-red-600 text-white w-full">
                                📜 Run Scroll Performance Test
                            </button>
                            <div id="scroll-test-results" class="p-md bg-gray-50 rounded text-sm">
                                Results will appear here...
                            </div>
                        </div>
                    </div>
                </div>
                
                {{-- Performance Tips --}}
                <div class="border-t pt-lg">
                    <h3 class="text-lg font-semibold mb-md">Performance Optimization Tips</h3>
                    <div class="grid grid--2 gap-lg">
                        <div>
                            <h4 class="font-semibold text-green-600 mb-sm">Implemented Optimizations</h4>
                            <ul class="text-sm text-gray-600 space-y-xs">
                                <li>✅ Lazy loading for images and content</li>
                                <li>✅ Virtual scrolling for large lists</li>
                                <li>✅ Debounced search inputs</li>
                                <li>✅ Request caching and deduplication</li>
                                <li>✅ Intersection Observer for visibility</li>
                                <li>✅ RequestAnimationFrame for smooth animations</li>
                            </ul>
                        </div>
                        
                        <div>
                            <h4 class="font-semibold text-blue-600 mb-sm">Performance Best Practices</h4>
                            <ul class="text-sm text-gray-600 space-y-xs">
                                <li>📊 Monitor Core Web Vitals</li>
                                <li>🎯 Minimize DOM manipulations</li>
                                <li>⚡ Use passive event listeners</li>
                                <li>🔄 Implement proper caching strategies</li>
                                <li>📱 Optimize for mobile devices</li>
                                <li>🎨 Use CSS transforms for animations</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Footer --}}
    <footer class="bg-gray-50 rounded-lg p-lg mt-2xl text-center" id="footer" role="contentinfo">
        <h2 class="text-lg font-semibold mb-md">Performance Optimization Implementation</h2>
        <div class="grid grid--3 gap-lg text-left">
            <div>
                <h3 class="font-semibold mb-sm">Core Features</h3>
                <ul class="text-sm text-gray-600 space-y-xs">
                    <li>Intersection Observer API</li>
                    <li>Virtual scrolling engine</li>
                    <li>Advanced debouncing utilities</li>
                    <li>Request deduplication</li>
                    <li>Intelligent caching</li>
                    <li>Performance monitoring</li>
                </ul>
            </div>
            <div>
                <h3 class="font-semibold mb-sm">Optimizations</h3>
                <ul class="text-sm text-gray-600 space-y-xs">
                    <li>Reduced initial page load</li>
                    <li>Minimal DOM elements</li>
                    <li>Optimized scroll performance</li>
                    <li>Efficient search handling</li>
                    <li>Smart resource loading</li>
                    <li>Memory usage optimization</li>
                </ul>
            </div>
            <div>
                <h3 class="font-semibold mb-sm">Browser Support</h3>
                <ul class="text-sm text-gray-600 space-y-xs">
                    <li>Chrome 51+ (Full support)</li>
                    <li>Firefox 55+ (Full support)</li>
                    <li>Safari 12.1+ (Full support)</li>
                    <li>Edge 15+ (Full support)</li>
                    <li>Mobile browsers (Optimized)</li>
                    <li>Fallback for older browsers</li>
                </ul>
            </div>
        </div>
    </footer>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Performance monitoring
    const performanceMonitor = {
        startTime: performance.now(),
        searchCount: 0,
        cacheHits: 0,
        responseTimes: [],
        
        init() {
            this.updateMetrics();
            this.setupFPSCounter();
            this.measurePageLoad();
            setInterval(() => this.updateMetrics(), 1000);
        },
        
        updateMetrics() {
            // DOM elements count
            document.getElementById('dom-elements').textContent = document.querySelectorAll('*').length;
            
            // Memory usage (if available)
            if (performance.memory) {
                const memory = (performance.memory.usedJSHeapSize / 1024 / 1024).toFixed(2);
                document.getElementById('memory-usage').textContent = `${memory} MB`;
            } else {
                document.getElementById('memory-usage').textContent = 'Not available';
            }
        },
        
        setupFPSCounter() {
            let frames = 0;
            let lastTime = performance.now();
            
            const countFPS = () => {
                frames++;
                const currentTime = performance.now();
                
                if (currentTime >= lastTime + 1000) {
                    document.getElementById('fps-counter').textContent = frames;
                    frames = 0;
                    lastTime = currentTime;
                }
                
                requestAnimationFrame(countFPS);
            };
            
            requestAnimationFrame(countFPS);
        },
        
        measurePageLoad() {
            const loadTime = performance.now() - this.startTime;
            document.getElementById('load-time').textContent = `${loadTime.toFixed(2)}ms`;
            document.getElementById('page-load-time').textContent = `${loadTime.toFixed(0)}ms`;
            
            // DOM ready time
            if (document.readyState === 'complete') {
                const domTime = performance.timing.domContentLoadedEventEnd - performance.timing.navigationStart;
                document.getElementById('dom-ready-time').textContent = `${domTime}ms`;
            }
            
            // Resource count
            const resources = performance.getEntriesByType('resource').length;
            document.getElementById('resource-count').textContent = resources;
        },
        
        recordSearchMetrics(fromCache, responseTime) {
            this.searchCount++;
            if (fromCache) this.cacheHits++;
            if (responseTime) this.responseTimes.push(responseTime);
            
            document.getElementById('search-count').textContent = this.searchCount;
            document.getElementById('cache-hits').textContent = this.cacheHits;
            
            if (this.responseTimes.length > 0) {
                const avgTime = this.responseTimes.reduce((a, b) => a + b, 0) / this.responseTimes.length;
                document.getElementById('avg-response-time').textContent = `${avgTime.toFixed(0)}ms`;
            }
        }
    };
    
    performanceMonitor.init();
    
    // Lazy loading controls
    document.getElementById('load-images').addEventListener('click', () => {
        if (window.lazyLoad) {
            window.lazyLoad.loadAll();
            showInfo('lazy-loading-info', '✅ All images loaded immediately!');
        }
    });
    
    document.getElementById('check-lazy-stats').addEventListener('click', () => {
        if (window.lazyLoad) {
            const stats = window.lazyLoad.getStats();
            showInfo('lazy-loading-info', `
                📊 Lazy Loading Stats:<br>
                Total items: ${stats.total}<br>
                Loaded: ${stats.loaded}<br>
                Pending: ${stats.pending}<br>
                Progress: ${stats.progress.toFixed(1)}%
            `);
        }
    });
    
    // Virtual scrolling demo
    let virtualScroller = null;
    
    document.getElementById('generate-items').addEventListener('click', () => {
        const itemCount = parseInt(document.getElementById('item-count').value);
        
        // Generate sample data
        const items = Array.from({ length: itemCount }, (_, index) => ({
            id: index + 1,
            title: `Ticket #${index + 1}`,
            event: `Event ${Math.floor(Math.random() * 100) + 1}`,
            price: `$${(Math.random() * 200 + 50).toFixed(2)}`,
            availability: Math.random() > 0.3 ? 'Available' : 'Limited'
        }));
        
        // Destroy existing scroller
        if (virtualScroller) {
            virtualScroller.destroy();
        }
        
        // Create new virtual scroller
        virtualScroller = new VirtualScroller('#virtual-scroll-container', {
            items: items,
            itemHeight: 80,
            renderItem: (item, index) => `
                <div class="virtual-item p-md border-b border-gray-200 flex items-center justify-between">
                    <div>
                        <div class="font-semibold">${item.title}</div>
                        <div class="text-sm text-gray-600">${item.event}</div>
                    </div>
                    <div class="text-right">
                        <div class="font-semibold text-green-600">${item.price}</div>
                        <div class="text-xs text-gray-500">${item.availability}</div>
                    </div>
                </div>
            `,
            onUpdate: (info) => {
                showInfo('virtual-scroll-info', `
                    📊 Virtual Scroll Stats:<br>
                    Visible: ${info.visibleItems.length} of ${info.totalItems}<br>
                    Range: ${info.startIndex} - ${info.endIndex}<br>
                    DOM Elements: ${info.visibleItems.length} (vs ${info.totalItems} without virtualization)
                `);
            },
            debug: true
        });
    });
    
    document.getElementById('scroll-to-middle').addEventListener('click', () => {
        if (virtualScroller) {
            const stats = virtualScroller.getStats();
            virtualScroller.scrollToIndex(Math.floor(stats.totalItems / 2), 'center');
        }
    });
    
    document.getElementById('get-virtual-stats').addEventListener('click', () => {
        if (virtualScroller) {
            const stats = virtualScroller.getStats();
            showInfo('virtual-scroll-info', `
                📊 Detailed Virtual Scroll Stats:<br>
                Total items: ${stats.totalItems}<br>
                Visible items: ${stats.visibleItems}<br>
                Scroll position: ${stats.scrollTop}px<br>
                Container height: ${stats.containerHeight}px<br>
                Total height: ${stats.totalHeight}px<br>
                Average item height: ${stats.averageItemHeight}px
            `);
        } else {
            showInfo('virtual-scroll-info', '❌ No virtual scroller active. Generate items first.');
        }
    });
    
    // Search demo with debouncing
    const searchInputHandler = new SearchInput('#search-input', {
        debounceMs: 300,
        minLength: 2,
        maxResults: 10,
        onSearch: async (query, signal) => {
            const startTime = performance.now();
            
            // Simulate API call
            await new Promise(resolve => setTimeout(resolve, Math.random() * 200 + 100));
            
            if (signal?.aborted) return [];
            
            const endTime = performance.now();
            performanceMonitor.recordSearchMetrics(false, endTime - startTime);
            
            // Mock search results
            const results = [
                `Lakers vs Warriors - ${query}`,
                `${query} Championship Final`,
                `Best ${query} Tickets Available`,
                `${query} - Premium Seats`,
                `${query} Season Pass`
            ].map((title, index) => ({ id: index, title }));
            
            return results;
        },
        onSelect: (result) => {
            console.log('Selected:', result);
        },
        resultTemplate: (result) => `
            <div class="search-result-item p-md hover:bg-gray-50 cursor-pointer">
                <div class="font-medium">${result.title}</div>
                <div class="text-xs text-gray-500">Sports Event Ticket</div>
            </div>
        `
    });
    
    document.getElementById('clear-search-cache').addEventListener('click', () => {
        if (searchInputHandler?.searchHandler) {
            searchInputHandler.searchHandler.clearCache();
            showInfo('virtual-scroll-info', '🗑️ Search cache cleared!');
        }
    });
    
    // Performance tests
    document.getElementById('dom-test').addEventListener('click', async () => {
        const button = document.getElementById('dom-test');
        const results = document.getElementById('dom-test-results');
        
        button.disabled = true;
        button.textContent = 'Running...';
        
        const startTime = performance.now();
        
        // Create and manipulate DOM elements
        const testContainer = document.createElement('div');
        for (let i = 0; i < 1000; i++) {
            const element = document.createElement('div');
            element.textContent = `Test element ${i}`;
            element.className = 'test-element';
            testContainer.appendChild(element);
        }
        
        document.body.appendChild(testContainer);
        
        // Force layout
        testContainer.offsetHeight;
        
        // Remove elements
        testContainer.remove();
        
        const endTime = performance.now();
        const duration = endTime - startTime;
        
        results.innerHTML = `
            ✅ DOM Test Completed<br>
            Duration: ${duration.toFixed(2)}ms<br>
            Operations: 1,000 element create/append/remove<br>
            Performance: ${duration < 50 ? 'Excellent' : duration < 100 ? 'Good' : 'Needs improvement'}
        `;
        
        button.disabled = false;
        button.textContent = '🔧 Run DOM Performance Test';
    });
    
    document.getElementById('scroll-test').addEventListener('click', () => {
        const button = document.getElementById('scroll-test');
        const results = document.getElementById('scroll-test-results');
        
        button.disabled = true;
        button.textContent = 'Running...';
        
        let frameCount = 0;
        const startTime = performance.now();
        
        const scrollHandler = () => {
            frameCount++;
            if (frameCount < 60) {
                requestAnimationFrame(scrollHandler);
            } else {
                const endTime = performance.now();
                const avgFrameTime = (endTime - startTime) / frameCount;
                const fps = 1000 / avgFrameTime;
                
                results.innerHTML = `
                    ✅ Scroll Test Completed<br>
                    Frames: ${frameCount}<br>
                    Avg Frame Time: ${avgFrameTime.toFixed(2)}ms<br>
                    Estimated FPS: ${fps.toFixed(1)}<br>
                    Performance: ${fps > 55 ? 'Excellent' : fps > 45 ? 'Good' : 'Needs improvement'}
                `;
                
                button.disabled = false;
                button.textContent = '📜 Run Scroll Performance Test';
            }
        };
        
        requestAnimationFrame(scrollHandler);
    });
    
    function showInfo(elementId, message) {
        const element = document.getElementById(elementId);
        element.innerHTML = message;
    }
});
</script>
@endsection
