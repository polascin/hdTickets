<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Select Tickets for Purchase') }}
            </h2>
            <div class="flex space-x-3">
                <a href="{{ route('purchase-decisions.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    Back to Queue
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" class="grid grid-cols-1 md:grid-cols-6 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Platform</label>
                            <x-platform-select 
                                name="platform" 
                                :value="request('platform')" 
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" 
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Event Title</label>
                            <input type="text" name="event_title" value="{{ request('event_title') }}" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Search events...">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Min Price</label>
                            <input type="number" name="min_price" value="{{ request('min_price') }}" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="0.00" step="0.01">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Max Price</label>
                            <input type="number" name="max_price" value="{{ request('max_price') }}" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="1000.00" step="0.01">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">High Demand Only</label>
                            <select name="high_demand_only" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">All Tickets</option>
                                <option value="1" {{ request('high_demand_only') === '1' ? 'selected' : '' }}>High Demand Only</option>
                            </select>
                        </div>

                        <div class="flex items-end">
                            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                                Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Available Tickets -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Available Tickets</h3>
                        <div class="text-sm text-gray-500">
                            {{ $availableTickets->total() }} tickets found
                        </div>
                    </div>

                    @if($availableTickets->isEmpty())
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a1 1 0 001 1h1m0 0v4a2 2 0 002 2h2M5 5a2 2 0 012-2h3a1 1 0 011 1v1m0 0v4a2 2 0 012 2h2M5 5v2m0 4v2m0 4v2"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No available tickets</h3>
                            <p class="mt-1 text-sm text-gray-500">Try adjusting your filters to see more results.</p>
                        </div>
                    @else
                        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                            @foreach($availableTickets as $ticket)
                                <div class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <h4 class="text-lg font-medium text-gray-900 mb-2">
                                                {{ Str::limit($ticket->event_title, 50) }}
                                            </h4>
                                            
                                            <div class="space-y-2 mb-4">
                                                <div class="flex items-center text-sm text-gray-500">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    </svg>
                                                    {{ $ticket->venue }}
                                                </div>
                                                
                                                @if($ticket->event_date)
                                                    <div class="flex items-center text-sm text-gray-500">
                                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                        </svg>
                                                        {{ $ticket->event_date->format('M j, Y g:i A') }}
                                                    </div>
                                                @endif
                                                
                                                <div class="flex items-center text-sm text-gray-500">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    {{ $ticket->platform_display_name }}
                                                </div>

                                                @if($ticket->section)
                                                    <div class="flex items-center text-sm text-gray-500">
                                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"></path>
                                                        </svg>
                                                        Section {{ $ticket->section }}
                                                        @if($ticket->row)
                                                            • Row {{ $ticket->row }}
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <div class="text-2xl font-bold text-gray-900">
                                                        {{ $ticket->formatted_price }}
                                                    </div>
                                                    @if($ticket->fees && $ticket->fees > 0)
                                                        <div class="text-sm text-gray-500">
                                                            + {{ $ticket->currency }} {{ number_format($ticket->fees, 2) }} fees
                                                        </div>
                                                    @endif
                                                </div>
                                                
                                                <div class="text-right">
                                                    @if($ticket->is_high_demand)
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 mb-2">
                                                            High Demand
                                                        </span>
                                                    @endif
                                                    
                                                    @if($ticket->demand_score)
                                                        <div class="text-xs text-gray-500">
                                                            Score: {{ $ticket->demand_score }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="mt-4 pt-4 border-t border-gray-200">
                                                <div class="text-xs text-gray-500 mb-2">
                                                    Scraped {{ $ticket->scraped_at->diffForHumans() }}
                                                </div>
                                                
                                                <button onclick="openAddToQueueModal({{ $ticket->id }}, '{{ addslashes($ticket->event_title) }}', '{{ $ticket->platform }}', {{ $ticket->total_price }})" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                                                    Add to Purchase Queue
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-6">
                            {{ $availableTickets->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Add to Queue Modal -->
    <div id="addToQueueModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form id="addToQueueForm" method="POST" action="">
                    @csrf
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Add to Purchase Queue
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500" id="modalTicketInfo">
                                        <!-- Ticket info will be populated here -->
                                    </p>
                                </div>
                                
                                <div class="mt-4 space-y-4">
                                    <div>
                                        <label for="priority" class="block text-sm font-medium text-gray-700">Priority</label>
                                        <select id="priority" name="priority" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                                            <option value="medium">Medium</option>
                                            <option value="high">High</option>
                                            <option value="urgent">Urgent</option>
                                            <option value="critical">Critical</option>
                                            <option value="low">Low</option>
                                        </select>
                                    </div>
                                    
                                    <div>
                                        <label for="max_price" class="block text-sm font-medium text-gray-700">Max Price (Optional)</label>
                                        <input type="number" id="max_price" name="max_price" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Leave empty for no limit">
                                    </div>
                                    
                                    <div>
                                        <label for="quantity" class="block text-sm font-medium text-gray-700">Quantity</label>
                                        <input type="number" id="quantity" name="quantity" min="1" max="10" value="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                                    </div>
                                    
                                    <div>
                                        <label for="scheduled_for" class="block text-sm font-medium text-gray-700">Schedule For (Optional)</label>
                                        <input type="datetime-local" id="scheduled_for" name="scheduled_for" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>
                                    
                                    <div>
                                        <label for="expires_at" class="block text-sm font-medium text-gray-700">Expires At (Optional)</label>
                                        <input type="datetime-local" id="expires_at" name="expires_at" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>
                                    
                                    <div>
                                        <label for="notes" class="block text-sm font-medium text-gray-700">Notes (Optional)</label>
                                        <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Add any notes about this purchase..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Add to Queue
                        </button>
                        <button type="button" onclick="closeAddToQueueModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openAddToQueueModal(ticketId, eventTitle, platform, price) {
            const modal = document.getElementById('addToQueueModal');
            const form = document.getElementById('addToQueueForm');
            const modalInfo = document.getElementById('modalTicketInfo');
            
            // Set form action
            form.action = `/purchase-decisions/add-to-queue/${ticketId}`;
            
            // Update modal info
            modalInfo.innerHTML = `<strong>${eventTitle}</strong><br>Platform: ${platform} • Price: $${price}`;
            
            // Set default max price
            document.getElementById('max_price').value = price;
            
            // Show modal
            modal.classList.remove('hidden');
        }
        
        function closeAddToQueueModal() {
            const modal = document.getElementById('addToQueueModal');
            modal.classList.add('hidden');
            
            // Reset form
            document.getElementById('addToQueueForm').reset();
        }
        
        // Close modal when clicking outside
        document.getElementById('addToQueueModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeAddToQueueModal();
            }
        });
    </script>
</x-app-layout>
