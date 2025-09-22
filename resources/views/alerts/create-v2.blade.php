@extends('layouts.modern-app')

@section('title', 'Create Price Alert')

@section('meta_description', 'Set up price alerts for sports events and get notified when tickets reach your target price')

@section('page-header')
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white">
                Create Price Alert 🔔
            </h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Get notified when ticket prices drop to your target range
            </p>
        </div>
        
        <div class="flex items-center gap-3">
            <a href="{{ route('alerts.index') }}" class="hdt-button hdt-button--outline hdt-button--sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Alerts
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div x-data="alertWizard()" x-init="init()" class="max-w-4xl mx-auto">
        
        <!-- Progress Steps -->
        <div class="mb-8">
            <nav class="flex items-center justify-center">
                <ol class="flex items-center space-x-4">
                    <template x-for="(step, index) in steps" :key="index">
                        <li class="flex items-center">
                            <div class="flex items-center space-x-2"
                                 :class="currentStep === index + 1 ? 'text-blue-600 dark:text-blue-400' : currentStep > index + 1 ? 'text-green-600 dark:text-green-400' : 'text-gray-400 dark:text-gray-500'">
                                <div class="w-8 h-8 rounded-full border-2 flex items-center justify-center text-sm font-medium"
                                     :class="currentStep === index + 1 ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : currentStep > index + 1 ? 'border-green-500 bg-green-50 dark:bg-green-900/20' : 'border-gray-300 dark:border-gray-600'">
                                    <template x-if="currentStep > index + 1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </template>
                                    <template x-if="currentStep <= index + 1">
                                        <span x-text="index + 1"></span>
                                    </template>
                                </div>
                                <span class="font-medium" x-text="step.name"></span>
                            </div>
                            <template x-if="index < steps.length - 1">
                                <div class="w-8 h-px bg-gray-300 dark:bg-gray-600 ml-4"></div>
                            </template>
                        </li>
                    </template>
                </ol>
            </nav>
        </div>

        <!-- Step 1: Event Selection -->
        <div x-show="currentStep === 1" x-transition class="hdt-card">
            <div class="hdt-card__header">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Select Event</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">Choose the sports event you want to monitor</p>
            </div>
            
            <div class="hdt-card__body space-y-6">
                
                <!-- Search and Filters -->
                <div class="space-y-4">
                    <div class="relative">
                        <input type="search" 
                               placeholder="Search for events, teams, or venues..."
                               class="w-full hdt-input pl-12"
                               x-model="searchQuery"
                               @input="searchEvents()">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                    </div>
                    
                    <!-- Quick Filters -->
                    <div class="flex flex-wrap gap-3">
                        <template x-for="sport in popularSports" :key="sport.id">
                            <button @click="filterBySport(sport.id)"
                                    :class="selectedSport === sport.id ? 'hdt-button--primary' : 'hdt-button--outline'"
                                    class="hdt-button hdt-button--sm">
                                <span x-text="sport.emoji"></span>
                                <span x-text="sport.name"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Events Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <template x-for="event in filteredEvents" :key="event.id">
                        <div @click="selectEvent(event)"
                             class="border rounded-lg p-4 cursor-pointer transition-all duration-200 hover:shadow-lg"
                             :class="selectedEvent?.id === event.id ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-200 dark:border-gray-700 hover:border-blue-300'">
                            
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-900 dark:text-gray-100" x-text="event.title"></h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1" x-text="event.venue"></p>
                                </div>
                                <span class="hdt-badge hdt-badge--secondary hdt-badge--xs" x-text="event.sport"></span>
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    <p x-text="formatDate(event.date) + ' • ' + event.time"></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">$<span x-text="event.currentPrice"></span></p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Current lowest</p>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Custom Event Input -->
                <div class="border-t pt-6">
                    <div class="flex items-center space-x-2 mb-4">
                        <input type="checkbox" x-model="useCustomEvent" class="rounded border-gray-300 dark:border-gray-600">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Can't find your event? Add custom event details</label>
                    </div>
                    
                    <div x-show="useCustomEvent" x-transition class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="hdt-form-group">
                            <label class="hdt-label">Event Title</label>
                            <input type="text" x-model="customEvent.title" class="hdt-input" placeholder="e.g. Lakers vs Warriors">
                        </div>
                        <div class="hdt-form-group">
                            <label class="hdt-label">Venue</label>
                            <input type="text" x-model="customEvent.venue" class="hdt-input" placeholder="e.g. Crypto.com Arena">
                        </div>
                        <div class="hdt-form-group">
                            <label class="hdt-label">Event Date</label>
                            <input type="date" x-model="customEvent.date" class="hdt-input">
                        </div>
                        <div class="hdt-form-group">
                            <label class="hdt-label">Event Time</label>
                            <input type="time" x-model="customEvent.time" class="hdt-input">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="hdt-card__footer">
                <div class="flex justify-between items-center">
                    <div></div>
                    <button @click="nextStep()"
                            :disabled="!canProceedFromStep1()"
                            class="hdt-button hdt-button--primary hdt-button--md"
                            :class="!canProceedFromStep1() ? 'opacity-50 cursor-not-allowed' : ''">
                        Continue to Price Setup
                    </button>
                </div>
            </div>
        </div>

        <!-- Step 2: Price Threshold -->
        <div x-show="currentStep === 2" x-transition class="hdt-card">
            <div class="hdt-card__header">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Set Price Alert</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">Configure when you want to be notified</p>
            </div>
            
            <div class="hdt-card__body space-y-6">
                
                <!-- Selected Event Summary -->
                <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="font-medium text-gray-900 dark:text-gray-100" x-text="getSelectedEventTitle()"></h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400" x-text="getSelectedEventVenue()"></p>
                        </div>
                        <button @click="prevStep()" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800">
                            Change Event
                        </button>
                    </div>
                </div>

                <!-- Price Alert Configuration -->
                <div class="space-y-6">
                    
                    <!-- Alert Type -->
                    <div class="hdt-form-group">
                        <label class="hdt-label">Alert Type</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-2">
                            <label class="flex items-center p-4 border rounded-lg cursor-pointer transition-colors"
                                   :class="alertConfig.type === 'price_drop' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-200 dark:border-gray-700'">
                                <input type="radio" value="price_drop" x-model="alertConfig.type" class="sr-only">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 bg-green-100 dark:bg-green-900/50 rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-gray-100">Price Drop</p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Alert when price goes below target</p>
                                    </div>
                                </div>
                            </label>
                            
                            <label class="flex items-center p-4 border rounded-lg cursor-pointer transition-colors"
                                   :class="alertConfig.type === 'availability' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-200 dark:border-gray-700'">
                                <input type="radio" value="availability" x-model="alertConfig.type" class="sr-only">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/50 rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-gray-100">Availability</p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Alert when new tickets become available</p>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Price Configuration (for price_drop alerts) -->
                    <div x-show="alertConfig.type === 'price_drop'" x-transition class="space-y-4">
                        <div class="hdt-form-group">
                            <label class="hdt-label">Target Price (USD)</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <span class="text-gray-500 dark:text-gray-400">$</span>
                                </div>
                                <input type="number" 
                                       x-model="alertConfig.targetPrice" 
                                       class="hdt-input pl-8" 
                                       min="1" 
                                       step="0.01"
                                       placeholder="150.00">
                            </div>
                            <div class="flex items-center justify-between mt-2">
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Current price: $<span x-text="getCurrentPrice()"></span>
                                </p>
                                <div class="text-sm">
                                    <template x-if="alertConfig.targetPrice && getCurrentPrice()">
                                        <span :class="alertConfig.targetPrice < getCurrentPrice() ? 'text-green-600 dark:text-green-400' : 'text-orange-600 dark:text-orange-400'">
                                            <template x-if="alertConfig.targetPrice < getCurrentPrice()">
                                                <span>💡 Good target! You'll save $<span x-text="(getCurrentPrice() - alertConfig.targetPrice).toFixed(2)"></span></span>
                                            </template>
                                            <template x-if="alertConfig.targetPrice >= getCurrentPrice()">
                                                <span>⚠️ Target is above current price</span>
                                            </template>
                                        </span>
                                    </template>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Quick Price Options -->
                        <div class="hdt-form-group">
                            <label class="hdt-label">Quick Price Options</label>
                            <div class="flex flex-wrap gap-2 mt-2">
                                <template x-for="percentage in [10, 15, 20, 25]" :key="percentage">
                                    <button @click="setPercentageDiscount(percentage)"
                                            class="hdt-button hdt-button--outline hdt-button--xs">
                                        <span x-text="percentage + '% off'"></span>
                                        <span class="ml-1 text-xs">($<span x-text="calculateDiscountPrice(percentage)"></span>)</span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Availability Configuration -->
                    <div x-show="alertConfig.type === 'availability'" x-transition class="space-y-4">
                        <div class="hdt-form-group">
                            <label class="hdt-label">Preferred Price Range (Optional)</label>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <span class="text-gray-500 dark:text-gray-400">$</span>
                                    </div>
                                    <input type="number" 
                                           x-model="alertConfig.minPrice" 
                                           class="hdt-input pl-8" 
                                           min="0" 
                                           step="0.01"
                                           placeholder="Min price">
                                </div>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <span class="text-gray-500 dark:text-gray-400">$</span>
                                    </div>
                                    <input type="number" 
                                           x-model="alertConfig.maxPrice" 
                                           class="hdt-input pl-8" 
                                           min="0" 
                                           step="0.01"
                                           placeholder="Max price">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="hdt-card__footer">
                <div class="flex justify-between items-center">
                    <button @click="prevStep()" class="hdt-button hdt-button--outline hdt-button--md">
                        Back to Event Selection
                    </button>
                    <button @click="nextStep()"
                            :disabled="!canProceedFromStep2()"
                            class="hdt-button hdt-button--primary hdt-button--md"
                            :class="!canProceedFromStep2() ? 'opacity-50 cursor-not-allowed' : ''">
                        Continue to Notifications
                    </button>
                </div>
            </div>
        </div>

        <!-- Step 3: Notification Settings -->
        <div x-show="currentStep === 3" x-transition class="hdt-card">
            <div class="hdt-card__header">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Notification Preferences</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">Choose how and when you want to be notified</p>
            </div>
            
            <div class="hdt-card__body space-y-6">
                
                <!-- Notification Channels -->
                <div class="hdt-form-group">
                    <label class="hdt-label">How do you want to be notified?</label>
                    <div class="space-y-3 mt-3">
                        <template x-for="channel in notificationChannels" :key="channel.id">
                            <label class="flex items-center p-4 border rounded-lg cursor-pointer transition-colors"
                                   :class="alertConfig.channels.includes(channel.id) ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-200 dark:border-gray-700'">
                                <input type="checkbox" 
                                       :value="channel.id" 
                                       x-model="alertConfig.channels" 
                                       class="rounded border-gray-300 dark:border-gray-600 mr-3">
                                <div class="flex items-center space-x-3 flex-1">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center"
                                         :class="channel.bgClass">
                                        <svg class="w-5 h-5" :class="channel.iconClass" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-html="channel.icon">
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-medium text-gray-900 dark:text-gray-100" x-text="channel.name"></p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400" x-text="channel.description"></p>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-xs px-2 py-1 rounded-full"
                                              :class="channel.available ? 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300' : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300'">
                                            <span x-text="channel.available ? 'Available' : 'Coming Soon'"></span>
                                        </span>
                                    </div>
                                </div>
                            </label>
                        </template>
                    </div>
                </div>

                <!-- Alert Frequency -->
                <div class="hdt-form-group">
                    <label class="hdt-label">Alert Frequency</label>
                    <select x-model="alertConfig.frequency" class="hdt-input">
                        <option value="instant">Instant - notify immediately when criteria is met</option>
                        <option value="hourly">Hourly digest - at most one alert per hour</option>
                        <option value="daily">Daily summary - once per day at preferred time</option>
                        <option value="weekly">Weekly report - summary of all matches</option>
                    </select>
                </div>

                <!-- Preferred Notification Time (for daily/weekly) -->
                <div x-show="['daily', 'weekly'].includes(alertConfig.frequency)" x-transition class="hdt-form-group">
                    <label class="hdt-label">Preferred Notification Time</label>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm text-gray-600 dark:text-gray-400">Time of Day</label>
                            <select x-model="alertConfig.preferredTime" class="hdt-input">
                                <option value="morning">Morning (8:00 AM)</option>
                                <option value="afternoon">Afternoon (2:00 PM)</option>
                                <option value="evening">Evening (6:00 PM)</option>
                                <option value="night">Night (10:00 PM)</option>
                            </select>
                        </div>
                        <div x-show="alertConfig.frequency === 'weekly'">
                            <label class="text-sm text-gray-600 dark:text-gray-400">Day of Week</label>
                            <select x-model="alertConfig.preferredDay" class="hdt-input">
                                <option value="sunday">Sunday</option>
                                <option value="monday">Monday</option>
                                <option value="tuesday">Tuesday</option>
                                <option value="wednesday">Wednesday</option>
                                <option value="thursday">Thursday</option>
                                <option value="friday">Friday</option>
                                <option value="saturday">Saturday</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Alert Expiry -->
                <div class="hdt-form-group">
                    <label class="hdt-label">Alert Duration</label>
                    <select x-model="alertConfig.expiresIn" class="hdt-input">
                        <option value="7">1 Week</option>
                        <option value="30">1 Month</option>
                        <option value="60">2 Months</option>
                        <option value="90">3 Months</option>
                        <option value="180">6 Months</option>
                        <option value="365">1 Year</option>
                        <option value="never">Never expire</option>
                    </select>
                </div>
            </div>
            
            <div class="hdt-card__footer">
                <div class="flex justify-between items-center">
                    <button @click="prevStep()" class="hdt-button hdt-button--outline hdt-button--md">
                        Back to Price Setup
                    </button>
                    <button @click="nextStep()"
                            :disabled="!canProceedFromStep3()"
                            class="hdt-button hdt-button--primary hdt-button--md"
                            :class="!canProceedFromStep3() ? 'opacity-50 cursor-not-allowed' : ''">
                        Review Alert
                    </button>
                </div>
            </div>
        </div>

        <!-- Step 4: Review & Create -->
        <div x-show="currentStep === 4" x-transition class="hdt-card">
            <div class="hdt-card__header">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Review Your Alert</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">Double-check your settings before creating the alert</p>
            </div>
            
            <div class="hdt-card__body space-y-6">
                
                <!-- Alert Summary -->
                <div class="bg-gradient-to-r from-blue-50 to-purple-50 dark:from-blue-900/20 dark:to-purple-900/20 rounded-lg p-6">
                    <div class="flex items-start space-x-4">
                        <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4 19h6v-2H4v2zM4 14h16v-2H4v2zM4 9h16V7H4v2z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-2">
                                <template x-if="alertConfig.type === 'price_drop'">
                                    <span>Price Drop Alert</span>
                                </template>
                                <template x-if="alertConfig.type === 'availability'">
                                    <span>Availability Alert</span>
                                </template>
                            </h4>
                            <p class="text-gray-700 dark:text-gray-300" x-text="getAlertSummary()"></p>
                        </div>
                    </div>
                </div>

                <!-- Detailed Settings -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <!-- Event Details -->
                    <div class="space-y-4">
                        <h5 class="font-medium text-gray-900 dark:text-gray-100">Event Details</h5>
                        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 space-y-2">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Event:</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="getSelectedEventTitle()"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Venue:</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="getSelectedEventVenue()"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Date & Time:</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="getSelectedEventDateTime()"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Alert Configuration -->
                    <div class="space-y-4">
                        <h5 class="font-medium text-gray-900 dark:text-gray-100">Alert Configuration</h5>
                        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 space-y-2">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Alert Type:</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="alertConfig.type === 'price_drop' ? 'Price Drop' : 'Availability'"></span>
                            </div>
                            <template x-if="alertConfig.type === 'price_drop'">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Target Price:</span>
                                    <span class="text-sm font-medium text-green-600 dark:text-green-400">$<span x-text="alertConfig.targetPrice"></span></span>
                                </div>
                            </template>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Frequency:</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="formatFrequency(alertConfig.frequency)"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Expires:</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="formatExpiry(alertConfig.expiresIn)"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notification Channels -->
                <div class="space-y-4">
                    <h5 class="font-medium text-gray-900 dark:text-gray-100">Notification Methods</h5>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="channelId in alertConfig.channels" :key="channelId">
                            <span class="hdt-badge hdt-badge--primary hdt-badge--sm" x-text="getChannelName(channelId)"></span>
                        </template>
                    </div>
                </div>
            </div>
            
            <div class="hdt-card__footer">
                <div class="flex justify-between items-center">
                    <button @click="prevStep()" class="hdt-button hdt-button--outline hdt-button--md">
                        Back to Notifications
                    </button>
                    <button @click="createAlert()"
                            :disabled="creating"
                            class="hdt-button hdt-button--primary hdt-button--md flex items-center space-x-2"
                            :class="creating ? 'opacity-50 cursor-not-allowed' : ''">
                        <template x-if="creating">
                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </template>
                        <span x-text="creating ? 'Creating Alert...' : 'Create Alert'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Alpine.js Component -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('alertWizard', () => ({
                currentStep: 1,
                creating: false,
                searchQuery: '',
                selectedSport: '',
                selectedEvent: null,
                useCustomEvent: false,
                
                steps: [
                    { name: 'Event', description: 'Select event' },
                    { name: 'Price', description: 'Set price alert' },
                    { name: 'Notifications', description: 'Configure alerts' },
                    { name: 'Review', description: 'Review & create' }
                ],
                
                popularSports: [
                    { id: 'nfl', name: 'NFL', emoji: '🏈' },
                    { id: 'nba', name: 'NBA', emoji: '🏀' },
                    { id: 'mlb', name: 'MLB', emoji: '⚾' },
                    { id: 'nhl', name: 'NHL', emoji: '🏒' }
                ],
                
                events: [
                    {
                        id: 1,
                        title: 'Los Angeles Lakers vs Golden State Warriors',
                        venue: 'Crypto.com Arena, Los Angeles',
                        date: '2024-12-25',
                        time: '8:00 PM',
                        sport: 'NBA',
                        currentPrice: 175
                    },
                    {
                        id: 2,
                        title: 'Kansas City Chiefs vs Buffalo Bills',
                        venue: 'Arrowhead Stadium, Kansas City',
                        date: '2025-01-15',
                        time: '3:00 PM',
                        sport: 'NFL',
                        currentPrice: 185
                    },
                    {
                        id: 3,
                        title: 'Boston Celtics vs Miami Heat',
                        venue: 'TD Garden, Boston',
                        date: '2024-12-30',
                        time: '7:30 PM',
                        sport: 'NBA',
                        currentPrice: 95
                    }
                ],
                filteredEvents: [],
                
                customEvent: {
                    title: '',
                    venue: '',
                    date: '',
                    time: '',
                    sport: 'Custom'
                },
                
                alertConfig: {
                    type: 'price_drop',
                    targetPrice: '',
                    minPrice: '',
                    maxPrice: '',
                    channels: ['email'],
                    frequency: 'instant',
                    preferredTime: 'morning',
                    preferredDay: 'monday',
                    expiresIn: '30'
                },
                
                notificationChannels: [
                    {
                        id: 'email',
                        name: 'Email',
                        description: 'Get alerts via email',
                        icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>',
                        iconClass: 'text-blue-600 dark:text-blue-400',
                        bgClass: 'bg-blue-100 dark:bg-blue-900/50',
                        available: true
                    },
                    {
                        id: 'sms',
                        name: 'SMS',
                        description: 'Text message notifications',
                        icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>',
                        iconClass: 'text-green-600 dark:text-green-400',
                        bgClass: 'bg-green-100 dark:bg-green-900/50',
                        available: true
                    },
                    {
                        id: 'push',
                        name: 'Push Notifications',
                        description: 'Browser notifications',
                        icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4 19h6v-2H4v2zM4 14h16v-2H4v2zM4 9h16V7H4v2z"/>',
                        iconClass: 'text-purple-600 dark:text-purple-400',
                        bgClass: 'bg-purple-100 dark:bg-purple-900/50',
                        available: true
                    },
                    {
                        id: 'discord',
                        name: 'Discord',
                        description: 'Discord channel notifications',
                        icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>',
                        iconClass: 'text-indigo-600 dark:text-indigo-400',
                        bgClass: 'bg-indigo-100 dark:bg-indigo-900/50',
                        available: false
                    }
                ],
                
                init() {
                    this.filteredEvents = [...this.events];
                },
                
                searchEvents() {
                    this.filterEvents();
                },
                
                filterBySport(sportId) {
                    this.selectedSport = this.selectedSport === sportId ? '' : sportId;
                    this.filterEvents();
                },
                
                filterEvents() {
                    let filtered = [...this.events];
                    
                    if (this.searchQuery) {
                        const query = this.searchQuery.toLowerCase();
                        filtered = filtered.filter(event => 
                            event.title.toLowerCase().includes(query) ||
                            event.venue.toLowerCase().includes(query) ||
                            event.sport.toLowerCase().includes(query)
                        );
                    }
                    
                    if (this.selectedSport) {
                        filtered = filtered.filter(event => 
                            event.sport.toLowerCase() === this.selectedSport.toLowerCase()
                        );
                    }
                    
                    this.filteredEvents = filtered;
                },
                
                selectEvent(event) {
                    this.selectedEvent = event;
                    this.useCustomEvent = false;
                },
                
                formatDate(dateString) {
                    const date = new Date(dateString);
                    return date.toLocaleDateString('en-US', { 
                        weekday: 'short', 
                        month: 'short', 
                        day: 'numeric' 
                    });
                },
                
                getCurrentPrice() {
                    if (this.useCustomEvent) return 0;
                    return this.selectedEvent ? this.selectedEvent.currentPrice : 0;
                },
                
                setPercentageDiscount(percentage) {
                    const currentPrice = this.getCurrentPrice();
                    if (currentPrice > 0) {
                        this.alertConfig.targetPrice = Math.round(currentPrice * (1 - percentage / 100));
                    }
                },
                
                calculateDiscountPrice(percentage) {
                    const currentPrice = this.getCurrentPrice();
                    return currentPrice > 0 ? Math.round(currentPrice * (1 - percentage / 100)) : 0;
                },
                
                getSelectedEventTitle() {
                    if (this.useCustomEvent) return this.customEvent.title || 'Custom Event';
                    return this.selectedEvent ? this.selectedEvent.title : '';
                },
                
                getSelectedEventVenue() {
                    if (this.useCustomEvent) return this.customEvent.venue || 'Custom Venue';
                    return this.selectedEvent ? this.selectedEvent.venue : '';
                },
                
                getSelectedEventDateTime() {
                    if (this.useCustomEvent) {
                        const date = this.customEvent.date ? new Date(this.customEvent.date).toLocaleDateString() : 'Not set';
                        const time = this.customEvent.time || 'Not set';
                        return `${date} at ${time}`;
                    }
                    return this.selectedEvent ? 
                        `${this.formatDate(this.selectedEvent.date)} at ${this.selectedEvent.time}` : '';
                },
                
                getAlertSummary() {
                    if (this.alertConfig.type === 'price_drop') {
                        return `You'll be notified when tickets for "${this.getSelectedEventTitle()}" drop to $${this.alertConfig.targetPrice} or below.`;
                    } else {
                        return `You'll be notified when new tickets become available for "${this.getSelectedEventTitle()}".`;
                    }
                },
                
                getChannelName(channelId) {
                    const channel = this.notificationChannels.find(c => c.id === channelId);
                    return channel ? channel.name : channelId;
                },
                
                formatFrequency(frequency) {
                    const map = {
                        instant: 'Instant',
                        hourly: 'Hourly Digest',
                        daily: 'Daily Summary',
                        weekly: 'Weekly Report'
                    };
                    return map[frequency] || frequency;
                },
                
                formatExpiry(days) {
                    if (days === 'never') return 'Never';
                    const num = parseInt(days);
                    if (num < 30) return `${num} days`;
                    if (num < 365) return `${Math.round(num / 30)} months`;
                    return `${Math.round(num / 365)} years`;
                },
                
                canProceedFromStep1() {
                    if (this.useCustomEvent) {
                        return this.customEvent.title && this.customEvent.venue;
                    }
                    return this.selectedEvent !== null;
                },
                
                canProceedFromStep2() {
                    if (this.alertConfig.type === 'price_drop') {
                        return this.alertConfig.targetPrice && this.alertConfig.targetPrice > 0;
                    }
                    return true; // Availability alerts don't require price
                },
                
                canProceedFromStep3() {
                    return this.alertConfig.channels.length > 0;
                },
                
                nextStep() {
                    if (this.currentStep < this.steps.length) {
                        this.currentStep++;
                    }
                },
                
                prevStep() {
                    if (this.currentStep > 1) {
                        this.currentStep--;
                    }
                },
                
                async createAlert() {
                    this.creating = true;
                    
                    try {
                        // Simulate API call
                        await new Promise(resolve => setTimeout(resolve, 2000));
                        
                        // Redirect to alerts page with success message
                        window.location.href = '/alerts?created=success';
                    } catch (error) {
                        console.error('Failed to create alert:', error);
                        alert('Failed to create alert. Please try again.');
                    } finally {
                        this.creating = false;
                    }
                }
            }));
        });
    </script>
@endsection