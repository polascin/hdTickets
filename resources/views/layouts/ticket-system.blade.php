<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="HD Tickets - Sports Event Ticket Monitoring, Scraping and Purchase System">
    <meta name="keywords" content="sports tickets, ticket monitoring, event tickets, ticket scraping, sports events">
    <meta name="author" content="HD Tickets">
    <meta name="robots" content="index, follow">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ request()->url() }}">
    <meta property="og:title" content="{{ $title ?? 'HD Tickets - Sports Event Tickets' }}">
    <meta property="og:description" content="Find and monitor sports event tickets across multiple platforms with real-time price tracking">
    <meta property="og:image" content="{{ asset('images/og-image.jpg') }}">
    <meta property="og:site_name" content="HD Tickets">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ request()->url() }}">
    <meta property="twitter:title" content="{{ $title ?? 'HD Tickets - Sports Event Tickets' }}">
    <meta property="twitter:description" content="Find and monitor sports event tickets across multiple platforms with real-time price tracking">
    <meta property="twitter:image" content="{{ asset('images/twitter-image.jpg') }}">
    
    <title>{{ $title ?? 'HD Tickets' }} - Sports Event Ticket Monitoring</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    
    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#3b82f6">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="HD Tickets">
    
    <!-- Preconnect for performance -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    
    <!-- WebSocket Configuration -->
    <script>
        window.pusherKey = '{{ config('broadcasting.connections.pusher.key') }}';
        window.pusherCluster = '{{ config('broadcasting.connections.pusher.options.cluster') }}';
        window.broadcastDriver = '{{ config('broadcasting.default') }}';
        
        // HD Tickets Configuration
        window.hdTicketsConfig = {
            enablePriceMonitoring: {{ config('broadcasting.default') !== 'null' ? 'true' : 'false' }},
            enableComparison: true,
            enableFiltering: true,
            enableAnalytics: {{ app()->environment('production') ? 'true' : 'false' }},
            debugMode: {{ app()->environment('local') ? 'true' : 'false' }},
            userId: {{ auth()->id() ?? 'null' }},
            csrfToken: '{{ csrf_token() }}',
            baseUrl: '{{ url('/') }}',
            apiUrl: '{{ url('/api') }}',
        };
    </script>
    
    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/css/tickets/tickets.css'])
    
    <!-- Additional Page Styles -->
    @stack('styles')
    
    <!-- Schema.org Structured Data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebApplication",
        "name": "HD Tickets",
        "description": "Sports Event Ticket Monitoring, Scraping and Purchase System",
        "url": "{{ url('/') }}",
        "applicationCategory": "Sports, Entertainment",
        "operatingSystem": "Web Browser",
        "offers": {
            "@type": "Offer",
            "category": "Sports Event Tickets"
        }
    }
    </script>
</head>

<body class="h-full bg-gray-50 antialiased" data-ticket-system>
    
    <!-- Page Loading Indicator -->
    <div id="page-loading" class="fixed inset-0 z-50 flex items-center justify-center bg-white bg-opacity-90 hidden">
        <div class="flex flex-col items-center space-y-4">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
            <p class="text-sm font-medium text-gray-600">Loading...</p>
        </div>
    </div>
    
    <!-- Connection Status Indicator -->
    <div id="connection-status" 
         class="fixed top-4 left-4 z-40 flex items-center space-x-2 px-3 py-2 bg-white rounded-lg shadow-lg border transition-all duration-300 opacity-0 pointer-events-none"
         data-connection-status="disconnected">
        <div data-status-icon class="w-3 h-3 bg-gray-400 rounded-full"></div>
        <span data-status-text class="text-sm font-medium text-gray-600">Connecting...</span>
    </div>
    
    <!-- Main App Container -->
    <div id="app" class="min-h-full">
        <!-- Navigation -->
        @include('components.navigation')
        
        <!-- Flash Messages -->
        @if(session('success') || session('error') || session('warning') || session('info'))
            <div class="fixed top-4 right-4 z-50 space-y-2" id="flash-messages">
                @if(session('success'))
                    <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg shadow-lg max-w-sm">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-sm font-medium">{{ session('success') }}</span>
                        </div>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg shadow-lg max-w-sm">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-sm font-medium">{{ session('error') }}</span>
                        </div>
                    </div>
                @endif
                
                @if(session('warning'))
                    <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded-lg shadow-lg max-w-sm">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-sm font-medium">{{ session('warning') }}</span>
                        </div>
                    </div>
                @endif
                
                @if(session('info'))
                    <div class="bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded-lg shadow-lg max-w-sm">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-sm font-medium">{{ session('info') }}</span>
                        </div>
                    </div>
                @endif
            </div>
        @endif
        
        <!-- Header -->
        @isset($header)
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endisset
        
        <!-- Main Content -->
        <main id="main-content" class="flex-1">
            @if($breadcrumbs ?? false)
                <!-- Breadcrumbs -->
                <nav class="bg-gray-50 border-b border-gray-200" aria-label="Breadcrumb">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div class="flex items-center space-x-4 py-4">
                            {{ $breadcrumbs }}
                        </div>
                    </div>
                </nav>
            @endif
            
            <!-- Page Content -->
            <div class="py-6">
                {{ $slot }}
            </div>
        </main>
        
        <!-- Footer -->
        @include('components.footer')
    </div>
    
    <!-- Loading Indicator Template -->
    <div id="loading-indicator" class="hidden fixed inset-0 z-40 bg-black bg-opacity-25 flex items-center justify-center">
        <div class="bg-white rounded-lg p-6 flex items-center space-x-4">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            <span class="text-gray-700 font-medium">Processing...</span>
        </div>
    </div>
    
    <!-- Notification Center (will be populated by JavaScript) -->
    <div id="notification-center" class="fixed top-4 right-4 z-50 space-y-2" style="max-width: 350px;"></div>
    
    <!-- Price Monitor Status (will be populated by JavaScript) -->
    <div id="price-monitor-status" class="hidden"></div>
    
    <!-- Comparison Modal Container (will be populated by JavaScript) -->
    <div id="compare-modal" class="hidden"></div>
    
    <!-- Install App Button (PWA) -->
    <button id="install-app-btn" 
            class="fixed bottom-4 right-4 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow-lg transition-colors duration-200 hidden z-30"
            aria-label="Install HD Tickets App">
        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
        </svg>
        Install App
    </button>
    
    <!-- JavaScript -->
    @vite(['resources/js/app.js'])
    
    <!-- Conditional Ticket System Scripts -->
    @if(request()->is('tickets*') || isset($loadTicketSystem))
        @vite(['resources/js/tickets/index.js'])
    @endif
    
    <!-- Additional Page Scripts -->
    @stack('scripts')
    
    <!-- Flash Message Auto-hide Script -->
    <script>
        // Auto-hide flash messages after 5 seconds
        setTimeout(() => {
            const flashMessages = document.getElementById('flash-messages');
            if (flashMessages) {
                flashMessages.style.opacity = '0';
                setTimeout(() => flashMessages.remove(), 300);
            }
        }, 5000);
        
        // Show connection status when WebSocket events occur
        document.addEventListener('echo:connected', () => {
            const status = document.getElementById('connection-status');
            if (status) {
                status.classList.remove('opacity-0', 'pointer-events-none');
                setTimeout(() => {
                    status.classList.add('opacity-0', 'pointer-events-none');
                }, 3000);
            }
        });
        
        document.addEventListener('echo:disconnected', () => {
            const status = document.getElementById('connection-status');
            if (status) {
                status.classList.remove('opacity-0', 'pointer-events-none');
            }
        });
        
        // Global keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            // Alt + S for search focus
            if (e.altKey && e.key.toLowerCase() === 's') {
                e.preventDefault();
                const searchInput = document.querySelector('input[name="keywords"], input[type="search"], #search-input');
                if (searchInput) {
                    searchInput.focus();
                    searchInput.select();
                }
            }
            
            // Escape to close modals
            if (e.key === 'Escape') {
                // Close any open modals
                const openModals = document.querySelectorAll('.modal:not(.hidden), [x-show]:not([x-show="false"])');
                openModals.forEach(modal => {
                    if (modal.style.display !== 'none') {
                        const closeButton = modal.querySelector('[data-dismiss], .modal-close, button[x-on\\:click*="show"]');
                        if (closeButton) closeButton.click();
                    }
                });
                
                // Close dropdowns
                const openDropdowns = document.querySelectorAll('[data-dropdown-open="true"]');
                openDropdowns.forEach(dropdown => {
                    dropdown.setAttribute('data-dropdown-open', 'false');
                });
            }
        });
        
        // Performance monitoring (development only)
        @if(app()->environment('local'))
        if ('performance' in window) {
            window.addEventListener('load', () => {
                setTimeout(() => {
                    const perfData = performance.getEntriesByType('navigation')[0];
                    console.log('🚀 Page Load Performance:', {
                        domContentLoaded: Math.round(perfData.domContentLoadedEventEnd - perfData.domContentLoadedEventStart) + 'ms',
                        loadComplete: Math.round(perfData.loadEventEnd - perfData.loadEventStart) + 'ms',
                        totalTime: Math.round(perfData.loadEventEnd - perfData.navigationStart) + 'ms'
                    });
                }, 1000);
            });
        }
        @endif
    </script>
    
    <!-- Google Analytics (if configured) -->
    @if(config('services.google.analytics_id'))
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ config('services.google.analytics_id') }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '{{ config('services.google.analytics_id') }}', {
            page_title: '{{ $title ?? 'HD Tickets' }}',
            custom_map: {
                'custom_parameter_1': 'ticket_system'
            }
        });
    </script>
    @endif
</body>
</html>
