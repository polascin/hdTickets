{{-- Resource Hints Component for HD Tickets --}}
{{-- Implements preconnect, dns-prefetch, preload, and prefetch hints for optimal loading performance --}}

@props([
    'preconnects' => [],
    'dnsPrefetch' => [],
    'preloads' => [],
    'prefetches' => [],
    'modulePreloads' => [],
    'environment' => app()->environment(),
])

{{-- DNS Prefetch for External Domains --}}
@foreach(array_merge([
    'https://fonts.bunny.net',
    'https://cdn.jsdelivr.net',
    'https://unpkg.com',
], $dnsPrefetch) as $domain)
    <link rel="dns-prefetch" href="{{ $domain }}">
@endforeach

{{-- Preconnect for Critical External Resources --}}
@foreach(array_merge([
    'https://fonts.bunny.net',
    'https://cdn.jsdelivr.net',
], $preconnects) as $origin)
    <link rel="preconnect" href="{{ $origin }}" crossorigin>
@endforeach

{{-- Preload Critical Assets --}}
@foreach(array_merge([
    // Critical fonts
    [
        'href' => 'https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap',
        'as' => 'style',
        'type' => 'text/css',
    ],
    // Critical CSS
    [
        'href' => asset('css/theme-system.css'),
        'as' => 'style',
        'type' => 'text/css',
    ],
    [
        'href' => asset('css/loading-states.css'),
        'as' => 'style',
        'type' => 'text/css',
    ],
    // Critical JavaScript
    [
        'href' => asset('js/loadingManager.js'),
        'as' => 'script',
        'type' => 'text/javascript',
    ],
], $preloads) as $preload)
    <link 
        rel="preload" 
        href="{{ $preload['href'] }}" 
        as="{{ $preload['as'] }}"
        @if(isset($preload['type']))
            type="{{ $preload['type'] }}"
        @endif
        @if(isset($preload['crossorigin']))
            crossorigin="{{ $preload['crossorigin'] }}"
        @endif
        @if($preload['as'] === 'style')
            onload="this.onload=null;this.rel='stylesheet'"
        @endif
    >
@endforeach

{{-- Module Preloads for ES Modules --}}
@if(file_exists(public_path('build/manifest.json')))
    @php
        $manifest = json_decode(file_get_contents(public_path('build/manifest.json')), true);
        $defaultModulePreloads = [];
        
        // Preload critical application modules
        if (isset($manifest['resources/js/app.js'])) {
            $defaultModulePreloads[] = $manifest['resources/js/app.js']['file'];
        }
        if (isset($manifest['resources/css/app.css'])) {
            $defaultModulePreloads[] = $manifest['resources/css/app.css']['file'];
        }
    @endphp
    
    @foreach(array_merge($defaultModulePreloads, $modulePreloads) as $moduleFile)
        <link rel="modulepreload" href="{{ asset('build/' . $moduleFile) }}">
    @endforeach
@endif

{{-- Prefetch for Future Navigation --}}
@foreach(array_merge([
    // Dashboard assets for likely navigation
    asset('css/dashboard.css'),
    asset('js/dashboard.js'),
    // Common images
    asset('assets/images/hdTicketsLogo.png'),
    asset('assets/images/sports-banner.jpg'),
], $prefetches) as $prefetch)
    <link rel="prefetch" href="{{ $prefetch }}">
@endforeach

{{-- Early Hints for Supported Browsers --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Modern browsers: Implement resource hints programmatically
    if ('connection' in navigator) {
        const connection = navigator.connection;
        const effectiveType = connection.effectiveType;
        
        // Adjust prefetching based on connection quality
        if (effectiveType === '4g' || effectiveType === 'wifi') {
            // High-quality connection: Prefetch more resources
            const highPriorityPrefetches = [
                '{{ asset("css/components.css") }}',
                '{{ asset("js/alpine.js") }}',
                '{{ asset("js/chart.js") }}',
            ];
            
            highPriorityPrefetches.forEach(url => {
                const link = document.createElement('link');
                link.rel = 'prefetch';
                link.href = url;
                document.head.appendChild(link);
            });
        } else if (effectiveType === 'slow-2g' || effectiveType === '2g') {
            // Slow connection: Minimal prefetching
            console.log('Slow connection detected, reducing prefetch operations');
        }
    }
    
    // Intersection Observer for lazy prefetching
    if ('IntersectionObserver' in window) {
        const prefetchObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const element = entry.target;
                    const prefetchUrl = element.dataset.prefetch;
                    
                    if (prefetchUrl) {
                        const link = document.createElement('link');
                        link.rel = 'prefetch';
                        link.href = prefetchUrl;
                        document.head.appendChild(link);
                        
                        prefetchObserver.unobserve(element);
                    }
                }
            });
        }, { rootMargin: '100px' });
        
        // Observe elements with data-prefetch attribute
        document.querySelectorAll('[data-prefetch]').forEach(el => {
            prefetchObserver.observe(el);
        });
    }
});
</script>

{{-- Service Worker Registration with Caching Strategy --}}
<script>
if ('serviceWorker' in navigator) {
    window.addEventListener('load', async () => {
        try {
            const registration = await navigator.serviceWorker.register('/sw.js');
            console.log('SW registered with scope:', registration.scope);
            
            // Update service worker periodically
            registration.update();
            
            // Handle service worker updates
            registration.addEventListener('updatefound', () => {
                const newWorker = registration.installing;
                
                newWorker.addEventListener('statechange', () => {
                    if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                        // Show update notification
                        showUpdateNotification();
                    }
                });
            });
            
            // Listen for messages from service worker
            navigator.serviceWorker.addEventListener('message', event => {
                const { type, payload } = event.data;
                
                switch (type) {
                    case 'CACHE_UPDATED':
                        console.log('Cache updated for:', payload.url);
                        break;
                    case 'OFFLINE_FALLBACK':
                        showOfflineNotification();
                        break;
                }
            });
            
        } catch (error) {
            console.log('SW registration failed:', error);
        }
    });
}

function showUpdateNotification() {
    if (window.loadingManager) {
        // Use the loading manager if available
        const notification = document.createElement('div');
        notification.className = 'fixed top-4 right-4 bg-blue-600 text-white p-4 rounded-lg shadow-lg z-50';
        notification.innerHTML = `
            <div class="flex items-center justify-between">
                <span class="mr-4">New version available!</span>
                <button onclick="reloadForUpdate()" class="bg-white text-blue-600 px-3 py-1 rounded text-sm font-medium">
                    Update
                </button>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-2 text-blue-200 hover:text-white">
                    ×
                </button>
            </div>
        `;
        document.body.appendChild(notification);
        
        // Auto-remove after 10 seconds
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 10000);
    }
}

function showOfflineNotification() {
    const notification = document.createElement('div');
    notification.className = 'fixed bottom-4 left-4 bg-yellow-600 text-white p-3 rounded-lg shadow-lg z-50';
    notification.innerHTML = `
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
            </svg>
            <span>You're offline. Some features may be limited.</span>
        </div>
    `;
    document.body.appendChild(notification);
    
    // Remove when online
    window.addEventListener('online', () => {
        if (notification.parentElement) {
            notification.remove();
        }
    });
}

function reloadForUpdate() {
    if ('serviceWorker' in navigator && navigator.serviceWorker.controller) {
        navigator.serviceWorker.controller.postMessage({ type: 'SKIP_WAITING' });
        window.location.reload();
    }
}
</script>

{{-- Critical Resource Hints for specific pages --}}
@stack('page-resource-hints')

{{-- Development mode: Resource hint debugging --}}
@if($environment === 'local')
    <script>
    console.group('Resource Hints Debug');
    console.log('DNS Prefetch domains:', @json(array_merge(['https://fonts.bunny.net', 'https://cdn.jsdelivr.net'], $dnsPrefetch)));
    console.log('Preconnect origins:', @json(array_merge(['https://fonts.bunny.net', 'https://cdn.jsdelivr.net'], $preconnects)));
    console.log('Preloaded resources:', document.querySelectorAll('link[rel="preload"]').length);
    console.log('Prefetched resources:', document.querySelectorAll('link[rel="prefetch"]').length);
    console.log('Module preloads:', document.querySelectorAll('link[rel="modulepreload"]').length);
    console.groupEnd();
    </script>
@endif
