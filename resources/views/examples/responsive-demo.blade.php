@extends('layouts.app-v2')

@section('title', 'Responsive Design Showcase')

@section('content')
{{-- Include Responsive Design CSS and JS --}}
<link rel="stylesheet" href="{{ asset('css/responsive-design.css') }}">
<script src="{{ asset('js/responsive-design.js') }}" defer></script>

<div class="container container--xl" id="main-content">
    {{-- Header Section --}}
    <header class="text-center space-y-lg mb-2xl">
        <h1 class="text-4xl">Responsive Design Showcase</h1>
        <p class="text-lg text-gray-600 max-w-3xl mx-auto">
            Comprehensive responsive design system for the HD Tickets platform with mobile-first approach, 
            fluid typography, and optimized components for all screen sizes.
        </p>
        
        {{-- Responsive Status Panel --}}
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-lg mb-xl" role="region" aria-labelledby="responsive-status">
            <h2 id="responsive-status" class="text-lg font-semibold text-blue-900 mb-md">Current Responsive Status</h2>
            <div x-data="responsiveDesign" class="grid grid--4 gap-md text-sm">
                <div class="bg-white rounded p-md">
                    <strong>Breakpoint:</strong>
                    <span x-text="breakpoint" class="uppercase font-mono"></span>
                </div>
                <div class="bg-white rounded p-md">
                    <strong>Viewport:</strong>
                    <span x-text="`${viewport.width}×${viewport.height}`"></span>
                </div>
                <div class="bg-white rounded p-md">
                    <strong>Device Type:</strong>
                    <span x-text="isMobile() ? 'Mobile' : isTablet() ? 'Tablet' : 'Desktop'"></span>
                </div>
                <div class="bg-white rounded p-md">
                    <strong>Touch Device:</strong>
                    <span x-text="document.body.classList.contains('touch-device') ? 'Yes' : 'No'"></span>
                </div>
            </div>
            
            {{-- Debug Controls --}}
            <div class="mt-lg flex flex-wrap gap-md justify-center">
                <button onclick="window.responsiveManager?.options && (window.responsiveManager.options.enableDebugMode = true) && window.responsiveManager.setupDebugMode()" 
                    class="btn btn-primary">
                    Enable Debug Mode
                </button>
                <button onclick="window.responsiveManager?.refresh()" class="btn btn-secondary">
                    Refresh Responsive System
                </button>
                <button onclick="document.body.classList.toggle('slow-connection')" class="btn btn-outline">
                    Toggle Slow Connection Mode
                </button>
            </div>
        </div>
    </header>

    {{-- Breakpoint Demonstration --}}
    <section class="mb-2xl" role="region" aria-labelledby="breakpoints-demo">
        <div class="card">
            <div class="card__header">
                <h2 id="breakpoints-demo" class="text-2xl">Responsive Breakpoints</h2>
                <p class="text-sm text-gray-600 mt-sm">Resize your browser to see breakpoints in action</p>
            </div>
            <div class="card__body">
                <div class="space-y-md">
                    <div class="p-md rounded bg-red-100 border border-red-200 hidden-lg">
                        <strong>XS Breakpoint</strong> (320px+) - Mobile portrait
                        <div class="text-sm text-gray-600">Single column layout, touch-optimized</div>
                    </div>
                    
                    <div class="p-md rounded bg-orange-100 border border-orange-200 visible-sm hidden-md hidden-lg">
                        <strong>SM Breakpoint</strong> (640px+) - Mobile landscape / small tablet
                        <div class="text-sm text-gray-600">Optimized for larger mobile screens</div>
                    </div>
                    
                    <div class="p-md rounded bg-yellow-100 border border-yellow-200 visible-md hidden-lg">
                        <strong>MD Breakpoint</strong> (768px+) - Tablet portrait
                        <div class="text-sm text-gray-600">Two-column layouts begin to appear</div>
                    </div>
                    
                    <div class="p-md rounded bg-green-100 border border-green-200 visible-lg">
                        <strong>LG+ Breakpoints</strong> (1024px+) - Desktop and large displays
                        <div class="text-sm text-gray-600">Full multi-column layouts and desktop features</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Responsive Grid System --}}
    <section class="mb-2xl" role="region" aria-labelledby="grid-demo">
        <div class="card">
            <div class="card__header">
                <h2 id="grid-demo" class="text-2xl">Responsive Grid System</h2>
                <p class="text-sm text-gray-600 mt-sm">Flexible grid layouts that adapt to screen size</p>
            </div>
            <div class="card__body space-y-xl">
                {{-- 2-Column Grid --}}
                <div>
                    <h3 class="text-lg font-semibold mb-md">2-Column Grid (Mobile: 1 col, Tablet+: 2 cols)</h3>
                    <div class="grid grid--2">
                        <div class="bg-blue-100 p-lg rounded text-center">Column 1</div>
                        <div class="bg-blue-200 p-lg rounded text-center">Column 2</div>
                    </div>
                </div>

                {{-- 3-Column Grid --}}
                <div>
                    <h3 class="text-lg font-semibold mb-md">3-Column Grid (Mobile: 1 col, Tablet: 2 cols, Desktop: 3 cols)</h3>
                    <div class="grid grid--3">
                        <div class="bg-green-100 p-lg rounded text-center">Column 1</div>
                        <div class="bg-green-200 p-lg rounded text-center">Column 2</div>
                        <div class="bg-green-300 p-lg rounded text-center">Column 3</div>
                    </div>
                </div>

                {{-- 4-Column Grid --}}
                <div>
                    <h3 class="text-lg font-semibold mb-md">4-Column Grid (Responsive scaling)</h3>
                    <div class="grid grid--4">
                        <div class="bg-purple-100 p-lg rounded text-center">Item 1</div>
                        <div class="bg-purple-200 p-lg rounded text-center">Item 2</div>
                        <div class="bg-purple-300 p-lg rounded text-center">Item 3</div>
                        <div class="bg-purple-400 p-lg rounded text-center">Item 4</div>
                    </div>
                </div>

                {{-- Complex Grid Layout --}}
                <div>
                    <h3 class="text-lg font-semibold mb-md">Complex Layout with Spanning</h3>
                    <div class="grid grid--12">
                        <div class="col-span-full lg:col-span-8 bg-indigo-100 p-lg rounded text-center">
                            Main Content (Full width on mobile, 8 cols on desktop)
                        </div>
                        <div class="col-span-full sm:col-span-6 lg:col-span-2 bg-indigo-200 p-lg rounded text-center">
                            Sidebar 1 (Full on mobile, half on tablet, 2 cols on desktop)
                        </div>
                        <div class="col-span-full sm:col-span-6 lg:col-span-2 bg-indigo-300 p-lg rounded text-center">
                            Sidebar 2 (Full on mobile, half on tablet, 2 cols on desktop)
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Responsive Typography --}}
    <section class="mb-2xl" role="region" aria-labelledby="typography-demo">
        <div class="card">
            <div class="card__header">
                <h2 id="typography-demo" class="text-2xl">Fluid Typography</h2>
                <p class="text-sm text-gray-600 mt-sm">Typography that scales smoothly across all screen sizes</p>
            </div>
            <div class="card__body space-y-lg">
                <div class="space-y-md">
                    <h1 class="text-5xl">Heading 1 - Fluid Scale</h1>
                    <h2 class="text-4xl">Heading 2 - Responsive</h2>
                    <h3 class="text-3xl">Heading 3 - Scalable</h3>
                    <h4 class="text-2xl">Heading 4 - Adaptive</h4>
                    <h5 class="text-xl">Heading 5 - Flexible</h5>
                    <h6 class="text-lg">Heading 6 - Optimized</h6>
                </div>
                
                <div class="space-y-md">
                    <p class="text-lg">Large paragraph text that adapts to screen size for optimal readability.</p>
                    <p class="text-base">Regular paragraph text with fluid scaling using CSS clamp() for consistent readability across devices.</p>
                    <p class="text-sm">Small text that remains legible on all screen sizes while maintaining proportional relationships.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Responsive Tables --}}
    <section class="mb-2xl" role="region" aria-labelledby="tables-demo">
        <div class="card">
            <div class="card__header">
                <h2 id="tables-demo" class="text-2xl">Responsive Data Tables</h2>
                <p class="text-sm text-gray-600 mt-sm">Tables that transform for mobile-friendly viewing</p>
            </div>
            <div class="card__body space-y-xl">
                {{-- Standard Responsive Table --}}
                <div>
                    <h3 class="text-lg font-semibold mb-md">Standard Responsive Table</h3>
                    <div class="table-wrapper">
                        <table class="table">
                            <caption class="sr-only">Sports event tickets with pricing and availability information</caption>
                            <thead>
                                <tr>
                                    <th scope="col">Event</th>
                                    <th scope="col">Date</th>
                                    <th scope="col">Time</th>
                                    <th scope="col">Price</th>
                                    <th scope="col">Availability</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td data-label="Event">Lakers vs Warriors</td>
                                    <td data-label="Date">Dec 15, 2024</td>
                                    <td data-label="Time">7:30 PM</td>
                                    <td data-label="Price">$125.00</td>
                                    <td data-label="Availability">
                                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">Available</span>
                                    </td>
                                    <td data-label="Action">
                                        <button class="btn btn--sm bg-blue-600 text-white">View Details</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td data-label="Event">Cowboys vs Giants</td>
                                    <td data-label="Date">Dec 20, 2024</td>
                                    <td data-label="Time">1:00 PM</td>
                                    <td data-label="Price">$89.50</td>
                                    <td data-label="Availability">
                                        <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs">Limited</span>
                                    </td>
                                    <td data-label="Action">
                                        <button class="btn btn--sm bg-blue-600 text-white">View Details</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td data-label="Event">Yankees vs Red Sox</td>
                                    <td data-label="Date">Apr 5, 2025</td>
                                    <td data-label="Time">7:05 PM</td>
                                    <td data-label="Price">$75.00</td>
                                    <td data-label="Availability">
                                        <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs">Sold Out</span>
                                    </td>
                                    <td data-label="Action">
                                        <button class="btn btn--sm bg-gray-400 text-white" disabled>Unavailable</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Card-Based Table for Mobile --}}
                <div>
                    <h3 class="text-lg font-semibold mb-md">Card-Based Table Alternative</h3>
                    <div class="table-cards">
                        <div class="table-card">
                            <div class="table-card__header">Lakers vs Warriors</div>
                            <div class="table-card__row">
                                <span class="table-card__label">Date:</span>
                                <span class="table-card__value">Dec 15, 2024</span>
                            </div>
                            <div class="table-card__row">
                                <span class="table-card__label">Time:</span>
                                <span class="table-card__value">7:30 PM</span>
                            </div>
                            <div class="table-card__row">
                                <span class="table-card__label">Price:</span>
                                <span class="table-card__value font-semibold text-green-600">$125.00</span>
                            </div>
                            <div class="table-card__row">
                                <span class="table-card__label">Status:</span>
                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">Available</span>
                            </div>
                        </div>
                        
                        <div class="table-card">
                            <div class="table-card__header">Cowboys vs Giants</div>
                            <div class="table-card__row">
                                <span class="table-card__label">Date:</span>
                                <span class="table-card__value">Dec 20, 2024</span>
                            </div>
                            <div class="table-card__row">
                                <span class="table-card__label">Time:</span>
                                <span class="table-card__value">1:00 PM</span>
                            </div>
                            <div class="table-card__row">
                                <span class="table-card__label">Price:</span>
                                <span class="table-card__value font-semibold text-green-600">$89.50</span>
                            </div>
                            <div class="table-card__row">
                                <span class="table-card__label">Status:</span>
                                <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs">Limited</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Dashboard Layout Demo --}}
    <section class="mb-2xl" role="region" aria-labelledby="dashboard-demo">
        <div class="card">
            <div class="card__header">
                <h2 id="dashboard-demo" class="text-2xl">Responsive Dashboard Layout</h2>
                <p class="text-sm text-gray-600 mt-sm">Dashboard widgets that adapt to available space</p>
            </div>
            <div class="card__body">
                <div class="dashboard-grid">
                    <div class="widget widget--small">
                        <h3 class="text-lg font-semibold mb-md">Small Widget</h3>
                        <div class="text-3xl font-bold text-blue-600">42</div>
                        <div class="text-sm text-gray-600">Active Events</div>
                    </div>
                    
                    <div class="widget widget--small">
                        <h3 class="text-lg font-semibold mb-md">Metrics</h3>
                        <div class="text-3xl font-bold text-green-600">$12,345</div>
                        <div class="text-sm text-gray-600">Revenue</div>
                    </div>
                    
                    <div class="widget widget--medium">
                        <h3 class="text-lg font-semibold mb-md">Medium Widget</h3>
                        <div class="space-y-sm">
                            <div class="flex justify-between">
                                <span>Basketball</span>
                                <span class="font-semibold">68%</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Football</span>
                                <span class="font-semibold">45%</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Baseball</span>
                                <span class="font-semibold">32%</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="widget widget--large">
                        <h3 class="text-lg font-semibold mb-md">Large Widget - Recent Activity</h3>
                        <div class="space-y-md">
                            <div class="flex items-center space-x-md">
                                <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                <div class="flex-1">
                                    <div class="font-medium">Lakers tickets updated</div>
                                    <div class="text-sm text-gray-600">2 minutes ago</div>
                                </div>
                            </div>
                            <div class="flex items-center space-x-md">
                                <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                <div class="flex-1">
                                    <div class="font-medium">New scraping job started</div>
                                    <div class="text-sm text-gray-600">5 minutes ago</div>
                                </div>
                            </div>
                            <div class="flex items-center space-x-md">
                                <div class="w-2 h-2 bg-yellow-500 rounded-full"></div>
                                <div class="flex-1">
                                    <div class="font-medium">Price alert triggered</div>
                                    <div class="text-sm text-gray-600">8 minutes ago</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Responsive Forms --}}
    <section class="mb-2xl" role="region" aria-labelledby="forms-demo">
        <div class="card">
            <div class="card__header">
                <h2 id="forms-demo" class="text-2xl">Responsive Form Layouts</h2>
                <p class="text-sm text-gray-600 mt-sm">Forms that adapt their layout for optimal usability</p>
            </div>
            <div class="card__body">
                <form class="space-y-lg">
                    <div class="form-row">
                        <div class="flex-1">
                            <label for="first-name" class="block text-sm font-medium text-gray-700 mb-sm">First Name</label>
                            <input type="text" id="first-name" name="first_name" class="w-full border border-gray-300 rounded-md px-md py-sm">
                        </div>
                        <div class="flex-1">
                            <label for="last-name" class="block text-sm font-medium text-gray-700 mb-sm">Last Name</label>
                            <input type="text" id="last-name" name="last_name" class="w-full border border-gray-300 rounded-md px-md py-sm">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-sm">Email Address</label>
                        <input type="email" id="email" name="email" class="w-full border border-gray-300 rounded-md px-md py-sm">
                    </div>
                    
                    <div class="form-row">
                        <div class="flex-1">
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-sm">Phone</label>
                            <input type="tel" id="phone" name="phone" class="w-full border border-gray-300 rounded-md px-md py-sm">
                        </div>
                        <div class="flex-1">
                            <label for="zip" class="block text-sm font-medium text-gray-700 mb-sm">ZIP Code</label>
                            <input type="text" id="zip" name="zip" class="w-full border border-gray-300 rounded-md px-md py-sm">
                        </div>
                    </div>
                    
                    <div class="flex mobile-flex-col gap-md">
                        <button type="submit" class="btn bg-blue-600 text-white flex-1 sm:flex-none">
                            Submit Form
                        </button>
                        <button type="reset" class="btn border border-gray-300 text-gray-700 flex-1 sm:flex-none">
                            Reset
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    {{-- Touch Optimization Demo --}}
    <section class="mb-2xl" role="region" aria-labelledby="touch-demo">
        <div class="card">
            <div class="card__header">
                <h2 id="touch-demo" class="text-2xl">Touch Optimization</h2>
                <p class="text-sm text-gray-600 mt-sm">Touch-friendly interactions and sizing</p>
            </div>
            <div class="card__body">
                <div class="space-y-lg">
                    <div>
                        <h3 class="text-lg font-semibold mb-md">Button Sizes</h3>
                        <div class="flex mobile-flex-col gap-md">
                            <button class="btn btn--sm bg-gray-600 text-white">Small (36px min height)</button>
                            <button class="btn bg-blue-600 text-white">Regular (44px min height)</button>
                            <button class="btn btn--lg bg-green-600 text-white">Large (48px min height)</button>
                            <button class="btn btn--xl bg-purple-600 text-white">Extra Large (56px min height)</button>
                        </div>
                    </div>
                    
                    <div>
                        <h3 class="text-lg font-semibold mb-md">Touch-Friendly Navigation</h3>
                        <div class="mobile-nav-demo border border-gray-200 rounded-lg p-md">
                            <div class="space-y-xs">
                                <a href="#" class="mobile-nav__item">Dashboard</a>
                                <a href="#" class="mobile-nav__item">Sports Tickets</a>
                                <a href="#" class="mobile-nav__item">My Alerts</a>
                                <a href="#" class="mobile-nav__item">Purchase Queue</a>
                                <a href="#" class="mobile-nav__item">Profile Settings</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Performance and Lazy Loading --}}
    <section class="mb-2xl" role="region" aria-labelledby="performance-demo">
        <div class="card">
            <div class="card__header">
                <h2 id="performance-demo" class="text-2xl">Performance Optimizations</h2>
                <p class="text-sm text-gray-600 mt-sm">Lazy loading and performance features</p>
            </div>
            <div class="card__body">
                <div class="grid grid--3 gap-lg">
                    <div class="text-center">
                        <div class="w-full h-48 bg-gray-200 rounded-lg mb-md flex items-center justify-center" 
                             data-lazy-background="https://via.placeholder.com/300x200/4F46E5/FFFFFF?text=Lazy+Image+1">
                            <span class="text-gray-500">Loading...</span>
                        </div>
                        <h3 class="font-semibold">Lazy Loaded Background</h3>
                        <p class="text-sm text-gray-600">Background image loads when scrolled into view</p>
                    </div>
                    
                    <div class="text-center">
                        <img class="w-full h-48 object-cover rounded-lg mb-md" 
                             data-lazy-src="https://via.placeholder.com/300x200/10B981/FFFFFF?text=Lazy+Image+2"
                             src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='300' height='200'%3E%3Crect width='300' height='200' fill='%23f3f4f6'/%3E%3Ctext x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' fill='%236b7280'%3ELoading...%3C/text%3E%3C/svg%3E"
                             alt="Lazy loaded demonstration image">
                        <h3 class="font-semibold">Lazy Loaded Image</h3>
                        <p class="text-sm text-gray-600">Image loads when scrolled into view</p>
                    </div>
                    
                    <div class="text-center">
                        <div class="w-full h-48 bg-gradient-to-r from-purple-500 to-pink-500 rounded-lg mb-md flex items-center justify-center text-white">
                            <span class="font-semibold">Optimized Content</span>
                        </div>
                        <h3 class="font-semibold">Performance Optimized</h3>
                        <p class="text-sm text-gray-600">Reduced animations on slow connections</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Footer --}}
    <footer class="bg-gray-50 rounded-lg p-lg mt-2xl text-center" id="footer" role="contentinfo">
        <h2 class="text-lg font-semibold mb-md">Responsive Design Implementation</h2>
        <div class="grid grid--3 gap-lg text-left">
            <div>
                <h3 class="font-semibold mb-sm">Breakpoints</h3>
                <ul class="text-sm text-gray-600 space-y-xs">
                    <li>XS: 320px+ (Mobile portrait)</li>
                    <li>SM: 640px+ (Mobile landscape)</li>
                    <li>MD: 768px+ (Tablet)</li>
                    <li>LG: 1024px+ (Desktop)</li>
                    <li>XL: 1280px+ (Large desktop)</li>
                    <li>2XL: 1536px+ (Extra large)</li>
                </ul>
            </div>
            <div>
                <h3 class="font-semibold mb-sm">Features</h3>
                <ul class="text-sm text-gray-600 space-y-xs">
                    <li>Mobile-first approach</li>
                    <li>Fluid typography with clamp()</li>
                    <li>Responsive grids</li>
                    <li>Touch optimization</li>
                    <li>Lazy loading</li>
                    <li>Container queries</li>
                </ul>
            </div>
            <div>
                <h3 class="font-semibold mb-sm">Performance</h3>
                <ul class="text-sm text-gray-600 space-y-xs">
                    <li>Optimized for slow connections</li>
                    <li>Reduced motion support</li>
                    <li>Image lazy loading</li>
                    <li>Touch target optimization</li>
                    <li>Responsive images</li>
                    <li>Print optimizations</li>
                </ul>
            </div>
        </div>
    </footer>
</div>

<style>
/* Demo-specific styles */
.mobile-nav-demo .mobile-nav__item {
    display: block;
    padding: var(--space-md);
    border-bottom: 1px solid #f3f4f6;
    color: #374151;
    text-decoration: none;
    font-weight: 500;
    min-height: 48px;
    display: flex;
    align-items: center;
}

.mobile-nav-demo .mobile-nav__item:hover {
    background-color: #f9fafb;
}

.mobile-nav-demo .mobile-nav__item:last-child {
    border-bottom: none;
}

/* Demonstrate responsive visibility */
.visible-sm { display: none; }
.visible-md { display: none; }
.visible-lg { display: none; }

@media (min-width: 640px) and (max-width: 767px) {
    .visible-sm { display: block; }
}

@media (min-width: 768px) and (max-width: 1023px) {
    .visible-md { display: block; }
}

@media (min-width: 1024px) {
    .visible-lg { display: block; }
}

/* Lazy loading styles */
[data-lazy-src], [data-lazy-background] {
    transition: opacity 0.3s ease;
}

[data-lazy-src]:not(.lazy-loaded), [data-lazy-background]:not(.lazy-loaded) {
    opacity: 0.7;
}

.lazy-loaded {
    opacity: 1;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update responsive status indicators
    function updateStatus() {
        const statusElements = document.querySelectorAll('[x-data="responsiveDesign"]');
        statusElements.forEach(el => {
            if (window.Alpine) {
                Alpine.store(el);
            }
        });
    }
    
    // Listen for breakpoint changes
    document.addEventListener('breakpointChange', updateStatus);
    document.addEventListener('orientationChange', updateStatus);
    
    // Initial status update
    setTimeout(updateStatus, 1000);
});
</script>
@endsection
