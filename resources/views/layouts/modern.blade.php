<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full" x-data="appStore()" x-init="initializeApp()">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#3b82f6">
    
    <title>@yield('title', 'HD Tickets') - {{ config('app.name', 'HD Tickets') }}</title>
    
    @if(View::hasSection('description'))
        <meta name="description" content="@yield('description')">
    @else
        <meta name="description" content="Advanced Sports Ticket Monitoring Platform">
    @endif
    
    <!-- Preload Critical Resources -->
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link rel="preload" href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" as="style">
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" as="style">
    
    <!-- Fonts -->
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    
    <!-- Main Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Custom Styles -->
    @stack('styles')
    
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <style>
        [x-cloak] { display: none !important; }
        
        /* Modern Dashboard Base Styles */
        .modern-card {
            @apply bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 transition-all duration-200 hover:shadow-md;
        }
        
        .modern-input {
            @apply w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white;
        }
        
        .modern-button {
            @apply inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200;
        }
        
        .status-indicator {
            @apply inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium;
        }
        
        .status-active {
            @apply bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200;
        }
        
        .status-inactive {
            @apply bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300;
        }
        
        .status-warning {
            @apply bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200;
        }
        
        .status-error {
            @apply bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200;
        }
        
        /* Loading States */
        .loading-skeleton {
            @apply animate-pulse bg-gray-200 dark:bg-gray-700 rounded;
        }
        
        /* Responsive utilities */
        @media (max-width: 640px) {
            .modern-card {
                @apply mx-2 rounded-lg;
            }
        }
    </style>
</head>

<body class="h-full font-sans antialiased bg-gray-50 dark:bg-gray-900">
    <div id="app" class="min-h-full">
        <!-- Navigation -->
        @include('layouts.navigation')
        
        <!-- Page Header -->
        @if(View::hasSection('header'))
        <header class="bg-white dark:bg-gray-800 shadow-sm">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                @yield('header')
            </div>
        </header>
        @endif
        
        <!-- Main Content -->
        <main class="flex-1">
            <!-- Flash Messages -->
            @if(session('success'))
            <div x-data="{ show: true }" 
                 x-show="show" 
                 x-transition
                 class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-700 rounded-md p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle text-green-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800 dark:text-green-200">
                                {{ session('success') }}
                            </p>
                        </div>
                        <div class="ml-auto pl-3">
                            <button @click="show = false" class="text-green-400 hover:text-green-600">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            
            @if(session('error'))
            <div x-data="{ show: true }" 
                 x-show="show" 
                 x-transition
                 class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="bg-red-50 dark:bg-red-900 border border-red-200 dark:border-red-700 rounded-md p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-800 dark:text-red-200">
                                {{ session('error') }}
                            </p>
                        </div>
                        <div class="ml-auto pl-3">
                            <button @click="show = false" class="text-red-400 hover:text-red-600">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            
            @yield('content')
        </main>
        
        <!-- Footer -->
        @if(View::hasSection('footer'))
        <footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                @yield('footer')
            </div>
        </footer>
        @endif
    </div>
    
    <!-- Loading Overlay -->
    <div x-data="{ loading: false }" 
         x-show="loading" 
         x-cloak
         class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-xl">
            <div class="flex items-center space-x-3">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                <span class="text-gray-900 dark:text-gray-100">Loading...</span>
            </div>
        </div>
    </div>
    
    <!-- Toast Notifications -->
    <div x-data="{ notifications: [] }" 
         @notify.window="notifications.push({id: Date.now(), ...$event.detail}); setTimeout(() => notifications.shift(), 5000)"
         class="fixed top-4 right-4 z-50">
        <template x-for="notification in notifications" :key="notification.id">
            <div x-transition:enter="transform ease-out duration-300 transition"
                 x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
                 x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="max-w-sm w-full bg-white dark:bg-gray-800 shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 mb-3">
                <div class="p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <i :class="{
                                'fas fa-check-circle text-green-400': notification.type === 'success',
                                'fas fa-exclamation-circle text-red-400': notification.type === 'error',
                                'fas fa-info-circle text-blue-400': notification.type === 'info',
                                'fas fa-exclamation-triangle text-yellow-400': notification.type === 'warning'
                            }"></i>
                        </div>
                        <div class="ml-3 w-0 flex-1 pt-0.5">
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="notification.title"></p>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400" x-text="notification.message"></p>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
    
    <!-- Scripts -->
    @stack('scripts')
    
    <script>
        // Global Alpine.js store for app state
        document.addEventListener('alpine:init', () => {
            Alpine.store('app', {
                loading: false,
                darkMode: localStorage.getItem('darkMode') === 'true',
                
                toggleDarkMode() {
                    this.darkMode = !this.darkMode;
                    localStorage.setItem('darkMode', this.darkMode);
                    document.documentElement.classList.toggle('dark', this.darkMode);
                },
                
                setLoading(state) {
                    this.loading = state;
                },
                
                notify(title, message, type = 'info') {
                    window.dispatchEvent(new CustomEvent('notify', {
                        detail: { title, message, type }
                    }));
                }
            });
        });
        
        // Initialize dark mode on page load
        if (localStorage.getItem('darkMode') === 'true') {
            document.documentElement.classList.add('dark');
        }
        
        // Real-time Dashboard Alpine.js component
        function realtimeDashboard() {
            return {
                connected: false,
                monitoring: false,
                stats: {
                    watchedTickets: 0,
                    activeScrapers: 0,
                    alertsSent: 0
                },
                updates: [],
                
                init() {
                    this.connectWebSocket();
                    this.fetchInitialData();
                },
                
                connectWebSocket() {
                    // WebSocket connection logic will be handled by websocketManager
                    if (window.websocketManager) {
                        window.websocketManager.connect();
                    }
                },
                
                fetchInitialData() {
                    // This will be called by the existing JavaScript in the template
                },
                
                startMonitoring() {
                    this.monitoring = true;
                    // API call handled in existing JavaScript
                },
                
                stopMonitoring() {
                    this.monitoring = false;
                    // API call handled in existing JavaScript
                },
                
                addUpdate(update) {
                    this.updates.unshift({
                        ...update,
                        timestamp: new Date().toLocaleTimeString(),
                        id: Date.now()
                    });
                    
                    // Keep only last 50 updates
                    if (this.updates.length > 50) {
                        this.updates.pop();
                    }
                },
                
                clearUpdates() {
                    this.updates = [];
                }
            }
        }
    </script>
</body>
</html>
