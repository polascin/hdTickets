@extends('layouts.app-v2')

@section('title', 'Purchase Failed')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Failure Header --}}
        <div class="text-center mb-8">
            <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-red-100 mb-4">
                <svg class="h-10 w-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-900">Purchase Failed</h1>
            <p class="text-lg text-gray-600 mt-2">Unfortunately, we couldn't complete your ticket purchase.</p>
        </div>

        <div class="lg:grid lg:grid-cols-12 lg:gap-x-12">
            {{-- Error Details --}}
            <div class="lg:col-span-8">
                <div class="bg-white shadow-lg rounded-lg overflow-hidden mb-6">
                    <div class="bg-red-50 px-6 py-4 border-b border-red-200">
                        <h2 class="text-xl font-semibold text-red-800">Purchase Error</h2>
                        <p class="text-sm text-red-600 mt-1">
                            {{ $errorTime ?? now()->format('F j, Y \a\t g:i A') }}
                        </p>
                    </div>

                    <div class="px-6 py-6">
                        {{-- Error Message --}}
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-3">What went wrong?</h3>
                            
                            @if(isset($errorMessage))
                                <div class="bg-red-50 border border-red-200 rounded-md p-4">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm text-red-800 font-medium">{{ $errorMessage }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- Common Error Types --}}
                            <div class="mt-6">
                                <h4 class="text-sm font-medium text-gray-900 mb-3">Common reasons for purchase failure:</h4>
                                <div class="space-y-3 text-sm text-gray-600">
                                    <div class="flex items-start">
                                        <svg class="w-4 h-4 mt-0.5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <div>
                                            <span class="font-medium">Insufficient subscription tickets:</span>
                                            <span>You may have reached your monthly ticket limit.</span>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-start">
                                        <svg class="w-4 h-4 mt-0.5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <div>
                                            <span class="font-medium">Ticket no longer available:</span>
                                            <span>The ticket may have sold out while you were purchasing.</span>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-start">
                                        <svg class="w-4 h-4 mt-0.5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <div>
                                            <span class="font-medium">Payment processing error:</span>
                                            <span>There may have been an issue with the payment system.</span>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-start">
                                        <svg class="w-4 h-4 mt-0.5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <div>
                                            <span class="font-medium">Account verification required:</span>
                                            <span>Your account may need additional verification.</span>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-start">
                                        <svg class="w-4 h-4 mt-0.5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <div>
                                            <span class="font-medium">Technical system error:</span>
                                            <span>A temporary technical issue prevented the purchase.</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Ticket Information (if available) --}}
                        @if(isset($ticket))
                            <div class="border-t border-gray-200 pt-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Attempted Purchase Details</h3>
                                
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <p class="font-semibold text-gray-900 mb-2">{{ $ticket->title }}</p>
                                            
                                            @if($ticket->event_date)
                                                <div class="flex items-center text-sm text-gray-600 mb-1">
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
                                        </div>

                                        <div>
                                            @if(isset($attemptedQuantity))
                                                <div class="text-sm text-gray-600 mb-1">
                                                    <span class="font-medium">Attempted quantity:</span> {{ $attemptedQuantity }} {{ $attemptedQuantity == 1 ? 'ticket' : 'tickets' }}
                                                </div>
                                            @endif
                                            
                                            <div class="text-sm text-gray-600">
                                                <span class="font-medium">Price:</span> ${{ number_format($ticket->price, 2) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Next Steps & Actions --}}
            <div class="lg:col-span-4">
                <div class="bg-white shadow-lg rounded-lg overflow-hidden sticky top-8">
                    <div class="px-6 py-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">What can you do?</h3>
                        
                        {{-- Troubleshooting Steps --}}
                        <div class="bg-blue-50 border border-blue-200 rounded-md p-4 mb-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h4 class="text-sm font-medium text-blue-800">Quick Troubleshooting</h4>
                                    <div class="mt-2 text-sm text-blue-700">
                                        <ul class="list-disc list-inside space-y-1">
                                            <li>Check your subscription status and ticket limits</li>
                                            <li>Verify the ticket is still available</li>
                                            <li>Ensure your account is fully verified</li>
                                            <li>Try again in a few minutes</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="space-y-3">
                            @if(isset($ticket))
                                <a href="{{ route('tickets.purchase', $ticket) }}" 
                                   class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 font-medium transition-colors duration-200 text-center block">
                                    Try Purchase Again
                                </a>
                            @endif
                            
                            <a href="{{ route('dashboard') }}" 
                               class="w-full bg-gray-600 text-white py-2 px-4 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 font-medium transition-colors duration-200 text-center block">
                                Check Account Status
                            </a>
                            
                            <a href="{{ route('tickets.main') }}"
                               class="w-full bg-white text-gray-700 py-2 px-4 rounded-md border border-gray-300 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 font-medium transition-colors duration-200 text-center block">
                                Browse Other Tickets
                            </a>
                        </div>

                        {{-- Subscription Information for Customers --}}
                        @if(auth()->user() && auth()->user()->isCustomer())
                            <div class="mt-6 pt-6 border-t border-gray-200">
                                <h4 class="text-sm font-medium text-gray-900 mb-2">Your Subscription</h4>
                                <div class="bg-gray-50 rounded-lg p-3">
                                    <div class="text-sm text-gray-600 space-y-1">
                                        <div class="flex justify-between">
                                            <span>Monthly Usage:</span>
                                            <span class="font-medium">
                                                {{ auth()->user()->getMonthlyTicketUsage() }} / 
                                                {{ auth()->user()->subscription?->plan?->ticket_limit ?? config('subscription.default_ticket_limit') }}
                                            </span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span>Status:</span>
                                            <span class="font-medium {{ auth()->user()->hasActiveSubscription() ? 'text-green-600' : 'text-red-600' }}">
                                                {{ auth()->user()->hasActiveSubscription() ? 'Active' : 'Inactive' }}
                                            </span>
                                        </div>
                                    </div>
                                    
                                    @if(!auth()->user()->hasActiveSubscription())
                                        <div class="mt-3">
                                            <a href="{{ route('subscription.plans') }}" 
                                               class="text-sm text-blue-600 hover:text-blue-800 font-medium underline">
                                                View Subscription Plans
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        {{-- Contact Support --}}
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <h4 class="text-sm font-medium text-gray-900 mb-2">Need Help?</h4>
                            <p class="text-sm text-gray-600 mb-3">
                                If you're still experiencing issues, our support team is here to help.
                            </p>
                            
                            <div class="text-sm text-gray-600 mb-3">
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

                            <button onclick="openSupportChat()" 
                                    class="w-full bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 font-medium transition-colors duration-200 text-sm">
                                Contact Support Chat
                            </button>
                        </div>

                        {{-- Error Reference --}}
                        @if(isset($errorReference))
                            <div class="mt-6 pt-6 border-t border-gray-200">
                                <h4 class="text-sm font-medium text-gray-900 mb-2">Error Reference</h4>
                                <div class="bg-gray-50 rounded p-2">
                                    <code class="text-xs text-gray-600 font-mono">{{ $errorReference }}</code>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">
                                    Please include this reference when contacting support.
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Additional Help --}}
        <div class="mt-8">
            <div class="bg-gray-100 rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-3">Frequently Asked Questions</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="font-medium text-gray-900 mb-2">Why did my purchase fail?</h4>
                        <p class="text-sm text-gray-600">
                            Purchase failures can occur due to subscription limits, ticket availability, 
                            payment issues, or temporary technical problems.
                        </p>
                    </div>
                    
                    <div>
                        <h4 class="font-medium text-gray-900 mb-2">Will I be charged for a failed purchase?</h4>
                        <p class="text-sm text-gray-600">
                            No, you will not be charged for failed purchases. Any attempted charges 
                            will be automatically refunded within 3-5 business days.
                        </p>
                    </div>
                    
                    <div>
                        <h4 class="font-medium text-gray-900 mb-2">How do I increase my ticket limit?</h4>
                        <p class="text-sm text-gray-600">
                            Customers can upgrade their subscription plan for higher ticket limits. 
                            Agents and administrators have unlimited access.
                        </p>
                    </div>
                    
                    <div>
                        <h4 class="font-medium text-gray-900 mb-2">When should I contact support?</h4>
                        <p class="text-sm text-gray-600">
                            Contact support if the error persists, you need account verification, 
                            or if you have questions about subscription limits.
                        </p>
                    </div>
                </div>

                <div class="mt-6 text-center">
                    <a href="{{ route('help.faq') }}" 
                       class="text-sm text-blue-600 hover:text-blue-800 font-medium underline">
                        View Complete FAQ →
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- JavaScript for Support Chat --}}
<script>
function openSupportChat() {
    // This would typically open a support chat widget
    // For now, we'll show a notification with instructions
    showNotification('info', 'Support chat will be available soon. Please use email or phone for immediate assistance.');
}

function showNotification(type, message) {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 max-w-sm w-full shadow-lg rounded-lg p-4 ${
        type === 'success' ? 'bg-green-100 border border-green-400' : 
        type === 'info' ? 'bg-blue-100 border border-blue-400' : 
        'bg-red-100 border border-red-400'
    }`;
    
    const iconColor = type === 'success' ? 'green-400' : type === 'info' ? 'blue-400' : 'red-400';
    const textColor = type === 'success' ? 'green-800' : type === 'info' ? 'blue-800' : 'red-800';
    
    notification.innerHTML = `
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-${iconColor}" fill="currentColor" viewBox="0 0 20 20">
                    ${type === 'success' ? 
                        '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>' :
                        type === 'info' ?
                        '<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>' :
                        '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>'
                    }
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-${textColor}">${message}</p>
            </div>
            <div class="ml-auto pl-3">
                <button class="inline-flex text-${iconColor} hover:text-${iconColor.replace('400', '600')}" onclick="this.parentElement.parentElement.parentElement.remove()">
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
    }, 8000);
}

// Auto-scroll to top on page load
document.addEventListener('DOMContentLoaded', function() {
    window.scrollTo(0, 0);
});
</script>
@endsection
