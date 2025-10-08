@extends('layouts.app-v2')

@section('title', 'Purchase Ticket - ' . $ticket->title)

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Breadcrumb --}}
        <nav class="flex mb-8" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-4">
                <li>
                    <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                        </svg>
                        <span class="sr-only">Home</span>
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <a href="{{ route('tickets.main') }}" class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700">Tickets</a>
                    </div>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span class="ml-4 text-sm font-medium text-gray-900">Purchase</span>
                    </div>
                </li>
            </ol>
        </nav>

        <div class="lg:grid lg:grid-cols-12 lg:gap-x-12 lg:items-start">
            {{-- Ticket Information --}}
            <div class="lg:col-span-7">
                <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                    <div class="px-6 py-6">
                        <h1 class="text-2xl font-bold text-gray-900 mb-4">{{ $ticket->title }}</h1>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Event Details --}}
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-3">Event Details</h3>
                                <div class="space-y-2">
                                    @if($ticket->event_date)
                                        <div class="flex items-center text-sm text-gray-600">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            {{ $ticket->event_date->format('F j, Y \a\t g:i A') }}
                                        </div>
                                    @endif
                                    
                                    @if($ticket->venue)
                                        <div class="flex items-center text-sm text-gray-600">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                            {{ $ticket->venue }}
                                        </div>
                                    @endif

                                    @if($ticket->location)
                                        <div class="flex items-center text-sm text-gray-600">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                            </svg>
                                            {{ $ticket->location }}
                                        </div>
                                    @endif

                                    @if($ticket->sport)
                                        <div class="flex items-center text-sm text-gray-600">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                            </svg>
                                            {{ ucfirst($ticket->sport) }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Pricing Information --}}
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-3">Pricing</h3>
                                <div class="space-y-2">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Ticket Price:</span>
                                        <span class="text-lg font-semibold text-gray-900">
                                            ${{ number_format($ticket->price, 2) }} {{ strtoupper($ticket->currency ?? 'USD') }}
                                        </span>
                                    </div>
                                    
                                    @if($ticket->available_quantity)
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm text-gray-600">Available:</span>
                                            <span class="text-sm font-medium text-green-600">{{ $ticket->available_quantity }} tickets</span>
                                        </div>
                                    @endif

                                    <div class="flex items-center">
                                        <div class="flex items-center">
                                            @if($ticket->is_available)
                                                <div class="w-2 h-2 bg-green-400 rounded-full mr-2"></div>
                                                <span class="text-sm text-green-600 font-medium">Available</span>
                                            @else
                                                <div class="w-2 h-2 bg-red-400 rounded-full mr-2"></div>
                                                <span class="text-sm text-red-600 font-medium">Unavailable</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($ticket->seat_details)
                            <div class="mt-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-3">Seat Details</h3>
                                <p class="text-sm text-gray-600">{{ $ticket->seat_details }}</p>
                            </div>
                        @endif

                        @if($ticket->description)
                            <div class="mt-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-3">Description</h3>
                                <p class="text-sm text-gray-600">{{ $ticket->description }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Purchase Form --}}
            <div class="lg:col-span-5 mt-8 lg:mt-0">
                <div class="bg-white shadow-lg rounded-lg overflow-hidden sticky top-8">
                    <div class="px-6 py-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-6">Purchase Tickets</h2>

                        {{-- User Information --}}
                        <div class="bg-blue-50 rounded-lg p-4 mb-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center">
                                        <span class="text-white text-sm font-medium">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-blue-800">{{ $user->name }}</h3>
                                    <p class="text-sm text-blue-600">{{ ucfirst($user->role) }} Account</p>
                                </div>
                            </div>

                            {{-- Usage Information --}}
                            @if($user->isCustomer())
                                <div class="mt-3 pt-3 border-t border-blue-200">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-blue-700">Monthly Usage:</span>
                                        <span class="text-blue-800 font-medium">
                                            {{ $user->getMonthlyTicketUsage() }} / 
                                            {{ $user->subscription?->plan?->ticket_limit ?? config('subscription.default_ticket_limit') }}
                                        </span>
                                    </div>
                                </div>
                            @elseif($user->isAgent() || $user->isAdmin())
                                <div class="mt-3 pt-3 border-t border-blue-200">
                                    <span class="text-sm text-blue-700">✓ Unlimited ticket access</span>
                                </div>
                            @endif
                        </div>

                        {{-- Purchase Eligibility Check --}}
                        @if(!$eligibilityInfo['can_purchase'])
                            <div class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-red-800">Purchase Not Available</h3>
                                        <div class="mt-2 text-sm text-red-700">
                                            <ul class="list-disc list-inside space-y-1">
                                                @foreach($eligibilityInfo['reasons'] as $reason)
                                                    <li>{{ $reason }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                        
                                        @if(in_array('Active subscription required', $eligibilityInfo['reasons']))
                                            <div class="mt-3">
                                                <a href="{{ route('subscription.plans') }}" 
                                                   class="text-sm text-red-800 font-medium hover:text-red-900 underline">
                                                    View Subscription Plans
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @else
                            {{-- Purchase Form --}}
                            <form id="purchaseForm" action="{{ route('tickets.purchase', $ticket) }}" method="POST">
                                @csrf
                                
                                {{-- Quantity Selection --}}
                                <div class="mb-6">
                                    <label for="quantity" class="block text-sm font-medium text-gray-700 mb-2">
                                        Number of Tickets
                                    </label>
                                    <select id="quantity" name="quantity" 
                                            class="block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                            required>
                                        @php
                                            $maxQuantity = min(10, $ticket->available_quantity ?? 10);
                                            if($user->isCustomer()) {
                                                $remaining = $eligibilityInfo['user_info']['remaining_tickets'];
                                                $maxQuantity = min($maxQuantity, $remaining);
                                            }
                                        @endphp
                                        
                                        @for($i = 1; $i <= $maxQuantity; $i++)
                                            <option value="{{ $i }}">{{ $i }} {{ $i == 1 ? 'ticket' : 'tickets' }}</option>
                                        @endfor
                                    </select>
                                </div>

                                {{-- Seat Preferences --}}
                                <div class="mb-6">
                                    <h4 class="text-sm font-medium text-gray-700 mb-3">Seat Preferences (Optional)</h4>
                                    
                                    <div class="space-y-4">
                                        <div>
                                            <label for="section" class="block text-sm text-gray-600 mb-1">Preferred Section</label>
                                            <input type="text" id="section" name="seat_preferences[section]" 
                                                   class="block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                                   placeholder="e.g., Lower level, Upper deck">
                                        </div>

                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label for="row" class="block text-sm text-gray-600 mb-1">Preferred Row</label>
                                                <input type="text" id="row" name="seat_preferences[row]" 
                                                       class="block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                                       placeholder="e.g., A, 1-10">
                                            </div>

                                            <div>
                                                <label for="seat_type" class="block text-sm text-gray-600 mb-1">Seat Type</label>
                                                <select id="seat_type" name="seat_preferences[seat_type]" 
                                                        class="block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                                    <option value="">Any</option>
                                                    <option value="standard">Standard</option>
                                                    <option value="premium">Premium</option>
                                                    <option value="vip">VIP</option>
                                                    <option value="accessible">Accessible</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div>
                                            <label class="flex items-center">
                                                <input type="checkbox" name="seat_preferences[accessibility_needs]" 
                                                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                                <span class="ml-2 text-sm text-gray-600">Accessibility accommodations needed</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                {{-- Special Requests --}}
                                <div class="mb-6">
                                    <label for="special_requests" class="block text-sm font-medium text-gray-700 mb-2">
                                        Special Requests (Optional)
                                    </label>
                                    <textarea id="special_requests" name="special_requests" rows="3"
                                              class="block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                              placeholder="Any specific requests or requirements..."></textarea>
                                </div>

                                {{-- Price Calculation --}}
                                <div class="bg-gray-50 rounded-lg p-4 mb-6">
                                    <h4 class="text-sm font-medium text-gray-900 mb-3">Price Calculation</h4>
                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between">
                                            <span>Ticket Price:</span>
                                            <span id="unitPrice">${{ number_format($ticket->price, 2) }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span>Quantity:</span>
                                            <span id="selectedQuantity">1</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span>Subtotal:</span>
                                            <span id="subtotal">${{ number_format($ticket->price, 2) }}</span>
                                        </div>
                                        <div class="flex justify-between text-xs text-gray-600">
                                            <span>Processing Fee (3%):</span>
                                            <span id="processingFee">${{ number_format($ticket->price * 0.03, 2) }}</span>
                                        </div>
                                        <div class="flex justify-between text-xs text-gray-600">
                                            <span>Service Fee:</span>
                                            <span id="serviceFee">$2.50</span>
                                        </div>
                                        <hr class="border-gray-300">
                                        <div class="flex justify-between font-semibold">
                                            <span>Total:</span>
                                            <span id="totalPrice">${{ number_format($ticket->price + ($ticket->price * 0.03) + 2.50, 2) }}</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Payment Method Selection --}}
                                <div class="mb-6">
                                    <h4 class="text-sm font-medium text-gray-700 mb-4">Payment Method</h4>
                                    <div class="space-y-3">
                                        <div class="flex space-x-3">
                                            <label class="flex-1 cursor-pointer">
                                                <input type="radio" name="payment_method" value="stripe" checked
                                                       class="sr-only" id="stripe-radio">
                                                <div class="border-2 rounded-lg p-3 transition-colors" 
                                                     id="stripe-option"
                                                     onclick="selectPaymentMethod('stripe')">
                                                    <div class="flex items-center justify-between">
                                                        <div class="flex items-center space-x-3">
                                                            <div class="w-5 h-5 rounded-full border-2 border-blue-500 bg-blue-500 flex items-center justify-center">
                                                                <div class="w-2 h-2 bg-white rounded-full"></div>
                                                            </div>
                                                            <span class="font-medium text-gray-900">Credit/Debit Card</span>
                                                        </div>
                                                        <div class="flex items-center space-x-2">
                                                            <svg class="w-8 h-5" viewBox="0 0 36 24" fill="none">
                                                                <rect width="36" height="24" rx="4" fill="#1A73E8"/>
                                                                <text x="6" y="14" font-family="Arial" font-size="6" fill="white" font-weight="bold">VISA</text>
                                                            </svg>
                                                            <svg class="w-8 h-5" viewBox="0 0 36 24" fill="none">
                                                                <rect width="36" height="24" rx="4" fill="#EB001B"/>
                                                                <circle cx="14" cy="12" r="6" fill="#EB001B"/>
                                                                <circle cx="22" cy="12" r="6" fill="#FF5F00"/>
                                                            </svg>
                                                        </div>
                                                    </div>
                                                </div>
                                            </label>
                                            
                                            <label class="flex-1 cursor-pointer">
                                                <input type="radio" name="payment_method" value="paypal"
                                                       class="sr-only" id="paypal-radio">
                                                <div class="border-2 border-gray-300 rounded-lg p-3 transition-colors" 
                                                     id="paypal-option"
                                                     onclick="selectPaymentMethod('paypal')">
                                                    <div class="flex items-center justify-between">
                                                        <div class="flex items-center space-x-3">
                                                            <div class="w-5 h-5 rounded-full border-2 border-gray-300 flex items-center justify-center">
                                                                <div class="w-2 h-2 rounded-full hidden"></div>
                                                            </div>
                                                            <span class="font-medium text-gray-900">PayPal</span>
                                                        </div>
                                                        <div class="flex items-center">
                                                            <svg class="w-16 h-6" viewBox="0 0 124 33" fill="none">
                                                                <path d="M46.211 6.749h-6.839a.95.95 0 00-.939.802l-2.766 17.537a.57.57 0 00.564.658h3.265a.95.95 0 00.939-.803l.746-4.73a.95.95 0 01.938-.803h2.165c4.505 0 7.105-2.18 7.784-6.5.306-1.89.013-3.375-.872-4.415-.972-1.142-2.696-1.746-4.985-1.746zM47.117 13.154c-.374 2.454-2.249 2.454-4.062 2.454h-1.032l.724-4.583a.57.57 0 01.563-.481h.473c1.235 0 2.4 0 3.002.704.359.42.469 1.044.332 1.906zM66.654 13.075h-3.275a.57.57 0 00-.563.481l-.145.916-.229-.332c-.709-1.029-2.29-1.373-3.868-1.373-3.619 0-6.71 2.741-7.312 6.586-.313 1.918.132 3.752 1.22 5.031.998 1.176 2.426 1.666 4.125 1.666 2.916 0 4.533-1.875 4.533-1.875l-.146.91a.57.57 0 00.562.66h2.95a.95.95 0 00.939-.803l1.77-11.209a.568.568 0 00-.561-.658zm-4.565 6.374c-.316 1.871-1.801 3.127-3.695 3.127-.951 0-1.711-.305-2.199-.883-.484-.574-.668-1.391-.514-2.301.295-1.855 1.805-3.152 3.67-3.152.93 0 1.686.309 2.184.892.499.589.697 1.411.554 2.317zM84.096 13.075h-3.291a.954.954 0 00-.787.417l-4.539 6.686-1.924-6.425a.953.953 0 00-.912-.678h-3.234a.57.57 0 00-.541.754l3.625 10.638-3.408 4.811a.57.57 0 00.465.9h3.287a.949.949 0 00.781-.408l10.946-15.8a.57.57 0 00-.468-.895z" fill="#253B80"/>
                                                                <path d="M94.992 6.749h-6.84a.95.95 0 00-.938.802l-2.766 17.537a.569.569 0 00.562.658h3.51a.665.665 0 00.656-.562l.785-4.971a.95.95 0 01.938-.803h2.164c4.506 0 7.105-2.18 7.785-6.5.307-1.89.012-3.375-.873-4.415-.971-1.142-2.694-1.746-4.983-1.746zm.789 6.405c-.373 2.454-2.248 2.454-4.062 2.454h-1.031l.725-4.583a.568.568 0 01.562-.481h.473c1.234 0 2.4 0 3.002.704.359.42.468 1.044.331 1.906zM115.434 13.075h-3.273a.567.567 0 00-.562.481l-.145.916-.230-.332c-.709-1.029-2.289-1.373-3.867-1.373-3.619 0-6.709 2.741-7.311 6.586-.312 1.918.131 3.752 1.219 5.031.998 1.176 2.426 1.666 4.125 1.666 2.916 0 4.533-1.875 4.533-1.875l-.146.91a.57.57 0 00.564.66h2.949a.95.95 0 00.938-.803l1.771-11.209a.571.571 0 00-.565-.658zm-4.565 6.374c-.314 1.871-1.801 3.127-3.695 3.127-.949 0-1.711-.305-2.199-.883-.484-.574-.666-1.391-.514-2.301.297-1.855 1.805-3.152 3.67-3.152.93 0 1.686.309 2.184.892.501.589.699 1.411.554 2.317z" fill="#179BD7"/>
                                                                <path d="M119.295 7.23l-2.807 17.858a.569.569 0 00.562.658h2.822c.469 0 .867-.34.939-.803l2.768-17.536a.57.57 0 00-.562-.659h-3.16a.571.571 0 00-.562.482z" fill="#179BD7"/>
                                                            </svg>
                                                        </div>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                        
                                        <div id="payment-method-info" class="text-sm text-gray-600 bg-gray-50 p-3 rounded-lg">
                                            <div id="stripe-info" class="flex items-center space-x-2">
                                                <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                                <span>Secure payment processed by Stripe. Your card details are encrypted and never stored.</span>
                                            </div>
                                            <div id="paypal-info" class="hidden flex items-center space-x-2">
                                                <svg class="w-4 h-4 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                                <span>Secure payment through PayPal. Complete your purchase without sharing financial details.</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Terms and Conditions --}}
                                <div class="mb-6">
                                    <div class="space-y-3">
                                        <label class="flex items-start">
                                            <input type="checkbox" name="accept_terms" required
                                                   class="mt-1 rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                            <span class="ml-2 text-sm text-gray-600">
                                                I agree to the <a href="{{ route('legal.terms-of-service') }}" target="_blank" class="text-blue-600 hover:text-blue-800 underline">Terms of Service</a> 
                                                and <a href="{{ route('legal.disclaimer') }}" target="_blank" class="text-blue-600 hover:text-blue-800 underline">Service Disclaimer</a>. 
                                                I understand that all sales are final and no refunds are provided.
                                            </span>
                                        </label>

                                        <label class="flex items-start">
                                            <input type="checkbox" name="confirm_purchase" required
                                                   class="mt-1 rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                            <span class="ml-2 text-sm text-gray-600">
                                                I confirm that I want to purchase these tickets and authorize the charge to my account.
                                            </span>
                                        </label>
                                    </div>
                                </div>

                                {{-- Purchase Buttons --}}
                                <div class="space-y-4">
                                    {{-- Traditional Form Submit Button (Stripe) --}}
                                    <div id="stripe-purchase-button">
                                        <button type="submit" id="purchaseButton"
                                                class="w-full bg-blue-600 text-white py-3 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 font-medium transition-colors duration-200 disabled:bg-gray-400 disabled:cursor-not-allowed">
                                            <span id="buttonText">Purchase Tickets</span>
                                            <svg id="loadingSpinner" class="hidden animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                        </button>
                                    </div>
                                    
                                    {{-- PayPal Purchase Button --}}
                                    <div id="paypal-purchase-section" class="hidden">
                                        <div class="space-y-3">
                                            <div id="paypal-button-container" class="min-h-[50px]">
                                                <!-- PayPal button will be rendered here -->
                                            </div>
                                            <div id="paypal-errors" class="text-red-600 text-sm" role="alert"></div>
                                            
                                            <div class="text-center">
                                                <button type="button" 
                                                        onclick="selectPaymentMethod('stripe')"
                                                        class="text-sm text-gray-600 hover:text-gray-800 underline">
                                                    ← Back to Credit Card
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- PayPal SDK --}}
<script src="https://www.paypal.com/sdk/js?client-id={{ config('services.paypal.sandbox.client_id') }}&intent=capture&currency=USD"></script>

{{-- JavaScript for Form Handling --}}
<script>
// Global variables
let paypalButtonRendered = false;
let selectedPaymentMethod = 'stripe';
let currentQuantity = 1;

document.addEventListener('DOMContentLoaded', function() {
    const quantitySelect = document.getElementById('quantity');
    const unitPrice = {{ $ticket->price }};
    const processingFeeRate = 0.03;
    const serviceFee = 2.50;

    // Update price calculation when quantity changes
    quantitySelect?.addEventListener('change', function() {
        currentQuantity = parseInt(this.value);
        updatePriceCalculation(currentQuantity);
    });

    function updatePriceCalculation(quantity) {
        const subtotal = unitPrice * quantity;
        const processingFee = subtotal * processingFeeRate;
        const total = subtotal + processingFee + serviceFee;

        document.getElementById('selectedQuantity').textContent = quantity;
        document.getElementById('subtotal').textContent = '$' + subtotal.toFixed(2);
        document.getElementById('processingFee').textContent = '$' + processingFee.toFixed(2);
        document.getElementById('totalPrice').textContent = '$' + total.toFixed(2);
    }

    // Handle form submission for Stripe
    const purchaseForm = document.getElementById('purchaseForm');
    const purchaseButton = document.getElementById('purchaseButton');
    const buttonText = document.getElementById('buttonText');
    const loadingSpinner = document.getElementById('loadingSpinner');

    purchaseForm?.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Only process if Stripe is selected
        if (selectedPaymentMethod !== 'stripe') {
            return;
        }
        
        // Validate required checkboxes
        const acceptTerms = document.querySelector('input[name="accept_terms"]');
        const confirmPurchase = document.querySelector('input[name="confirm_purchase"]');
        
        if (!acceptTerms?.checked || !confirmPurchase?.checked) {
            showNotification('error', 'Please accept the terms and confirm your purchase.');
            return;
        }
        
        // Disable button and show loading
        purchaseButton.disabled = true;
        buttonText.textContent = 'Processing...';
        loadingSpinner.classList.remove('hidden');

        // Submit form via fetch API for better UX
        const formData = new FormData(this);
        formData.set('payment_method', 'stripe');
        
        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message and redirect
                showNotification('success', 'Purchase completed successfully!');
                setTimeout(() => {
                    window.location.href = data.redirect_url || '/tickets/purchase-history';
                }, 2000);
            } else {
                // Show error message
                showNotification('error', data.message || 'Purchase failed. Please try again.');
                
                // Re-enable button
                purchaseButton.disabled = false;
                buttonText.textContent = 'Purchase Tickets';
                loadingSpinner.classList.add('hidden');
            }
        })
        .catch(error => {
            console.error('Purchase error:', error);
            showNotification('error', 'An unexpected error occurred. Please try again.');
            
            // Re-enable button
            purchaseButton.disabled = false;
            buttonText.textContent = 'Purchase Tickets';
            loadingSpinner.classList.add('hidden');
        });
    });
    
    // Initialize PayPal if available
    if (typeof paypal !== 'undefined') {
        initializePayPalButton();
    }
});

// Payment Method Selection Functions
function selectPaymentMethod(method) {
    selectedPaymentMethod = method;
    
    const stripeOption = document.getElementById('stripe-option');
    const paypalOption = document.getElementById('paypal-option');
    const stripeRadio = document.getElementById('stripe-radio');
    const paypalRadio = document.getElementById('paypal-radio');
    const stripeInfo = document.getElementById('stripe-info');
    const paypalInfo = document.getElementById('paypal-info');
    const stripePurchaseButton = document.getElementById('stripe-purchase-button');
    const paypalPurchaseSection = document.getElementById('paypal-purchase-section');
    
    if (method === 'stripe') {
        // Update visual selection
        stripeOption.classList.add('border-blue-500', 'bg-blue-50');
        stripeOption.classList.remove('border-gray-300');
        stripeOption.querySelector('.w-5.h-5 > div').classList.add('bg-blue-500');
        stripeOption.querySelector('.w-5.h-5').classList.add('border-blue-500', 'bg-blue-500');
        stripeOption.querySelector('.w-5.h-5').classList.remove('border-gray-300');
        stripeOption.querySelector('.w-2.h-2').classList.remove('hidden');
        
        paypalOption.classList.remove('border-blue-500', 'bg-blue-50');
        paypalOption.classList.add('border-gray-300');
        paypalOption.querySelector('.w-5.h-5 > div').classList.remove('bg-blue-500');
        paypalOption.querySelector('.w-5.h-5').classList.remove('border-blue-500', 'bg-blue-500');
        paypalOption.querySelector('.w-5.h-5').classList.add('border-gray-300');
        paypalOption.querySelector('.w-2.h-2').classList.add('hidden');
        
        // Update radio buttons
        stripeRadio.checked = true;
        paypalRadio.checked = false;
        
        // Update info text
        stripeInfo.classList.remove('hidden');
        paypalInfo.classList.add('hidden');
        
        // Show/hide purchase sections
        stripePurchaseButton.classList.remove('hidden');
        paypalPurchaseSection.classList.add('hidden');
        
    } else if (method === 'paypal') {
        // Update visual selection
        paypalOption.classList.add('border-blue-500', 'bg-blue-50');
        paypalOption.classList.remove('border-gray-300');
        paypalOption.querySelector('.w-5.h-5 > div').classList.add('bg-blue-500');
        paypalOption.querySelector('.w-5.h-5').classList.add('border-blue-500', 'bg-blue-500');
        paypalOption.querySelector('.w-5.h-5').classList.remove('border-gray-300');
        paypalOption.querySelector('.w-2.h-2').classList.remove('hidden');
        
        stripeOption.classList.remove('border-blue-500', 'bg-blue-50');
        stripeOption.classList.add('border-gray-300');
        stripeOption.querySelector('.w-5.h-5 > div').classList.remove('bg-blue-500');
        stripeOption.querySelector('.w-5.h-5').classList.remove('border-blue-500', 'bg-blue-500');
        stripeOption.querySelector('.w-5.h-5').classList.add('border-gray-300');
        stripeOption.querySelector('.w-2.h-2').classList.add('hidden');
        
        // Update radio buttons
        paypalRadio.checked = true;
        stripeRadio.checked = false;
        
        // Update info text
        paypalInfo.classList.remove('hidden');
        stripeInfo.classList.add('hidden');
        
        // Show/hide purchase sections
        stripePurchaseButton.classList.add('hidden');
        paypalPurchaseSection.classList.remove('hidden');
        
        // Initialize PayPal button if not already done
        if (!paypalButtonRendered && typeof paypal !== 'undefined') {
            initializePayPalButton();
        }
    }
}

// PayPal Integration Functions
function initializePayPalButton() {
    if (typeof paypal === 'undefined') {
        console.error('PayPal SDK not loaded');
        return;
    }
    
    // Clear any existing PayPal buttons
    document.getElementById('paypal-button-container').innerHTML = '';
    
    paypal.Buttons({
        style: {
            layout: 'vertical',
            color: 'blue',
            shape: 'rect',
            label: 'pay',
            height: 50
        },
        
        createOrder: function(data, actions) {
            // Validate required checkboxes before creating order
            const acceptTerms = document.querySelector('input[name="accept_terms"]');
            const confirmPurchase = document.querySelector('input[name="confirm_purchase"]');
            
            if (!acceptTerms?.checked || !confirmPurchase?.checked) {
                showNotification('error', 'Please accept the terms and confirm your purchase.');
                return Promise.reject('Terms not accepted');
            }
            
            const quantity = currentQuantity;
            const unitPrice = {{ $ticket->price }};
            const subtotal = unitPrice * quantity;
            const processingFee = subtotal * 0.03;
            const total = subtotal + processingFee + 2.50;
            
            return actions.order.create({
                purchase_units: [{
                    amount: {
                        value: total.toFixed(2),
                        currency_code: 'USD',
                        breakdown: {
                            item_total: {
                                value: subtotal.toFixed(2),
                                currency_code: 'USD'
                            },
                            handling: {
                                value: (processingFee + 2.50).toFixed(2),
                                currency_code: 'USD'
                            }
                        }
                    },
                    items: [{
                        name: '{{ addslashes($ticket->title) }}',
                        quantity: quantity.toString(),
                        unit_amount: {
                            value: unitPrice.toFixed(2),
                            currency_code: 'USD'
                        },
                        category: 'DIGITAL_GOODS'
                    }],
                    description: 'Sports Event Ticket Purchase',
                    custom_id: 'ticket_{{ $ticket->id }}',
                    invoice_id: 'HDT_' + Date.now()
                }],
                application_context: {
                    brand_name: 'HD Tickets',
                    locale: 'en-GB',
                    user_action: 'PAY_NOW'
                }
            });
        },
        
        onApprove: function(data, actions) {
            return handlePayPalApproval(data.orderID);
        },
        
        onError: function(err) {
            console.error('PayPal error:', err);
            showNotification('error', 'PayPal payment failed. Please try again or use a different payment method.');
        },
        
        onCancel: function(data) {
            console.log('PayPal payment cancelled:', data);
            document.getElementById('paypal-errors').textContent = 'Payment cancelled. You can try again.';
        }
    }).render('#paypal-button-container');
    
    paypalButtonRendered = true;
}

// Handle PayPal payment approval
async function handlePayPalApproval(orderID) {
    try {
        showNotification('info', 'Processing PayPal payment...');
        
        // Get form data
        const formData = new FormData(document.getElementById('purchaseForm'));
        formData.set('payment_method', 'paypal');
        formData.set('paypal_order_id', orderID);
        
        const response = await fetch('{{ route("tickets.purchase", $ticket) }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('success', 'PayPal payment completed successfully!');
            setTimeout(() => {
                window.location.href = data.redirect_url || '/tickets/purchase-history';
            }, 2000);
        } else {
            throw new Error(data.message || 'Payment processing failed');
        }
    } catch (error) {
        console.error('PayPal approval error:', error);
        showNotification('error', error.message || 'Payment processing failed. Please try again.');
    }

    function showNotification(type, message) {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 max-w-sm w-full shadow-lg rounded-lg p-4 ${
            type === 'success' ? 'bg-green-100 border border-green-400' : 'bg-red-100 border border-red-400'
        }`;
        
        notification.innerHTML = `
            <div class="flex">
                <div class="flex-shrink-0">
                    ${type === 'success' ? 
                        '<svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>' :
                        '<svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>'
                    }
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium ${type === 'success' ? 'text-green-800' : 'text-red-800'}">${message}</p>
                </div>
                <div class="ml-auto pl-3">
                    <button class="inline-flex ${type === 'success' ? 'text-green-400 hover:text-green-600' : 'text-red-400 hover:text-red-600'}" onclick="this.parentElement.parentElement.parentElement.remove()">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                </div>
            </div>
        `;

        document.body.appendChild(notification);

        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 5000);
    }
});
</script>
@endsection
