@props(['ticket'])

<div
    x-data="ticketAvailability(@js($ticket))"
    x-init="initializeEcho"
    class="p-4 bg-white rounded-lg shadow"
>
    <div class="flex items-center justify-between">
        <h3 class="text-lg font-semibold" x-text="ticket.title"></h3>
        <span 
            x-show="ticket.is_available"
            class="px-2 py-1 text-sm font-medium text-green-700 bg-green-100 rounded-full"
        >
            Available
        </span>
        <span 
            x-show="!ticket.is_available"
            class="px-2 py-1 text-sm font-medium text-red-700 bg-red-100 rounded-full"
        >
            Sold Out
        </span>
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
            class="w-full px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
        >
            Buy Ticket
        </button>
        <button
            x-show="!ticket.is_available || ticket.available_quantity === 0"
            disabled
            class="w-full px-4 py-2 text-white bg-gray-400 rounded cursor-not-allowed"
        >
            Sold Out
        </button>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('ticketAvailability', (initialTicket) => ({
        ticket: initialTicket,
        
        initializeEcho() {
            Echo.channel('tickets')
                .listen('TicketAvailabilityUpdated', (e) => {
                    if (e.id === this.ticket.id) {
                        this.ticket = { ...this.ticket, ...e };
                    }
                });
        },
        
        formatPrice(price) {
            return new Intl.NumberFormat('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(price);
        },
        
        buyTicket() {
            // Implementation for ticket purchase
            window.location.href = this.ticket.ticket_url;
        }
    }));
});
</script>
@endpush
