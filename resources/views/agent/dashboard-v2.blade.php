@extends('layouts.modern-app')

@section('title', 'Agent Dashboard')

@section('meta_description', 'Agent dashboard for active ticket monitoring, price alerts, and purchase queue management')

@push('head')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

@section('page-header')
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white">
                Agent Dashboard 🎯
            </h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Monitor tickets, alerts, and manage customer requests
            </p>
        </div>
        
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
            <!-- Status Indicator -->
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 bg-green-400 rounded-full animate-pulse"></div>
                <span class="text-sm text-gray-600 dark:text-gray-400">Online</span>
            </div>
            
            <!-- Quick Actions -->
            <div class="flex items-center gap-2">
                <button class="hdt-button hdt-button--primary hdt-button--sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Create Alert
                </button>
                <button class="hdt-button hdt-button--secondary hdt-button--sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Refresh All
                </button>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div x-data="agentDashboard()" x-init="init()" class="space-y-6">
        
        <!-- Performance Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            
            <!-- Active Alerts -->
            <div class="hdt-card">
                <div class="hdt-card__body">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/50 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4 19h6v-2H4v2zM4 14h16v-2H4v2zM4 9h16V7H4v2z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Active Alerts</p>
                            <div class="flex items-center mt-1">
                                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100" x-text="metrics.activeAlerts"></p>
                                <span class="ml-2 text-xs text-blue-600 dark:text-blue-400">
                                    <span x-text="metrics.newAlertsToday"></span> new today
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Triggered Alerts -->
            <div class="hdt-card">
                <div class="hdt-card__body">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-yellow-100 dark:bg-yellow-900/50 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Triggered Today</p>
                            <div class="flex items-center mt-1">
                                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100" x-text="metrics.triggeredToday"></p>
                                <span class="ml-2 text-xs" :class="metrics.triggerTrend === 'up' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
                                    <span x-text="metrics.triggerChange"></span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Purchases Handled -->
            <div class="hdt-card">
                <div class="hdt-card__body">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-100 dark:bg-green-900/50 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m1.6 8L6 5H2m5 8v6a2 2 0 002 2h6a2 2 0 002-2v-6m-6 0v6m-6-6h12"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Purchases Today</p>
                            <div class="flex items-center mt-1">
                                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100" x-text="metrics.purchasesToday"></p>
                                <span class="ml-2 text-xs text-green-600 dark:text-green-400">
                                    $<span x-text="metrics.revenueToday.toLocaleString()"></span> revenue
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Inquiries -->
            <div class="hdt-card">
                <div class="hdt-card__body">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900/50 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Pending Inquiries</p>
                            <div class="flex items-center mt-1">
                                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100" x-text="metrics.pendingInquiries"></p>
                                <span class="ml-2 text-xs text-gray-500 dark:text-gray-400">
                                    Avg: <span x-text="metrics.avgResponseTime"></span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Price Alerts Panel -->
            <div class="lg:col-span-2">
                <div class="hdt-card">
                    <div class="hdt-card__header">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Recent Price Alerts</h3>
                        <div class="flex items-center gap-2">
                            <select class="hdt-input hdt-input--xs" x-model="alertFilter" @change="filterAlerts()">
                                <option value="all">All Alerts</option>
                                <option value="triggered">Recently Triggered</option>
                                <option value="pending">Pending</option>
                                <option value="high_priority">High Priority</option>
                            </select>
                            <button @click="refreshAlerts()" class="hdt-button hdt-button--outline hdt-button--xs">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="hdt-card__body">
                        <div class="space-y-4">
                            <template x-for="alert in filteredAlerts" :key="alert.id">
                                <div class="flex items-start justify-between p-4 rounded-lg border"
                                     :class="getAlertStatusClass(alert.status)">
                                    
                                    <div class="flex items-start space-x-3 flex-1">
                                        <!-- Alert Icon -->
                                        <div class="flex-shrink-0 mt-1">
                                            <div class="w-8 h-8 rounded-full flex items-center justify-center"
                                                 :class="getAlertIconClass(alert.priority)">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                          :d="getAlertIconPath(alert.status)"/>
                                                </svg>
                                            </div>
                                        </div>
                                        
                                        <!-- Alert Details -->
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center justify-between">
                                                <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="alert.eventTitle"></h4>
                                                <span class="text-xs px-2 py-1 rounded-full font-medium"
                                                      :class="getPriorityClass(alert.priority)" 
                                                      x-text="alert.priority + ' priority'"></span>
                                            </div>
                                            
                                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                                <span x-text="alert.venue"></span> • <span x-text="alert.eventDate"></span>
                                            </p>
                                            
                                            <!-- Price Information -->
                                            <div class="flex items-center gap-4 mt-2">
                                                <div>
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">Target Price:</span>
                                                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100">$<span x-text="alert.targetPrice"></span></span>
                                                </div>
                                                <div x-show="alert.status === 'triggered'">
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">Current Price:</span>
                                                    <span class="text-sm font-medium" 
                                                          :class="alert.currentPrice <= alert.targetPrice ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
                                                        $<span x-text="alert.currentPrice"></span>
                                                    </span>
                                                </div>
                                            </div>
                                            
                                            <!-- Customer Info -->
                                            <div class="flex items-center gap-2 mt-2">
                                                <img :src="alert.customerAvatar" :alt="alert.customerName" class="w-5 h-5 rounded-full">
                                                <span class="text-xs text-gray-500 dark:text-gray-400" x-text="alert.customerName + ' • Created ' + alert.createdAt"></span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Actions -->
                                    <div class="flex items-center gap-2 ml-4">
                                        <button @click="viewAlertDetails(alert)" 
                                                class="hdt-button hdt-button--outline hdt-button--xs">
                                            View
                                        </button>
                                        <template x-if="alert.status === 'triggered'">
                                            <button @click="processAlert(alert)" 
                                                    class="hdt-button hdt-button--primary hdt-button--xs">
                                                Process
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Purchase Queue & Quick Stats -->
            <div class="space-y-6">
                
                <!-- Purchase Queue -->
                <div class="hdt-card">
                    <div class="hdt-card__header">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Purchase Queue</h3>
                        <span class="hdt-badge hdt-badge--primary hdt-badge--sm" x-text="purchaseQueue.length + ' pending'"></span>
                    </div>
                    <div class="hdt-card__body">
                        <div class="space-y-3">
                            <template x-for="purchase in purchaseQueue.slice(0, 5)" :key="purchase.id">
                                <div class="flex items-center justify-between p-3 rounded-lg border border-gray-200 dark:border-gray-700">
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="purchase.eventTitle"></h4>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            <span x-text="purchase.quantity"></span> tickets • $<span x-text="purchase.totalPrice"></span>
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400" x-text="'Customer: ' + purchase.customerName"></p>
                                    </div>
                                    <div class="flex flex-col items-end gap-1">
                                        <span class="text-xs px-2 py-1 rounded-full font-medium"
                                              :class="getUrgencyClass(purchase.urgency)"
                                              x-text="purchase.urgency"></span>
                                        <button @click="processPurchase(purchase)"
                                                class="hdt-button hdt-button--primary hdt-button--xs">
                                            Process
                                        </button>
                                    </div>
                                </div>
                            </template>
                            
                            <template x-if="purchaseQueue.length > 5">
                                <div class="text-center pt-2">
                                    <button class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300">
                                        View all <span x-text="purchaseQueue.length"></span> purchases
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Customer Inquiries -->
                <div class="hdt-card">
                    <div class="hdt-card__header">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Recent Inquiries</h3>
                    </div>
                    <div class="hdt-card__body">
                        <div class="space-y-3">
                            <template x-for="inquiry in customerInquiries.slice(0, 4)" :key="inquiry.id">
                                <div class="flex items-start space-x-3">
                                    <img :src="inquiry.customerAvatar" :alt="inquiry.customerName" class="w-8 h-8 rounded-full">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center justify-between">
                                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="inquiry.customerName"></h4>
                                            <span class="text-xs text-gray-500 dark:text-gray-400" x-text="inquiry.timestamp"></span>
                                        </div>
                                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1" x-text="inquiry.subject"></p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 truncate" x-text="inquiry.preview"></p>
                                        <div class="flex items-center gap-2 mt-2">
                                            <button class="hdt-button hdt-button--outline hdt-button--xs">Reply</button>
                                            <span class="text-xs px-2 py-1 rounded-full"
                                                  :class="inquiry.urgent ? 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300' : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300'">
                                                <template x-if="inquiry.urgent">
                                                    <span>Urgent</span>
                                                </template>
                                                <template x-if="!inquiry.urgent">
                                                    <span>Normal</span>
                                                </template>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Monitoring Grid -->
        <div class="hdt-card">
            <div class="hdt-card__header">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Active Ticket Monitoring</h3>
                <div class="flex items-center gap-2">
                    <button @click="toggleAutoRefresh()" 
                            :class="autoRefresh ? 'hdt-button--primary' : 'hdt-button--outline'"
                            class="hdt-button hdt-button--xs">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        <span x-text="autoRefresh ? 'Auto: ON' : 'Auto: OFF'"></span>
                    </button>
                    <span x-show="autoRefresh" class="text-xs text-gray-500 dark:text-gray-400">
                        Next update: <span x-text="nextUpdateIn"></span>s
                    </span>
                </div>
            </div>
            <div class="hdt-card__body">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Event</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Platform</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Current Price</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Change</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Alerts</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                            <template x-for="ticket in monitoredTickets" :key="ticket.id">
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="ticket.eventTitle"></div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400" x-text="ticket.venue + ' • ' + ticket.eventDate"></div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="hdt-badge hdt-badge--secondary hdt-badge--xs" x-text="ticket.platform"></span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">$<span x-text="ticket.currentPrice"></span></div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400"><span x-text="ticket.availableTickets"></span> available</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <span class="text-sm font-medium"
                                                  :class="ticket.priceChange.startsWith('+') ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400'">
                                                <span x-text="ticket.priceChange"></span>
                                            </span>
                                            <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                      :d="ticket.priceChange.startsWith('+') ? 'M5 10l7-7m0 0l7 7m-7-7v18' : 'M19 14l-7 7m0 0l-7-7m7 7V3'"/>
                                            </svg>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="hdt-badge hdt-badge--info hdt-badge--xs" x-text="ticket.activeAlerts + ' alerts'"></span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <button @click="viewTicketDetails(ticket)" class="hdt-button hdt-button--outline hdt-button--xs">
                                            Details
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Alpine.js Component -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('agentDashboard', () => ({
                loading: false,
                autoRefresh: true,
                nextUpdateIn: 30,
                alertFilter: 'all',
                
                metrics: {
                    activeAlerts: 47,
                    newAlertsToday: 12,
                    triggeredToday: 18,
                    triggerChange: '+3 vs yesterday',
                    triggerTrend: 'up',
                    purchasesToday: 24,
                    revenueToday: 8750,
                    pendingInquiries: 7,
                    avgResponseTime: '2.3h'
                },
                
                alerts: [
                    {
                        id: 1,
                        eventTitle: 'Lakers vs Warriors',
                        venue: 'Crypto.com Arena',
                        eventDate: 'Dec 25, 2024',
                        targetPrice: 150,
                        currentPrice: 145,
                        status: 'triggered',
                        priority: 'high',
                        customerName: 'John Smith',
                        customerAvatar: 'https://ui-avatars.com/api/?name=John+Smith&background=random',
                        createdAt: '2 hours ago'
                    },
                    {
                        id: 2,
                        eventTitle: 'Chiefs vs Bills',
                        venue: 'Arrowhead Stadium',
                        eventDate: 'Jan 15, 2025',
                        targetPrice: 200,
                        currentPrice: 185,
                        status: 'triggered',
                        priority: 'medium',
                        customerName: 'Sarah Johnson',
                        customerAvatar: 'https://ui-avatars.com/api/?name=Sarah+Johnson&background=random',
                        createdAt: '5 hours ago'
                    },
                    {
                        id: 3,
                        eventTitle: 'Celtics vs Heat',
                        venue: 'TD Garden',
                        eventDate: 'Dec 30, 2024',
                        targetPrice: 120,
                        currentPrice: 135,
                        status: 'pending',
                        priority: 'low',
                        customerName: 'Mike Davis',
                        customerAvatar: 'https://ui-avatars.com/api/?name=Mike+Davis&background=random',
                        createdAt: '1 day ago'
                    }
                ],
                filteredAlerts: [],
                
                purchaseQueue: [
                    {
                        id: 1,
                        eventTitle: 'Lakers vs Warriors',
                        quantity: 2,
                        totalPrice: 350,
                        customerName: 'Alice Brown',
                        urgency: 'high',
                        timeLeft: '15 mins'
                    },
                    {
                        id: 2,
                        eventTitle: 'Chiefs vs Bills',
                        quantity: 4,
                        totalPrice: 740,
                        customerName: 'Bob Wilson',
                        urgency: 'medium',
                        timeLeft: '45 mins'
                    },
                    {
                        id: 3,
                        eventTitle: 'Rangers vs Devils',
                        quantity: 1,
                        totalPrice: 95,
                        customerName: 'Carol Lee',
                        urgency: 'low',
                        timeLeft: '2 hours'
                    }
                ],
                
                customerInquiries: [
                    {
                        id: 1,
                        customerName: 'David Chen',
                        customerAvatar: 'https://ui-avatars.com/api/?name=David+Chen&background=random',
                        subject: 'Ticket availability question',
                        preview: 'Hi, I was wondering if there are any updates on the Lakers...',
                        timestamp: '10 mins ago',
                        urgent: true
                    },
                    {
                        id: 2,
                        customerName: 'Emma Watson',
                        customerAvatar: 'https://ui-avatars.com/api/?name=Emma+Watson&background=random',
                        subject: 'Refund request',
                        preview: 'I need to request a refund for my Chiefs tickets due to...',
                        timestamp: '25 mins ago',
                        urgent: false
                    },
                    {
                        id: 3,
                        customerName: 'Frank Miller',
                        customerAvatar: 'https://ui-avatars.com/api/?name=Frank+Miller&background=random',
                        subject: 'Seat upgrade inquiry',
                        preview: 'Is it possible to upgrade my seats for the upcoming...',
                        timestamp: '1 hour ago',
                        urgent: false
                    }
                ],
                
                monitoredTickets: [
                    {
                        id: 1,
                        eventTitle: 'Lakers vs Warriors',
                        venue: 'Crypto.com Arena',
                        eventDate: 'Dec 25',
                        platform: 'Ticketmaster',
                        currentPrice: 175,
                        priceChange: '+$5',
                        availableTickets: 8,
                        activeAlerts: 3
                    },
                    {
                        id: 2,
                        eventTitle: 'Chiefs vs Bills',
                        venue: 'Arrowhead Stadium',
                        eventDate: 'Jan 15',
                        platform: 'StubHub',
                        currentPrice: 185,
                        priceChange: '-$20',
                        availableTickets: 12,
                        activeAlerts: 5
                    },
                    {
                        id: 3,
                        eventTitle: 'Rangers vs Devils',
                        venue: 'Madison Square Garden',
                        eventDate: 'Dec 28',
                        platform: 'SeatGeek',
                        currentPrice: 95,
                        priceChange: '+$10',
                        availableTickets: 15,
                        activeAlerts: 2
                    }
                ],
                
                async init() {
                    this.filteredAlerts = [...this.alerts];
                    this.startAutoRefresh();
                },
                
                filterAlerts() {
                    switch (this.alertFilter) {
                        case 'triggered':
                            this.filteredAlerts = this.alerts.filter(alert => alert.status === 'triggered');
                            break;
                        case 'pending':
                            this.filteredAlerts = this.alerts.filter(alert => alert.status === 'pending');
                            break;
                        case 'high_priority':
                            this.filteredAlerts = this.alerts.filter(alert => alert.priority === 'high');
                            break;
                        default:
                            this.filteredAlerts = [...this.alerts];
                    }
                },
                
                getAlertStatusClass(status) {
                    return status === 'triggered' ? 
                        'border-yellow-200 bg-yellow-50 dark:border-yellow-800 dark:bg-yellow-900/20' : 
                        'border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800';
                },
                
                getAlertIconClass(priority) {
                    const classes = {
                        high: 'bg-red-100 text-red-600 dark:bg-red-900/50 dark:text-red-400',
                        medium: 'bg-yellow-100 text-yellow-600 dark:bg-yellow-900/50 dark:text-yellow-400',
                        low: 'bg-blue-100 text-blue-600 dark:bg-blue-900/50 dark:text-blue-400'
                    };
                    return classes[priority] || classes.low;
                },
                
                getAlertIconPath(status) {
                    return status === 'triggered' ? 
                        'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z' : 
                        'M15 17h5l-5 5v-5zM4 19h6v-2H4v2zM4 14h16v-2H4v2zM4 9h16V7H4v2z';
                },
                
                getPriorityClass(priority) {
                    const classes = {
                        high: 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300',
                        medium: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300',
                        low: 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300'
                    };
                    return classes[priority] || classes.low;
                },
                
                getUrgencyClass(urgency) {
                    const classes = {
                        high: 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300',
                        medium: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300',
                        low: 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300'
                    };
                    return classes[urgency] || classes.medium;
                },
                
                refreshAlerts() {
                    this.loading = true;
                    setTimeout(() => {
                        this.loading = false;
                        // Simulate data refresh
                    }, 1000);
                },
                
                viewAlertDetails(alert) {
                    window.location.href = `/alerts/${alert.id}`;
                },
                
                processAlert(alert) {
                    window.location.href = `/alerts/${alert.id}/process`;
                },
                
                processPurchase(purchase) {
                    window.location.href = `/purchases/${purchase.id}/process`;
                },
                
                viewTicketDetails(ticket) {
                    window.location.href = `/tickets/${ticket.id}`;
                },
                
                toggleAutoRefresh() {
                    this.autoRefresh = !this.autoRefresh;
                    if (this.autoRefresh) {
                        this.startAutoRefresh();
                    }
                },
                
                startAutoRefresh() {
                    if (this.autoRefresh) {
                        const timer = setInterval(() => {
                            if (!this.autoRefresh) {
                                clearInterval(timer);
                                return;
                            }
                            
                            this.nextUpdateIn--;
                            if (this.nextUpdateIn <= 0) {
                                this.refreshData();
                                this.nextUpdateIn = 30;
                            }
                        }, 1000);
                    }
                },
                
                refreshData() {
                    // Simulate real-time data updates
                    this.monitoredTickets.forEach(ticket => {
                        // Random price fluctuation
                        const change = (Math.random() - 0.5) * 10;
                        ticket.currentPrice = Math.max(50, Math.round(ticket.currentPrice + change));
                        ticket.priceChange = change > 0 ? `+$${Math.round(Math.abs(change))}` : `-$${Math.round(Math.abs(change))}`;
                    });
                }
            }));
        });
    </script>
@endsection