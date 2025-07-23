<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>{{ config('app.name', 'HD Tickets') }} - @yield('title', 'Dashboard')</title>
    <meta name="description" content="@yield('description', 'Professional sports ticket monitoring and management platform')">
    <link rel="icon" type="image/png" href="{{ asset('assets/images/hdTicketsLogo.png') }}">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <style>
            /* Modern Design System */
            :root {
                --color-primary: 59 130 246;
                --color-secondary: 99 102 241;
                --color-accent: 236 72 153;
                --color-success: 34 197 94;
                --color-warning: 251 191 36;
                --color-error: 239 68 68;
                --color-gray-50: 249 250 251;
                --color-gray-100: 243 244 246;
                --color-gray-200: 229 231 235;
                --color-gray-300: 209 213 219;
                --color-gray-400: 156 163 175;
                --color-gray-500: 107 114 128;
                --color-gray-600: 75 85 99;
                --color-gray-700: 55 65 81;
                --color-gray-800: 31 41 55;
                --color-gray-900: 17 24 39;
                --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
                --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
                --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
                --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
                --border-radius-sm: 0.375rem;
                --border-radius-md: 0.5rem;
                --border-radius-lg: 0.75rem;
                --border-radius-xl: 1rem;
            }

            body { 
                font-family: 'Inter', ui-sans-serif, system-ui, -apple-system, sans-serif;
                font-feature-settings: 'cv11', 'ss01';
                font-variation-settings: 'opsz' 32;
                -webkit-font-smoothing: antialiased;
                -moz-osx-font-smoothing: grayscale;
            }

            /* Modern Dashboard Components */
            .dashboard-card {
                background: white;
                border-radius: var(--border-radius-xl);
                box-shadow: var(--shadow-sm);
                border: 1px solid rgb(var(--color-gray-200));
                padding: 1.5rem;
                transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
                position: relative;
                overflow: hidden;
            }

            .dashboard-card:hover {
                box-shadow: var(--shadow-lg);
                transform: translateY(-2px);
                border-color: rgb(var(--color-gray-300));
            }

            .stat-card {
                background: linear-gradient(135deg, rgb(var(--color-primary)) 0%, rgb(var(--color-secondary)) 100%);
                color: white;
                border: none;
                position: relative;
                overflow: hidden;
            }

            .stat-card::before {
                content: '';
                position: absolute;
                top: 0;
                right: 0;
                width: 100px;
                height: 100px;
                background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
                border-radius: 50%;
                transform: translate(30%, -30%);
            }

            .hero-gradient {
                background: linear-gradient(135deg, 
                    rgb(var(--color-primary)) 0%, 
                    rgb(var(--color-secondary)) 50%, 
                    rgb(var(--color-accent)) 100%);
            }

            .glass-effect {
                backdrop-filter: blur(12px);
                background: rgba(255, 255, 255, 0.1);
                border: 1px solid rgba(255, 255, 255, 0.2);
            }

            .animate-float {
                animation: float 6s ease-in-out infinite;
            }

            @keyframes float {
                0%, 100% { transform: translateY(0px); }
                50% { transform: translateY(-10px); }
            }

            .animate-pulse-slow {
                animation: pulse-slow 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
            }

            @keyframes pulse-slow {
                0%, 100% { opacity: 1; }
                50% { opacity: 0.6; }
            }

            /* Enhanced Grid System */
            .grid { display: grid; gap: 1.5rem; }
            .grid-cols-1 { grid-template-columns: repeat(1, minmax(0, 1fr)); }
            .grid-cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .grid-cols-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .grid-cols-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }

            @media (min-width: 640px) {
                .sm\\:grid-cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
                .sm\\:grid-cols-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            }

            @media (min-width: 768px) {
                .md\\:grid-cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
                .md\\:grid-cols-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
                .md\\:grid-cols-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
            }

            @media (min-width: 1024px) {
                .lg\\:grid-cols-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
                .lg\\:grid-cols-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
                .lg\\:grid-cols-5 { grid-template-columns: repeat(5, minmax(0, 1fr)); }
            }

            /* Utility Classes */
            .bg-gray-50 { background-color: rgb(var(--color-gray-50)); }
            .bg-white { background-color: white; }
            .text-gray-500 { color: rgb(var(--color-gray-500)); }
            .text-gray-600 { color: rgb(var(--color-gray-600)); }
            .text-gray-700 { color: rgb(var(--color-gray-700)); }
            .text-gray-900 { color: rgb(var(--color-gray-900)); }
            .border-gray-200 { border-color: rgb(var(--color-gray-200)); }
            .shadow { box-shadow: var(--shadow-md); }
            .shadow-lg { box-shadow: var(--shadow-lg); }
            .rounded-xl { border-radius: var(--border-radius-xl); }
            .rounded-2xl { border-radius: 1rem; }
            .transition { transition: all 0.15s ease-in-out; }
            .transform { transform: translateZ(0); }
            .hover\\:shadow-xl:hover { box-shadow: var(--shadow-xl); }
            .hover\\:-translate-y-1:hover { transform: translateY(-0.25rem); }

            /* Stats Card Elements */
            .stat-label {
                color: rgba(255, 255, 255, 0.8);
                font-size: 0.875rem;
                font-weight: 500;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                margin-bottom: 0.5rem;
            }

            .stat-value {
                color: white;
                font-size: 2rem;
                font-weight: 700;
                line-height: 1;
            }

            /* Layout */
            .container { max-width: 80rem; margin: 0 auto; padding: 0 1rem; }
            .max-w-7xl { max-width: 80rem; }
            .mx-auto { margin-left: auto; margin-right: auto; }
            .px-4 { padding-left: 1rem; padding-right: 1rem; }
            .sm\\:px-6 { padding-left: 1rem; padding-right: 1rem; }
            .lg\\:px-8 { padding-left: 1rem; padding-right: 1rem; }
            @media (min-width: 640px) { 
                .container { padding: 0 1.5rem; }
                .sm\\:px-6 { padding-left: 1.5rem; padding-right: 1.5rem; }
            }
            @media (min-width: 1024px) { 
                .container { padding: 0 2rem; }
                .lg\\:px-8 { padding-left: 2rem; padding-right: 2rem; }
            }
            .text-center { text-align: center; }
            .space-y-6 > * + * { margin-top: 1.5rem; }
            .space-y-8 > * + * { margin-top: 2rem; }
            .flex { display: flex; }
            .items-center { align-items: center; }
            .justify-between { justify-content: space-between; }
            .justify-center { justify-content: center; }
            .min-h-screen { min-height: 100vh; }
            .p-4 { padding: 1rem; }
            .p-6 { padding: 1.5rem; }
            .p-8 { padding: 2rem; }
            .px-4 { padding-left: 1rem; padding-right: 1rem; }
            .py-6 { padding-top: 1.5rem; padding-bottom: 1.5rem; }
            .mb-4 { margin-bottom: 1rem; }
            .mb-6 { margin-bottom: 1.5rem; }
            .mb-8 { margin-bottom: 2rem; }
            .ml-3 { margin-left: 0.75rem; }
            .ml-4 { margin-left: 1rem; }
            .mr-4 { margin-right: 1rem; }
            .flex-1 { flex: 1 1 0%; }
            .flex-shrink-0 { flex-shrink: 0; }
            .flex-col { flex-direction: column; }
            .space-y-3 > * + * { margin-top: 0.75rem; }
            .gap-4 { gap: 1rem; }
            .gap-6 { gap: 1.5rem; }
            .gap-8 { gap: 2rem; }
            .rounded-lg { border-radius: 0.5rem; }
            .rounded-full { border-radius: 9999px; }
            .border { border-width: 1px; }
            .w-2 { width: 0.5rem; }
            .h-2 { height: 0.5rem; }
            .w-3 { width: 0.75rem; }
            .h-3 { height: 0.75rem; }
            .w-8 { width: 2rem; }
            .h-8 { height: 2rem; }
            .w-12 { width: 3rem; }
            .h-12 { height: 3rem; }
            .w-16 { width: 4rem; }
            .h-16 { height: 4rem; }
            .mx-auto { margin-left: auto; margin-right: auto; }
            .mb-2 { margin-bottom: 0.5rem; }
            .text-xs { font-size: 0.75rem; line-height: 1rem; }
            .text-sm { font-size: 0.875rem; line-height: 1.25rem; }
            .text-lg { font-size: 1.125rem; line-height: 1.75rem; }
            .text-xl { font-size: 1.25rem; line-height: 1.75rem; }
            .text-2xl { font-size: 1.5rem; line-height: 2rem; }
            .font-medium { font-weight: 500; }
            .font-semibold { font-weight: 600; }
            .font-bold { font-weight: 700; }
            .leading-tight { line-height: 1.25; }
        </style>
    @endif
    
    @stack('styles')
</head>
<body class="h-full font-sans antialiased bg-gray-50">
    <div id="app" class="min-h-screen flex flex-col">
        @include('layouts.navigation')
        
        <!-- Page Header -->
        @hasSection('header')
            <header class="bg-white shadow-sm border-b border-gray-200">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    @yield('header')
                </div>
            </header>
        @endif

        <!-- Main Content -->
        <main class="flex-1">
            <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
                @yield('content')
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-gray-200 mt-auto">
            <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col sm:flex-row justify-between items-center text-sm text-gray-500 text-center sm:text-left">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:space-x-4">
                        <div>© {{ date('Y') }} {{ config('app.name') }}. All rights reserved! Developed by <a href="mailto:walter.csoelle@gmail.com" class="font-medium text-blue-600 hover:text-blue-800 underline">Walter Csoelle</a>.</div>
                    </div>
                    <div class="flex flex-col sm:flex-row sm:space-x-4 text-xs sm:text-sm">
                        <span>System Health: <span class="text-green-600 font-medium">Operational. </span></span>
                        <span class="mt-1 sm:mt-0">Last Updated: <span id="footer-time">{{ now()->format('H:i:s') }}</span></span>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    @stack('scripts')
    
    <script>
        // Update footer time
        setInterval(() => {
            const timeEl = document.getElementById('footer-time');
            if (timeEl) {
                timeEl.textContent = new Date().toLocaleTimeString();
            }
        }, 1000);

        // Add smooth scrolling
        document.documentElement.style.scrollBehavior = 'smooth';
    </script>
</body>
</html>
