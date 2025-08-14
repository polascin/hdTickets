@props(['ticket'])

<div
    x-data="ticketAvailability(@js($ticket))"
    x-init="initializeEcho"
    x-cloak
    class="p-4 bg-white rounded-lg shadow transition-all duration-200 hover:shadow-md"
>
    <div class="flex items-center justify-between">
        <h3 class="text-lg font-semibold" x-text="ticket.title"></h3>
        <span 
            :class="stockStatusClass"
            class="px-2 py-1 text-sm font-medium rounded-full transition-colors duration-200"
            x-text="stockStatusText"
        ></span>
        
        <!-- Error message display -->
        <div x-show="error" x-cloak 
             class="mt-2 p-2 text-sm text-red-700 bg-red-100 rounded border border-red-200"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100"
             x-text="error"></div>
    </div>

    <div class="mt-4">
        <div class="flex items-center justify-between">
            <span class="text-gray-600">Available Tickets:</span>
            <span 
                class="text-lg font-bold"
                x-text="ticket.available_quantity"
            ></span>
        </div>
        <div class="flex items-center justify-between mt-2">
            <span class="text-gray-600">Price:</span>
            <span class="text-lg font-bold">
                {{ $ticket->currency }} <span x-text="formatPrice(ticket.price)"></span>
            </span>
        </div>
    </div>

    <div class="mt-4">
        <button
            x-show="ticket.is_available && ticket.available_quantity > 0"
            @click="buyTicket"
            :disabled="loading"
            :class="loading ? 'opacity-50 cursor-not-allowed' : 'hover:bg-blue-700'"
            class="w-full px-4 py-2 text-white bg-blue-600 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200"
        >
            <span x-show="!loading">Buy Ticket</span>
            <span x-show="loading" x-cloak class="flex items-center justify-center">
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Processing...
            </span>
        </button>
        <button
            x-show="!ticket.is_available || ticket.available_quantity === 0"
            x-cloak
            disabled
            class="w-full px-4 py-2 text-white bg-gray-400 rounded cursor-not-allowed transition-all duration-200"
        >
            Sold Out
        </button>
        
        <!-- Low stock warning -->
        <div x-show="isLowStock" x-cloak 
             class="mt-2 p-2 text-sm text-yellow-700 bg-yellow-100 rounded border border-yellow-200"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100">
            ⚠️ Only <span x-text="ticket.available_quantity"></span> tickets left!
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('ticketAvailability', (initialTicket) => ({
        ticket: initialTicket,
        loading: false,
        error: null,
        
        init() {
            // Enhanced initialization with error handling
            this.initializeEcho();
            
            // Watch for ticket changes and update UI accordingly
            this.$watch('ticket.available_quantity', (value) => {
                if (value <= 0) {
                    this.ticket.is_available = false;
                }
            });
        },
        
        initializeEcho() {
            try {
                if (typeof Echo !== 'undefined') {
                    Echo.channel('tickets')
                        .listen('TicketAvailabilityUpdated', (e) => {
                            if (e.id === this.ticket.id) {
                                this.updateTicket(e);
                            }
                        })
                        .error((error) => {
                            console.error('Echo error for ticket updates:', error);
                            this.error = 'Real-time updates temporarily unavailable';
                        });
                } else {
                    console.warn('Laravel Echo not available - real-time updates disabled');
                }
            } catch (error) {
                console.error('Failed to initialize Echo for tickets:', error);
                this.error = 'Real-time updates unavailable';
            }
        },
        
        updateTicket(updatedData) {
            // Smooth ticket data update with transition
            const previousAvailability = this.ticket.is_available;
            this.ticket = { ...this.ticket, ...updatedData };
            
            // Show notification if availability changed
            if (previousAvailability !== this.ticket.is_available) {
                this.showAvailabilityChange(previousAvailability);
            }
        },
        
        showAvailabilityChange(wasAvailable) {
            const message = this.ticket.is_available 
                ? 'Tickets are now available!' 
                : 'Tickets are now sold out';
            const type = this.ticket.is_available ? 'success' : 'warning';
            
            // Use Alpine store notification if available
            if (Alpine.store('app')?.notify) {
                Alpine.store('app').notify('Availability Update', message, type);
            }
        },
        
        formatPrice(price) {
            return new Intl.NumberFormat('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(price);
        },
        
        async buyTicket() {
            if (!this.ticket.is_available || this.ticket.available_quantity <= 0) {
                this.showError('This ticket is no longer available');
                return;
            }
            
            this.loading = true;
            this.error = null;
            
            try {
                // Optimistic UI update - decrease quantity immediately
                this.ticket.available_quantity--;
                
                // Navigate to purchase page
                window.location.href = this.ticket.ticket_url;
            } catch (error) {
                // Revert optimistic update on error
                this.ticket.available_quantity++;
                this.showError('Failed to process ticket purchase');
                this.loading = false;
            }
        },
        
        showError(message) {
            this.error = message;
            // Clear error after 5 seconds
            setTimeout(() => {
                this.error = null;
            }, 5000);
        },
        
        get isLowStock() {
            return this.ticket.available_quantity > 0 && this.ticket.available_quantity <= 5;
        },
        
        get stockStatusClass() {
            if (!this.ticket.is_available || this.ticket.available_quantity === 0) {
                return 'text-red-700 bg-red-100';
            } else if (this.isLowStock) {
                return 'text-yellow-700 bg-yellow-100';
            } else {
                return 'text-green-700 bg-green-100';
            }
        },
        
        get stockStatusText() {
            if (!this.ticket.is_available || this.ticket.available_quantity === 0) {
                return 'Sold Out';
            } else if (this.isLowStock) {
                return 'Limited Stock';
            } else {
                return 'Available';
            }
        }
    }));
});
</script>
@endpush
