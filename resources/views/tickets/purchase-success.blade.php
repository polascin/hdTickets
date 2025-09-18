@extends('layouts.app-v2')

@section('title', 'Purchase Successful!')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Success Header --}}
        <div class="text-center mb-8">
            <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-green-100 mb-4">
                <svg class="h-10 w-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-900">Purchase Successful!</h1>
            <p class="text-lg text-gray-600 mt-2">Your ticket purchase has been completed successfully.</p>
        </div>

        <div class="lg:grid lg:grid-cols-12 lg:gap-x-12">
            {{-- Purchase Details --}}
            <div class="lg:col-span-8">
                <div class="bg-white shadow-lg rounded-lg overflow-hidden mb-6">
                    <div class="bg-green-50 px-6 py-4 border-b border-green-200">
                        <h2 class="text-xl font-semibold text-green-800">Purchase Confirmation</h2>
                        <p class="text-sm text-green-600 mt-1">
                            Order #{{ $purchase->id }} - {{ $purchase->created_at->format('F j, Y \a\t g:i A') }}
                        </p>
                    </div>

                    <div class="px-6 py-6">
                        {{-- Order Status --}}
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-green-400 rounded-full mr-3"></div>
                                <span class="text-sm font-medium text-gray-900">
                                    Status: {{ ucfirst($purchase->status) }}
                                </span>
                            </div>
                            <div class="text-sm text-gray-500">
                                Purchase ID: {{ $purchase->purchase_id }}
                            </div>
                        </div>

                        {{-- Ticket Information --}}
                        <div class="border-t border-gray-200 pt-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Ticket Details</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <h4 class="font-medium text-gray-700 mb-2">Event Information</h4>
                                    <div class="space-y-1 text-sm text-gray-600">
                                        <p class="font-semibold text-gray-900">{{ $purchase->ticket->title }}</p>
                                        
                                        @if($purchase->ticket->event_date)
                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                                {{ $purchase->ticket->event_date->format('F j, Y \a\t g:i A') }}
                                            </div>
                                        @endif
                                        
                                        @if($purchase->ticket->venue)
                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                </svg>
                                                {{ $purchase->ticket->venue }}
                                            </div>
                                        @endif

                                        @if($purchase->ticket->location)
                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                                </svg>
                                                {{ $purchase->ticket->location }}
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div>
                                    <h4 class="font-medium text-gray-700 mb-2">Purchase Details</h4>
                                    <div class="space-y-1 text-sm text-gray-600">
                                        <div class="flex justify-between">
                                            <span>Quantity:</span>
                                            <span class="font-medium">{{ $purchase->quantity }} {{ $purchase->quantity == 1 ? 'ticket' : 'tickets' }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span>Unit Price:</span>
                                            <span class="font-medium">${{ number_format($purchase->unit_price, 2) }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span>Subtotal:</span>
                                            <span class="font-medium">${{ number_format($purchase->subtotal, 2) }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span>Total Amount:</span>
                                            <span class="font-semibold text-gray-900">${{ number_format($purchase->total_amount, 2) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Seat Preferences --}}
                            @if($purchase->seat_preferences)
                                <div class="mt-6">
                                    <h4 class="font-medium text-gray-700 mb-2">Seat Preferences</h4>
                                    <div class="bg-gray-50 rounded-lg p-4">
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                            @if(!empty($purchase->seat_preferences['section']))
                                                <div>
                                                    <span class="text-gray-600">Section:</span>
                                                    <span class="font-medium ml-1">{{ $purchase->seat_preferences['section'] }}</span>
                                                </div>
                                            @endif
                                            
                                            @if(!empty($purchase->seat_preferences['row']))
                                                <div>
                                                    <span class="text-gray-600">Row:</span>
                                                    <span class="font-medium ml-1">{{ $purchase->seat_preferences['row'] }}</span>
                                                </div>
                                            @endif
                                            
                                            @if(!empty($purchase->seat_preferences['seat_type']))
                                                <div>
                                                    <span class="text-gray-600">Type:</span>
                                                    <span class="font-medium ml-1">{{ ucfirst($purchase->seat_preferences['seat_type']) }}</span>
                                                </div>
                                            @endif
                                        </div>
                                        
                                        @if(!empty($purchase->seat_preferences['accessibility_needs']))
                                            <div class="mt-2 text-sm text-blue-600">
                                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                Accessibility accommodations requested
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            {{-- Special Requests --}}
                            @if($purchase->special_requests)
                                <div class="mt-6">
                                    <h4 class="font-medium text-gray-700 mb-2">Special Requests</h4>
                                    <div class="bg-gray-50 rounded-lg p-4">
                                        <p class="text-sm text-gray-600">{{ $purchase->special_requests }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Next Steps & Actions --}}
            <div class="lg:col-span-4">
                <div class="bg-white shadow-lg rounded-lg overflow-hidden sticky top-8">
                    <div class="px-6 py-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Next Steps</h3>
                        
                        {{-- Important Information --}}
                        <div class="bg-blue-50 border border-blue-200 rounded-md p-4 mb-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h4 class="text-sm font-medium text-blue-800">Important Notice</h4>
                                    <div class="mt-2 text-sm text-blue-700">
                                        <ul class="list-disc list-inside space-y-1">
                                            <li>A confirmation email has been sent to {{ auth()->user()->email }}</li>
                                            <li>Ticket delivery details will be provided separately</li>
                                            <li>Please save this confirmation for your records</li>
                                            <li>Contact support if you have any questions</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="space-y-3">
                            <a href="{{ route('tickets.purchase-history') }}" 
                               class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 font-medium transition-colors duration-200 text-center block">
                                View Purchase History
                            </a>
                            
                            <a href="{{ route('tickets.main') }}"
                               class="w-full bg-white text-gray-700 py-2 px-4 rounded-md border border-gray-300 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 font-medium transition-colors duration-200 text-center block">
                                Browse More Tickets
                            </a>
                            
                            <a href="{{ route('dashboard') }}" 
                               class="w-full bg-white text-gray-700 py-2 px-4 rounded-md border border-gray-300 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 font-medium transition-colors duration-200 text-center block">
                                Return to Dashboard
                            </a>
                        </div>

                        {{-- Contact Information --}}
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <h4 class="text-sm font-medium text-gray-900 mb-2">Need Help?</h4>
                            <p class="text-sm text-gray-600 mb-3">
                                If you have any questions about your purchase or need assistance, please contact our support team.
                            </p>
                            <div class="text-sm text-gray-600">
                                <div class="flex items-center mb-1">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                    support@hdtickets.com
                                </div>
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                    +1 (555) 123-4567
                                </div>
                            </div>
                        </div>

                        {{-- Social Sharing --}}
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <h4 class="text-sm font-medium text-gray-900 mb-3">Share the Excitement!</h4>
                            <div class="flex space-x-2">
                                <button onclick="shareOnFacebook()" 
                                        class="flex-1 bg-blue-600 text-white py-2 px-3 rounded text-xs font-medium hover:bg-blue-700 transition-colors duration-200">
                                    Facebook
                                </button>
                                <button onclick="shareOnTwitter()" 
                                        class="flex-1 bg-sky-500 text-white py-2 px-3 rounded text-xs font-medium hover:bg-sky-600 transition-colors duration-200">
                                    Twitter
                                </button>
                                <button onclick="copyLink()" 
                                        class="flex-1 bg-gray-500 text-white py-2 px-3 rounded text-xs font-medium hover:bg-gray-600 transition-colors duration-200">
                                    Copy Link
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Additional Information --}}
        <div class="mt-8 bg-yellow-50 border border-yellow-200 rounded-md p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">Reminder</h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <p>
                            Please remember that all ticket sales are final and non-refundable as stated in our 
                            <a href="{{ route('legal.terms-of-service') }}" class="underline font-medium">Terms of Service</a>. 
                            Make sure to review all event details and your purchase information carefully.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- JavaScript for Social Sharing --}}
<script>
function shareOnFacebook() {
    const url = encodeURIComponent(window.location.origin);
    const text = encodeURIComponent('Just got tickets to {{ $purchase->ticket->title }}! #SportTickets #HDTickets');
    window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}&quote=${text}`, '_blank', 'width=600,height=400');
}

function shareOnTwitter() {
    const url = encodeURIComponent(window.location.origin);
    const text = encodeURIComponent('Just got tickets to {{ $purchase->ticket->title }}! 🎟️ #SportTickets #HDTickets');
    window.open(`https://twitter.com/intent/tweet?text=${text}&url=${url}`, '_blank', 'width=600,height=400');
}

function copyLink() {
    const url = window.location.origin;
    navigator.clipboard.writeText(url).then(() => {
        // Show success message
        showNotification('success', 'Link copied to clipboard!');
    }).catch(() => {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = url;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        showNotification('success', 'Link copied to clipboard!');
    });
}

function showNotification(type, message) {
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

    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}

// Auto-scroll to top on page load
document.addEventListener('DOMContentLoaded', function() {
    window.scrollTo(0, 0);
});
</script>
@endsection
