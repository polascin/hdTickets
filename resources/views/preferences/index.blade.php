@extends('layouts.app-v2')

@section('title', 'User Preferences')

@section('content')
<div class="min-h-screen bg-gray-50" x-data="userPreferences">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                            <svg class="w-8 h-8 mr-3 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            User Preferences
                        </h1>
                        <p class="mt-1 text-sm text-gray-500">
                            Customize your sports ticket monitoring experience
                        </p>
                    </div>
                    <div class="flex items-center space-x-3">
                        <!-- Sync Status -->
                        <div class="flex items-center px-3 py-1 rounded-full text-sm font-medium" 
                             :class="syncStatus === 'synced' ? 'bg-green-100 text-green-800' : syncStatus === 'syncing' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800'">
                            <div class="w-2 h-2 rounded-full mr-2" 
                                 :class="syncStatus === 'synced' ? 'bg-green-500' : syncStatus === 'syncing' ? 'bg-blue-500 animate-pulse' : 'bg-yellow-500'"></div>
                            <span x-text="syncStatus === 'synced' ? 'Synced' : syncStatus === 'syncing' ? 'Syncing...' : 'Pending'"></span>
                        </div>
                        
                        <!-- Export Button -->
                        <button @click="exportPreferences" 
                                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                            <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Export
                        </button>
                        
                        <!-- Save Button -->
                        <button @click="saveAllPreferences" :disabled="saving"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 disabled:opacity-50">
                            <svg x-show="saving" class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span x-text="saving ? 'Saving...' : 'Save All'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Tab Navigation -->
        <div class="mb-8">
            <nav class="flex space-x-8" aria-label="Tabs">
                <template x-for="tab in tabs" :key="tab.id">
                    <button @click="activeTab = tab.id"
                            :class="activeTab === tab.id ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" x-html="tab.icon"></svg>
                        <span x-text="tab.name"></span>
                        <span x-show="tab.count" class="ml-2 bg-gray-100 text-gray-900 py-0.5 px-2.5 rounded-full text-xs font-medium" x-text="tab.count"></span>
                    </button>
                </template>
            </nav>
        </div>

        <!-- Tab Content -->
        <div class="space-y-8">
            
            <!-- Sports Preferences Tab -->
            <div x-show="activeTab === 'sports'" class="space-y-6">
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Favorite Sports</h3>
                        <p class="mt-1 text-sm text-gray-500">Select the sports you're most interested in monitoring</p>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                            <template x-for="sport in availableSports" :key="sport.id">
                                <label class="relative flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50" 
                                       :class="preferences.sports.includes(sport.id) ? 'border-purple-500 bg-purple-50' : 'border-gray-200'">
                                    <input type="checkbox" 
                                           :value="sport.id" 
                                           x-model="preferences.sports"
                                           class="sr-only">
                                    <div class="flex items-center">
                                        <div class="text-2xl mr-3" x-text="sport.icon"></div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900" x-text="sport.name"></div>
                                            <div class="text-xs text-gray-500" x-text="`${sport.eventCount} events`"></div>
                                        </div>
                                    </div>
                                    <div class="absolute top-2 right-2" x-show="preferences.sports.includes(sport.id)">
                                        <svg class="w-5 h-5 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                </label>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Sports Priority Settings -->
                <div class="bg-white shadow rounded-lg" x-show="preferences.sports.length > 0">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Priority Settings</h3>
                        <p class="mt-1 text-sm text-gray-500">Drag to reorder your sports by priority (highest priority first)</p>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3" x-ref="sportsPriority">
                            <template x-for="(sportId, index) in preferences.sports" :key="sportId">
                                <div class="flex items-center p-3 bg-gray-50 rounded-lg border border-gray-200 cursor-move">
                                    <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                                    </svg>
                                    <div class="flex-1 flex items-center">
                                        <span class="text-2xl mr-3" x-text="getSport(sportId).icon"></span>
                                        <span class="font-medium" x-text="getSport(sportId).name"></span>
                                    </div>
                                    <span class="text-sm text-gray-500" x-text="`Priority ${index + 1}`"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Teams Tab -->
            <div x-show="activeTab === 'teams'" class="space-y-6">
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Favorite Teams</h3>
                                <p class="mt-1 text-sm text-gray-500">Add teams you want to follow and get alerts for</p>
                            </div>
                            <button @click="showAddTeamModal = true" 
                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-purple-700 bg-purple-100 hover:bg-purple-200">
                                <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                Add Team
                            </button>
                        </div>
                    </div>
                    <div class="p-6">
                        <div x-show="preferences.teams.length === 0" class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No favorite teams</h3>
                            <p class="mt-1 text-sm text-gray-500">Add teams to get personalized alerts and recommendations</p>
                        </div>
                        
                        <div x-show="preferences.teams.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <template x-for="team in preferences.teams" :key="team.id">
                                <div class="relative flex items-center p-4 border border-gray-200 rounded-lg">
                                    <img x-show="team.logo" :src="team.logo" :alt="team.name" class="w-10 h-10 rounded-full mr-3">
                                    <div x-show="!team.logo" class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center mr-3">
                                        <span class="text-sm font-bold text-gray-600" x-text="team.name.substring(0, 2).toUpperCase()"></span>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="text-sm font-medium text-gray-900" x-text="team.name"></h4>
                                        <p class="text-sm text-gray-500" x-text="team.sport"></p>
                                        <p class="text-xs text-gray-400" x-text="team.city"></p>
                                    </div>
                                    <div class="absolute top-2 right-2">
                                        <button @click="removeTeam(team.id)" class="text-gray-400 hover:text-red-500">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Venues Tab -->
            <div x-show="activeTab === 'venues'" class="space-y-6">
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Favorite Venues</h3>
                                <p class="mt-1 text-sm text-gray-500">Track events at your preferred venues and locations</p>
                            </div>
                            <button @click="showAddVenueModal = true" 
                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-purple-700 bg-purple-100 hover:bg-purple-200">
                                <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                Add Venue
                            </button>
                        </div>
                    </div>
                    <div class="p-6">
                        <div x-show="preferences.venues.length === 0" class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No favorite venues</h3>
                            <p class="mt-1 text-sm text-gray-500">Add venues to get alerts for events at your preferred locations</p>
                        </div>
                        
                        <div x-show="preferences.venues.length > 0" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <template x-for="venue in preferences.venues" :key="venue.id">
                                <div class="relative flex items-start p-4 border border-gray-200 rounded-lg">
                                    <div class="flex-1">
                                        <h4 class="text-sm font-medium text-gray-900" x-text="venue.name"></h4>
                                        <p class="text-sm text-gray-500 mt-1" x-text="venue.city + ', ' + venue.state"></p>
                                        <p class="text-xs text-gray-400 mt-1" x-text="`Capacity: ${venue.capacity.toLocaleString()}`"></p>
                                        <div class="mt-2 flex flex-wrap gap-1">
                                            <template x-for="sport in venue.sports" :key="sport">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800" x-text="sport"></span>
                                            </template>
                                        </div>
                                    </div>
                                    <div class="absolute top-2 right-2">
                                        <button @click="removeVenue(venue.id)" class="text-gray-400 hover:text-red-500">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Price Preferences Tab -->
            <div x-show="activeTab === 'pricing'" class="space-y-6">
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Price Preferences</h3>
                        <p class="mt-1 text-sm text-gray-500">Set your budget ranges and price alert thresholds</p>
                    </div>
                    <div class="p-6 space-y-6">
                        
                        <!-- Budget Range -->
                        <div>
                            <label class="block text-sm font-medium text-gray-900 mb-3">Typical Budget Range</label>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Minimum ($)</label>
                                    <input type="number" 
                                           x-model="preferences.pricing.budgetMin"
                                           class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Maximum ($)</label>
                                    <input type="number" 
                                           x-model="preferences.pricing.budgetMax"
                                           class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
                                </div>
                            </div>
                        </div>

                        <!-- Price Alert Thresholds -->
                        <div>
                            <label class="block text-sm font-medium text-gray-900 mb-3">Price Drop Alert Thresholds</label>
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <label class="text-sm font-medium text-gray-700">Small Price Drop</label>
                                        <p class="text-xs text-gray-500">Get notified for modest price reductions</p>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <input type="range" 
                                               x-model="preferences.pricing.alertThresholds.small" 
                                               min="1" max="20" step="1"
                                               class="w-24">
                                        <span class="text-sm font-medium w-8" x-text="preferences.pricing.alertThresholds.small + '%'"></span>
                                    </div>
                                </div>
                                
                                <div class="flex items-center justify-between">
                                    <div>
                                        <label class="text-sm font-medium text-gray-700">Medium Price Drop</label>
                                        <p class="text-xs text-gray-500">Alert for significant price reductions</p>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <input type="range" 
                                               x-model="preferences.pricing.alertThresholds.medium" 
                                               min="10" max="40" step="1"
                                               class="w-24">
                                        <span class="text-sm font-medium w-8" x-text="preferences.pricing.alertThresholds.medium + '%'"></span>
                                    </div>
                                </div>
                                
                                <div class="flex items-center justify-between">
                                    <div>
                                        <label class="text-sm font-medium text-gray-700">Large Price Drop</label>
                                        <p class="text-xs text-gray-500">Alert for major bargain opportunities</p>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <input type="range" 
                                               x-model="preferences.pricing.alertThresholds.large" 
                                               min="20" max="70" step="5"
                                               class="w-24">
                                        <span class="text-sm font-medium w-8" x-text="preferences.pricing.alertThresholds.large + '%'"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Price Strategy Preference -->
                        <div>
                            <label class="block text-sm font-medium text-gray-900 mb-3">Preferred Purchase Strategy</label>
                            <div class="space-y-3">
                                <template x-for="strategy in priceStrategies" :key="strategy.id">
                                    <label class="relative flex items-start p-4 border rounded-lg cursor-pointer hover:bg-gray-50"
                                           :class="preferences.pricing.strategy === strategy.id ? 'border-purple-500 bg-purple-50' : 'border-gray-200'">
                                        <input type="radio" 
                                               :value="strategy.id" 
                                               x-model="preferences.pricing.strategy"
                                               class="sr-only">
                                        <div class="flex-1">
                                            <div class="flex items-center">
                                                <div class="text-lg mr-3" x-text="strategy.icon"></div>
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900" x-text="strategy.name"></div>
                                                    <div class="text-sm text-gray-500" x-text="strategy.description"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="ml-4" x-show="preferences.pricing.strategy === strategy.id">
                                            <svg class="w-5 h-5 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                    </label>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Location Preferences Tab -->
            <div x-show="activeTab === 'location'" class="space-y-6">
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Location Preferences</h3>
                        <p class="mt-1 text-sm text-gray-500">Set your preferred cities and travel distance</p>
                    </div>
                    <div class="p-6 space-y-6">
                        
                        <!-- Primary Location -->
                        <div>
                            <label class="block text-sm font-medium text-gray-900 mb-2">Primary Location</label>
                            <input type="text" 
                                   x-model="preferences.location.primary"
                                   placeholder="Enter your city (e.g., New York, NY)"
                                   class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
                            <p class="mt-1 text-xs text-gray-500">This will be used to prioritize nearby events</p>
                        </div>

                        <!-- Secondary Locations -->
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <label class="block text-sm font-medium text-gray-900">Secondary Locations</label>
                                <button @click="addSecondaryLocation()" 
                                        class="inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded text-purple-700 bg-purple-100 hover:bg-purple-200">
                                    <svg class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                    Add Location
                                </button>
                            </div>
                            <div class="space-y-2" x-show="preferences.location.secondary.length > 0">
                                <template x-for="(location, index) in preferences.location.secondary" :key="index">
                                    <div class="flex items-center space-x-2">
                                        <input type="text" 
                                               x-model="preferences.location.secondary[index]"
                                               placeholder="Enter city"
                                               class="flex-1 border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
                                        <button @click="removeSecondaryLocation(index)" class="text-red-500 hover:text-red-700">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Travel Distance -->
                        <div>
                            <label class="block text-sm font-medium text-gray-900 mb-3">Maximum Travel Distance</label>
                            <div class="flex items-center space-x-4">
                                <input type="range" 
                                       x-model="preferences.location.maxDistance" 
                                       min="0" max="500" step="25"
                                       class="flex-1">
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm font-medium" x-text="preferences.location.maxDistance"></span>
                                    <span class="text-sm text-gray-500">miles</span>
                                </div>
                            </div>
                            <p class="mt-1 text-xs text-gray-500" x-text="preferences.location.maxDistance == 0 ? 'Local events only' : preferences.location.maxDistance >= 500 ? 'No distance limit' : `Events within ${preferences.location.maxDistance} miles`"></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Advanced Settings Tab -->
            <div x-show="activeTab === 'advanced'" class="space-y-6">
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Advanced Settings</h3>
                        <p class="mt-1 text-sm text-gray-500">Fine-tune your monitoring and alert preferences</p>
                    </div>
                    <div class="p-6 space-y-6">
                        
                        <!-- Alert Frequency -->
                        <div>
                            <label class="block text-sm font-medium text-gray-900 mb-3">Alert Frequency</label>
                            <select x-model="preferences.advanced.alertFrequency"
                                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
                                <option value="real-time">Real-time (immediate)</option>
                                <option value="hourly">Hourly digest</option>
                                <option value="daily">Daily summary</option>
                                <option value="weekly">Weekly roundup</option>
                            </select>
                        </div>

                        <!-- Monitoring Window -->
                        <div>
                            <label class="block text-sm font-medium text-gray-900 mb-3">Event Monitoring Window</label>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Days in advance</label>
                                    <input type="number" 
                                           x-model="preferences.advanced.monitoringWindow.days"
                                           min="1" max="365"
                                           class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Hours before event</label>
                                    <input type="number" 
                                           x-model="preferences.advanced.monitoringWindow.hours"
                                           min="1" max="72"
                                           class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
                                </div>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Monitor events from now until the specified time before the event starts</p>
                        </div>

                        <!-- Data Preferences -->
                        <div>
                            <label class="block text-sm font-medium text-gray-900 mb-3">Data & Privacy</label>
                            <div class="space-y-3">
                                <label class="flex items-center">
                                    <input type="checkbox" 
                                           x-model="preferences.advanced.dataCollection.analytics"
                                           class="rounded border-gray-300 text-purple-600 shadow-sm focus:border-purple-300 focus:ring focus:ring-purple-200 focus:ring-opacity-50">
                                    <span class="ml-2 text-sm text-gray-700">Allow analytics data collection to improve recommendations</span>
                                </label>
                                
                                <label class="flex items-center">
                                    <input type="checkbox" 
                                           x-model="preferences.advanced.dataCollection.personalization"
                                           class="rounded border-gray-300 text-purple-600 shadow-sm focus:border-purple-300 focus:ring focus:ring-purple-200 focus:ring-opacity-50">
                                    <span class="ml-2 text-sm text-gray-700">Enable personalized content and recommendations</span>
                                </label>
                                
                                <label class="flex items-center">
                                    <input type="checkbox" 
                                           x-model="preferences.advanced.dataCollection.marketing"
                                           class="rounded border-gray-300 text-purple-600 shadow-sm focus:border-purple-300 focus:ring focus:ring-purple-200 focus:ring-opacity-50">
                                    <span class="ml-2 text-sm text-gray-700">Receive personalized marketing communications</span>
                                </label>
                            </div>
                        </div>

                        <!-- Auto-Actions -->
                        <div>
                            <label class="block text-sm font-medium text-gray-900 mb-3">Automation Settings</label>
                            <div class="space-y-3">
                                <label class="flex items-center">
                                    <input type="checkbox" 
                                           x-model="preferences.advanced.automation.autoBookmark"
                                           class="rounded border-gray-300 text-purple-600 shadow-sm focus:border-purple-300 focus:ring focus:ring-purple-200 focus:ring-opacity-50">
                                    <span class="ml-2 text-sm text-gray-700">Auto-bookmark events from favorite teams</span>
                                </label>
                                
                                <label class="flex items-center">
                                    <input type="checkbox" 
                                           x-model="preferences.advanced.automation.autoAlert"
                                           class="rounded border-gray-300 text-purple-600 shadow-sm focus:border-purple-300 focus:ring focus:ring-purple-200 focus:ring-opacity-50">
                                    <span class="ml-2 text-sm text-gray-700">Automatically create alerts for matching events</span>
                                </label>
                                
                                <label class="flex items-center">
                                    <input type="checkbox" 
                                           x-model="preferences.advanced.automation.smartSuggestions"
                                           class="rounded border-gray-300 text-purple-600 shadow-sm focus:border-purple-300 focus:ring focus:ring-purple-200 focus:ring-opacity-50">
                                    <span class="ml-2 text-sm text-gray-700">Enable AI-powered smart suggestions</span>
                                </label>
                            </div>
                        </div>

                        <!-- Reset Section -->
                        <div class="pt-6 border-t border-gray-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="text-sm font-medium text-gray-900">Reset Preferences</h4>
                                    <p class="text-xs text-gray-500">Restore all settings to their default values</p>
                                </div>
                                <button @click="resetAllPreferences" 
                                        class="inline-flex items-center px-3 py-2 border border-red-300 shadow-sm text-sm leading-4 font-medium rounded-md text-red-700 bg-white hover:bg-red-50">
                                    <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    Reset All
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Team Modal -->
    <div x-show="showAddTeamModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click.away="showAddTeamModal = false">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Add Favorite Team</h3>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search Teams</label>
                    <input type="text" 
                           x-model="teamSearch"
                           @input="searchTeams"
                           placeholder="Start typing team name..."
                           class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
                </div>

                <div x-show="teamSearchResults.length > 0" class="mb-4 max-h-48 overflow-y-auto border border-gray-200 rounded-md">
                    <template x-for="team in teamSearchResults" :key="team.id">
                        <div @click="selectTeam(team)" 
                             class="p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0">
                            <div class="flex items-center">
                                <img x-show="team.logo" :src="team.logo" :alt="team.name" class="w-8 h-8 rounded-full mr-3">
                                <div x-show="!team.logo" class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center mr-3">
                                    <span class="text-xs font-bold text-gray-600" x-text="team.name.substring(0, 2).toUpperCase()"></span>
                                </div>
                                <div>
                                    <div class="text-sm font-medium" x-text="team.name"></div>
                                    <div class="text-xs text-gray-500" x-text="team.sport + ' • ' + team.city"></div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="flex items-center justify-end space-x-2">
                    <button @click="showAddTeamModal = false" 
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Venue Modal -->
    <div x-show="showAddVenueModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click.away="showAddVenueModal = false">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Add Favorite Venue</h3>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search Venues</label>
                    <input type="text" 
                           x-model="venueSearch"
                           @input="searchVenues"
                           placeholder="Start typing venue name..."
                           class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
                </div>

                <div x-show="venueSearchResults.length > 0" class="mb-4 max-h-48 overflow-y-auto border border-gray-200 rounded-md">
                    <template x-for="venue in venueSearchResults" :key="venue.id">
                        <div @click="selectVenue(venue)" 
                             class="p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0">
                            <div>
                                <div class="text-sm font-medium" x-text="venue.name"></div>
                                <div class="text-xs text-gray-500" x-text="venue.city + ', ' + venue.state + ' • ' + venue.capacity.toLocaleString() + ' capacity'"></div>
                                <div class="text-xs text-gray-400 mt-1" x-text="venue.sports.join(', ')"></div>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="flex items-center justify-end space-x-2">
                    <button @click="showAddVenueModal = false" 
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Alpine.js Component Script -->
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('userPreferences', () => ({
        activeTab: 'sports',
        saving: false,
        syncStatus: 'synced',
        showAddTeamModal: false,
        showAddVenueModal: false,
        teamSearch: '',
        venueSearch: '',
        teamSearchResults: [],
        venueSearchResults: [],
        
        tabs: [
            { 
                id: 'sports', 
                name: 'Sports', 
                icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />',
                count: 0
            },
            { 
                id: 'teams', 
                name: 'Teams', 
                icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />',
                count: 0
            },
            { 
                id: 'venues', 
                name: 'Venues', 
                icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />',
                count: 0
            },
            { 
                id: 'pricing', 
                name: 'Pricing', 
                icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.745 3.745 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.745 3.745 0 013.296-1.043A3.745 3.745 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.745 3.745 0 013.296 1.043 3.745 3.745 0 011.043 3.296A3.745 3.745 0 0121 12z" />',
                count: 0
            },
            { 
                id: 'location', 
                name: 'Location', 
                icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />',
                count: 0
            },
            { 
                id: 'advanced', 
                name: 'Advanced', 
                icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4" />',
                count: 0
            }
        ],
        
        availableSports: [
            { id: 'football', name: 'Football', icon: '🏈', eventCount: 1247 },
            { id: 'basketball', name: 'Basketball', icon: '🏀', eventCount: 892 },
            { id: 'baseball', name: 'Baseball', icon: '⚾', eventCount: 2156 },
            { id: 'hockey', name: 'Hockey', icon: '🏒', eventCount: 634 },
            { id: 'soccer', name: 'Soccer', icon: '⚽', eventCount: 978 },
            { id: 'tennis', name: 'Tennis', icon: '🎾', eventCount: 432 },
            { id: 'golf', name: 'Golf', icon: '⛳', eventCount: 234 },
            { id: 'racing', name: 'Racing', icon: '🏎️', eventCount: 156 },
            { id: 'wrestling', name: 'Wrestling', icon: '🤼', eventCount: 89 },
            { id: 'boxing', name: 'Boxing', icon: '🥊', eventCount: 67 },
            { id: 'mma', name: 'MMA', icon: '🥋', eventCount: 45 },
            { id: 'other', name: 'Other', icon: '🎪', eventCount: 123 }
        ],
        
        priceStrategies: [
            {
                id: 'budget',
                name: 'Budget Conscious',
                description: 'Focus on finding the best deals and discounts',
                icon: '💰'
            },
            {
                id: 'balanced',
                name: 'Balanced Approach',
                description: 'Balance between price and seating quality',
                icon: '⚖️'
            },
            {
                id: 'premium',
                name: 'Premium Experience',
                description: 'Prioritize best seats and premium experiences',
                icon: '⭐'
            }
        ],

        preferences: {
            sports: ['football', 'basketball'],
            teams: [],
            venues: [],
            pricing: {
                budgetMin: 50,
                budgetMax: 300,
                alertThresholds: {
                    small: 5,
                    medium: 15,
                    large: 30
                },
                strategy: 'balanced'
            },
            location: {
                primary: '',
                secondary: [],
                maxDistance: 100
            },
            advanced: {
                alertFrequency: 'real-time',
                monitoringWindow: {
                    days: 30,
                    hours: 24
                },
                dataCollection: {
                    analytics: true,
                    personalization: true,
                    marketing: false
                },
                automation: {
                    autoBookmark: false,
                    autoAlert: true,
                    smartSuggestions: true
                }
            }
        },
        
        init() {
            this.loadUserPreferences();
            this.updateTabCounts();
        },
        
        async loadUserPreferences() {
            try {
                const response = await fetch('/api/preferences', {
                    headers: {
                        'Authorization': `Bearer ${this.getAuthToken()}`,
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    if (data.preferences) {
                        this.preferences = { ...this.preferences, ...data.preferences };
                    }
                    this.updateTabCounts();
                }
            } catch (error) {
                console.error('Error loading preferences:', error);
            }
        },
        
        async saveAllPreferences() {
            this.saving = true;
            this.syncStatus = 'syncing';
            
            try {
                const response = await fetch('/api/preferences', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ preferences: this.preferences })
                });
                
                if (response.ok) {
                    this.syncStatus = 'synced';
                    this.showSuccess('Preferences saved successfully');
                    this.updateTabCounts();
                } else {
                    throw new Error('Failed to save preferences');
                }
            } catch (error) {
                this.syncStatus = 'pending';
                this.showError('Failed to save preferences');
                console.error('Error saving preferences:', error);
            } finally {
                this.saving = false;
            }
        },
        
        async searchTeams() {
            if (this.teamSearch.length < 2) {
                this.teamSearchResults = [];
                return;
            }
            
            try {
                const response = await fetch(`/api/preferences/teams/search?q=${encodeURIComponent(this.teamSearch)}`, {
                    headers: {
                        'Authorization': `Bearer ${this.getAuthToken()}`,
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    this.teamSearchResults = data.teams || [];
                }
            } catch (error) {
                console.error('Error searching teams:', error);
            }
        },
        
        async searchVenues() {
            if (this.venueSearch.length < 2) {
                this.venueSearchResults = [];
                return;
            }
            
            try {
                const response = await fetch(`/api/preferences/venues/search?q=${encodeURIComponent(this.venueSearch)}`, {
                    headers: {
                        'Authorization': `Bearer ${this.getAuthToken()}`,
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    this.venueSearchResults = data.venues || [];
                }
            } catch (error) {
                console.error('Error searching venues:', error);
            }
        },
        
        selectTeam(team) {
            if (!this.preferences.teams.find(t => t.id === team.id)) {
                this.preferences.teams.push(team);
                this.updateTabCounts();
            }
            this.showAddTeamModal = false;
            this.teamSearch = '';
            this.teamSearchResults = [];
        },
        
        selectVenue(venue) {
            if (!this.preferences.venues.find(v => v.id === venue.id)) {
                this.preferences.venues.push(venue);
                this.updateTabCounts();
            }
            this.showAddVenueModal = false;
            this.venueSearch = '';
            this.venueSearchResults = [];
        },
        
        removeTeam(teamId) {
            this.preferences.teams = this.preferences.teams.filter(t => t.id !== teamId);
            this.updateTabCounts();
        },
        
        removeVenue(venueId) {
            this.preferences.venues = this.preferences.venues.filter(v => v.id !== venueId);
            this.updateTabCounts();
        },
        
        getSport(sportId) {
            return this.availableSports.find(s => s.id === sportId) || {};
        },
        
        addSecondaryLocation() {
            this.preferences.location.secondary.push('');
        },
        
        removeSecondaryLocation(index) {
            this.preferences.location.secondary.splice(index, 1);
        },
        
        async resetAllPreferences() {
            if (confirm('Are you sure you want to reset all preferences to defaults? This action cannot be undone.')) {
                this.preferences = {
                    sports: [],
                    teams: [],
                    venues: [],
                    pricing: {
                        budgetMin: 50,
                        budgetMax: 300,
                        alertThresholds: {
                            small: 5,
                            medium: 15,
                            large: 30
                        },
                        strategy: 'balanced'
                    },
                    location: {
                        primary: '',
                        secondary: [],
                        maxDistance: 100
                    },
                    advanced: {
                        alertFrequency: 'real-time',
                        monitoringWindow: {
                            days: 30,
                            hours: 24
                        },
                        dataCollection: {
                            analytics: true,
                            personalization: true,
                            marketing: false
                        },
                        automation: {
                            autoBookmark: false,
                            autoAlert: true,
                            smartSuggestions: true
                        }
                    }
                };
                this.updateTabCounts();
                await this.saveAllPreferences();
            }
        },
        
        async exportPreferences() {
            try {
                const response = await fetch('/api/preferences/export', {
                    headers: {
                        'Authorization': `Bearer ${this.getAuthToken()}`,
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                
                if (response.ok) {
                    const blob = await response.blob();
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `hd-tickets-preferences-${new Date().toISOString().split('T')[0]}.json`;
                    a.click();
                    window.URL.revokeObjectURL(url);
                    this.showSuccess('Preferences exported successfully');
                }
            } catch (error) {
                this.showError('Failed to export preferences');
                console.error('Error exporting preferences:', error);
            }
        },
        
        updateTabCounts() {
            this.tabs.find(t => t.id === 'sports').count = this.preferences.sports.length;
            this.tabs.find(t => t.id === 'teams').count = this.preferences.teams.length;
            this.tabs.find(t => t.id === 'venues').count = this.preferences.venues.length;
        },
        
        getAuthToken() {
            return document.querySelector('meta[name="auth-token"]')?.getAttribute('content') || 
                   localStorage.getItem('auth_token') || '';
        },
        
        showSuccess(message) {
            window.dispatchEvent(new CustomEvent('notify', {
                detail: {
                    message: message,
                    type: 'success',
                    duration: 3000
                }
            }));
        },
        
        showError(message) {
            window.dispatchEvent(new CustomEvent('notify', {
                detail: {
                    message: message,
                    type: 'error',
                    duration: 5000
                }
            }));
        }
    }));
});
</script>
@endsection
