<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>{{ config('app.name', 'HD Tickets') }} - Sports Event Tickets</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    
    <!-- Preconnect to external domains for better performance -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;400;500;600;700;800;900&family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Meta tags for SEO and social sharing -->
    <meta name="description" content="Find the best sports event tickets from multiple platforms. Compare prices, track availability, and never miss your favorite games.">
    <meta name="keywords" content="sports tickets, event tickets, ticket comparison, sports events, ticket monitoring">
    <meta name="author" content="HD Tickets">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="{{ config('app.name') }} - Sports Event Tickets">
    <meta property="og:description" content="Find the best sports event tickets from multiple platforms. Compare prices and never miss your favorite games.">
    <meta property="og:image" content="{{ asset('images/og-image.png') }}">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url()->current() }}">
    <meta property="twitter:title" content="{{ config('app.name') }} - Sports Event Tickets">
    <meta property="twitter:description" content="Find the best sports event tickets from multiple platforms. Compare prices and never miss your favorite games.">
    <meta property="twitter:image" content="{{ asset('images/og-image.png') }}">
    
    <!-- Theme color for mobile browsers -->
    <meta name="theme-color" content="#3b82f6">
    <meta name="msapplication-TileColor" content="#3b82f6">
    
    <!-- Progressive Web App manifest -->
    <link rel="manifest" href="/manifest.json">
    
    <!-- Apple Touch Icon -->
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    
    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/react-app/index.tsx'])
    
    <!-- Additional CSS variables for theme consistency -->
    <style>
        :root {
            --color-primary: #3b82f6;
            --color-primary-dark: #1d4ed8;
            --color-secondary: #64748b;
            --color-success: #22c55e;
            --color-warning: #f59e0b;
            --color-error: #ef4444;
            --color-background: #ffffff;
            --color-surface: #f8fafc;
            --color-text: #1e293b;
            --color-text-muted: #64748b;
            --color-border: #e2e8f0;
        }
        
        [data-theme="dark"] {
            --color-background: #0f172a;
            --color-surface: #1e293b;
            --color-text: #f1f5f9;
            --color-text-muted: #94a3b8;
            --color-border: #334155;
        }
        
        /* Loading animation */
        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid var(--color-primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Smooth transitions */
        * {
            transition: background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease;
        }
    </style>
</head>
<body class="h-full bg-gray-50 dark:bg-gray-900 font-sans antialiased">
    <!-- Loading screen -->
    <div id="loading-screen" class="fixed inset-0 flex items-center justify-center bg-white dark:bg-gray-900 z-50">
        <div class="text-center">
            <div class="loading-spinner mx-auto mb-4"></div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Loading HD Tickets</h2>
            <p class="text-gray-600 dark:text-gray-400">Please wait while we prepare your sports ticketing experience...</p>
        </div>
    </div>

    <!-- React Application Root -->
    <div id="react-app-root" class="h-full"></div>
    
    <!-- Fallback content for users with JavaScript disabled -->
    <noscript>
        <div class="min-h-screen flex items-center justify-center bg-gray-50">
            <div class="max-w-md mx-auto text-center p-6">
                <h1 class="text-2xl font-bold text-gray-900 mb-4">JavaScript Required</h1>
                <p class="text-gray-600 mb-4">
                    HD Tickets requires JavaScript to function properly. Please enable JavaScript in your browser settings and refresh this page.
                </p>
                <button onclick="window.location.reload()" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                    Refresh Page
                </button>
            </div>
        </div>
    </noscript>
    
    <!-- Hide loading screen when React app is ready -->
    <script>
        // Remove loading screen after a maximum timeout
        const maxLoadTime = 10000; // 10 seconds
        const hideLoadingScreen = () => {
            const loadingScreen = document.getElementById('loading-screen');
            if (loadingScreen) {
                loadingScreen.style.opacity = '0';
                setTimeout(() => {
                    loadingScreen.remove();
                }, 300);
            }
        };

        // Hide loading screen when React app mounts
        const checkReactMount = () => {
            const reactRoot = document.getElementById('react-app-root');
            if (reactRoot && reactRoot.children.length > 0) {
                hideLoadingScreen();
            } else {
                setTimeout(checkReactMount, 100);
            }
        };

        // Start checking after DOM is loaded
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                setTimeout(checkReactMount, 100);
                setTimeout(hideLoadingScreen, maxLoadTime); // Failsafe
            });
        } else {
            setTimeout(checkReactMount, 100);
            setTimeout(hideLoadingScreen, maxLoadTime); // Failsafe
        }
        
        // Pass Laravel data to React app
        window.appConfig = {
            csrfToken: '{{ csrf_token() }}',
            apiUrl: '{{ config("app.url") }}/api',
            appName: '{{ config("app.name") }}',
            appEnv: '{{ config("app.env") }}',
            locale: '{{ app()->getLocale() }}',
            user: @json(auth()->user() ?? null),
        };
        
        // Service Worker registration for PWA
        if ('serviceWorker' in navigator && '{{ config("app.env") }}' === 'production') {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js')
                    .then(function(registration) {
                        console.log('ServiceWorker registration successful');
                    })
                    .catch(function(err) {
                        console.log('ServiceWorker registration failed');
                    });
            });
        }
    </script>
    
    @stack('scripts')
</body>
</html>