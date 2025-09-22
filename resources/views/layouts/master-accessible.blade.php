{{--
    HD Tickets - Accessible Master Layout System
    
    Enhanced unified layout with WCAG 2.1 AA compliance features:
    - Semantic landmarks and ARIA roles
    - Skip navigation links
    - Focus management and keyboard navigation
    - Screen reader support
    - High contrast and reduced motion support
    - Mobile-first responsive design
    - Role-based layout variants
    
    Usage:
    @extends('layouts.master-accessible')
    @section('content')
        Your page content
    @endsection
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="@yield('meta_description', 'Professional sports event tickets monitoring, scraping and purchase platform for ticket enthusiasts.')">
    
    {{-- Theme colors for mobile browsers --}}
    <meta name="theme-color" content="#3B82F6" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#1F2937" media="(prefers-color-scheme: dark)">
    
    {{-- PWA manifest --}}
    <link rel="manifest" href="/manifest.json">
    
    {{-- Page Title --}}
    <title>
        @hasSection('title')
            @yield('title') - {{ config('app.name', 'HD Tickets') }}
        @else
            {{ config('app.name', 'HD Tickets') }} - Sports Event Tickets Platform
        @endif
    </title>
    
    {{-- Favicon and Touch Icons --}}
    <link rel="icon" type="image/png" href="{{ asset('assets/images/hdTicketsLogo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/images/hdTicketsLogo.png') }}">
    
    {{-- Preload critical fonts --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet">
    
    {{-- Application Styles --}}
    @vite([
        'resources/css/design-tokens.css',
        'resources/css/layout-system.css', 
        'resources/css/design-system.css',
        'resources/css/app.css', 
        'resources/js/accessibility-manager.js',
        'resources/js/theme-manager.js',
        'resources/js/app.js'
    ])
    
    {{-- Additional Head Content --}}
    @stack('head')
</head>

<body class="h-full font-sans antialiased {{ auth()->user() ? auth()->user()->role . '-layout' : 'guest-layout' }}"
      x-data="accessibleLayoutManager()"
      x-init="init()"
      :class="{ 
          'hdt-theme-transitions': !$store.a11y.hasReducedMotion,
          'hdt-reduced-motion': $store.a11y.hasReducedMotion,
          'sidebar-open': sidebarOpen,
          'mobile-menu-open': mobileMenuOpen 
      }">

    {{-- Skip Navigation Links --}}
    <div class="hdt-skip-links" role="navigation" aria-label="Skip navigation links">
        <a href="#main-content" class="hdt-skip-link sr-only-focusable" @click="skipToContent()">
            Skip to main content
        </a>
        <a href="#main-navigation" class="hdt-skip-link sr-only-focusable" @click="skipToNavigation()">
            Skip to navigation
        </a>
        @if(auth()->check())
            <a href="#user-menu" class="hdt-skip-link sr-only-focusable" @click="skipToUserMenu()">
                Skip to user menu
            </a>
        @endif
    </div>

    {{-- Layout Grid Container --}}
    <div class="layout-grid min-h-screen" role="application" aria-label="{{ config('app.name') }} Application">
        
        {{-- Header Banner --}}
        <header class="layout-header" 
                role="banner" 
                aria-label="Site header with navigation and user controls">
            @include('layouts.partials.header-accessible')
        </header>

        {{-- Main Navigation Sidebar --}}
        @auth
            <nav class="layout-sidebar" 
                 role="navigation" 
                 aria-label="Main navigation"
                 id="main-navigation"
                 :aria-hidden="!sidebarOpen && isMobile"
                 x-show="sidebarOpen || isDesktop"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="-translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="-translate-x-full">
                @include('layouts.partials.sidebar-accessible')
            </nav>
            
            {{-- Mobile Sidebar Backdrop --}}
            <div x-show="mobileMenuOpen && isMobile" 
                 x-transition:enter="transition-opacity ease-linear duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity ease-linear duration-300"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 z-40 bg-black bg-opacity-75 lg:hidden"
                 @click="closeMobileMenu()"
                 @keydown.escape="closeMobileMenu()"
                 aria-hidden="true"
                 tabindex="-1">
            </div>
        @endauth

        {{-- Main Content Area --}}
        <main class="layout-main" 
              role="main" 
              id="main-content"
              tabindex="-1"
              aria-label="Main content"
              x-ref="mainContent">
            
            {{-- Breadcrumb Navigation --}}
            @if(isset($breadcrumbs) && count($breadcrumbs) > 0)
                <nav aria-label="Breadcrumb" role="navigation" class="mb-6">
                    <ol class="flex items-center space-x-2 text-sm text-text-tertiary">
                        @foreach($breadcrumbs as $index => $breadcrumb)
                            <li class="flex items-center">
                                @if($index > 0)
                                    <svg class="w-4 h-4 mr-2 text-text-quaternary" 
                                         fill="none" 
                                         stroke="currentColor" 
                                         viewBox="0 0 24 24" 
                                         aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                @endif
                                
                                @if($loop->last)
                                    <span aria-current="page" class="font-medium text-text-primary">
                                        {{ $breadcrumb['title'] }}
                                    </span>
                                @else
                                    <a href="{{ $breadcrumb['url'] }}" 
                                       class="hover:text-text-secondary hdt-transition focus:hdt-focus-ring">
                                        {{ $breadcrumb['title'] }}
                                    </a>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                </nav>
            @endif
            
            {{-- Flash Messages Region --}}
            @include('layouts.partials.flash-messages-accessible')
            
            {{-- Page Header Region --}}
            @hasSection('page-header')
                <div class="page-header mb-8" role="region" aria-label="Page header">
                    @yield('page-header')
                </div>
            @endif

            {{-- Main Content Region --}}
            <div class="main-content padding-responsive" role="region" aria-label="Main content area">
                @yield('content')
            </div>
        </main>

        {{-- Footer --}}
        <footer class="layout-footer" 
                role="contentinfo" 
                aria-label="Site footer with links and information">
            @include('layouts.partials.footer-accessible')
        </footer>
    </div>

    {{-- Loading State Overlay --}}
    <div x-show="loading" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center bg-surface-primary bg-opacity-90"
         role="status" 
         aria-label="Loading"
         aria-live="polite">
        <div class="flex flex-col items-center">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-hd-primary-600" aria-hidden="true"></div>
            <span class="mt-4 text-lg font-medium text-text-primary">Loading...</span>
        </div>
    </div>

    {{-- Screen Reader Announcements Region --}}
    <div id="hdt-announce-region" 
         aria-live="polite" 
         aria-atomic="true" 
         role="status" 
         class="sr-only"></div>

    {{-- Alpine.js Layout Manager --}}
    <script>
        function accessibleLayoutManager() {
            return {
                // Layout state
                sidebarOpen: window.innerWidth >= 1024,
                mobileMenuOpen: false,
                loading: false,
                
                // Viewport detection
                isMobile: window.innerWidth < 768,
                isTablet: window.innerWidth >= 768 && window.innerWidth < 1024,
                isDesktop: window.innerWidth >= 1024,
                
                // User role
                userRole: '{{ auth()->check() ? auth()->user()->role ?? "guest" : "guest" }}',
                
                // Focus management
                lastFocusedElement: null,
                
                init() {
                    // Handle window resize
                    window.addEventListener('resize', this.handleResize.bind(this));
                    
                    // Global keyboard handlers
                    document.addEventListener('keydown', this.handleGlobalKeydown.bind(this));
                    
                    // Store focus before navigation
                    this.setupFocusManagement();
                    
                    // Initialize viewport state
                    this.updateViewport();
                    
                    // Announce page load for screen readers
                    this.$nextTick(() => {
                        this.$store.a11y.announce(`Loaded ${document.title}`, 'polite');
                    });
                },
                
                handleResize() {
                    this.updateViewport();
                },
                
                updateViewport() {
                    this.isMobile = window.innerWidth < 768;
                    this.isTablet = window.innerWidth >= 768 && window.innerWidth < 1024;
                    this.isDesktop = window.innerWidth >= 1024;
                    
                    // Auto-manage sidebar based on viewport
                    if (this.isDesktop) {
                        this.mobileMenuOpen = false;
                        if (!this.sidebarOpen) {
                            this.sidebarOpen = true;
                        }
                    } else if (this.isMobile) {
                        this.sidebarOpen = false;
                    }
                },
                
                handleGlobalKeydown(e) {
                    // Global accessibility shortcuts
                    if (e.altKey) {
                        switch (e.key) {
                            case '1':
                                e.preventDefault();
                                this.skipToContent();
                                break;
                            case '2':
                                e.preventDefault();
                                this.skipToNavigation();
                                break;
                            case 'r':
                                e.preventDefault();
                                this.announceCurrentLocation();
                                break;
                        }
                    }
                    
                    // Close mobile menu with Escape
                    if (e.key === 'Escape') {
                        if (this.mobileMenuOpen) {
                            this.closeMobileMenu();
                        }
                    }
                },
                
                setupFocusManagement() {
                    // Track focus for restoration
                    document.addEventListener('focusin', (e) => {
                        if (!e.target.closest('[role="dialog"]') && !e.target.closest('.hdt-modal')) {
                            this.lastFocusedElement = e.target;
                        }
                    });
                },
                
                // Navigation methods
                toggleSidebar() {
                    if (this.isMobile) {
                        this.toggleMobileMenu();
                    } else {
                        this.sidebarOpen = !this.sidebarOpen;
                        this.$store.a11y.announce(
                            this.sidebarOpen ? 'Sidebar opened' : 'Sidebar closed'
                        );
                    }
                },
                
                toggleMobileMenu() {
                    this.mobileMenuOpen = !this.mobileMenuOpen;
                    
                    if (this.mobileMenuOpen) {
                        this.$nextTick(() => {
                            // Focus first navigation item
                            const firstNavItem = document.querySelector('#main-navigation a, #main-navigation button');
                            if (firstNavItem) {
                                firstNavItem.focus();
                            }
                        });
                        this.$store.a11y.announce('Navigation menu opened', 'assertive');
                    } else {
                        this.$store.a11y.announce('Navigation menu closed');
                    }
                },
                
                closeMobileMenu() {
                    this.mobileMenuOpen = false;
                    this.$store.a11y.announce('Navigation menu closed');
                    
                    // Return focus to menu button
                    this.$nextTick(() => {
                        const menuButton = document.querySelector('[aria-controls="main-navigation"]');
                        if (menuButton) {
                            menuButton.focus();
                        }
                    });
                },
                
                // Skip link methods
                skipToContent() {
                    const mainContent = this.$refs.mainContent;
                    if (mainContent) {
                        mainContent.focus();
                        mainContent.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        this.$store.a11y.announce('Skipped to main content');
                    }
                },
                
                skipToNavigation() {
                    const navigation = document.getElementById('main-navigation');
                    if (navigation) {
                        const firstNavItem = navigation.querySelector('a, button, [tabindex="0"]');
                        if (firstNavItem) {
                            firstNavItem.focus();
                            this.$store.a11y.announce('Skipped to navigation');
                        }
                    }
                },
                
                skipToUserMenu() {
                    const userMenu = document.getElementById('user-menu');
                    if (userMenu) {
                        const menuTrigger = userMenu.querySelector('button, [role="button"]');
                        if (menuTrigger) {
                            menuTrigger.focus();
                            this.$store.a11y.announce('Skipped to user menu');
                        }
                    }
                },
                
                announceCurrentLocation() {
                    const title = document.title;
                    const heading = document.querySelector('h1');
                    let announcement = `Current page: ${title}`;
                    
                    if (heading) {
                        announcement += `. Main heading: ${heading.textContent.trim()}`;
                    }
                    
                    this.$store.a11y.announce(announcement, 'assertive');
                },
                
                // Loading state
                showLoading(message = 'Loading...') {
                    this.loading = true;
                    this.$store.a11y.announce(message, 'polite');
                },
                
                hideLoading(message = 'Loading complete') {
                    this.loading = false;
                    this.$store.a11y.announce(message, 'polite');
                },
                
                // Utility methods
                focusElement(selector) {
                    const element = document.querySelector(selector);
                    if (element) {
                        element.focus();
                        return true;
                    }
                    return false;
                },
                
                scrollToElement(selector, behavior = 'smooth') {
                    const element = document.querySelector(selector);
                    if (element) {
                        element.scrollIntoView({ behavior, block: 'start' });
                        return true;
                    }
                    return false;
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