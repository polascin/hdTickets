@props([
    'type' => 'line', // line, bar, pie, doughnut, radar, polarArea
    'data' => [],
    'options' => [],
    'width' => null,
    'height' => null,
    'aspectRatio' => '2/1',
    'loading' => false,
    'loadingText' => 'Loading chart...',
    'errorText' => 'Failed to load chart',
    'retryText' => 'Retry',
    'theme' => 'auto', // auto, light, dark
    'responsive' => true,
    'maintainAspectRatio' => true,
    'id' => null,
    'ariaLabel' => null,
    'caption' => null
])

@php
    $chartId = $id ?? 'chart-' . uniqid();
    $canvasId = $chartId . '-canvas';
    $captionId = $chartId . '-caption';
    
    $containerClasses = [
        'hdt-chart',
        'relative',
        'w-full',
        $responsive ? 'hdt-chart--responsive' : ''
    ];
    
    $containerStyles = [];
    if ($width) $containerStyles[] = "width: {$width}";
    if ($height) $containerStyles[] = "height: {$height}";
    if (!$maintainAspectRatio && $aspectRatio) $containerStyles[] = "aspect-ratio: {$aspectRatio}";
@endphp

@if(count($containerStyles) > 0)
<div 
    id="{{ $chartId }}"
    @if(count(array_filter($containerClasses)) > 0)
        class="{{ implode(' ', array_filter($containerClasses)) }}"
    @endif
    style="{{ implode('; ', $containerStyles) }}"
    {{ $attributes->except(['class', 'id', 'style']) }}
    x-data="{
@else
<div 
    id="{{ $chartId }}"
    class="{{ implode(' ', array_filter($containerClasses)) }}"
    {{ $attributes->except(['class', 'id', 'style']) }}
    x-data="{
@endif
        chart: null,
        loading: {{ $loading ? 'true' : 'false' }},
        error: false,
        retryCount: 0,
        maxRetries: 3,
        chartData: {{ json_encode($data) }},
        chartOptions: {{ json_encode($options) }},
        chartType: '{{ $type }}',
        theme: '{{ $theme }}',
        
        async init() {
            await this.initChart();
        },
        
        async initChart() {
            this.loading = true;
            this.error = false;
            
            try {
                // Check if element is in viewport before loading
                if (!this.isInViewport()) {
                    this.setupIntersectionObserver();
                    return;
                }
                
                await this.loadChartJS();
                await this.createChart();
                
            } catch (error) {
                console.error('Chart initialization failed:', error);
                this.handleError();
            }
        },
        
        isInViewport() {
            const rect = this.$el.getBoundingClientRect();
            return (
                rect.top < window.innerHeight &&
                rect.bottom > 0 &&
                rect.left < window.innerWidth &&
                rect.right > 0
            );
        },
        
        setupIntersectionObserver() {
            const observer = new IntersectionObserver(
                (entries) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting) {
                            this.initChart();
                            observer.disconnect();
                        }
                    });
                },
                { 
                    rootMargin: '50px',
                    threshold: 0.1 
                }
            );
            
            observer.observe(this.$el);
        },
        
        async loadChartJS() {
            if (window.Chart) return;
            
            // Dynamic import of Chart.js
            const { Chart, registerables } = await import('https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.min.js');
            Chart.register(...registerables);
            window.Chart = Chart;
        },
        
        async createChart() {
            if (this.chart) {
                this.chart.destroy();
            }
            
            const canvas = this.$refs.canvas;
            if (!canvas) {
                throw new Error('Canvas element not found');
            }
            
            const ctx = canvas.getContext('2d');
            const config = this.buildChartConfig();
            
            this.chart = new Chart(ctx, config);
            this.loading = false;
            
            // Announce chart creation to screen readers
            this.announceChartReady();
        },
        
        buildChartConfig() {
            const defaultOptions = {
                responsive: {{ $responsive ? 'true' : 'false' }},
                maintainAspectRatio: {{ $maintainAspectRatio ? 'true' : 'false' }},
                plugins: {
                    legend: {
                        labels: {
                            color: this.getThemeColor('text')
                        }
                    },
                    tooltip: {
                        backgroundColor: this.getThemeColor('tooltip-bg'),
                        titleColor: this.getThemeColor('tooltip-text'),
                        bodyColor: this.getThemeColor('tooltip-text'),
                        borderColor: this.getThemeColor('border'),
                        borderWidth: 1
                    }
                },
                scales: this.buildScales()
            };
            
            const mergedOptions = this.deepMerge(defaultOptions, this.chartOptions);
            
            return {
                type: this.chartType,
                data: this.processChartData(),
                options: mergedOptions
            };
        },
        
        processChartData() {
            const data = { ...this.chartData };
            
            // Apply theme colors if not explicitly set
            if (data.datasets) {
                data.datasets = data.datasets.map((dataset, index) => {
                    if (!dataset.backgroundColor) {
                        dataset.backgroundColor = this.getThemeColors('background')[index % this.getThemeColors('background').length];
                    }
                    if (!dataset.borderColor) {
                        dataset.borderColor = this.getThemeColors('border')[index % this.getThemeColors('border').length];
                    }
                    return dataset;
                });
            }
            
            return data;
        },
        
        buildScales() {
            if (['pie', 'doughnut', 'polarArea'].includes(this.chartType)) {
                return {};
            }
            
            return {
                x: {
                    ticks: {
                        color: this.getThemeColor('text-secondary')
                    },
                    grid: {
                        color: this.getThemeColor('grid')
                    }
                },
                y: {
                    ticks: {
                        color: this.getThemeColor('text-secondary')
                    },
                    grid: {
                        color: this.getThemeColor('grid')
                    }
                }
            };
        },
        
        getThemeColor(type) {
            const isDark = this.theme === 'dark' || 
                          (this.theme === 'auto' && 
                           (document.documentElement.classList.contains('dark') || 
                            document.documentElement.classList.contains('hdt-theme-dark')));
            
            const colors = {
                light: {
                    'text': '#1f2937',
                    'text-secondary': '#6b7280',
                    'border': '#e5e7eb',
                    'grid': '#f3f4f6',
                    'tooltip-bg': '#1f2937',
                    'tooltip-text': '#ffffff'
                },
                dark: {
                    'text': '#f9fafb',
                    'text-secondary': '#d1d5db',
                    'border': '#374151',
                    'grid': '#374151',
                    'tooltip-bg': '#374151',
                    'tooltip-text': '#f9fafb'
                }
            };
            
            return colors[isDark ? 'dark' : 'light'][type];
        },
        
        getThemeColors(type) {
            const isDark = this.theme === 'dark' || 
                          (this.theme === 'auto' && 
                           (document.documentElement.classList.contains('dark') || 
                            document.documentElement.classList.contains('hdt-theme-dark')));
            
            const colorSets = {
                light: {
                    'background': [
                        'rgba(59, 130, 246, 0.1)',
                        'rgba(16, 185, 129, 0.1)', 
                        'rgba(245, 158, 11, 0.1)',
                        'rgba(239, 68, 68, 0.1)',
                        'rgba(139, 92, 246, 0.1)'
                    ],
                    'border': [
                        'rgba(59, 130, 246, 1)',
                        'rgba(16, 185, 129, 1)',
                        'rgba(245, 158, 11, 1)',
                        'rgba(239, 68, 68, 1)',
                        'rgba(139, 92, 246, 1)'
                    ]
                },
                dark: {
                    'background': [
                        'rgba(96, 165, 250, 0.2)',
                        'rgba(52, 211, 153, 0.2)',
                        'rgba(251, 191, 36, 0.2)',
                        'rgba(248, 113, 113, 0.2)',
                        'rgba(167, 139, 250, 0.2)'
                    ],
                    'border': [
                        'rgba(96, 165, 250, 1)',
                        'rgba(52, 211, 153, 1)',
                        'rgba(251, 191, 36, 1)',
                        'rgba(248, 113, 113, 1)',
                        'rgba(167, 139, 250, 1)'
                    ]
                }
            };
            
            return colorSets[isDark ? 'dark' : 'light'][type];
        },
        
        deepMerge(target, source) {
            const output = { ...target };
            
            for (const key in source) {
                if (source[key] && typeof source[key] === 'object' && !Array.isArray(source[key])) {
                    output[key] = this.deepMerge(output[key] || {}, source[key]);
                } else {
                    output[key] = source[key];
                }
            }
            
            return output;
        },
        
        handleError() {
            this.loading = false;
            this.error = true;
            
            // Announce error to screen readers
            this.announceError();
        },
        
        async retry() {
            if (this.retryCount >= this.maxRetries) return;
            
            this.retryCount++;
            await this.initChart();
        },
        
        announceChartReady() {
            this.announce('Chart loaded successfully');
        },
        
        announceError() {
            this.announce('Chart failed to load');
        },
        
        announce(message) {
            // Create temporary element for screen reader announcement
            const announcer = document.createElement('div');
            announcer.setAttribute('aria-live', 'polite');
            announcer.setAttribute('aria-atomic', 'true');
            announcer.className = 'sr-only';
            announcer.textContent = message;
            
            document.body.appendChild(announcer);
            setTimeout(() => document.body.removeChild(announcer), 1000);
        },
        
        updateChart(newData, newOptions = null) {
            if (!this.chart) return;
            
            this.chartData = newData;
            if (newOptions) this.chartOptions = newOptions;
            
            this.chart.data = this.processChartData();
            if (newOptions) {
                this.chart.options = this.deepMerge(this.chart.options, newOptions);
            }
            
            this.chart.update('none'); // No animation for better performance
        },
        
        destroy() {
            if (this.chart) {
                this.chart.destroy();
                this.chart = null;
            }
        }
    }"
    x-intersect="initChart()"
    @theme-changed.window="
        if (chart) {
            const config = buildChartConfig();
            chart.options = config.options;
            chart.data = processChartData();
            chart.update();
        }
    ">

    {{-- Chart Caption --}}
    @if($caption)
        <div id="{{ $captionId }}" 
             class="hdt-chart__caption text-sm font-medium text-text-primary mb-4">
            {{ $caption }}
        </div>
    @endif

    {{-- Loading State --}}
    <div x-show="loading" 
         class="hdt-chart__loading"
         role="status"
         aria-label="{{ $loadingText }}">
        <div class="hdt-chart__skeleton">
            <div class="hdt-chart__skeleton-bars">
                @for($i = 0; $i < 5; $i++)
                    <div class="hdt-chart__skeleton-bar" 
                         style="height: {{ rand(30, 100) }}%"></div>
                @endfor
            </div>
            <div class="hdt-chart__skeleton-axis"></div>
        </div>
        <p class="hdt-chart__loading-text mt-2 text-center text-sm text-text-secondary">
            {{ $loadingText }}
        </p>
    </div>

    {{-- Error State --}}
    <div x-show="error" 
         class="hdt-chart__error text-center py-8"
         role="alert">
        <svg class="w-12 h-12 text-text-quaternary mx-auto mb-4" 
             fill="none" 
             stroke="currentColor" 
             viewBox="0 0 24 24">
            <path stroke-linecap="round" 
                  stroke-linejoin="round" 
                  stroke-width="2" 
                  d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
        </svg>
        <p class="text-text-secondary mb-4">{{ $errorText }}</p>
        <button @click="retry()" 
                x-show="retryCount < maxRetries"
                class="hdt-btn hdt-btn--secondary hdt-btn--sm"
                type="button">
            {{ $retryText }}
        </button>
    </div>

    {{-- Chart Canvas --}}
    <div x-show="!loading && !error" class="hdt-chart__container">
        <canvas 
            x-ref="canvas"
            id="{{ $canvasId }}"
            class="hdt-chart__canvas"
            @if($ariaLabel) aria-label="{{ $ariaLabel }}" @endif
            @if($caption) aria-describedby="{{ $captionId }}" @endif
            role="img">
            {{-- Fallback content for non-JS users --}}
            <p>Your browser does not support charts. The data would be displayed here as a visual chart.</p>
        </canvas>
    </div>

</div>

@pushOnce('styles')
<style>
/* Chart Component Styles */
.hdt-chart {
    font-family: var(--hdt-font-family-sans);
    background: var(--hdt-color-surface-primary);
    border-radius: var(--hdt-border-radius-md);
    padding: var(--hdt-spacing-4);
}

.hdt-chart--responsive {
    width: 100%;
    height: auto;
}

/* Chart Container */
.hdt-chart__container {
    position: relative;
    width: 100%;
    height: 100%;
}

.hdt-chart__canvas {
    display: block;
    width: 100%;
    height: 100%;
}

/* Loading State */
.hdt-chart__loading {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 200px;
}

.hdt-chart__skeleton {
    width: 100%;
    max-width: 400px;
    height: 150px;
    position: relative;
}

.hdt-chart__skeleton-bars {
    display: flex;
    align-items: flex-end;
    justify-content: space-around;
    height: 120px;
    gap: 8px;
    margin-bottom: 10px;
}

.hdt-chart__skeleton-bar {
    flex: 1;
    max-width: 40px;
    background: var(--hdt-color-surface-tertiary);
    border-radius: var(--hdt-border-radius-sm);
    animation: skeleton-pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

.hdt-chart__skeleton-axis {
    height: 2px;
    background: var(--hdt-color-border-primary);
    border-radius: 1px;
}

@keyframes skeleton-pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
}

.hdt-reduced-motion .hdt-chart__skeleton-bar {
    animation: none;
}

.hdt-chart__loading-text {
    color: var(--hdt-color-text-secondary);
}

/* Error State */
.hdt-chart__error {
    min-height: 200px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: var(--hdt-color-text-secondary);
}

/* Chart Caption */
.hdt-chart__caption {
    text-align: center;
    color: var(--hdt-color-text-primary);
}

/* Dark Mode Adjustments */
.hdt-theme-dark .hdt-chart {
    background: var(--hdt-color-surface-secondary);
}

.hdt-theme-dark .hdt-chart__skeleton-bar {
    background: var(--hdt-color-surface-quaternary);
}

/* High Contrast Mode */
@media (prefers-contrast: high) {
    .hdt-chart {
        border: 2px solid var(--hdt-color-border-primary);
    }
    
    .hdt-chart__skeleton-bar {
        border: 1px solid var(--hdt-color-border-primary);
    }
}

/* Print Styles */
@media print {
    .hdt-chart {
        background: white !important;
        border: 1px solid black;
    }
    
    .hdt-chart__loading,
    .hdt-chart__error {
        display: none;
    }
    
    .hdt-chart__canvas {
        background: white !important;
    }
}

/* Responsive Breakpoints */
@media (max-width: 640px) {
    .hdt-chart {
        padding: var(--hdt-spacing-3);
    }
    
    .hdt-chart__skeleton-bars {
        height: 100px;
        gap: 4px;
    }
}

/* Screen Reader Only */
.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}

/* Focus Management */
.hdt-chart__canvas:focus {
    outline: 2px solid var(--hdt-color-focus-ring);
    outline-offset: 2px;
}

/* Chart Variants */
.hdt-chart--compact {
    padding: var(--hdt-spacing-2);
    min-height: 150px;
}

.hdt-chart--comfortable {
    padding: var(--hdt-spacing-6);
    min-height: 250px;
}

/* Animation Support */
.hdt-chart__container {
    transition: opacity 300ms ease-in-out;
}

.hdt-reduced-motion .hdt-chart__container {
    transition: none;
}
</style>
@endPushOnce

@pushOnce('scripts')
<script>
// Global chart utilities
window.HDTCharts = {
    // Common chart configurations
    defaults: {
        responsive: true,
        maintainAspectRatio: true,
        interaction: {
            intersect: false,
            mode: 'index'
        },
        animation: {
            duration: 300
        }
    },
    
    // Accessibility helpers
    addA11ySupport(chart) {
        const canvas = chart.canvas;
        canvas.setAttribute('role', 'img');
        
        // Add keyboard navigation
        canvas.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                // Trigger tooltip on first data point
                chart.tooltip.setActiveElements([{
                    datasetIndex: 0,
                    index: 0
                }]);
                chart.update('none');
            }
        });
    },
    
    // Theme integration
    getThemeConfig(isDark = false) {
        return {
            plugins: {
                legend: {
                    labels: {
                        color: isDark ? '#f9fafb' : '#1f2937'
                    }
                }
            },
            scales: {
                x: {
                    ticks: { color: isDark ? '#d1d5db' : '#6b7280' },
                    grid: { color: isDark ? '#374151' : '#f3f4f6' }
                },
                y: {
                    ticks: { color: isDark ? '#d1d5db' : '#6b7280' },
                    grid: { color: isDark ? '#374151' : '#f3f4f6' }
                }
            }
        };
    }
};

// Respect reduced motion preference
if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    window.HDTCharts.defaults.animation.duration = 0;
}
</script>
@endPushOnce