<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="authenticated" content="{{ auth()->check() ? 'true' : 'false' }}">

    <!-- Enhanced Mobile Meta Tags -->
    <meta name="format-detection" content="telephone=no">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="HD Tickets">

    <title>{{ config('app.name', 'HD Tickets') }} - @yield('title', 'Dashboard')</title>
    <meta name="description" content="Professional sports ticket monitoring and alerting platform">
    <link rel="icon" type="image/png" href="{{ asset('assets/images/hdTicketsLogo.png') }}">

    <!-- Critical CSS for above-the-fold content -->
    <style>
        {!! file_get_contents(resource_path('css/critical.css')) !!}
    </style>

    <!-- Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <!-- Alpine.js fallback components -->
        <script>
            document.addEventListener('alpine:init', () => {
                // Register navigationData component fallback
                Alpine.data('navigationData', () => ({
                    open: false,
                    mobileMenuOpen: false,
                    adminDropdownOpen: false,
                    profileDropdownOpen: false,

                    init() {
                        console.log('🔧 NavigationData fallback component initialized');
                    },

                    closeAll() {
                        this.adminDropdownOpen = false;
                        this.profileDropdownOpen = false;
                        this.mobileMenuOpen = false;
                    }
                }));

                console.log('✅ Alpine.js fallback components registered');
            });
        </script>
    @endif <!-- Additional Styles -->
    @stack('styles')
</head>

<body class="font-sans antialiased">
    <!-- Full-width layout for enhanced dashboard -->
    <div class="min-h-screen bg-gray-50">
        @include('layouts.navigation')

        <!-- Full-width main content area -->
        <div class="w-full">
            @yield('content')
        </div>
    </div>

    <!-- JavaScript -->
    @stack('scripts')
</body>

</html>
