<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $ticketSource->event_name }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    {{ $ticketSource->venue }} • {{ $ticketSource->platform_name }}
                </p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('ticket-sources.index') }}" 
                   class="btn-secondary inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg font-medium text-sm text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to List
                </a>
                <a href="{{ route('ticket-sources.edit', $ticketSource) }}" 
                   class="btn-primary inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Information -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Event Details Card -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                            <h3 class="text-lg font-semibold text-gray-900">Event Details</h3>
                        </div>
                        <div class="p-6">
                            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Event Name</dt>
                                    <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ $ticketSource->event_name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Source Name</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $ticketSource->name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Venue</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $ticketSource->venue }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Platform</dt>
                                    <dd class="mt-1">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $ticketSource->platform_name }}
                                        </span>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Event Date</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        @if($ticketSource->event_date)
                                            <div>
                                                <div class="font-medium">{{ $ticketSource->event_date->format('l, F j, Y') }}</div>
                                                <div class="text-gray-600">{{ $ticketSource->event_date->format('g:i A') }}</div>
                                                @if($ticketSource->time_until_event)
                                                    <div class="text-xs text-gray-500 mt-1">{{ $ticketSource->time_until_event }}</div>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-gray-500">To be determined</span>
                                        @endif
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Price Range</dt>
                                    <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ $ticketSource->formatted_price }}</dd>
                                </div>
                            </dl>

                            @if($ticketSource->description)
                                <div class="mt-6 pt-6 border-t border-gray-200">
                                    <dt class="text-sm font-medium text-gray-500 mb-2">Description</dt>
                                    <dd class="text-sm text-gray-900">{{ $ticketSource->description }}</dd>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Technical Details Card -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                            <h3 class="text-lg font-semibold text-gray-900">Technical Details</h3>
                        </div>
                        <div class="p-6">
                            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">External ID</dt>
                                    <dd class="mt-1 text-sm text-gray-900 font-mono">
                                        {{ $ticketSource->external_id ?: 'Not set' }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Currency</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ $ticketSource->currency }} ({{ $ticketSource->getCurrencySymbol() }})
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Country</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ strtoupper($ticketSource->country) }}
                                        </span>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Language</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $ticketSource->language }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Created</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ $ticketSource->created_at->format('M j, Y g:i A') }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ $ticketSource->updated_at->format('M j, Y g:i A') }}
                                    </dd>
                                </div>
                            </dl>

                            @if($ticketSource->url)
                                <div class="mt-6 pt-6 border-t border-gray-200">
                                    <dt class="text-sm font-medium text-gray-500 mb-2">Source URL</dt>
                                    <dd class="text-sm">
                                        <a href="{{ $ticketSource->url }}" 
                                           target="_blank" 
                                           class="text-blue-600 hover:text-blue-900 break-all inline-flex items-center">
                                            {{ $ticketSource->url }}
                                            <svg class="w-4 h-4 ml-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                            </svg>
                                        </a>
                                    </dd>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Status Sidebar -->
                <div class="lg:col-span-1 space-y-6">
                    <!-- Status Card -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                            <h3 class="text-lg font-semibold text-gray-900">Current Status</h3>
                        </div>
                        <div class="p-6 space-y-4">
                            <div>
                                <div class="text-sm font-medium text-gray-500 mb-2">Availability</div>
                                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium {{ $ticketSource->status_badge_class }}">
                                    {{ $ticketSource->status_name }}
                                </span>
                            </div>

                            <div>
                                <div class="text-sm font-medium text-gray-500 mb-2">Active Status</div>
                                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium {{ $ticketSource->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $ticketSource->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>

                            <div>
                                <div class="text-sm font-medium text-gray-500 mb-2">Last Checked</div>
                                <div class="text-sm text-gray-900">
                                    <div class="font-medium">{{ $ticketSource->last_checked_human }}</div>
                                    @if($ticketSource->last_checked)
                                        <div class="text-gray-600 text-xs mt-1">
                                            {{ $ticketSource->last_checked->format('M j, Y g:i A') }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions Card -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                            <h3 class="text-lg font-semibold text-gray-900">Actions</h3>
                        </div>
                        <div class="p-6 space-y-3">
                            @if($ticketSource->url)
                                <a href="{{ $ticketSource->url }}" 
                                   target="_blank"
                                   class="w-full inline-flex justify-center items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                    Visit Source
                                </a>
                            @endif

                            <a href="{{ route('ticket-sources.refresh', $ticketSource) }}"
                               class="w-full inline-flex justify-center items-center px-4 py-2 bg-green-600 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Refresh Status
                            </a>

                            <a href="{{ route('ticket-sources.edit', $ticketSource) }}"
                               class="w-full inline-flex justify-center items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Edit Source
                            </a>

                            <form action="{{ route('ticket-sources.toggle', $ticketSource) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" 
                                        class="w-full inline-flex justify-center items-center px-4 py-2 {{ $ticketSource->is_active ? 'bg-red-600 hover:bg-red-700 focus:ring-red-500' : 'bg-green-600 hover:bg-green-700 focus:ring-green-500' }} border border-transparent rounded-lg font-medium text-sm text-white focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors">
                                    @if($ticketSource->is_active)
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Deactivate
                                    @else
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Activate
                                    @endif
                                </button>
                            </form>

                            <div class="pt-4 border-t border-gray-200">
                                <form action="{{ route('ticket-sources.destroy', $ticketSource) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this ticket source? This action cannot be undone.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="w-full inline-flex justify-center items-center px-4 py-2 bg-red-600 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        Delete Source
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Platform Type Info -->
                    @if($ticketSource->isPlatformClub() || $ticketSource->isPlatformVenue())
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    @if($ticketSource->isPlatformClub())
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                    @else
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    @endif
                                </div>
                                <div class="ml-3">
                                    <h4 class="text-sm font-medium text-blue-800">
                                        {{ $ticketSource->isPlatformClub() ? 'Sports Club' : 'Venue' }} Platform
                                    </h4>
                                    <p class="text-sm text-blue-600">
                                        This is {{ $ticketSource->isPlatformClub() ? 'an official sports club' : 'a venue-specific' }} ticket source.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
