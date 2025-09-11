{{--
    HD Tickets - Unified Master Layout System
    
    This master layout handles all device types with:
    - Mobile-first responsive design
    - Role-based layout variants (admin, agent, customer)
    - Flexible layout regions (header, sidebar, main, footer)
    - CSS Grid and Flexbox for responsive breakpoints
    - Progressive enhancement for larger screens
    
    Usage:
    @extends('layouts.master')
    @section('content')
        Your page content
    @endsection
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="themeManager()" :class="{ 'dark': darkMode }">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, user-scalable=yes">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#3B82F6">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        
        {{-- Page Title --}}
        <title>
            @hasSection('title')
                @yield('title') - {{ config('app.name', 'HD Tickets') }}
            @else
                {{ config('app.name', 'HD Tickets') }} - Sports Event Tickets Platform
            @endif
        </title>
        
        {{-- Meta Description --}}
        <meta name="description" content="@yield('meta_description', 'Professional sports event tickets monitoring, scraping and purchase platform for ticket enthusiasts.')">
        
        {{-- Favicon and Touch Icons --}}
        <link rel="icon" type="image/png" href="{{ asset('assets/images/hdTicketsLogo.png') }}">
        <link rel="apple-touch-icon" href="{{ asset('assets/images/hdTicketsLogo.png') }}">
        
        {{-- Preload Critical Resources --}}
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link rel="preload" href="{{ css_with_timestamp('https://fonts.bunny.net/css?family=Inter:400,500,600,700&display=swap') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
        
        {{-- CSS Framework - Bootstrap with customizations --}}
        <link href="{{ css_with_timestamp('https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css') }}" rel="stylesheet">
        
        {{-- Font Awesome Icons --}}
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer">
        
        {{-- Application Styles --}}
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            {{-- Fallback CSS and JS when Vite is not available --}}
            <link rel="stylesheet" href="{{ asset('resources/css/app.css') }}">
            <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
        @endif
        
        {{-- Additional Head Content --}}
        @stack('head')
    </head>
    
    <body class="font-sans antialiased bg-gray-50 dark:bg-slate-900 transition-colors duration-200" 
          x-data="layoutManager()" 
          :class="{ 
              'sidebar-open': sidebarOpen && !isMobile,
              'mobile-menu-open': mobileMenuOpen && isMobile,
              'admin-layout': userRole === 'admin',
              'agent-layout': userRole === 'agent',
              'customer-layout': userRole === 'customer'
          }">
        
        {{-- Layout Grid Container --}}
        <div class="layout-grid min-h-screen">
            
            {{-- Header Region --}}
            <header class="layout-header" id="main-header">
                @yield('header', '@include("layouts.partials.header")')
            </header>
            
            {{-- Sidebar Region (Desktop) --}}
            @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isAgent()))
                <aside class="layout-sidebar" id="main-sidebar">
                    @yield('sidebar', '@include("layouts.partials.sidebar")')
                </aside>
            @endif
            
            {{-- Main Content Region --}}
            <main class="layout-main" id="main-content" role="main">
                {{-- Flash Messages --}}
                @include('layouts.partials.flash-messages')
                
                {{-- Page Header with Breadcrumbs --}}
                @hasSection('page-header')
                    <div class="page-header-container">
                        @yield('page-header')
                    </div>
                @endif
                
                {{-- Main Content --}}
                <div class="content-wrapper padding-responsive">
                    @yield('content')
                </div>
            </main>
            
            {{-- Footer Region --}}
            <footer class="layout-footer" id="main-footer">
                @yield('footer', '@include("layouts.partials.footer")')
            </footer>
            
        </div>
        
        {{-- Mobile Overlay for Sidebar --}}
        @auth
            <div x-show="mobileMenuOpen && isMobile" 
                 x-cloak
                 x-transition:enter="transition-opacity ease-linear duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity ease-linear duration-300"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="mobileMenuOpen = false"
                 class="mobile-overlay fixed inset-0 z-40 bg-black bg-opacity-50 lg:hidden">
            </div>
        @endauth
        
        {{-- Loading Spinner --}}
        <div id="loading-spinner" 
             x-show="loading" 
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center bg-white dark:bg-slate-900 bg-opacity-75">
            <div class="loading-indicator">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-3 text-gray-600 dark:text-gray-300">Loading...</p>
            </div>
        </div>
        
        {{-- Bootstrap JS --}}
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        
        {{-- Alpine.js Components --}}
        <script>
            // Theme Manager
            function themeManager() {
                return {
                    darkMode: localStorage.getItem('darkMode') === 'true' || 
                             (!localStorage.getItem('darkMode') && window.matchMedia('(prefers-color-scheme: dark)').matches),
                    
                    init() {
                        this.$watch('darkMode', (value) => {
                            localStorage.setItem('darkMode', value);
                            document.documentElement.classList.toggle('dark', value);
                        });
                        
                        // Initialize theme
                        document.documentElement.classList.toggle('dark', this.darkMode);
                    },
                    
                    toggleTheme() {
                        this.darkMode = !this.darkMode;
                    }
                }
            }
            
            // Layout Manager
            function layoutManager() {
                return {
                    sidebarOpen: window.innerWidth >= 1024, // Desktop default
                    mobileMenuOpen: false,
                    loading: false,
                    isMobile: window.innerWidth < 768,
                    isTablet: window.innerWidth >= 768 && window.innerWidth < 1024,
                    isDesktop: window.innerWidth >= 1024,
                    userRole: '{{ auth()->check() ? auth()->user()->getRoleNames()->first() : "guest" }}',
                    
                    init() {
                        // Handle window resize
                        window.addEventListener('resize', () => {
                            this.updateViewport();
                        });
                        
                        // Close mobile menu on route change
                        document.addEventListener('DOMContentLoaded', () => {
                            const links = document.querySelectorAll('a[href]');
                            links.forEach(link => {
                                link.addEventListener('click', () => {
                                    if (this.isMobile) {
                                        this.mobileMenuOpen = false;
                                    }
                                });
                            });
                        });
                        
                        // Handle escape key
                        document.addEventListener('keydown', (e) => {
                            if (e.key === 'Escape') {
                                this.mobileMenuOpen = false;
                            }
                        });
                    },
                    
                    updateViewport() {
                        this.isMobile = window.innerWidth < 768;
                        this.isTablet = window.innerWidth >= 768 && window.innerWidth < 1024;
                        this.isDesktop = window.innerWidth >= 1024;
                        
                        // Auto-close mobile menu on desktop
                        if (this.isDesktop) {
                            this.mobileMenuOpen = false;
                            this.sidebarOpen = true;
                        }
                    },
                    
                    toggleSidebar() {
                        if (this.isMobile) {
                            this.mobileMenuOpen = !this.mobileMenuOpen;
                        } else {
                            this.sidebarOpen = !this.sidebarOpen;
                        }
                    },
                    
                    showLoading() {
                        this.loading = true;
                    },
                    
                    hideLoading() {
                        this.loading = false;
                    }
                }
            }
        </script>
        
        {{-- Additional JavaScript --}}
        @stack('scripts')
        
        {{-- Page-specific JavaScript --}}
        @yield('javascript')
        
    </body>
</html>
