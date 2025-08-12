<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="HD Tickets - Professional sports ticket monitoring and alerting platform. Never miss your favorite team's games again!">
        <meta name="keywords" content="sports tickets, ticket alerts, sports events, ticket monitoring">
        <meta name="author" content="HD Tickets">

        <title>{{ config('app.name', 'HD Tickets') }} - Sports Ticket Monitoring</title>
        <link rel="icon" type="image/png" href="{{ asset('assets/images/hdTicketsLogo.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Styles -->
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <script src="https://cdn.tailwindcss.com"></script>
            <script>
                tailwind.config = {
                    theme: {
                        extend: {
                            fontFamily: {
                                'figtree': ['Figtree', 'sans-serif'],
                            },
                            animation: {
                                'float': 'float 6s ease-in-out infinite',
                                'pulse-slow': 'pulse 3s ease-in-out infinite',
                                'fade-in-up': 'fadeInUp 0.8s ease-out forwards',
                                'slide-in-left': 'slideInLeft 0.6s ease-out forwards',
                                'slide-in-right': 'slideInRight 0.6s ease-out forwards',
                            },
                            keyframes: {
                                float: {
                                    '0%, 100%': { transform: 'translateY(0px)' },
                                    '50%': { transform: 'translateY(-10px)' },
                                },
                                fadeInUp: {
                                    '0%': { opacity: '0', transform: 'translateY(30px)' },
                                    '100%': { opacity: '1', transform: 'translateY(0)' },
                                },
                                slideInLeft: {
                                    '0%': { opacity: '0', transform: 'translateX(-50px)' },
                                    '100%': { opacity: '1', transform: 'translateX(0)' },
                                },
                                slideInRight: {
                                    '0%': { opacity: '0', transform: 'translateX(50px)' },
                                    '100%': { opacity: '1', transform: 'translateX(0)' },
                                },
                            }
                        }
                    }
                }
            </script>
        @endif
    </head>
    <body class="font-figtree bg-gradient-to-br from-blue-50 via-white to-purple-50 min-h-screen">
        <!-- Skip Link for Accessibility -->
        <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 bg-blue-600 text-white px-4 py-2 rounded-md z-50 transition-all duration-200">Skip to main content</a>
        
        <!-- Navigation Bar -->
        <nav class="fixed top-0 left-0 right-0 z-50 bg-white/90 backdrop-blur-sm border-b border-gray-200 transition-all duration-300" id="navbar">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <!-- Logo -->
                    <div class="flex items-center">
                        <img src="{{ asset('assets/images/hdTicketsLogo.png') }}" alt="HD Tickets Logo" class="w-8 h-8 rounded-lg">
                        <span class="ml-2 text-lg font-bold bg-gradient-to-r from-blue-600 to-purple-600 text-transparent bg-clip-text">HD Tickets</span>
                    </div>
                    
                    <!-- Desktop Navigation -->
                    <div class="hidden md:flex items-center space-x-4">
                        @if (Route::has('login'))
                            @auth
                                <a href="{{ url('/dashboard') }}" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors">Dashboard</a>
                                <form method="POST" action="{{ route('logout') }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-gray-700 hover:text-red-600 px-3 py-2 rounded-md text-sm font-medium transition-colors">Logout</button>
                                </form>
                            @else
                                <a href="{{ route('login') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">Sign In</a>
                            @endauth
                        @endif
                    </div>
                    
                    <!-- Mobile menu button -->
                    <div class="md:hidden">
                        <button id="mobile-menu-button" class="text-gray-700 hover:text-blue-600 focus:outline-none focus:text-blue-600" aria-label="Open main menu">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path id="menu-icon" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                                <path id="close-icon" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Mobile Navigation Menu -->
            <div id="mobile-menu" class="md:hidden hidden bg-white border-t border-gray-200">
                <div class="px-2 pt-2 pb-3 space-y-1">
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/dashboard') }}" class="block text-gray-700 hover:text-blue-600 hover:bg-gray-50 px-3 py-2 rounded-md text-base font-medium transition-colors">Dashboard</a>
                            <form method="POST" action="{{ route('logout') }}" class="block">
                                @csrf
                                <button type="submit" class="w-full text-left text-gray-700 hover:text-red-600 hover:bg-gray-50 px-3 py-2 rounded-md text-base font-medium transition-colors">Logout</button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="block bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-md text-base font-medium transition-colors text-center">Sign In</a>
                        @endauth
                    @endif
                </div>
            </div>
        </nav>

        <!-- Background decorations -->
        <div class="fixed inset-0 overflow-hidden pointer-events-none" aria-hidden="true">
            <div class="absolute -top-4 -right-4 w-72 h-72 bg-purple-300 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-float"></div>
            <div class="absolute -bottom-8 -left-4 w-72 h-72 bg-yellow-300 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-float" style="animation-delay: 2s;"></div>
            <div class="absolute top-8 left-8 w-72 h-72 bg-pink-300 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-float" style="animation-delay: 4s;"></div>
        </div>

        <div class="relative z-10 min-h-screen flex flex-col items-center justify-center px-4 sm:px-6 lg:px-8">
            <!-- Main content -->
            <main id="main-content" class="max-w-4xl mx-auto text-center" tabindex="-1">
                <!-- Logo/Icon -->
                <div class="mb-8">
                    <div class="inline-flex items-center justify-center mb-6">
                        <img src="{{ asset('assets/images/hdTicketsLogo.png') }}" alt="HD Tickets - Sports Ticket Monitoring Platform Logo" class="w-20 h-20 rounded-2xl shadow-2xl animate-pulse-slow" loading="eager">
                    </div>
                </div>

                <!-- Main heading -->
                <h1 class="text-3xl sm:text-4xl md:text-6xl lg:text-7xl font-bold mb-4 sm:mb-6">
                    <span class="bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-600 text-transparent bg-clip-text animate-pulse">
                        HD Tickets
                    </span>
                </h1>

                <!-- Subtitle -->
                <div class="text-lg sm:text-xl md:text-2xl text-gray-600 mb-3 sm:mb-4 font-medium px-2">
                    <span class="bg-gradient-to-r from-purple-500 to-pink-500 text-transparent bg-clip-text">
                        Never Miss Your Team Again
                    </span>
                </div>

                <!-- Description -->
                <p class="text-base sm:text-lg md:text-xl text-gray-700 mb-8 sm:mb-12 max-w-3xl mx-auto leading-relaxed px-2">
                    Real-time monitoring and alerts for sports event tickets across multiple platforms. 
                    Track availability, prices, and get instant notifications when your favorite teams' tickets become available.
                </p>

                <!-- Features grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12 max-w-4xl mx-auto">
                    <div class="bg-white/70 backdrop-blur-sm rounded-2xl p-6 shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 focus-within:ring-2 focus-within:ring-blue-500 focus-within:ring-offset-2">
                        <div class="w-12 h-12 bg-blue-500 rounded-xl flex items-center justify-center mb-4 mx-auto">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" role="img" aria-label="Lightning bolt icon">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-800 mb-2">Real-time Alerts</h3>
                        <p class="text-gray-600">Get instant notifications when tickets become available for your favorite events</p>
                    </div>

                    <div class="bg-white/70 backdrop-blur-sm rounded-2xl p-6 shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 focus-within:ring-2 focus-within:ring-purple-500 focus-within:ring-offset-2" style="animation-delay: 0.2s;">
                        <div class="w-12 h-12 bg-purple-500 rounded-xl flex items-center justify-center mb-4 mx-auto">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" role="img" aria-label="Bar chart icon">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-800 mb-2">Price Tracking</h3>
                        <p class="text-gray-600">Monitor ticket prices across multiple platforms and find the best deals</p>
                    </div>

                    <div class="bg-white/70 backdrop-blur-sm rounded-2xl p-6 shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 focus-within:ring-2 focus-within:ring-green-500 focus-within:ring-offset-2" style="animation-delay: 0.4s;">
                        <div class="w-12 h-12 bg-green-500 rounded-xl flex items-center justify-center mb-4 mx-auto">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" role="img" aria-label="Clock icon">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-800 mb-2">24/7 Monitoring</h3>
                        <p class="text-gray-600">Our system works around the clock so you never miss an opportunity</p>
                    </div>
                </div>

                @if (Route::has('login'))
                    @auth
                        <!-- Authenticated user welcome -->
                        <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-8 shadow-2xl mb-8 max-w-md mx-auto">
                            <div class="flex items-center justify-center mb-4">
                                <div class="w-16 h-16 bg-gradient-to-br from-green-400 to-green-600 rounded-full flex items-center justify-center shadow-lg">
                                    <span class="text-white text-xl font-bold">
                                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                    </span>
                                </div>
                            </div>
                            <h2 class="text-2xl font-semibold text-gray-800 mb-2">
                                Welcome back, {{ Auth::user()->name }}!
                            </h2>
                            <p class="text-gray-600 mb-6">Ready to catch some amazing games?</p>
                        </div>
                    @endauth
                @endif

                <!-- Action buttons -->
                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/dashboard') }}" 
                               class="bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-semibold py-4 px-8 rounded-2xl shadow-2xl hover:shadow-3xl transform hover:-translate-y-1 transition-all duration-300 text-lg flex items-center space-x-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                                <span>Go to Dashboard</span>
                            </a>
                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button type="submit" 
                                        class="bg-white hover:bg-gray-50 text-gray-700 font-semibold py-4 px-8 rounded-2xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300 text-lg border border-gray-200">
                                    Logout
                                </button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" 
                               class="bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-semibold py-4 px-8 rounded-2xl shadow-2xl hover:shadow-3xl transform hover:-translate-y-1 transition-all duration-300 text-lg flex items-center space-x-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                                </svg>
                                <span>Sign In</span>
                            </a>
                            <div class="bg-white/80 rounded-2xl p-6 shadow-lg border border-gray-200">
                                <p class="text-gray-600 text-sm text-center">
                                    <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                    New user registration is restricted to administrators only.
                                    <br>Please contact your administrator for access.
                                </p>
                            </div>
                        @endauth
                    @endif
                </div>

                <!-- Footer info -->
                <div class="mt-16 text-center">
                    <p class="text-gray-500 text-sm">
                        Join thousands of sports fans who never miss their favorite games
                    </p>
                </div>
            </main>
        </div>

        <script>
            // Enhanced interactive effects
            document.addEventListener('DOMContentLoaded', function() {
                // Animate feature cards on scroll with improved performance
                const cards = document.querySelectorAll('.transform.hover\\:-translate-y-2');
                
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach((entry, index) => {
                        if (entry.isIntersecting) {
                            setTimeout(() => {
                                entry.target.classList.add('animate-fade-in-up');
                                entry.target.style.opacity = '1';
                            }, index * 200);
                        }
                    });
                }, {
                    threshold: 0.1,
                    rootMargin: '0px 0px -50px 0px'
                });
                
                // Initially hide cards for animation
                cards.forEach(card => {
                    card.style.opacity = '0';
                    observer.observe(card);
                });

                // Add smooth scroll behavior for skip link
                const skipLink = document.querySelector('a[href="#main-content"]');
                if (skipLink) {
                    skipLink.addEventListener('click', function(e) {
                        e.preventDefault();
                        document.getElementById('main-content').focus();
                        document.getElementById('main-content').scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    });
                }

                // Enhanced button interactions
                const buttons = document.querySelectorAll('a, button');
                buttons.forEach(button => {
                    button.addEventListener('mouseenter', function() {
                        this.style.transform = 'translateY(-2px) scale(1.02)';
                    });
                    
                    button.addEventListener('mouseleave', function() {
                        this.style.transform = '';
                    });
                    
                    button.addEventListener('focus', function() {
                        this.style.outline = '2px solid #3B82F6';
                        this.style.outlineOffset = '2px';
                    });
                    
                    button.addEventListener('blur', function() {
                        this.style.outline = '';
                        this.style.outlineOffset = '';
                    });
                });

                // Add parallax effect to background elements
                window.addEventListener('scroll', function() {
                    const scrolled = window.pageYOffset;
                    const backgroundElements = document.querySelectorAll('.animate-float');
                    
                    backgroundElements.forEach((element, index) => {
                        const speed = 0.5 + (index * 0.1);
                        element.style.transform = `translateY(${scrolled * speed}px)`;
                    });
                });

                // Logo click animation
                const logo = document.querySelector('img[alt*="HD Tickets"]');
                if (logo) {
                    logo.addEventListener('click', function() {
                        this.style.transform = 'scale(1.1) rotate(5deg)';
                        setTimeout(() => {
                            this.style.transform = '';
                        }, 300);
                    });
                    
                    logo.style.cursor = 'pointer';
                }

                // Add enhanced CSS for improved animations and responsiveness
                const enhancedStyle = document.createElement('style');
                enhancedStyle.textContent = `
                    @keyframes fadeInUp {
                        from {
                            opacity: 0;
                            transform: translateY(30px);
                        }
                        to {
                            opacity: 1;
                            transform: translateY(0);
                        }
                    }
                    
                    @keyframes bounceIn {
                        0% {
                            opacity: 0;
                            transform: scale(0.3);
                        }
                        50% {
                            opacity: 1;
                            transform: scale(1.05);
                        }
                        70% {
                            transform: scale(0.9);
                        }
                        100% {
                            opacity: 1;
                            transform: scale(1);
                        }
                    }
                    
                    .animate-bounce-in {
                        animation: bounceIn 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94);
                    }
                    
                    /* Enhanced mobile responsiveness */
                    @media (max-width: 640px) {
                        .feature-card {
                            margin-bottom: 1rem;
                        }
                        
                        .feature-card:hover {
                            transform: translateY(-4px) scale(1.02);
                        }
                    }
                    
                    /* Improved accessibility */
                    @media (prefers-reduced-motion: reduce) {
                        .animate-float,
                        .animate-pulse-slow,
                        .animate-fade-in-up {
                            animation: none !important;
                        }
                        
                        .transform.hover\\:-translate-y-2:hover {
                            transform: none !important;
                        }
                    }
                    
                    /* Enhanced focus styles */
                    *:focus {
                        outline: 2px solid #3B82F6;
                        outline-offset: 2px;
                        border-radius: 4px;
                    }
                    
                    /* Smooth transitions for all interactive elements */
                    a, button, .transform {
                        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                    }
                `;
                document.head.appendChild(enhancedStyle);
                
                // Mobile navigation functionality
                const mobileMenuButton = document.getElementById('mobile-menu-button');
                const mobileMenu = document.getElementById('mobile-menu');
                const menuIcon = document.getElementById('menu-icon');
                const closeIcon = document.getElementById('close-icon');
                
                if (mobileMenuButton && mobileMenu) {
                    mobileMenuButton.addEventListener('click', function() {
                        const isHidden = mobileMenu.classList.contains('hidden');
                        
                        if (isHidden) {
                            mobileMenu.classList.remove('hidden');
                            mobileMenu.style.maxHeight = mobileMenu.scrollHeight + 'px';
                            menuIcon.classList.add('hidden');
                            closeIcon.classList.remove('hidden');
                        } else {
                            mobileMenu.style.maxHeight = '0';
                            setTimeout(() => {
                                mobileMenu.classList.add('hidden');
                            }, 300);
                            menuIcon.classList.remove('hidden');
                            closeIcon.classList.add('hidden');
                        }
                    });
                    
                    // Close mobile menu when clicking outside
                    document.addEventListener('click', function(event) {
                        if (!mobileMenuButton.contains(event.target) && !mobileMenu.contains(event.target)) {
                            mobileMenu.style.maxHeight = '0';
                            setTimeout(() => {
                                mobileMenu.classList.add('hidden');
                            }, 300);
                            menuIcon.classList.remove('hidden');
                            closeIcon.classList.add('hidden');
                        }
                    });
                }
                
                // Navbar scroll effect
                const navbar = document.getElementById('navbar');
                if (navbar) {
                    window.addEventListener('scroll', function() {
                        if (window.scrollY > 50) {
                            navbar.classList.add('bg-white/95');
                            navbar.classList.remove('bg-white/90');
                        } else {
                            navbar.classList.add('bg-white/90');
                            navbar.classList.remove('bg-white/95');
                        }
                    });
                }
                
                // Preload critical images
                const criticalImages = [
                    '{{ asset('assets/images/hdTicketsLogo.png') }}'
                ];
                
                criticalImages.forEach(src => {
                    const img = new Image();
                    img.src = src;
                });
                
                // Add performance monitoring
                if ('performance' in window) {
                    window.addEventListener('load', function() {
                        const perfData = window.performance.timing;
                        const loadTime = perfData.loadEventEnd - perfData.navigationStart;
                        
                        // Log performance data for optimization
                        console.log(`Homepage loaded in ${loadTime}ms`);
                        
                        // Show performance metrics in debug mode
                        if (window.location.search.includes('debug=1')) {
                            const perfDiv = document.createElement('div');
                            perfDiv.innerHTML = `<div style="position: fixed; bottom: 10px; right: 10px; background: rgba(0,0,0,0.8); color: white; padding: 10px; border-radius: 5px; font-size: 12px; z-index: 9999;">Load Time: ${loadTime}ms</div>`;
                            document.body.appendChild(perfDiv);
                        }
                    });
                }
            });
        </script>
    </body>
</html>
