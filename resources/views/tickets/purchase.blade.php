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

                                {{-- Purchase Button --}}
                                <button type="submit" id="purchaseButton"
                                        class="w-full bg-blue-600 text-white py-3 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 font-medium transition-colors duration-200 disabled:bg-gray-400 disabled:cursor-not-allowed">
                                    <span id="buttonText">Purchase Tickets</span>
                                    <svg id="loadingSpinner" class="hidden animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- JavaScript for Form Handling --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const quantitySelect = document.getElementById('quantity');
    const unitPrice = {{ $ticket->price }};
    const processingFeeRate = 0.03;
    const serviceFee = 2.50;

    // Update price calculation when quantity changes
    quantitySelect?.addEventListener('change', function() {
        const quantity = parseInt(this.value);
        updatePriceCalculation(quantity);
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

    // Handle form submission
    const purchaseForm = document.getElementById('purchaseForm');
    const purchaseButton = document.getElementById('purchaseButton');
    const buttonText = document.getElementById('buttonText');
    const loadingSpinner = document.getElementById('loadingSpinner');

    purchaseForm?.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Disable button and show loading
        purchaseButton.disabled = true;
        buttonText.textContent = 'Processing...';
        loadingSpinner.classList.remove('hidden');

        // Submit form via fetch API for better UX
        const formData = new FormData(this);
        
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
                    window.location.href = '/tickets/purchase-history';
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
