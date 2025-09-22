@props([
    'title' => null,
    'value' => null,
    'previousValue' => null,
    'unit' => null,
    'format' => 'number', // number, currency, percentage
    'locale' => 'en',
    'currency' => 'USD',
    'precision' => 0,
    'delta' => null,
    'deltaType' => 'auto', // auto, positive, negative, neutral
    'showDelta' => true,
    'showTrend' => false,
    'trendData' => [],
    'trendColor' => null,
    'icon' => null,
    'href' => null,
    'loading' => false,
    'variant' => 'default', // default, primary, success, warning, danger, info
    'size' => 'md', // sm, md, lg
    'description' => null,
    'footnote' => null
])

@php
    $cardId = 'metric-card-' . uniqid();
    $element = $href ? 'a' : 'div';
    
    // Calculate delta if not provided
    if ($delta === null && $previousValue !== null && is_numeric($value) && is_numeric($previousValue)) {
        if ($previousValue != 0) {
            $delta = (($value - $previousValue) / $previousValue) * 100;
        } else {
            $delta = $value > 0 ? 100 : 0;
        }
    }
    
    // Determine delta type
    if ($deltaType === 'auto' && $delta !== null) {
        if ($delta > 0) {
            $deltaType = 'positive';
        } elseif ($delta < 0) {
            $deltaType = 'negative';
        } else {
            $deltaType = 'neutral';
        }
    }
    
    $sizeClasses = [
        'sm' => 'hdt-metric-card--sm',
        'md' => 'hdt-metric-card--md',
        'lg' => 'hdt-metric-card--lg'
    ];
    
    $variantClasses = [
        'default' => 'hdt-metric-card--default',
        'primary' => 'hdt-metric-card--primary',
        'success' => 'hdt-metric-card--success',
        'warning' => 'hdt-metric-card--warning',
        'danger' => 'hdt-metric-card--danger',
        'info' => 'hdt-metric-card--info'
    ];
    
    $cardClasses = [
        'hdt-metric-card',
        'bg-surface-primary border border-border-primary rounded-lg',
        'transition-all duration-150',
        $sizeClasses[$size] ?? $sizeClasses['md'],
        $variantClasses[$variant] ?? $variantClasses['default'],
        $href ? 'hdt-metric-card--interactive' : '',
        $loading ? 'hdt-metric-card--loading' : ''
    ];
@endphp

<{{ $element }}
    @if($href) href="{{ $href }}" @endif
    id="{{ $cardId }}"
    class="{{ implode(' ', array_filter($cardClasses)) }}"
    {{ $attributes->except(['class', 'id', 'href']) }}
    x-data="{
        value: {{ is_numeric($value) ? $value : 'null' }},
        previousValue: {{ is_numeric($previousValue) ? $previousValue : 'null' }},
        delta: {{ is_numeric($delta) ? $delta : 'null' }},
        loading: {{ $loading ? 'true' : 'false' }},
        trendData: {{ json_encode($trendData) }},
        animatedValue: 0,
        
        init() {
            if (this.value !== null && !this.loading) {
                this.animateValue();
            }
            this.setupTrendChart();
        },
        
        animateValue() {
            const duration = 1000;
            const steps = 60;
            const stepValue = (this.value - this.animatedValue) / steps;
            let currentStep = 0;
            
            const animate = () => {
                if (currentStep < steps) {
                    this.animatedValue += stepValue;
                    currentStep++;
                    requestAnimationFrame(animate);
                } else {
                    this.animatedValue = this.value;
                }
            };
            
            if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                animate();
            } else {
                this.animatedValue = this.value;
            }
        },
        
        setupTrendChart() {
            if (!{{ $showTrend ? 'true' : 'false' }} || !this.trendData.length) return;
            
            this.$nextTick(() => {
                this.drawSparkline();
            });
        },
        
        drawSparkline() {
            const canvas = this.$refs.sparkline;
            if (!canvas || !this.trendData.length) return;
            
            const ctx = canvas.getContext('2d');
            const rect = canvas.getBoundingClientRect();
            const width = rect.width;
            const height = rect.height;
            
            // Set actual canvas size for high DPI displays
            const dpr = window.devicePixelRatio || 1;
            canvas.width = width * dpr;
            canvas.height = height * dpr;
            ctx.scale(dpr, dpr);
            
            // Clear canvas
            ctx.clearRect(0, 0, width, height);
            
            // Prepare data
            const values = this.trendData.map(point => typeof point === 'object' ? point.value : point);
            const min = Math.min(...values);
            const max = Math.max(...values);
            const range = max - min || 1;
            
            // Calculate points
            const points = values.map((value, index) => ({
                x: (index / (values.length - 1)) * width,
                y: height - ((value - min) / range) * height
            }));
            
            // Draw line
            ctx.strokeStyle = this.getTrendColor();
            ctx.lineWidth = 2;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
            
            ctx.beginPath();
            points.forEach((point, index) => {
                if (index === 0) {
                    ctx.moveTo(point.x, point.y);
                } else {
                    ctx.lineTo(point.x, point.y);
                }
            });
            ctx.stroke();
            
            // Draw area under curve (optional)
            if (this.shouldFillArea()) {
                const gradient = ctx.createLinearGradient(0, 0, 0, height);
                gradient.addColorStop(0, this.getTrendColor() + '40'); // 40 = 25% opacity
                gradient.addColorStop(1, this.getTrendColor() + '00'); // 00 = 0% opacity
                
                ctx.fillStyle = gradient;
                ctx.beginPath();
                ctx.moveTo(points[0].x, height);
                points.forEach((point, index) => {
                    if (index === 0) {
                        ctx.lineTo(point.x, point.y);
                    } else {
                        ctx.lineTo(point.x, point.y);
                    }
                });
                ctx.lineTo(points[points.length - 1].x, height);
                ctx.closePath();
                ctx.fill();
            }
        },
        
        getTrendColor() {
            if ('{{ $trendColor }}') return '{{ $trendColor }}';
            
            // Use delta type to determine color
            const deltaType = '{{ $deltaType }}';
            switch (deltaType) {
                case 'positive': return '#16a34a'; // green-600
                case 'negative': return '#dc2626'; // red-600  
                case 'neutral': return '#6b7280'; // gray-500
                default: return '#3b82f6'; // blue-500
            }
        },
        
        shouldFillArea() {
            return {{ $variant === 'success' || $variant === 'primary' ? 'true' : 'false' }};
        },
        
        formatValue(val) {
            if (val === null || val === undefined) return '--';
            
            const format = '{{ $format }}';
            const locale = '{{ $locale }}';
            const precision = {{ $precision }};
            
            switch (format) {
                case 'currency':
                    return new Intl.NumberFormat(locale, {
                        style: 'currency',
                        currency: '{{ $currency }}',
                        minimumFractionDigits: precision,
                        maximumFractionDigits: precision
                    }).format(val);
                case 'percentage':
                    return new Intl.NumberFormat(locale, {
                        style: 'percent',
                        minimumFractionDigits: precision,
                        maximumFractionDigits: precision
                    }).format(val / 100);
                case 'number':
                default:
                    return new Intl.NumberFormat(locale, {
                        minimumFractionDigits: precision,
                        maximumFractionDigits: precision
                    }).format(val);
            }
        },
        
        updateValue(newValue, newPreviousValue = null) {
            this.value = newValue;
            if (newPreviousValue !== null) {
                this.previousValue = newPreviousValue;
            }
            
            // Recalculate delta
            if (this.previousValue !== null && this.previousValue !== 0) {
                this.delta = ((this.value - this.previousValue) / this.previousValue) * 100;
            }
            
            this.animateValue();
        }
    }">

    {{-- Loading Overlay --}}
    <div x-show="loading" 
         class="hdt-metric-card__loading"
         role="status"
         aria-label="Loading metric data">
        <div class="hdt-metric-card__skeleton">
            <div class="hdt-skeleton hdt-skeleton--title"></div>
            <div class="hdt-skeleton hdt-skeleton--value"></div>
            <div class="hdt-skeleton hdt-skeleton--delta"></div>
        </div>
    </div>

    {{-- Card Content --}}
    <div x-show="!loading" class="hdt-metric-card__content">
        
        {{-- Header --}}
        <div class="hdt-metric-card__header">
            @if($icon)
                <div class="hdt-metric-card__icon" aria-hidden="true">
                    {!! $icon !!}
                </div>
            @endif
            
            @if($title)
                <h3 class="hdt-metric-card__title">{{ $title }}</h3>
            @endif
        </div>

        {{-- Main Value --}}
        <div class="hdt-metric-card__main">
            <div class="hdt-metric-card__value-container">
                <span class="hdt-metric-card__value" 
                      x-text="formatValue(animatedValue)">
                    {{ $loading ? '--' : $value }}
                </span>
                @if($unit)
                    <span class="hdt-metric-card__unit">{{ $unit }}</span>
                @endif
            </div>

            {{-- Delta Indicator --}}
            @if($showDelta && $delta !== null)
                <div class="hdt-metric-card__delta hdt-metric-card__delta--{{ $deltaType }}"
                     title="Change from previous period">
                    <svg class="hdt-metric-card__delta-icon" 
                         fill="none" 
                         stroke="currentColor" 
                         viewBox="0 0 24 24"
                         aria-hidden="true">
                        @if($deltaType === 'positive')
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                        @elseif($deltaType === 'negative')
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                        @else
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                        @endif
                    </svg>
                    <span class="hdt-metric-card__delta-value">
                        {{ abs($delta) }}%
                    </span>
                </div>
            @endif
        </div>

        {{-- Trend Sparkline --}}
        @if($showTrend && !empty($trendData))
            <div class="hdt-metric-card__trend">
                <canvas x-ref="sparkline" 
                        class="hdt-metric-card__sparkline"
                        aria-label="Trend chart showing data over time">
                </canvas>
            </div>
        @endif

        {{-- Description --}}
        @if($description)
            <p class="hdt-metric-card__description">{{ $description }}</p>
        @endif

        {{-- Footer --}}
        @if($footnote)
            <div class="hdt-metric-card__footer">
                <small class="hdt-metric-card__footnote">{{ $footnote }}</small>
            </div>
        @endif

    </div>

</{{ $element }}>

@pushOnce('styles')
<style>
/* Metric Card Base Styles */
.hdt-metric-card {
    --hdt-metric-card-bg: var(--hdt-color-surface-primary);
    --hdt-metric-card-border: var(--hdt-color-border-primary);
    --hdt-metric-card-accent: var(--hdt-color-primary-600);
    
    background: var(--hdt-metric-card-bg);
    border-color: var(--hdt-metric-card-border);
    position: relative;
    overflow: hidden;
    font-family: var(--hdt-font-family-sans);
}

/* Card Sizes */
.hdt-metric-card--sm {
    padding: var(--hdt-spacing-4);
}

.hdt-metric-card--sm .hdt-metric-card__value {
    font-size: 1.5rem;
    line-height: 1.2;
}

.hdt-metric-card--md {
    padding: var(--hdt-spacing-6);
}

.hdt-metric-card--md .hdt-metric-card__value {
    font-size: 2rem;
    line-height: 1.2;
}

.hdt-metric-card--lg {
    padding: var(--hdt-spacing-8);
}

.hdt-metric-card--lg .hdt-metric-card__value {
    font-size: 2.5rem;
    line-height: 1.2;
}

/* Card Variants */
.hdt-metric-card--primary {
    --hdt-metric-card-accent: var(--hdt-color-primary-600);
    border-color: var(--hdt-color-primary-200);
}

.hdt-metric-card--success {
    --hdt-metric-card-accent: var(--hdt-color-success-600);
    border-color: var(--hdt-color-success-200);
}

.hdt-metric-card--warning {
    --hdt-metric-card-accent: var(--hdt-color-warning-600);
    border-color: var(--hdt-color-warning-200);
}

.hdt-metric-card--danger {
    --hdt-metric-card-accent: var(--hdt-color-danger-600);
    border-color: var(--hdt-color-danger-200);
}

.hdt-metric-card--info {
    --hdt-metric-card-accent: var(--hdt-color-info-600);
    border-color: var(--hdt-color-info-200);
}

/* Interactive States */
.hdt-metric-card--interactive {
    cursor: pointer;
    text-decoration: none;
    color: inherit;
}

.hdt-metric-card--interactive:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    border-color: var(--hdt-metric-card-accent);
}

.hdt-metric-card--interactive:focus {
    outline: 2px solid var(--hdt-color-focus-ring);
    outline-offset: 2px;
}

.hdt-metric-card--interactive:active {
    transform: translateY(-1px);
}

.hdt-reduced-motion .hdt-metric-card--interactive:hover,
.hdt-reduced-motion .hdt-metric-card--interactive:active {
    transform: none;
}

/* Loading State */
.hdt-metric-card--loading {
    pointer-events: none;
}

.hdt-metric-card__loading {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: var(--hdt-metric-card-bg);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
}

.hdt-metric-card__skeleton {
    width: 100%;
    padding: inherit;
}

.hdt-skeleton {
    background: var(--hdt-color-surface-tertiary);
    border-radius: var(--hdt-border-radius-sm);
    animation: skeleton-pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

.hdt-skeleton--title {
    height: 1rem;
    width: 60%;
    margin-bottom: 1rem;
}

.hdt-skeleton--value {
    height: 2rem;
    width: 80%;
    margin-bottom: 0.5rem;
}

.hdt-skeleton--delta {
    height: 0.875rem;
    width: 40%;
}

@keyframes skeleton-pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
}

.hdt-reduced-motion .hdt-skeleton {
    animation: none;
}

/* Card Content */
.hdt-metric-card__content {
    display: flex;
    flex-direction: column;
    gap: var(--hdt-spacing-3);
}

/* Header */
.hdt-metric-card__header {
    display: flex;
    align-items: center;
    gap: var(--hdt-spacing-2);
}

.hdt-metric-card__icon {
    flex-shrink: 0;
    width: 1.5rem;
    height: 1.5rem;
    color: var(--hdt-metric-card-accent);
}

.hdt-metric-card__icon svg {
    width: 100%;
    height: 100%;
}

.hdt-metric-card__title {
    font-size: var(--hdt-font-size-sm);
    font-weight: 600;
    color: var(--hdt-color-text-secondary);
    margin: 0;
    line-height: 1.4;
}

/* Main Value Area */
.hdt-metric-card__main {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    gap: var(--hdt-spacing-2);
}

.hdt-metric-card__value-container {
    display: flex;
    align-items: baseline;
    gap: var(--hdt-spacing-1);
}

.hdt-metric-card__value {
    font-weight: 700;
    color: var(--hdt-color-text-primary);
    font-variant-numeric: tabular-nums;
    font-feature-settings: 'tnum';
}

.hdt-metric-card__unit {
    font-size: 0.875em;
    font-weight: 500;
    color: var(--hdt-color-text-tertiary);
}

/* Delta Indicator */
.hdt-metric-card__delta {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    font-size: var(--hdt-font-size-sm);
    font-weight: 600;
    padding: 0.125rem 0.375rem;
    border-radius: var(--hdt-border-radius-sm);
}

.hdt-metric-card__delta--positive {
    color: var(--hdt-color-success-700);
    background-color: var(--hdt-color-success-100);
}

.hdt-metric-card__delta--negative {
    color: var(--hdt-color-danger-700);
    background-color: var(--hdt-color-danger-100);
}

.hdt-metric-card__delta--neutral {
    color: var(--hdt-color-text-tertiary);
    background-color: var(--hdt-color-surface-tertiary);
}

.hdt-metric-card__delta-icon {
    width: 0.875rem;
    height: 0.875rem;
    flex-shrink: 0;
}

/* Trend Sparkline */
.hdt-metric-card__trend {
    height: 3rem;
    margin-top: auto;
}

.hdt-metric-card__sparkline {
    width: 100%;
    height: 100%;
    display: block;
}

/* Description */
.hdt-metric-card__description {
    font-size: var(--hdt-font-size-sm);
    color: var(--hdt-color-text-tertiary);
    margin: 0;
    line-height: 1.4;
}

/* Footer */
.hdt-metric-card__footer {
    margin-top: auto;
    padding-top: var(--hdt-spacing-2);
    border-top: 1px solid var(--hdt-color-border-secondary);
}

.hdt-metric-card__footnote {
    font-size: var(--hdt-font-size-xs);
    color: var(--hdt-color-text-quaternary);
    line-height: 1.3;
}

/* Dark Mode Adjustments */
.hdt-theme-dark .hdt-metric-card--primary {
    border-color: var(--hdt-color-primary-800);
}

.hdt-theme-dark .hdt-metric-card--success {
    border-color: var(--hdt-color-success-800);
}

.hdt-theme-dark .hdt-metric-card--warning {
    border-color: var(--hdt-color-warning-800);
}

.hdt-theme-dark .hdt-metric-card--danger {
    border-color: var(--hdt-color-danger-800);
}

.hdt-theme-dark .hdt-metric-card--info {
    border-color: var(--hdt-color-info-800);
}

.hdt-theme-dark .hdt-metric-card__delta--positive {
    color: var(--hdt-color-success-300);
    background-color: var(--hdt-color-success-900);
}

.hdt-theme-dark .hdt-metric-card__delta--negative {
    color: var(--hdt-color-danger-300);
    background-color: var(--hdt-color-danger-900);
}

/* Role-Specific Theming */
.hdt-theme-organizer .hdt-metric-card--primary {
    --hdt-metric-card-accent: var(--hdt-color-organizer-600);
    border-color: var(--hdt-color-organizer-200);
}

.hdt-theme-attendee .hdt-metric-card--primary {
    --hdt-metric-card-accent: var(--hdt-color-attendee-600);
    border-color: var(--hdt-color-attendee-200);
}

.hdt-theme-vendor .hdt-metric-card--primary {
    --hdt-metric-card-accent: var(--hdt-color-vendor-600);
    border-color: var(--hdt-color-vendor-200);
}

/* High Contrast Mode */
@media (prefers-contrast: high) {
    .hdt-metric-card {
        border-width: 2px;
    }
    
    .hdt-metric-card--interactive:hover {
        border-width: 3px;
    }
}

/* Print Styles */
@media print {
    .hdt-metric-card {
        background: white !important;
        border: 1px solid black !important;
        box-shadow: none !important;
        transform: none !important;
    }
    
    .hdt-metric-card__loading {
        display: none;
    }
}

/* Responsive Design */
@media (max-width: 640px) {
    .hdt-metric-card--md {
        padding: var(--hdt-spacing-4);
    }
    
    .hdt-metric-card--lg {
        padding: var(--hdt-spacing-5);
    }
    
    .hdt-metric-card__main {
        flex-direction: column;
        align-items: flex-start;
        gap: var(--hdt-spacing-2);
    }
}

/* Animation Support */
.hdt-metric-card {
    transition: all 200ms ease-out;
}

.hdt-reduced-motion .hdt-metric-card {
    transition: none;
}
</style>
@endPushOnce