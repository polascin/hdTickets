@extends('layouts.app-v2')

@section('title', 'Notification Settings')

@section('content')
<div class="min-h-screen bg-gray-50" x-data="notificationSettings">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Notification Settings</h1>
                        <p class="mt-1 text-sm text-gray-500">
                            Customize how and when you receive notifications for ticket alerts and system updates
                        </p>
                    </div>
                    <div class="flex items-center space-x-3">
                        <!-- Connection Status -->
                        <div class="flex items-center" x-show="connectionStatus">
                            <div class="flex items-center px-3 py-1 rounded-full text-xs font-medium"
                                 :class="connectionStatus.connected ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                                <div class="w-2 h-2 rounded-full mr-2"
                                     :class="connectionStatus.connected ? 'bg-green-500' : 'bg-red-500'"></div>
                                <span x-text="connectionStatus.connected ? 'Connected' : 'Disconnected'"></span>
                            </div>
                        </div>
                        <!-- Test Button -->
                        <button @click="testNotification" 
                                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5l-5-5h5v-12h5v12z" />
                            </svg>
                            Test Notification
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Main Settings -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- General Settings -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">General Settings</h3>
                        <p class="mt-1 text-sm text-gray-500">Configure your overall notification preferences</p>
                    </div>
                    <div class="p-6 space-y-6">
                        <!-- Enable Notifications -->
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="text-sm font-medium text-gray-900">Enable Notifications</label>
                                <p class="text-sm text-gray-500">Turn all notifications on or off</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" x-model="preferences.notificationsEnabled" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>

                        <!-- Browser Notifications -->
                        <div class="flex items-center justify-between" x-show="preferences.notificationsEnabled">
                            <div>
                                <label class="text-sm font-medium text-gray-900">Browser Notifications</label>
                                <p class="text-sm text-gray-500">Show notifications in your browser</p>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="text-xs text-gray-400" x-text="browserPermissionStatus"></span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" x-model="preferences.browserNotifications" 
                                           :disabled="browserPermissionStatus === 'denied'"
                                           @change="handleBrowserNotificationToggle" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600 disabled:opacity-50"></div>
                                </label>
                            </div>
                        </div>

                        <!-- Sound Notifications -->
                        <div class="flex items-center justify-between" x-show="preferences.notificationsEnabled">
                            <div>
                                <label class="text-sm font-medium text-gray-900">Sound Notifications</label>
                                <p class="text-sm text-gray-500">Play sounds when notifications arrive</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" x-model="preferences.audioEnabled" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>

                        <!-- Vibrate (Mobile) -->
                        <div class="flex items-center justify-between" x-show="preferences.notificationsEnabled && isMobile">
                            <div>
                                <label class="text-sm font-medium text-gray-900">Vibration</label>
                                <p class="text-sm text-gray-500">Vibrate device for notifications</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" x-model="preferences.vibrateEnabled" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Notification Types -->
                <div class="bg-white shadow rounded-lg" x-show="preferences.notificationsEnabled">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Notification Types</h3>
                        <p class="mt-1 text-sm text-gray-500">Choose which types of notifications you want to receive</p>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <!-- Price Alerts -->
                            <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                            <svg class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.745 3.745 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.745 3.745 0 013.296-1.043A3.745 3.745 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.745 3.745 0 013.296 1.043 3.745 3.745 0 011.043 3.296A3.745 3.745 0 0121 12z" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <h4 class="text-sm font-medium text-gray-900">Price Alerts</h4>
                                        <p class="text-sm text-gray-500">When ticket prices drop to your target price</p>
                                    </div>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" x-model="preferences.types.price_alert" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>

                            <!-- Availability Alerts -->
                            <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                            <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <h4 class="text-sm font-medium text-gray-900">Ticket Availability</h4>
                                        <p class="text-sm text-gray-500">When tickets become available for events you're tracking</p>
                                    </div>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" x-model="preferences.types.availability" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>

                            <!-- Purchase Updates -->
                            <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                                            <svg class="w-4 h-4 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m-.4-2L4 3H2m5 10v6a1 1 0 001 1h8a1 1 0 001-1v-6m-9 0V9a1 1 0 011-1h6a1 1 0 011 1v4.01" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <h4 class="text-sm font-medium text-gray-900">Purchase Updates</h4>
                                        <p class="text-sm text-gray-500">Status updates for your ticket purchases</p>
                                    </div>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" x-model="preferences.types.purchase_update" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>

                            <!-- System Notifications -->
                            <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                                            <svg class="w-4 h-4 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <h4 class="text-sm font-medium text-gray-900">System Notifications</h4>
                                        <p class="text-sm text-gray-500">Important system updates and announcements</p>
                                    </div>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" x-model="preferences.types.system" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>

                            <!-- Platform Alerts -->
                            <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                            <svg class="w-4 h-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.314 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <h4 class="text-sm font-medium text-gray-900">Platform Alerts</h4>
                                        <p class="text-sm text-gray-500">Alerts about ticket platform issues or outages</p>
                                    </div>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" x-model="preferences.types.platform_alert" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>

                            <!-- Maintenance Notifications -->
                            <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                                            <svg class="w-4 h-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <h4 class="text-sm font-medium text-gray-900">Maintenance</h4>
                                        <p class="text-sm text-gray-500">Scheduled maintenance and system downtime notifications</p>
                                    </div>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" x-model="preferences.types.maintenance" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quiet Hours -->
                <div class="bg-white shadow rounded-lg" x-show="preferences.notificationsEnabled">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Quiet Hours</h3>
                        <p class="mt-1 text-sm text-gray-500">Set times when you don't want to receive non-urgent notifications</p>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <!-- Enable Quiet Hours -->
                            <div class="flex items-center justify-between">
                                <div>
                                    <label class="text-sm font-medium text-gray-900">Enable Quiet Hours</label>
                                    <p class="text-sm text-gray-500">High priority notifications will still be shown</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" x-model="preferences.quietHours" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>

                            <!-- Time Settings -->
                            <div class="grid grid-cols-2 gap-4" x-show="preferences.quietHours">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Start Time</label>
                                    <input type="time" x-model="preferences.quietHoursStart"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">End Time</label>
                                    <input type="time" x-model="preferences.quietHoursEnd"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Save Button -->
                <div class="flex justify-end">
                    <button @click="savePreferences" :disabled="saving"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg x-show="saving" class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="saving ? 'Saving...' : 'Save Settings'"></span>
                    </button>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                
                <!-- System Status -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">System Status</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">WebSocket Connection</span>
                            <div class="flex items-center">
                                <div class="w-2 h-2 rounded-full mr-2"
                                     :class="connectionStatus?.connected ? 'bg-green-500' : 'bg-red-500'"></div>
                                <span :class="connectionStatus?.connected ? 'text-green-600' : 'text-red-600'"
                                      x-text="connectionStatus?.connected ? 'Connected' : 'Disconnected'"></span>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">Browser Notifications</span>
                            <span :class="browserPermissionStatus === 'granted' ? 'text-green-600' : browserPermissionStatus === 'denied' ? 'text-red-600' : 'text-yellow-600'"
                                  x-text="browserPermissionStatus"></span>
                        </div>
                        
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">Service Worker</span>
                            <span :class="serviceWorkerStatus ? 'text-green-600' : 'text-gray-600'"
                                  x-text="serviceWorkerStatus ? 'Active' : 'Not Available'"></span>
                        </div>
                        
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">Audio Support</span>
                            <span :class="audioSupport ? 'text-green-600' : 'text-gray-600'"
                                  x-text="audioSupport ? 'Available' : 'Not Available'"></span>
                        </div>
                    </div>
                </div>

                <!-- Recent Notifications -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900">Recent Notifications</h3>
                        <button @click="loadNotificationHistory" class="text-sm text-blue-600 hover:text-blue-500">
                            Refresh
                        </button>
                    </div>
                    <div class="max-h-96 overflow-y-auto">
                        <template x-for="notification in recentNotifications" :key="notification.id">
                            <div class="p-4 border-b border-gray-100 last:border-b-0">
                                <div class="flex items-start space-x-3">
                                    <div class="flex-shrink-0 w-2 h-2 mt-2 rounded-full"
                                         :class="getNotificationColor(notification.type)"></div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-gray-900" x-text="notification.title"></p>
                                        <p class="text-sm text-gray-500" x-text="notification.message"></p>
                                        <p class="text-xs text-gray-400 mt-1" x-text="formatTime(notification.timestamp)"></p>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <div x-show="recentNotifications.length === 0" class="p-4 text-center text-gray-500">
                            No recent notifications
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Actions</h3>
                    </div>
                    <div class="p-6 space-y-3">
                        <button @click="testNotification"
                                class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5l-5-5h5v-12h5v12z" />
                            </svg>
                            Test Notification
                        </button>
                        
                        <button @click="clearNotificationHistory"
                                class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Clear History
                        </button>
                        
                        <a href="/notifications"
                           class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5l-5-5h5v-12h5v12z" />
                            </svg>
                            View All Notifications
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Alpine.js Component Script -->
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('notificationSettings', () => ({
        preferences: {
            notificationsEnabled: true,
            browserNotifications: true,
            audioEnabled: true,
            vibrateEnabled: true,
            quietHours: false,
            quietHoursStart: '22:00',
            quietHoursEnd: '08:00',
            types: {
                price_alert: true,
                availability: true,
                system: true,
                purchase_update: true,
                maintenance: false,
                platform_alert: true
            }
        },
        
        saving: false,
        connectionStatus: null,
        browserPermissionStatus: 'default',
        serviceWorkerStatus: false,
        audioSupport: false,
        isMobile: false,
        recentNotifications: [],
        
        init() {
            this.loadPreferences();
            this.checkSystemCapabilities();
            this.loadNotificationHistory();
            this.checkConnectionStatus();
            this.setupEventListeners();
            
            // Check if mobile device
            this.isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        },
        
        loadPreferences() {
            try {
                const stored = localStorage.getItem('hd_notification_preferences');
                if (stored) {
                    this.preferences = { ...this.preferences, ...JSON.parse(stored) };
                }
            } catch (error) {
                console.error('Failed to load preferences:', error);
            }
        },
        
        async savePreferences() {
            this.saving = true;
            
            try {
                // Save to localStorage
                localStorage.setItem('hd_notification_preferences', JSON.stringify(this.preferences));
                
                // Save to server
                const response = await fetch('/api/user/notification-preferences', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(this.preferences)
                });
                
                if (response.ok) {
                    this.showNotification('Settings saved successfully', 'success');
                    
                    // Trigger preferences update event
                    document.dispatchEvent(new CustomEvent('notification-preferences-changed', {
                        detail: this.preferences
                    }));
                } else {
                    throw new Error('Failed to save preferences');
                }
            } catch (error) {
                console.error('Failed to save preferences:', error);
                this.showNotification('Failed to save settings', 'error');
            } finally {
                this.saving = false;
            }
        },
        
        checkSystemCapabilities() {
            // Check browser notification permission
            if ('Notification' in window) {
                this.browserPermissionStatus = Notification.permission;
            }
            
            // Check service worker
            this.serviceWorkerStatus = 'serviceWorker' in navigator;
            
            // Check audio context support
            this.audioSupport = !!(window.AudioContext || window.webkitAudioContext);
        },
        
        async handleBrowserNotificationToggle() {
            if (this.preferences.browserNotifications && this.browserPermissionStatus !== 'granted') {
                try {
                    const permission = await Notification.requestPermission();
                    this.browserPermissionStatus = permission;
                    
                    if (permission !== 'granted') {
                        this.preferences.browserNotifications = false;
                        this.showNotification('Browser notification permission was denied', 'warning');
                    }
                } catch (error) {
                    console.error('Failed to request notification permission:', error);
                    this.preferences.browserNotifications = false;
                }
            }
        },
        
        testNotification() {
            if (window.hdNotifications) {
                window.hdNotifications.testNotification();
            } else {
                // Fallback test notification
                if ('Notification' in window && Notification.permission === 'granted') {
                    new Notification('HD Tickets Test', {
                        body: 'This is a test notification from HD Tickets',
                        icon: '/images/icons/notification-icon.png'
                    });
                } else {
                    this.showNotification('Test notification sent', 'info');
                }
            }
        },
        
        checkConnectionStatus() {
            if (window.hdNotifications) {
                this.connectionStatus = window.hdNotifications.getConnectionStatus();
            }
            
            // Update connection status periodically
            setInterval(() => {
                if (window.hdNotifications) {
                    this.connectionStatus = window.hdNotifications.getConnectionStatus();
                }
            }, 5000);
        },
        
        setupEventListeners() {
            // Listen for notification events
            document.addEventListener('notification-received', (event) => {
                this.loadNotificationHistory();
            });
            
            // Listen for connection changes
            document.addEventListener('notification-connection-changed', (event) => {
                this.checkConnectionStatus();
            });
        },
        
        loadNotificationHistory() {
            try {
                const history = JSON.parse(localStorage.getItem('hd_notification_history') || '[]');
                this.recentNotifications = history.slice(0, 10); // Show last 10
            } catch (error) {
                console.error('Failed to load notification history:', error);
                this.recentNotifications = [];
            }
        },
        
        clearNotificationHistory() {
            if (confirm('Are you sure you want to clear all notification history?')) {
                localStorage.removeItem('hd_notification_history');
                this.recentNotifications = [];
                this.showNotification('Notification history cleared', 'success');
            }
        },
        
        getNotificationColor(type) {
            const colors = {
                'price_alert': 'bg-green-500',
                'availability': 'bg-blue-500',
                'purchase_update': 'bg-purple-500',
                'system': 'bg-yellow-500',
                'platform_alert': 'bg-red-500',
                'maintenance': 'bg-gray-500'
            };
            return colors[type] || 'bg-gray-500';
        },
        
        formatTime(timestamp) {
            const date = new Date(timestamp);
            const now = new Date();
            const diff = now - date;
            
            if (diff < 60000) return 'Just now';
            if (diff < 3600000) return `${Math.floor(diff / 60000)}m ago`;
            if (diff < 86400000) return `${Math.floor(diff / 3600000)}h ago`;
            return date.toLocaleDateString();
        },
        
        showNotification(message, type = 'info') {
            // Trigger toast notification
            window.dispatchEvent(new CustomEvent('notify', {
                detail: {
                    message: message,
                    type: type,
                    duration: 3000
                }
            }));
        }
    }));
});
</script>
@endsection
