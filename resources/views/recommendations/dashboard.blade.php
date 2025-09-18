@extends('layouts.app-v2')

@section('title', 'AI Recommendations')

@section('content')
<div class="min-h-screen bg-gray-50" x-data="recommendationDashboard">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                            <svg class="w-8 h-8 mr-3 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                            </svg>
                            AI Recommendations
                        </h1>
                        <p class="mt-1 text-sm text-gray-500">
                            Personalized sports event suggestions powered by machine learning
                        </p>
                    </div>
                    <div class="flex items-center space-x-3">
                        <!-- Recommendation Quality Score -->
                        <div class="flex items-center px-3 py-1 bg-green-100 rounded-full text-sm font-medium text-green-800" x-show="qualityScore">
                            <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span x-text="`Quality: ${Math.round(qualityScore * 100)}%`"></span>
                        </div>
                        
                        <!-- Refresh Button -->
                        <button @click="refreshRecommendations" 
                                :disabled="loading"
                                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50">
                            <svg class="h-4 w-4 mr-2" :class="{ 'animate-spin': loading }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Refresh
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            
            <!-- Main Content Area -->
            <div class="lg:col-span-3 space-y-8">
                
                <!-- Loading State -->
                <div x-show="loading" class="text-center py-12">
                    <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
                    <p class="mt-4 text-gray-600">Generating personalized recommendations...</p>
                </div>

                <!-- Event Recommendations -->
                <div x-show="!loading && recommendations.events" class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                                    </svg>
                                    Recommended Events for You
                                </h3>
                                <p class="mt-1 text-sm text-gray-500" x-text="`${recommendations.events?.recommendations?.length || 0} personalized recommendations`"></p>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="text-xs text-gray-400" x-text="`Confidence: ${Math.round((recommendations.events?.confidence_score || 0) * 100)}%`"></span>
                                <div class="flex space-x-1" x-show="recommendations.events?.strategies_used">
                                    <template x-for="strategy in recommendations.events.strategies_used" :key="strategy">
                                        <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full" x-text="strategy.replace('_', ' ')"></span>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6" x-show="recommendations.events?.recommendations">
                            <template x-for="event in recommendations.events.recommendations" :key="event.id">
                                <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 hover:shadow-md transition-all duration-200">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <h4 class="text-sm font-medium text-gray-900" x-text="event.title"></h4>
                                            <p class="text-sm text-gray-500 mt-1" x-text="event.sport || 'Sports Event'"></p>
                                            <p class="text-sm text-gray-500" x-text="event.location"></p>
                                            <div class="flex items-center mt-2">
                                                <svg class="w-4 h-4 text-green-600 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.745 3.745 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.745 3.745 0 013.296-1.043A3.745 3.745 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.745 3.745 0 013.296 1.043 3.745 3.745 0 011.043 3.296A3.745 3.745 0 0121 12z" />
                                                </svg>
                                                <span class="text-sm font-medium text-green-600" x-text="`$${event.price}`"></span>
                                            </div>
                                        </div>
                                        <div class="ml-4 flex flex-col items-end">
                                            <div class="flex items-center mb-2">
                                                <div class="w-12 bg-gray-200 rounded-full h-2">
                                                    <div class="bg-blue-600 h-2 rounded-full" :style="`width: ${(event.final_score || event.score || 0.5) * 100}%`"></div>
                                                </div>
                                                <span class="text-xs text-gray-500 ml-2" x-text="`${Math.round((event.final_score || event.score || 0.5) * 100)}%`"></span>
                                            </div>
                                            <button class="text-xs bg-blue-600 text-white px-3 py-1 rounded-md hover:bg-blue-700 transition-colors">
                                                View Details
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <!-- Recommendation Reasons -->
                                    <div class="mt-3 pt-3 border-t border-gray-100" x-show="event.recommendation_reasons">
                                        <div class="flex flex-wrap gap-1">
                                            <template x-for="reason in event.recommendation_reasons" :key="reason">
                                                <span class="px-2 py-1 bg-gray-100 text-gray-700 text-xs rounded" x-text="formatReason(reason)"></span>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                        
                        <!-- Empty State -->
                        <div x-show="!recommendations.events?.recommendations?.length" class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No recommendations yet</h3>
                            <p class="mt-1 text-sm text-gray-500">We need more data about your preferences to provide recommendations.</p>
                        </div>
                    </div>
                </div>

                <!-- Pricing Strategies -->
                <div x-show="!loading && recommendations.pricing" class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.745 3.745 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.745 3.745 0 013.296-1.043A3.745 3.745 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.745 3.745 0 013.296 1.043 3.745 3.745 0 011.043 3.296A3.745 3.745 0 0121 12z" />
                            </svg>
                            Optimal Pricing Strategies
                        </h3>
                        <p class="mt-1 text-sm text-gray-500">
                            AI-powered pricing recommendations based on your purchasing behavior
                        </p>
                    </div>
                    
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <template x-for="strategy in recommendations.pricing?.strategies || []" :key="strategy.type">
                                <div class="border border-gray-200 rounded-lg p-4" :class="strategy.type === 'budget_conscious' ? 'border-green-300 bg-green-50' : strategy.type === 'premium_experience' ? 'border-purple-300 bg-purple-50' : 'border-gray-200'">
                                    <div class="flex items-start justify-between">
                                        <div>
                                            <h4 class="text-sm font-medium text-gray-900" x-text="strategy.title"></h4>
                                            <p class="text-sm text-gray-600 mt-1" x-text="strategy.description"></p>
                                            
                                            <!-- Strategy Features -->
                                            <div class="mt-3" x-show="strategy.priority_features">
                                                <div class="flex flex-wrap gap-1">
                                                    <template x-for="feature in strategy.priority_features" :key="feature">
                                                        <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded" x-text="feature.replace('_', ' ')"></span>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="flex items-center">
                                                <div class="w-12 bg-gray-200 rounded-full h-2">
                                                    <div class="bg-green-600 h-2 rounded-full" :style="`width: ${(strategy.confidence || 0.5) * 100}%`"></div>
                                                </div>
                                                <span class="text-xs text-gray-500 ml-2" x-text="`${Math.round((strategy.confidence || 0.5) * 100)}%`"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Smart Alert Settings -->
                <div x-show="!loading && recommendations.alerts" class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5l-5-5h5v-12h5v12z" />
                            </svg>
                            Smart Alert Recommendations
                        </h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Optimized notification settings based on your activity patterns
                        </p>
                    </div>
                    
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            
                            <!-- Optimal Timing -->
                            <div x-show="recommendations.alerts?.optimal_timing">
                                <h4 class="text-sm font-medium text-gray-900 mb-3">Optimal Timing</h4>
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-gray-600">Peak Activity Hours:</span>
                                        <span class="font-medium" x-text="recommendations.alerts.optimal_timing.peak_activity_hours?.join(', ')"></span>
                                    </div>
                                    <div class="flex items-center justify-between text-sm" x-show="recommendations.alerts.optimal_timing.quiet_hours_suggestion">
                                        <span class="text-gray-600">Suggested Quiet Hours:</span>
                                        <span class="font-medium text-xs" x-text="recommendations.alerts.optimal_timing.quiet_hours_suggestion"></span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Alert Frequency -->
                            <div x-show="recommendations.alerts?.frequency">
                                <h4 class="text-sm font-medium text-gray-900 mb-3">Alert Frequency</h4>
                                <div class="space-y-2">
                                    <template x-for="[type, frequency] in Object.entries(recommendations.alerts.frequency)" :key="type">
                                        <div class="flex items-center justify-between text-sm">
                                            <span class="text-gray-600" x-text="type.replace('_', ' ')"></span>
                                            <span class="font-medium capitalize" x-text="frequency"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            
                            <!-- Smart Filters -->
                            <div x-show="recommendations.alerts?.smart_filters">
                                <h4 class="text-sm font-medium text-gray-900 mb-3">Smart Filters</h4>
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between text-sm" x-show="recommendations.alerts.smart_filters.minimum_savings_threshold">
                                        <span class="text-gray-600">Min. Savings:</span>
                                        <span class="font-medium" x-text="`${Math.round(recommendations.alerts.smart_filters.minimum_savings_threshold * 100)}%`"></span>
                                    </div>
                                    <div class="flex items-center justify-between text-sm" x-show="recommendations.alerts.smart_filters.location_radius_km">
                                        <span class="text-gray-600">Location Radius:</span>
                                        <span class="font-medium" x-text="`${recommendations.alerts.smart_filters.location_radius_km} km`"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-6 pt-4 border-t border-gray-200">
                            <button @click="applyAlertRecommendations()" class="bg-orange-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-orange-700 transition-colors">
                                Apply Alert Settings
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                
                <!-- Personalization Score -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Personalization Score</h3>
                    </div>
                    <div class="p-6">
                        <div class="text-center">
                            <!-- Circular Progress -->
                            <div class="relative inline-block">
                                <svg class="w-24 h-24 transform -rotate-90" viewBox="0 0 100 100">
                                    <circle cx="50" cy="50" r="45" fill="none" stroke="#e5e7eb" stroke-width="10"></circle>
                                    <circle cx="50" cy="50" r="45" fill="none" stroke="#3b82f6" stroke-width="10"
                                            :stroke-dasharray="`${2 * Math.PI * 45}`"
                                            :stroke-dashoffset="`${2 * Math.PI * 45 * (1 - (userContextScore || 0))}`"
                                            stroke-linecap="round" class="transition-all duration-500"></circle>
                                </svg>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <span class="text-2xl font-bold text-gray-900" x-text="`${Math.round((userContextScore || 0) * 100)}%`"></span>
                                </div>
                            </div>
                            
                            <p class="mt-4 text-sm text-gray-600">
                                Higher scores mean more accurate recommendations
                            </p>
                            
                            <!-- Improvement Tips -->
                            <div class="mt-4 p-3 bg-blue-50 rounded-lg" x-show="userContextScore < 0.7">
                                <p class="text-sm text-blue-800 font-medium">💡 Improve your score:</p>
                                <ul class="text-xs text-blue-700 mt-2 text-left space-y-1">
                                    <li>• Set favorite teams and venues</li>
                                    <li>• Browse more sports events</li>
                                    <li>• Make a few ticket purchases</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Team Recommendations -->
                <div class="bg-white shadow rounded-lg" x-show="recommendations.teams?.recommendations?.length">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Recommended Teams</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3">
                            <template x-for="team in recommendations.teams.recommendations.slice(0, 5)" :key="team.id">
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white text-sm font-bold" x-text="team.name?.charAt(0) || 'T'"></div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-gray-900" x-text="team.name"></p>
                                            <p class="text-xs text-gray-500" x-text="team.sport"></p>
                                        </div>
                                    </div>
                                    <button class="text-xs bg-blue-600 text-white px-2 py-1 rounded hover:bg-blue-700">
                                        Follow
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Venue Recommendations -->
                <div class="bg-white shadow rounded-lg" x-show="recommendations.venues?.recommendations?.length">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Recommended Venues</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3">
                            <template x-for="venue in recommendations.venues.recommendations.slice(0, 4)" :key="venue.id">
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900" x-text="venue.name"></p>
                                            <p class="text-xs text-gray-500" x-text="venue.location"></p>
                                        </div>
                                    </div>
                                    <button class="text-xs bg-gray-600 text-white px-2 py-1 rounded hover:bg-gray-700">
                                        Watch
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- AI Insights -->
                <div class="bg-white shadow rounded-lg" x-show="recommendations.meta">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">AI Insights</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">Generation Time:</span>
                            <span class="font-medium" x-text="`${recommendations.meta.generation_time_ms}ms`"></span>
                        </div>
                        
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">AI Model Version:</span>
                            <span class="font-medium" x-text="recommendations.meta.recommendation_version"></span>
                        </div>
                        
                        <div class="flex items-center justify-between text-sm" x-show="recommendations.meta.ab_variant">
                            <span class="text-gray-500">Test Variant:</span>
                            <span class="font-medium capitalize" x-text="recommendations.meta.ab_variant"></span>
                        </div>
                        
                        <div class="pt-4 border-t border-gray-200">
                            <p class="text-xs text-gray-500" x-text="`Last updated: ${formatTime(recommendations.meta.generated_at)}`"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Alpine.js Component Script -->
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('recommendationDashboard', () => ({
        recommendations: {},
        loading: true,
        qualityScore: 0,
        userContextScore: 0,
        
        init() {
            this.loadRecommendations();
        },
        
        async loadRecommendations() {
            this.loading = true;
            
            try {
                const response = await fetch('/api/recommendations', {
                    headers: {
                        'Authorization': `Bearer ${this.getAuthToken()}`,
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                
                if (response.ok) {
                    this.recommendations = await response.json();
                    this.qualityScore = this.recommendations.events?.confidence_score || 0;
                    this.userContextScore = this.recommendations.meta?.user_context_score || 0;
                } else {
                    this.showError('Failed to load recommendations');
                }
            } catch (error) {
                console.error('Error loading recommendations:', error);
                this.showError('Unable to load recommendations');
            } finally {
                this.loading = false;
            }
        },
        
        async refreshRecommendations() {
            this.loading = true;
            
            try {
                const response = await fetch('/api/recommendations/refresh', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                
                if (response.ok) {
                    await this.loadRecommendations();
                    this.showSuccess('Recommendations refreshed');
                } else {
                    this.showError('Failed to refresh recommendations');
                }
            } catch (error) {
                console.error('Error refreshing recommendations:', error);
                this.showError('Unable to refresh recommendations');
                this.loading = false;
            }
        },
        
        async applyAlertRecommendations() {
            const alertSettings = this.recommendations.alerts;
            if (!alertSettings) return;
            
            try {
                const response = await fetch('/api/user/notification-preferences', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        apply_ai_recommendations: true,
                        ai_alert_settings: alertSettings
                    })
                });
                
                if (response.ok) {
                    this.showSuccess('Alert settings applied successfully');
                } else {
                    this.showError('Failed to apply alert settings');
                }
            } catch (error) {
                console.error('Error applying alert settings:', error);
                this.showError('Unable to apply alert settings');
            }
        },
        
        formatReason(reason) {
            const reasonMap = {
                'preference_based': 'Matches Your Preferences',
                'collaborative_filtering': 'Similar Users Liked',
                'trending_events': 'Currently Trending',
                'seasonal_recommendations': 'Seasonal Match',
                'price_optimal': 'Great Value'
            };
            
            return reasonMap[reason] || reason.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
        },
        
        formatTime(timestamp) {
            const date = new Date(timestamp);
            const now = new Date();
            const diff = now - date;
            
            if (diff < 60000) return 'Just now';
            if (diff < 3600000) return `${Math.floor(diff / 60000)} minutes ago`;
            if (diff < 86400000) return `${Math.floor(diff / 3600000)} hours ago`;
            return date.toLocaleDateString();
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
