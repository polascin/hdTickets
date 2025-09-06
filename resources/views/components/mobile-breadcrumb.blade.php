{{-- Mobile Breadcrumb Navigation Component for HD Tickets --}}
{{-- Provides contextual navigation for better mobile UX --}}

@props([
    'items' => [],
    'showHome' => true,
    'homeUrl' => '/',
    'homeLabel' => 'Home',
    'separator' => '›',
    'maxItems' => 4,
    'class' => '',
    'id' => null
])

@php
    // Ensure items is an array
    if (!is_array($items)) {
        $items = [];
    }
    
    // Add home breadcrumb if requested
    if ($showHome && !empty($items)) {
        array_unshift($items, [
            'title' => $homeLabel,
            'url' => $homeUrl,
            'active' => false
        ]);
    }
    
    // Limit items if necessary
    if (count($items) > $maxItems) {
        $items = [
            $items[0], // Always keep home
            ['title' => '...', 'url' => '#', 'active' => false, 'ellipsis' => true],
            ...array_slice($items, -($maxItems - 2)) // Keep last items
        ];
    }
    
    $breadcrumbId = $id ?? 'mobile-breadcrumb-' . uniqid();
@endphp

@if(!empty($items))
    <nav 
        class="mobile-breadcrumb {{ $class }}" 
        id="{{ $breadcrumbId }}"
        aria-label="Breadcrumb navigation"
        role="navigation"
    >
        <div class="mobile-breadcrumb-container">
            @foreach($items as $index => $item)
                @php
                    $isLast = $index === count($items) - 1;
                    $isEllipsis = isset($item['ellipsis']) && $item['ellipsis'];
                @endphp
                
                @if($isEllipsis)
                    {{-- Ellipsis item --}}
                    <span 
                        class="mobile-breadcrumb-item mobile-breadcrumb-item--ellipsis"
                        aria-hidden="true"
                        title="More navigation levels"
                    >
                        {{ $item['title'] }}
                    </span>
                @else
                    {{-- Regular breadcrumb item --}}
                    @if($isLast)
                        <span 
                            class="mobile-breadcrumb-item mobile-breadcrumb-item--current"
                            aria-current="page"
                            title="Current page: {{ $item['title'] }}"
                        >
                            {{ $item['title'] }}
                        </span>
                    @else
                        <a 
                            href="{{ $item['url'] }}" 
                            class="mobile-breadcrumb-item mobile-breadcrumb-item--link"
                            title="Navigate to {{ $item['title'] }}"
                            @if($index === 0) aria-label="Navigate to {{ $item['title'] }} (Home)" @endif
                        >
                            @if($index === 0 && $showHome)
                                {{-- Home icon --}}
                                <svg 
                                    class="mobile-breadcrumb-icon" 
                                    fill="none" 
                                    stroke="currentColor" 
                                    viewBox="0 0 24 24" 
                                    aria-hidden="true"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                </svg>
                                <span class="sr-only">{{ $item['title'] }}</span>
                            @else
                                {{ $item['title'] }}
                            @endif
                        </a>
                    @endif
                @endif
                
                {{-- Separator --}}
                @if(!$isLast)
                    <span 
                        class="mobile-breadcrumb-separator" 
                        aria-hidden="true"
                        role="presentation"
                    >
                        {{ $separator }}
                    </span>
                @endif
            @endforeach
        </div>
        
        {{-- Back button for mobile --}}
        @if(count($items) > 1)
            <button 
                type="button" 
                class="mobile-breadcrumb-back"
                onclick="history.back()"
                title="Go back to previous page"
                aria-label="Go back to previous page"
            >
                <svg 
                    class="mobile-breadcrumb-back-icon" 
                    fill="none" 
                    stroke="currentColor" 
                    viewBox="0 0 24 24" 
                    aria-hidden="true"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                <span class="mobile-breadcrumb-back-text">Back</span>
            </button>
        @endif
    </nav>

    {{-- Structured Data for SEO --}}
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "BreadcrumbList",
        "itemListElement": [
            @foreach($items as $index => $item)
                @if(!isset($item['ellipsis']) || !$item['ellipsis'])
                    {
                        "@type": "ListItem",
                        "position": {{ $index + 1 }},
                        "name": "{{ $item['title'] }}",
                        "item": "{{ url($item['url']) }}"
                    }@if($index < count($items) - 1 && (!isset($items[$index + 1]['ellipsis']) || !$items[$index + 1]['ellipsis'])),@endif
                @endif
            @endforeach
        ]
    }
    </script>
@endif

{{-- JavaScript integration for dynamic breadcrumbs --}}
@pushOnce('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const breadcrumb = document.getElementById('{{ $breadcrumbId }}');
    if (!breadcrumb) return;
    
    // Mobile breadcrumb functionality
    class MobileBreadcrumb {
        constructor(element) {
            this.element = element;
            this.init();
        }
        
        init() {
            this.setupSwipeGestures();
            this.setupKeyboardNavigation();
            this.optimizeForSmallScreens();
        }
        
        setupSwipeGestures() {
            let startX, endX;
            
            this.element.addEventListener('touchstart', (e) => {
                startX = e.touches[0].clientX;
            }, { passive: true });
            
            this.element.addEventListener('touchend', (e) => {
                endX = e.changedTouches[0].clientX;
                this.handleSwipe(startX, endX);
            }, { passive: true });
        }
        
        handleSwipe(startX, endX) {
            const threshold = 100; // Minimum swipe distance
            const diff = startX - endX;
            
            if (Math.abs(diff) > threshold) {
                if (diff > 0) {
                    // Swipe left - go forward (if applicable)
                    this.navigateForward();
                } else {
                    // Swipe right - go back
                    this.navigateBack();
                }
            }
        }
        
        navigateBack() {
            const backButton = this.element.querySelector('.mobile-breadcrumb-back');
            if (backButton) {
                backButton.click();
            } else if (window.history.length > 1) {
                window.history.back();
            }
        }
        
        navigateForward() {
            // Navigate forward if there's history
            if (window.history.length > window.history.state?.position || 0) {
                window.history.forward();
            }
        }
        
        setupKeyboardNavigation() {
            const links = this.element.querySelectorAll('.mobile-breadcrumb-item--link');
            
            links.forEach((link, index) => {
                link.addEventListener('keydown', (e) => {
                    switch(e.key) {
                        case 'ArrowLeft':
                            e.preventDefault();
                            if (index > 0) {
                                links[index - 1].focus();
                            }
                            break;
                        case 'ArrowRight':
                            e.preventDefault();
                            if (index < links.length - 1) {
                                links[index + 1].focus();
                            }
                            break;
                        case 'Home':
                            e.preventDefault();
                            links[0].focus();
                            break;
                        case 'End':
                            e.preventDefault();
                            links[links.length - 1].focus();
                            break;
                    }
                });
            });
        }
        
        optimizeForSmallScreens() {
            const container = this.element.querySelector('.mobile-breadcrumb-container');
            if (!container) return;
            
            // Check if breadcrumb overflows
            const checkOverflow = () => {
                if (container.scrollWidth > container.clientWidth) {
                    this.element.classList.add('mobile-breadcrumb--overflow');
                    this.addScrollIndicators();
                } else {
                    this.element.classList.remove('mobile-breadcrumb--overflow');
                    this.removeScrollIndicators();
                }
            };
            
            // Check on load and resize
            checkOverflow();
            window.addEventListener('resize', checkOverflow);
        }
        
        addScrollIndicators() {
            if (this.element.querySelector('.mobile-breadcrumb-scroll-indicator')) return;
            
            const indicator = document.createElement('div');
            indicator.className = 'mobile-breadcrumb-scroll-indicator';
            indicator.innerHTML = '→';
            indicator.setAttribute('aria-hidden', 'true');
            this.element.appendChild(indicator);
        }
        
        removeScrollIndicators() {
            const indicator = this.element.querySelector('.mobile-breadcrumb-scroll-indicator');
            if (indicator) {
                indicator.remove();
            }
        }
    }
    
    // Initialize mobile breadcrumb
    new MobileBreadcrumb(breadcrumb);
    
    // Track breadcrumb usage
    breadcrumb.addEventListener('click', (e) => {
        if (e.target.classList.contains('mobile-breadcrumb-item--link')) {
            if (window.gtag) {
                gtag('event', 'breadcrumb_navigation', {
                    breadcrumb_text: e.target.textContent.trim(),
                    breadcrumb_position: Array.from(breadcrumb.querySelectorAll('.mobile-breadcrumb-item--link')).indexOf(e.target) + 1,
                    page_location: window.location.pathname
                });
            }
        }
    });
});
</script>
@endPushOnce

{{-- Component-specific styles --}}
@pushOnce('styles')
<style>
/* Mobile breadcrumb specific styles */
.mobile-breadcrumb {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 16px;
    background: var(--bg-elevated);
    border-bottom: 1px solid var(--border-primary);
    font-size: 14px;
    min-height: 44px; /* Touch-friendly height */
    position: relative;
}

.mobile-breadcrumb-container {
    flex: 1;
    display: flex;
    align-items: center;
    overflow-x: auto;
    scrollbar-width: none;
    -ms-overflow-style: none;
    scroll-behavior: smooth;
}

.mobile-breadcrumb-container::-webkit-scrollbar {
    display: none;
}

.mobile-breadcrumb-item {
    white-space: nowrap;
    display: inline-flex;
    align-items: center;
    min-height: 32px; /* Touch target */
    padding: 4px 6px;
    border-radius: 4px;
    transition: all 0.2s ease;
}

.mobile-breadcrumb-item--link {
    color: var(--color-primary);
    text-decoration: none;
    font-weight: 500;
}

.mobile-breadcrumb-item--link:hover,
.mobile-breadcrumb-item--link:focus {
    background: var(--bg-tertiary);
    color: var(--color-primary-dark);
    outline: 2px solid var(--color-primary);
    outline-offset: 1px;
}

.mobile-breadcrumb-item--link:active {
    transform: scale(0.95);
}

.mobile-breadcrumb-item--current {
    color: var(--text-primary);
    font-weight: 600;
    background: var(--bg-tertiary);
}

.mobile-breadcrumb-item--ellipsis {
    color: var(--text-tertiary);
    font-weight: bold;
    cursor: default;
}

.mobile-breadcrumb-icon {
    width: 16px;
    height: 16px;
    margin-right: 4px;
}

.mobile-breadcrumb-separator {
    margin: 0 8px;
    color: var(--text-tertiary);
    font-size: 12px;
    user-select: none;
}

.mobile-breadcrumb-back {
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 8px 12px;
    background: var(--bg-primary);
    border: 1px solid var(--border-primary);
    border-radius: 6px;
    color: var(--text-secondary);
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    margin-left: 8px;
    flex-shrink: 0;
}

.mobile-breadcrumb-back:hover,
.mobile-breadcrumb-back:focus {
    background: var(--color-primary);
    color: white;
    border-color: var(--color-primary);
    outline: 2px solid var(--color-primary);
    outline-offset: 1px;
}

.mobile-breadcrumb-back:active {
    transform: scale(0.95);
}

.mobile-breadcrumb-back-icon {
    width: 16px;
    height: 16px;
}

.mobile-breadcrumb-back-text {
    display: none;
}

/* Overflow indicator */
.mobile-breadcrumb--overflow::after {
    content: '';
    position: absolute;
    right: 80px; /* Account for back button */
    top: 0;
    bottom: 0;
    width: 20px;
    background: linear-gradient(to right, transparent, var(--bg-elevated));
    pointer-events: none;
    z-index: 1;
}

.mobile-breadcrumb-scroll-indicator {
    position: absolute;
    right: 85px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-tertiary);
    font-size: 12px;
    animation: pulse 1.5s infinite;
    pointer-events: none;
    z-index: 2;
}

@keyframes pulse {
    0%, 100% { opacity: 0.5; }
    50% { opacity: 1; }
}

/* Responsive adjustments */
@media (max-width: 480px) {
    .mobile-breadcrumb {
        padding: 6px 12px;
        font-size: 13px;
    }
    
    .mobile-breadcrumb-back-text {
        display: inline;
    }
    
    .mobile-breadcrumb-item {
        min-height: 28px;
        padding: 2px 4px;
    }
    
    .mobile-breadcrumb-separator {
        margin: 0 6px;
        font-size: 11px;
    }
}

/* Dark mode adjustments */
[data-theme="dark"] .mobile-breadcrumb {
    background: var(--bg-elevated);
    border-bottom-color: var(--border-primary);
}

[data-theme="dark"] .mobile-breadcrumb-back:hover,
[data-theme="dark"] .mobile-breadcrumb-back:focus {
    background: var(--color-primary);
    color: white;
}

/* High contrast mode */
@media (prefers-contrast: high) {
    .mobile-breadcrumb-item--link {
        text-decoration: underline;
    }
    
    .mobile-breadcrumb-back {
        border-width: 2px;
    }
}

/* Reduced motion */
@media (prefers-reduced-motion: reduce) {
    .mobile-breadcrumb-item,
    .mobile-breadcrumb-back {
        transition: none;
    }
    
    .mobile-breadcrumb-scroll-indicator {
        animation: none;
    }
    
    .mobile-breadcrumb-container {
        scroll-behavior: auto;
    }
}

/* Print styles */
@media print {
    .mobile-breadcrumb {
        display: none;
    }
}
</style>
@endPushOnce
