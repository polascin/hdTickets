@extends('layouts.app')

@section('title', 'Notifications - HD Tickets')

@section('content')
<div class="min-h-screen bg-gray-50" x-data="notificationsCenter()">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="py-6">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Notifications</h1>
                        <p class="mt-2 text-gray-600">Stay updated with price alerts, ticket availability, and system updates</p>
                    </div>
                    <div class="flex items-center space-x-3">
                        <!-- Mark All Read -->
                        <button 
                            @click="markAllAsRead"
                            :disabled="unreadCount === 0"
                            class="inline-flex items-center px-4 py-2 bg-purple-600 text-white font-medium rounded-lg hover:bg-purple-700 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors duration-200"
                        >
                            <svg class="h-4 w-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            Mark All Read
                        </button>
                        <!-- Settings -->
                        <button 
                            @click="showSettings = true"
                            class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition-colors duration-200"
                        >
                            <svg class="h-4 w-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
                            </svg>
                            Settings
                        </button>
                    </div>
                </div>
                
                <!-- Stats Bar -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-6">
                    <div class="bg-blue-50 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="p-2 bg-blue-100 rounded-lg">
                                <svg class="h-6 w-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"></path>
                                    <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-blue-900">Total</p>
                                <p class="text-2xl font-bold text-blue-600" x-text="notifications.length"></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-green-50 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="p-2 bg-green-100 rounded-lg">
                                <svg class="h-6 w-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-green-900">Price Alerts</p>
                                <p class="text-2xl font-bold text-green-600" x-text="priceAlertCount"></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-yellow-50 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="p-2 bg-yellow-100 rounded-lg">
                                <svg class="h-6 w-6 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-yellow-900">Availability</p>
                                <p class="text-2xl font-bold text-yellow-600" x-text="availabilityAlertCount"></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-red-50 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="p-2 bg-red-100 rounded-lg">
                                <svg class="h-6 w-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-red-900">Unread</p>
                                <p class="text-2xl font-bold text-red-600" x-text="unreadCount"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Sidebar -->
            <div class="w-full lg:w-80 space-y-6">
                <!-- Filter Tabs -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Filter Notifications</h3>
                    <div class="space-y-2">
                        <button 
                            @click="activeFilter = 'all'"
                            :class="activeFilter === 'all' ? 'bg-purple-50 text-purple-700 border-purple-200' : 'text-gray-700 hover:bg-gray-50'"
                            class="w-full flex items-center justify-between px-3 py-2 text-left rounded-lg border transition-colors duration-200"
                        >
                            <span class="flex items-center">
                                <svg class="h-4 w-4 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" />
                                </svg>
                                All
                            </span>
                            <span class="text-sm font-medium" x-text="notifications.length"></span>
                        </button>
                        
                        <button 
                            @click="activeFilter = 'unread'"
                            :class="activeFilter === 'unread' ? 'bg-purple-50 text-purple-700 border-purple-200' : 'text-gray-700 hover:bg-gray-50'"
                            class="w-full flex items-center justify-between px-3 py-2 text-left rounded-lg border transition-colors duration-200"
                        >
                            <span class="flex items-center">
                                <svg class="h-4 w-4 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                </svg>
                                Unread
                            </span>
                            <span class="text-sm font-medium" x-text="unreadCount"></span>
                        </button>
                        
                        <button 
                            @click="activeFilter = 'price_alert'"
                            :class="activeFilter === 'price_alert' ? 'bg-purple-50 text-purple-700 border-purple-200' : 'text-gray-700 hover:bg-gray-50'"
                            class="w-full flex items-center justify-between px-3 py-2 text-left rounded-lg border transition-colors duration-200"
                        >
                            <span class="flex items-center">
                                <svg class="h-4 w-4 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z" />
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd" />
                                </svg>
                                Price Alerts
                            </span>
                            <span class="text-sm font-medium" x-text="priceAlertCount"></span>
                        </button>
                        
                        <button 
                            @click="activeFilter = 'availability'"
                            :class="activeFilter === 'availability' ? 'bg-purple-50 text-purple-700 border-purple-200' : 'text-gray-700 hover:bg-gray-50'"
                            class="w-full flex items-center justify-between px-3 py-2 text-left rounded-lg border transition-colors duration-200"
                        >
                            <span class="flex items-center">
                                <svg class="h-4 w-4 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                                </svg>
                                Availability
                            </span>
                            <span class="text-sm font-medium" x-text="availabilityAlertCount"></span>
                        </button>
                        
                        <button 
                            @click="activeFilter = 'system'"
                            :class="activeFilter === 'system' ? 'bg-purple-50 text-purple-700 border-purple-200' : 'text-gray-700 hover:bg-gray-50'"
                            class="w-full flex items-center justify-between px-3 py-2 text-left rounded-lg border transition-colors duration-200"
                        >
                            <span class="flex items-center">
                                <svg class="h-4 w-4 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M12.316 3.051a1 1 0 01.633 1.265l-4 12a1 1 0 11-1.898-.632l4-12a1 1 0 011.265-.633zM5.707 6.293a1 1 0 010 1.414L3.414 10l2.293 2.293a1 1 0 11-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0zm8.586 0a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 11-1.414-1.414L16.586 10l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                                System
                            </span>
                            <span class="text-sm font-medium" x-text="systemAlertCount"></span>
                        </button>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                    <div class="space-y-2">
                        <button 
                            @click="deleteRead"
                            :disabled="readCount === 0"
                            class="w-full text-left px-3 py-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors duration-200 disabled:text-gray-400 disabled:hover:bg-transparent"
                        >
                            <div class="flex items-center">
                                <svg class="h-4 w-4 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" clip-rule="evenodd" />
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                Delete Read Notifications
                            </div>
                        </button>
                        
                        <button 
                            @click="exportNotifications"
                            class="w-full text-left px-3 py-2 text-gray-700 hover:bg-gray-50 rounded-lg transition-colors duration-200"
                        >
                            <div class="flex items-center">
                                <svg class="h-4 w-4 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                                Export History
                            </div>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="flex-1">
                <!-- Loading State -->
                <div x-show="loading" class="flex items-center justify-center py-12">
                    <div class="inline-flex items-center px-4 py-2 font-semibold leading-6 text-sm shadow rounded-md text-purple-600 bg-white">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-purple-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Loading notifications...
                    </div>
                </div>

                <!-- Empty State -->
                <div x-show="!loading && filteredNotifications.length === 0" class="text-center py-12">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                        <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM9 12v3m4-6v4m4-7v2M5 15l4-2-4-2m0 4h4m0 0v-3m0 3l-4-2"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No notifications</h3>
                    <p class="text-gray-600 mb-4">You're all caught up! No new notifications to show.</p>
                    <button 
                        @click="refreshNotifications"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700"
                    >
                        Refresh
                    </button>
                </div>

                <!-- Notifications List -->
                <div x-show="!loading && filteredNotifications.length > 0" class="space-y-4">
                    <template x-for="notification in filteredNotifications" :key="notification.id">
                        <div 
                            class="bg-white rounded-lg shadow-sm border border-gray-200 transition-all duration-200 hover:shadow-md"
                            :class="notification.read_at ? 'opacity-75' : 'border-l-4 border-l-purple-500'"
                        >
                            <div class="p-6">
                                <div class="flex items-start justify-between">
                                    <div class="flex items-start space-x-4">
                                        <!-- Icon -->
                                        <div 
                                            class="flex-shrink-0 p-2 rounded-full"
                                            :class="getNotificationIconClass(notification.type)"
                                        >
                                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20" x-html="getNotificationIcon(notification.type)"></svg>
                                        </div>
                                        
                                        <!-- Content -->
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center space-x-2 mb-2">
                                                <h3 class="text-lg font-semibold text-gray-900" x-text="notification.title"></h3>
                                                <span 
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                                                    :class="getNotificationTypeClass(notification.type)"
                                                    x-text="getNotificationTypeLabel(notification.type)"
                                                ></span>
                                                <span x-show="!notification.read_at" class="w-2 h-2 bg-purple-500 rounded-full"></span>
                                            </div>
                                            
                                            <p class="text-gray-600 mb-3" x-text="notification.message"></p>
                                            
                                            <!-- Notification Data -->
                                            <div x-show="notification.data" class="bg-gray-50 rounded-lg p-3 mb-3">
                                                <template x-if="notification.type === 'price_alert'">
                                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                                        <div>
                                                            <span class="text-gray-500">Event:</span>
                                                            <span class="font-medium text-gray-900 ml-1" x-text="notification.data?.event_title"></span>
                                                        </div>
                                                        <div>
                                                            <span class="text-gray-500">Old Price:</span>
                                                            <span class="font-medium text-gray-900 ml-1">$<span x-text="notification.data?.old_price"></span></span>
                                                        </div>
                                                        <div>
                                                            <span class="text-gray-500">New Price:</span>
                                                            <span class="font-bold text-green-600 ml-1">$<span x-text="notification.data?.new_price"></span></span>
                                                        </div>
                                                        <div>
                                                            <span class="text-gray-500">Savings:</span>
                                                            <span class="font-bold text-green-600 ml-1">$<span x-text="(notification.data?.old_price - notification.data?.new_price).toFixed(2)"></span></span>
                                                        </div>
                                                    </div>
                                                </template>
                                                
                                                <template x-if="notification.type === 'availability_alert'">
                                                    <div class="flex items-center justify-between text-sm">
                                                        <div>
                                                            <span class="text-gray-500">Event:</span>
                                                            <span class="font-medium text-gray-900 ml-1" x-text="notification.data?.event_title"></span>
                                                        </div>
                                                        <div>
                                                            <span class="text-gray-500">Status:</span>
                                                            <span class="font-bold text-blue-600 ml-1" x-text="notification.data?.status"></span>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                            
                                            <!-- Actions -->
                                            <div class="flex items-center space-x-4 text-sm">
                                                <span class="text-gray-500" x-text="formatRelativeTime(notification.created_at)"></span>
                                                
                                                <template x-if="notification.data?.ticket_id">
                                                    <button 
                                                        @click="viewTicket(notification.data.ticket_id)"
                                                        class="text-purple-600 hover:text-purple-800 font-medium"
                                                    >
                                                        View Ticket
                                                    </button>
                                                </template>
                                                
                                                <template x-if="notification.data?.action_url">
                                                    <button 
                                                        @click="window.open(notification.data.action_url, '_blank')"
                                                        class="text-purple-600 hover:text-purple-800 font-medium"
                                                    >
                                                        Take Action
                                                    </button>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Actions Dropdown -->
                                    <div class="flex items-start space-x-2">
                                        <button 
                                            v-show="!notification.read_at"
                                            @click="markAsRead(notification.id)"
                                            class="text-gray-400 hover:text-gray-600 p-1"
                                            title="Mark as read"
                                        >
                                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                        
                                        <div x-data="{ open: false }" class="relative">
                                            <button 
                                                @click="open = !open"
                                                class="text-gray-400 hover:text-gray-600 p-1"
                                            >
                                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                                                </svg>
                                            </button>
                                            
                                            <div 
                                                x-show="open" 
                                                @click.away="open = false"
                                                x-transition
                                                class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 border border-gray-200"
                                            >
                                                <div class="py-1">
                                                    <button 
                                                        @click="markAsRead(notification.id); open = false"
                                                        x-show="!notification.read_at"
                                                        class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                                    >
                                                        Mark as read
                                                    </button>
                                                    <button 
                                                        @click="markAsUnread(notification.id); open = false"
                                                        x-show="notification.read_at"
                                                        class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                                    >
                                                        Mark as unread
                                                    </button>
                                                    <button 
                                                        @click="deleteNotification(notification.id); open = false"
                                                        class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50"
                                                    >
                                                        Delete
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Load More -->
                <div x-show="hasMoreNotifications" class="text-center mt-8">
                    <button 
                        @click="loadMoreNotifications"
                        :disabled="loadingMore"
                        class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-purple-600 bg-purple-50 hover:bg-purple-100 disabled:opacity-50"
                    >
                        <span x-show="!loadingMore">Load More</span>
                        <span x-show="loadingMore" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Loading...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Settings Modal -->
    <div 
        x-show="showSettings" 
        x-cloak
        class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50 p-4"
        @click.self="showSettings = false"
    >
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Notification Settings</h3>
                <button 
                    @click="showSettings = false"
                    class="text-gray-400 hover:text-gray-600"
                >
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <div class="p-6 space-y-4">
                <!-- Email Notifications -->
                <div>
                    <label class="flex items-center justify-between">
                        <div>
                            <span class="text-sm font-medium text-gray-900">Email Notifications</span>
                            <p class="text-xs text-gray-600">Receive notifications via email</p>
                        </div>
                        <input 
                            type="checkbox" 
                            x-model="settings.email_notifications"
                            class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded"
                        >
                    </label>
                </div>
                
                <!-- Push Notifications -->
                <div>
                    <label class="flex items-center justify-between">
                        <div>
                            <span class="text-sm font-medium text-gray-900">Push Notifications</span>
                            <p class="text-xs text-gray-600">Receive browser push notifications</p>
                        </div>
                        <input 
                            type="checkbox" 
                            x-model="settings.push_notifications"
                            class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded"
                        >
                    </label>
                </div>
                
                <!-- Price Alert Threshold -->
                <div>
                    <label class="block text-sm font-medium text-gray-900 mb-2">Price Drop Threshold</label>
                    <select 
                        x-model="settings.price_drop_threshold"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                    >
                        <option value="any">Any price drop</option>
                        <option value="5">5% or more</option>
                        <option value="10">10% or more</option>
                        <option value="15">15% or more</option>
                        <option value="20">20% or more</option>
                    </select>
                </div>
                
                <!-- Notification Frequency -->
                <div>
                    <label class="block text-sm font-medium text-gray-900 mb-2">Notification Frequency</label>
                    <select 
                        x-model="settings.notification_frequency"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                    >
                        <option value="instant">Instant</option>
                        <option value="hourly">Hourly digest</option>
                        <option value="daily">Daily digest</option>
                        <option value="weekly">Weekly digest</option>
                    </select>
                </div>
            </div>
            
            <div class="px-6 py-4 border-t border-gray-200 flex justify-between">
                <button 
                    @click="showSettings = false"
                    class="px-4 py-2 text-gray-600 hover:text-gray-800 font-medium"
                >
                    Cancel
                </button>
                <button 
                    @click="saveSettings"
                    :disabled="savingSettings"
                    class="px-6 py-2 bg-purple-600 text-white rounded-lg font-medium hover:bg-purple-700 disabled:opacity-50 transition-colors duration-200"
                >
                    <span x-show="!savingSettings">Save Settings</span>
                    <span x-show="savingSettings">Saving...</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function notificationsCenter() {
    return {
        // State
        loading: true,
        loadingMore: false,
        showSettings: false,
        savingSettings: false,
        
        // Data
        notifications: [],
        currentPage: 1,
        hasMoreNotifications: false,
        
        // Filters
        activeFilter: 'all',
        
        // Settings
        settings: {
            email_notifications: true,
            push_notifications: true,
            price_drop_threshold: 'any',
            notification_frequency: 'instant'
        },
        
        // WebSocket connection
        echo: null,
        
        async init() {
            await this.loadNotifications();
            await this.loadSettings();
            this.initializeWebSocket();
            this.requestNotificationPermission();
        },
        
        async loadNotifications() {
            this.loading = true;
            try {
                const response = await fetch('/api/notifications?page=1');
                const data = await response.json();
                
                if (data.success) {
                    this.notifications = data.notifications || [];
                    this.hasMoreNotifications = data.has_more || false;
                }
            } catch (error) {
                console.error('Error loading notifications:', error);
                this.showNotification('Error', 'Failed to load notifications', 'error');
            } finally {
                this.loading = false;
            }
        },
        
        async loadMoreNotifications() {
            if (this.loadingMore) return;
            
            this.loadingMore = true;
            try {
                const response = await fetch(`/api/notifications?page=${this.currentPage + 1}`);
                const data = await response.json();
                
                if (data.success && data.notifications) {
                    this.notifications.push(...data.notifications);
                    this.currentPage++;
                    this.hasMoreNotifications = data.has_more || false;
                }
            } catch (error) {
                console.error('Error loading more notifications:', error);
            } finally {
                this.loadingMore = false;
            }
        },
        
        async refreshNotifications() {
            this.currentPage = 1;
            await this.loadNotifications();
        },
        
        async loadSettings() {
            try {
                const response = await fetch('/api/notification-settings');
                const data = await response.json();
                
                if (data.success) {
                    this.settings = { ...this.settings, ...data.settings };
                }
            } catch (error) {
                console.error('Error loading settings:', error);
            }
        },
        
        async saveSettings() {
            this.savingSettings = true;
            try {
                const response = await fetch('/api/notification-settings', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(this.settings)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.showSettings = false;
                    this.showNotification('Settings Saved', 'Notification preferences updated', 'success');
                } else {
                    throw new Error(data.message || 'Failed to save settings');
                }
            } catch (error) {
                this.showNotification('Error', 'Failed to save settings', 'error');
            } finally {
                this.savingSettings = false;
            }
        },
        
        initializeWebSocket() {
            if (window.Echo) {
                this.echo = window.Echo.private('notifications.' + window.userId)
                    .listen('PriceAlertTriggered', (e) => {
                        this.addNewNotification(e.notification);
                        this.showBrowserNotification('Price Alert', e.notification.message);
                    })
                    .listen('AvailabilityChanged', (e) => {
                        this.addNewNotification(e.notification);
                        this.showBrowserNotification('Availability Update', e.notification.message);
                    })
                    .listen('SystemNotification', (e) => {
                        this.addNewNotification(e.notification);
                        this.showBrowserNotification('System Update', e.notification.message);
                    });
            }
        },
        
        requestNotificationPermission() {
            if ('Notification' in window && Notification.permission === 'default') {
                Notification.requestPermission();
            }
        },
        
        addNewNotification(notification) {
            // Add to the beginning of the array
            this.notifications.unshift(notification);
            
            // Show toast notification
            this.showNotification(notification.title, notification.message, 'info');
        },
        
        showBrowserNotification(title, message) {
            if ('Notification' in window && Notification.permission === 'granted' && this.settings.push_notifications) {
                new Notification(title, {
                    body: message,
                    icon: '/images/notification-icon.png',
                    tag: 'hd-tickets-notification'
                });
            }
        },
        
        async markAsRead(notificationId) {
            try {
                const response = await fetch(`/api/notifications/${notificationId}/read`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                
                if (response.ok) {
                    const notification = this.notifications.find(n => n.id === notificationId);
                    if (notification) {
                        notification.read_at = new Date().toISOString();
                    }
                }
            } catch (error) {
                console.error('Error marking notification as read:', error);
            }
        },
        
        async markAsUnread(notificationId) {
            try {
                const response = await fetch(`/api/notifications/${notificationId}/unread`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                
                if (response.ok) {
                    const notification = this.notifications.find(n => n.id === notificationId);
                    if (notification) {
                        notification.read_at = null;
                    }
                }
            } catch (error) {
                console.error('Error marking notification as unread:', error);
            }
        },
        
        async markAllAsRead() {
            try {
                const response = await fetch('/api/notifications/mark-all-read', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                
                if (response.ok) {
                    this.notifications.forEach(notification => {
                        notification.read_at = new Date().toISOString();
                    });
                    this.showNotification('Success', 'All notifications marked as read', 'success');
                }
            } catch (error) {
                this.showNotification('Error', 'Failed to mark all as read', 'error');
            }
        },
        
        async deleteNotification(notificationId) {
            if (!confirm('Are you sure you want to delete this notification?')) return;
            
            try {
                const response = await fetch(`/api/notifications/${notificationId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                
                if (response.ok) {
                    this.notifications = this.notifications.filter(n => n.id !== notificationId);
                    this.showNotification('Success', 'Notification deleted', 'success');
                }
            } catch (error) {
                this.showNotification('Error', 'Failed to delete notification', 'error');
            }
        },
        
        async deleteRead() {
            if (!confirm('Are you sure you want to delete all read notifications?')) return;
            
            try {
                const response = await fetch('/api/notifications/delete-read', {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                
                if (response.ok) {
                    this.notifications = this.notifications.filter(n => !n.read_at);
                    this.showNotification('Success', 'Read notifications deleted', 'success');
                }
            } catch (error) {
                this.showNotification('Error', 'Failed to delete read notifications', 'error');
            }
        },
        
        exportNotifications() {
            window.open('/api/notifications/export?format=csv', '_blank');
        },
        
        viewTicket(ticketId) {
            window.location.href = `/tickets/${ticketId}`;
        },
        
        // Computed properties
        get filteredNotifications() {
            switch (this.activeFilter) {
                case 'unread':
                    return this.notifications.filter(n => !n.read_at);
                case 'price_alert':
                    return this.notifications.filter(n => n.type === 'price_alert');
                case 'availability':
                    return this.notifications.filter(n => n.type === 'availability_alert');
                case 'system':
                    return this.notifications.filter(n => n.type === 'system');
                default:
                    return this.notifications;
            }
        },
        
        get unreadCount() {
            return this.notifications.filter(n => !n.read_at).length;
        },
        
        get readCount() {
            return this.notifications.filter(n => n.read_at).length;
        },
        
        get priceAlertCount() {
            return this.notifications.filter(n => n.type === 'price_alert').length;
        },
        
        get availabilityAlertCount() {
            return this.notifications.filter(n => n.type === 'availability_alert').length;
        },
        
        get systemAlertCount() {
            return this.notifications.filter(n => n.type === 'system').length;
        },
        
        // Utility methods
        formatRelativeTime(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diffInMinutes = Math.floor((now - date) / (1000 * 60));
            
            if (diffInMinutes < 1) return 'Just now';
            if (diffInMinutes < 60) return `${diffInMinutes}m ago`;
            if (diffInMinutes < 1440) return `${Math.floor(diffInMinutes / 60)}h ago`;
            if (diffInMinutes < 10080) return `${Math.floor(diffInMinutes / 1440)}d ago`;
            return date.toLocaleDateString();
        },
        
        getNotificationIcon(type) {
            const icons = {
                price_alert: '<path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"/>',
                availability_alert: '<path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>',
                system: '<path fill-rule="evenodd" d="M12.316 3.051a1 1 0 01.633 1.265l-4 12a1 1 0 11-1.898-.632l4-12a1 1 0 011.265-.633zM5.707 6.293a1 1 0 010 1.414L3.414 10l2.293 2.293a1 1 0 11-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0zm8.586 0a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 11-1.414-1.414L16.586 10l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"/>',
                default: '<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>'
            };
            return icons[type] || icons.default;
        },
        
        getNotificationIconClass(type) {
            const classes = {
                price_alert: 'bg-green-100 text-green-600',
                availability_alert: 'bg-blue-100 text-blue-600',
                system: 'bg-gray-100 text-gray-600',
                default: 'bg-purple-100 text-purple-600'
            };
            return classes[type] || classes.default;
        },
        
        getNotificationTypeClass(type) {
            const classes = {
                price_alert: 'bg-green-100 text-green-800',
                availability_alert: 'bg-blue-100 text-blue-800',
                system: 'bg-gray-100 text-gray-800',
                default: 'bg-purple-100 text-purple-800'
            };
            return classes[type] || classes.default;
        },
        
        getNotificationTypeLabel(type) {
            const labels = {
                price_alert: 'Price Alert',
                availability_alert: 'Availability',
                system: 'System',
                default: 'Notification'
            };
            return labels[type] || labels.default;
        },
        
        showNotification(title, message, type = 'info') {
            if (window.hdTicketsFeedback) {
                window.hdTicketsFeedback[type](title, message);
            }
        }
    };
}

// Set user ID for WebSocket channels
window.userId = @json(auth()->id());
</script>
@endsection
