@extends('layouts.app-v2')

@section('title', 'Responsive Dashboard - HD Tickets')

@section('content')
{{-- Mobile Navigation Component --}}
<x-mobile-navigation 
    :user="auth()->user()" 
    :notifications="[]"
    :unread-count="0" 
/>

{{-- Main Dashboard Container --}}
<div class="hd-container-fluid hd-py-6">
    {{-- Dashboard Header --}}
    <div class="hd-flex hd-items-center hd-justify-between hd-mb-8">
        <div>
            <h1 class="hd-text-fluid-3xl font-bold text-gray-900 dark:text-white">
                Sports Tickets Dashboard
            </h1>
            <p class="hd-text-fluid-lg text-gray-600 dark:text-gray-400">
                Welcome back, {{ auth()->user()->name ?? 'User' }}! Here's your ticket overview.
            </p>
        </div>
        
        {{-- Quick Actions (responsive) --}}
        <div class="hd-flex hd-gap-3 hd-flex-col hd-flex-sm-row">
            <button class="btn-secondary hd-hidden-mobile" onclick="refreshDashboard()">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Refresh
            </button>
            <button class="btn-primary" onclick="createNewEvent()">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                New Event
            </button>
        </div>
    </div>

    {{-- Stats Grid (responsive) --}}
    <div class="hd-grid hd-gap-6 hd-mb-8">
        {{-- Total Events Card --}}
        <div class="hd-col-12 hd-col-sm-6 hd-col-lg-3">
            <div class="dashboard-card hd-p-6" data-container-query>
                <div class="hd-flex hd-items-center hd-justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Events</p>
                        <p class="hd-text-fluid-2xl font-bold text-gray-900 dark:text-white">1,247</p>
                        <p class="text-sm text-green-600 dark:text-green-400">+12% from last month</p>
                    </div>
                    <div class="hd-p-3 bg-blue-100 dark:bg-blue-800 rounded-full">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tickets Sold Card --}}
        <div class="hd-col-12 hd-col-sm-6 hd-col-lg-3">
            <div class="dashboard-card hd-p-6" data-container-query>
                <div class="hd-flex hd-items-center hd-justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Tickets Sold</p>
                        <p class="hd-text-fluid-2xl font-bold text-gray-900 dark:text-white">34,521</p>
                        <p class="text-sm text-green-600 dark:text-green-400">+8% from last month</p>
                    </div>
                    <div class="hd-p-3 bg-green-100 dark:bg-green-800 rounded-full">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Revenue Card --}}
        <div class="hd-col-12 hd-col-sm-6 hd-col-lg-3">
            <div class="dashboard-card hd-p-6" data-container-query>
                <div class="hd-flex hd-items-center hd-justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Revenue</p>
                        <p class="hd-text-fluid-2xl font-bold text-gray-900 dark:text-white">$2.1M</p>
                        <p class="text-sm text-red-600 dark:text-red-400">-2% from last month</p>
                    </div>
                    <div class="hd-p-3 bg-yellow-100 dark:bg-yellow-800 rounded-full">
                        <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Active Users Card --}}
        <div class="hd-col-12 hd-col-sm-6 hd-col-lg-3">
            <div class="dashboard-card hd-p-6" data-container-query>
                <div class="hd-flex hd-items-center hd-justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Active Users</p>
                        <p class="hd-text-fluid-2xl font-bold text-gray-900 dark:text-white">8,492</p>
                        <p class="text-sm text-green-600 dark:text-green-400">+23% from last month</p>
                    </div>
                    <div class="hd-p-3 bg-purple-100 dark:bg-purple-800 rounded-full">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content Grid --}}
    <div class="hd-grid hd-gap-6">
        {{-- Chart Section (responsive container) --}}
        <div class="hd-col-12 hd-col-lg-8">
            <div class="dashboard-card hd-p-6" data-container-query>
                <div class="hd-flex hd-items-center hd-justify-between hd-mb-6">
                    <h3 class="hd-text-fluid-xl font-semibold text-gray-900 dark:text-white">
                        Revenue Trends
                    </h3>
                    <div class="hd-flex hd-gap-2">
                        <button class="btn-sm btn-ghost" onclick="changeChartPeriod('7d')">7D</button>
                        <button class="btn-sm btn-ghost" onclick="changeChartPeriod('30d')">30D</button>
                        <button class="btn-sm btn-primary" onclick="changeChartPeriod('90d')">90D</button>
                    </div>
                </div>
                
                {{-- Placeholder for chart (responsive canvas) --}}
                <div class="hd-aspect-video bg-gray-100 dark:bg-gray-700 rounded-lg hd-flex hd-items-center hd-justify-center">
                    <div class="text-center">
                        <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        <p class="text-gray-500 dark:text-gray-400">Chart will be rendered here</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Stats Sidebar --}}
        <div class="hd-col-12 hd-col-lg-4">
            <div class="dashboard-card hd-p-6" data-container-query>
                <h3 class="hd-text-fluid-xl font-semibold text-gray-900 dark:text-white hd-mb-6">
                    Quick Stats
                </h3>
                
                {{-- Stats List --}}
                <div class="hd-flex hd-flex-col hd-gap-4">
                    <div class="hd-flex hd-items-center hd-justify-between hd-p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Football Events</span>
                        <span class="text-lg font-bold text-blue-600 dark:text-blue-400">487</span>
                    </div>
                    
                    <div class="hd-flex hd-items-center hd-justify-between hd-p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Basketball Events</span>
                        <span class="text-lg font-bold text-green-600 dark:text-green-400">312</span>
                    </div>
                    
                    <div class="hd-flex hd-items-center hd-justify-between hd-p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Baseball Events</span>
                        <span class="text-lg font-bold text-yellow-600 dark:text-yellow-400">248</span>
                    </div>
                    
                    <div class="hd-flex hd-items-center hd-justify-between hd-p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Hockey Events</span>
                        <span class="text-lg font-bold text-purple-600 dark:text-purple-400">200</span>
                    </div>
                </div>

                {{-- View All Button --}}
                <button class="btn-secondary w-full hd-mt-6" onclick="viewAllEvents()">
                    View All Events
                </button>
            </div>
        </div>
    </div>

    {{-- Recent Activity Section --}}
    <div class="hd-mt-8">
        <div class="dashboard-card hd-p-6">
            <div class="hd-flex hd-items-center hd-justify-between hd-mb-6">
                <h3 class="hd-text-fluid-xl font-semibold text-gray-900 dark:text-white">
                    Recent Tickets
                </h3>
                <button class="btn-ghost text-sm" onclick="viewAllTickets()">
                    View All →
                </button>
            </div>

            {{-- Responsive Table Component --}}
            <div id="recent-tickets-table" data-responsive-table>
                {{-- This would be replaced with actual data --}}
                <div class="hd-grid-auto-fit" style="--min-item-width: 300px;">
                    @for($i = 1; $i <= 6; $i++)
                    <div class="ticket-card hd-p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                        <div class="hd-flex hd-items-start hd-justify-between hd-mb-3">
                            <div class="hd-flex-1">
                                <h4 class="font-semibold text-gray-900 dark:text-white">
                                    Event Title {{ $i }}
                                </h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Section A - Row 12
                                </p>
                            </div>
                            <span class="status-online">Available</span>
                        </div>
                        
                        <div class="hd-flex hd-items-center hd-justify-between">
                            <span class="hd-text-fluid-lg font-bold text-green-600 dark:text-green-400">
                                ${{ 50 + ($i * 15) }}
                            </span>
                            <button class="btn-sm btn-primary" onclick="purchaseTicket({{ $i }})">
                                Purchase
                            </button>
                        </div>
                    </div>
                    @endfor
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Floating Action Button (Mobile) --}}
<div class="hd-hidden-desktop fixed bottom-6 right-6 z-50">
    <button class="w-14 h-14 bg-blue-600 hover:bg-blue-700 text-white rounded-full shadow-lg hd-flex hd-items-center hd-justify-center transition-all duration-200 hover:scale-110"
            onclick="showMobileMenu()">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
    </button>
</div>

{{-- JavaScript for Interactive Features --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize responsive table
    if (window.GridLayoutHelpers) {
        const tableContainer = document.getElementById('recent-tickets-table');
        if (tableContainer) {
            GridLayoutHelpers.createCardGrid(tableContainer.querySelector('.hd-grid-auto-fit'), 'md');
        }
    }

    // Add pull-to-refresh on mobile
    if ('ontouchstart' in window) {
        let pullToRefresh = false;
        let startY = 0;
        
        document.addEventListener('touchstart', function(e) {
            startY = e.touches[0].pageY;
        });
        
        document.addEventListener('touchmove', function(e) {
            if (window.scrollY === 0 && e.touches[0].pageY > startY + 100) {
                pullToRefresh = true;
                // Show pull indicator
                showPullIndicator();
            }
        });
        
        document.addEventListener('touchend', function(e) {
            if (pullToRefresh) {
                refreshDashboard();
                pullToRefresh = false;
                hidePullIndicator();
            }
        });
    }

    // Touch feedback for cards
    document.querySelectorAll('.ticket-card').forEach(card => {
        card.addEventListener('touchstart', function(e) {
            this.style.transform = 'scale(0.98)';
        });
        
        card.addEventListener('touchend', function(e) {
            this.style.transform = 'scale(1)';
        });
    });
});

// Dashboard functions
function refreshDashboard() {
    console.log('Refreshing dashboard...');
    // Add actual refresh logic here
    
    // Show loading state
    document.querySelectorAll('.dashboard-card').forEach(card => {
        card.style.opacity = '0.7';
    });
    
    // Simulate API call
    setTimeout(() => {
        document.querySelectorAll('.dashboard-card').forEach(card => {
            card.style.opacity = '1';
        });
        
        // Show success message
        showToast('Dashboard refreshed successfully!', 'success');
    }, 1500);
}

function createNewEvent() {
    console.log('Creating new event...');
    // Redirect to event creation or open modal
}

function changeChartPeriod(period) {
    console.log('Changing chart period to:', period);
    // Update chart data
}

function viewAllEvents() {
    console.log('Viewing all events...');
    // Navigate to events page
}

function viewAllTickets() {
    console.log('Viewing all tickets...');
    // Navigate to tickets page
}

function purchaseTicket(ticketId) {
    console.log('Purchasing ticket:', ticketId);
    // Handle ticket purchase
    showToast(`Ticket ${ticketId} added to cart!`, 'success');
}

function showMobileMenu() {
    // Toggle mobile menu
    document.querySelector('[data-mobile-menu]')?.click();
}

function showPullIndicator() {
    // Show pull-to-refresh indicator
    const indicator = document.createElement('div');
    indicator.id = 'pull-indicator';
    indicator.className = 'fixed top-0 left-0 right-0 bg-blue-600 text-white text-center py-2 text-sm z-50';
    indicator.textContent = 'Release to refresh...';
    document.body.appendChild(indicator);
}

function hidePullIndicator() {
    const indicator = document.getElementById('pull-indicator');
    if (indicator) {
        indicator.remove();
    }
}

function showToast(message, type = 'info') {
    // Simple toast notification
    const toast = document.createElement('div');
    toast.className = `toast ${type} fixed top-4 right-4 z-50 entering`;
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => toast.classList.replace('entering', 'entered'), 10);
    setTimeout(() => {
        toast.classList.replace('entered', 'exiting');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}
</script>
@endsection
