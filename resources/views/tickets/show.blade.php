<x-app-layout>
    <div class="min-h-screen bg-gray-50" x-data="ticketDetails()">
        {{-- Back Navigation --}}
        <div class="bg-white border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex items-center justify-between">
                    <button onclick="history.back()" class="flex items-center text-gray-600 hover:text-gray-900 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        Back to Search
                    </button>
                    
                    @if(auth()->user()->role === 'customer')
                        <div class="text-sm text-gray-600">
                            Monthly Usage: <span class="font-semibold">{{ auth()->user()->getMonthlyTicketUsage() ?? 0 }}/{{ auth()->user()->getMonthlyTicketLimit() ?? 100 }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="grid lg:grid-cols-3 gap-8">
                {{-- Main Content --}}
                <div class="lg:col-span-2">
                    {{-- Event Header --}}
                    <div class="bg-white rounded-2xl shadow-lg overflow-hidden mb-8">
                        <div class="relative">
                            <img :src="ticket.image || '/images/default-event.jpg'" 
                                 :alt="ticket.event_title"
                                 class="w-full h-64 object-cover">
                            <div class="absolute inset-0 bg-black bg-opacity-40"></div>
                            <div class="absolute bottom-6 left-6 text-white">
                                <div class="flex items-center space-x-4 mb-2">
                                    <span class="px-3 py-1 bg-indigo-600 rounded-full text-sm font-medium" x-text="ticket.sport"></span>
                                    <span x-show="ticket.discount_percentage" 
                                          class="px-3 py-1 bg-red-500 rounded-full text-sm font-bold" 
                                          x-text="ticket.discount_percentage + '% OFF'"></span>
                                </div>
                                <h1 class="text-3xl font-bold mb-2" x-text="ticket.event_title"></h1>
                                <div class="flex items-center space-x-6 text-sm">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        <span x-text="ticket.venue + ', ' + ticket.city"></span>
                                    </div>
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <span x-text="ticket.date_time"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="absolute top-6 right-6">
                                <button @click="toggleWatchlist()" 
                                        :class="ticket.in_watchlist ? 'text-red-500 bg-white' : 'text-white bg-black bg-opacity-50 hover:bg-opacity-70'"
                                        class="p-3 rounded-full transition-all duration-200 transform hover:scale-110">
                                    <svg class="w-6 h-6" :fill="ticket.in_watchlist ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Ticket Options --}}
                    <div class="bg-white rounded-2xl shadow-lg p-6 mb-8">
                        <h2 class="text-xl font-semibold text-gray-900 mb-6">Available Tickets</h2>
                        
                        <div class="space-y-4">
                            <template x-for="option in ticket.options" :key="option.id">
                                <div class="border border-gray-200 rounded-xl p-4 hover:border-indigo-300 transition-colors cursor-pointer"
                                     :class="selectedOption?.id === option.id ? 'border-indigo-500 bg-indigo-50' : ''"
                                     @click="selectOption(option)">
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center space-x-3">
                                                <div class="w-4 h-4 border border-gray-300 rounded-full flex items-center justify-center"
                                                     :class="selectedOption?.id === option.id ? 'border-indigo-500 bg-indigo-500' : ''">
                                                    <div x-show="selectedOption?.id === option.id" class="w-2 h-2 bg-white rounded-full"></div>
                                                </div>
                                                <div>
                                                    <h3 class="font-medium text-gray-900" x-text="option.section"></h3>
                                                    <p class="text-sm text-gray-600" x-text="option.row ? 'Row ' + option.row : 'General Admission'"></p>
                                                </div>
                                            </div>
                                            <div class="mt-2 flex items-center space-x-4 text-sm text-gray-500">
                                                <span x-text="option.quantity + ' available'"></span>
                                                <span class="flex items-center">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                    <span x-text="option.guarantee"></span>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-xl font-bold text-gray-900" x-text="'$' + option.price"></div>
                                            <div x-show="option.original_price && option.original_price > option.price" 
                                                 class="text-sm text-gray-400 line-through" 
                                                 x-text="'$' + option.original_price"></div>
                                            <div x-show="option.fees" 
                                                 class="text-xs text-gray-500" 
                                                 x-text="'+ $' + option.fees + ' fees'"></div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Event Details --}}
                    <div class="bg-white rounded-2xl shadow-lg p-6 mb-8">
                        <h2 class="text-xl font-semibold text-gray-900 mb-6">Event Details</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="font-medium text-gray-900 mb-3">Event Information</h3>
                                <dl class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <dt class="text-gray-600">Date & Time:</dt>
                                        <dd class="font-medium text-gray-900" x-text="ticket.date_time"></dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-gray-600">Duration:</dt>
                                        <dd class="font-medium text-gray-900" x-text="ticket.duration || 'Approx. 3 hours'"></dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-gray-600">League:</dt>
                                        <dd class="font-medium text-gray-900" x-text="ticket.league"></dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-gray-600">Season:</dt>
                                        <dd class="font-medium text-gray-900" x-text="ticket.season"></dd>
                                    </div>
                                </dl>
                            </div>
                            
                            <div>
                                <h3 class="font-medium text-gray-900 mb-3">Venue Information</h3>
                                <dl class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <dt class="text-gray-600">Venue:</dt>
                                        <dd class="font-medium text-gray-900" x-text="ticket.venue"></dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-gray-600">Address:</dt>
                                        <dd class="font-medium text-gray-900" x-text="ticket.address"></dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-gray-600">Capacity:</dt>
                                        <dd class="font-medium text-gray-900" x-text="ticket.capacity?.toLocaleString() || 'N/A'"></dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-gray-600">Parking:</dt>
                                        <dd class="font-medium text-gray-900" x-text="ticket.parking_info || 'Available'"></dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <div x-show="ticket.description" class="mt-6 pt-6 border-t border-gray-200">
                            <h3 class="font-medium text-gray-900 mb-3">About This Event</h3>
                            <p class="text-gray-600" x-text="ticket.description"></p>
                        </div>
                    </div>

                    {{-- Similar Events --}}
                    <div class="bg-white rounded-2xl shadow-lg p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-6">Similar Events</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <template x-for="similar in similarEvents" :key="similar.id">
                                <div class="border border-gray-200 rounded-xl p-4 hover:shadow-md transition-shadow cursor-pointer"
                                     @click="window.location.href = '/tickets/' + similar.id">
                                    <div class="flex items-start space-x-3">
                                        <img :src="similar.image || '/images/default-event.jpg'" 
                                             :alt="similar.event_title"
                                             class="w-16 h-16 object-cover rounded-lg">
                                        <div class="flex-1 min-w-0">
                                            <h3 class="font-medium text-gray-900 text-sm" x-text="similar.event_title"></h3>
                                            <p class="text-xs text-gray-600" x-text="similar.venue"></p>
                                            <p class="text-xs text-gray-500" x-text="similar.date"></p>
                                        </div>
                                        <div class="text-right">
                                            <div class="font-bold text-gray-900 text-sm" x-text="'$' + similar.price"></div>
                                            <div class="text-xs text-gray-500" x-text="similar.quantity + ' left'"></div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Purchase Sidebar --}}
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-2xl shadow-lg p-6 sticky top-8">
                        <div class="mb-6">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="text-lg font-semibold text-gray-900">Purchase Tickets</h3>
                                <span :class="`px-2 py-1 rounded-full text-xs font-medium ${ticket.status === 'available' ? 'bg-green-100 text-green-800' : ticket.status === 'limited' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'}`"
                                      x-text="ticket.status === 'available' ? 'Available' : ticket.status === 'limited' ? 'Limited' : 'Sold Out'"></span>
                            </div>
                            <p x-show="!selectedOption" class="text-sm text-gray-600">Select a ticket option to continue</p>
                        </div>

                        <div x-show="selectedOption">
                            {{-- Selected Option Summary --}}
                            <div class="mb-6 p-4 bg-indigo-50 border border-indigo-200 rounded-lg">
                                <h4 class="font-medium text-indigo-900 mb-2">Selected Ticket</h4>
                                <div class="space-y-1 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-indigo-700">Section:</span>
                                        <span class="font-medium text-indigo-900" x-text="selectedOption?.section"></span>
                                    </div>
                                    <div x-show="selectedOption?.row" class="flex justify-between">
                                        <span class="text-indigo-700">Row:</span>
                                        <span class="font-medium text-indigo-900" x-text="selectedOption?.row"></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-indigo-700">Quantity:</span>
                                        <span class="font-medium text-indigo-900" x-text="quantity"></span>
                                    </div>
                                </div>
                            </div>

                            {{-- Quantity Selector --}}
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
                                <div class="flex items-center space-x-3">
                                    <button @click="decreaseQuantity()" 
                                            :disabled="quantity <= 1"
                                            class="w-8 h-8 rounded-full border border-gray-300 flex items-center justify-center hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                        </svg>
                                    </button>
                                    <input type="number" 
                                           x-model.number="quantity"
                                           min="1" 
                                           :max="selectedOption?.quantity || 1"
                                           class="w-16 text-center border border-gray-300 rounded-lg py-1">
                                    <button @click="increaseQuantity()" 
                                            :disabled="quantity >= (selectedOption?.quantity || 1)"
                                            class="w-8 h-8 rounded-full border border-gray-300 flex items-center justify-center hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                    </button>
                                </div>
                                <p class="text-xs text-gray-500 mt-1" x-text="'Max ' + (selectedOption?.quantity || 1) + ' tickets available'"></p>
                            </div>

                            {{-- Seat Preferences --}}
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Seat Preferences (Optional)</label>
                                <select x-model="seatPreference" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                    <option value="">No preference</option>
                                    <option value="together">Seats together</option>
                                    <option value="aisle">Aisle seats preferred</option>
                                    <option value="center">Center section preferred</option>
                                    <option value="accessible">Accessible seating</option>
                                </select>
                            </div>

                            {{-- Price Breakdown --}}
                            <div class="mb-6 space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Ticket Price:</span>
                                    <span class="font-medium" x-text="'$' + ((selectedOption?.price || 0) * quantity).toFixed(2)"></span>
                                </div>
                                <div x-show="selectedOption?.fees" class="flex justify-between">
                                    <span class="text-gray-600">Service Fees:</span>
                                    <span class="font-medium" x-text="'$' + ((selectedOption?.fees || 0) * quantity).toFixed(2)"></span>
                                </div>
                                <div class="flex justify-between border-t border-gray-200 pt-2 font-semibold">
                                    <span>Total:</span>
                                    <span x-text="'$' + totalPrice.toFixed(2)"></span>
                                </div>
                            </div>

                            {{-- Special Requests --}}
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Special Requests</label>
                                <textarea x-model="specialRequests" 
                                          rows="3"
                                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"
                                          placeholder="Any special accommodations needed..."></textarea>
                            </div>

                            {{-- Terms Acceptance --}}
                            <div class="mb-6">
                                <label class="flex items-start space-x-2">
                                    <input type="checkbox" x-model="acceptTerms" class="mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-xs text-gray-600">
                                        I agree to the <a href="/legal/terms" target="_blank" class="text-indigo-600 hover:text-indigo-700">Terms of Service</a> 
                                        and understand that all ticket sales are final.
                                    </span>
                                </label>
                            </div>

                            {{-- Purchase Buttons --}}
                            <div class="space-y-3">
                                @if(auth()->user()->role === 'customer' || auth()->user()->role === 'agent')
                                    <button @click="purchaseTickets()" 
                                            :disabled="!acceptTerms || isPurchasing || !canPurchase()"
                                            class="w-full bg-indigo-600 text-white py-3 px-4 rounded-lg font-semibold hover:bg-indigo-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                        <span x-show="!isPurchasing">
                                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M8 11v6h8v-6M8 11H6a2 2 0 00-2 2v6a2 2 0 002 2h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2"/>
                                            </svg>
                                            Purchase Tickets
                                        </span>
                                        <span x-show="isPurchasing">Processing...</span>
                                    </button>
                                    
                                    <button @click="createPriceAlert()" 
                                            class="w-full bg-yellow-100 text-yellow-700 py-2 px-4 rounded-lg font-medium hover:bg-yellow-200 transition-colors">
                                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4 19l1-7H3l1 7zM15 3H4l11 4v12z"/>
                                        </svg>
                                        Set Price Alert
                                    </button>
                                @else
                                    <div class="text-center py-4">
                                        <p class="text-gray-600 mb-4">Sign in to purchase tickets</p>
                                        <a href="/login" class="bg-indigo-600 text-white py-2 px-4 rounded-lg font-medium hover:bg-indigo-700 transition-colors">
                                            Sign In
                                        </a>
                                    </div>
                                @endif
                            </div>

                            {{-- Purchase Restrictions --}}
                            @if(auth()->user()->role === 'customer')
                                <div x-show="!canPurchase()" class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                                    <div class="flex items-start space-x-2">
                                        <svg class="w-4 h-4 text-red-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.124 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                        </svg>
                                        <div class="text-sm text-red-700">
                                            <p class="font-medium">Purchase limit reached</p>
                                            <p class="mt-1">
                                                @if(!auth()->user()->hasActiveSubscription() && !auth()->user()->isInFreeTrial())
                                                    You need an active subscription to purchase tickets.
                                                @else
                                                    You've reached your monthly ticket limit. 
                                                @endif
                                                <a href="{{ route('subscriptions.dashboard') }}" class="underline hover:no-underline">Upgrade your plan</a>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Guarantee Information --}}
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <h4 class="font-medium text-gray-900 mb-3">Our Guarantee</h4>
                            <div class="space-y-2 text-xs text-gray-600">
                                <div class="flex items-start space-x-2">
                                    <svg class="w-3 h-3 text-green-500 mt-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    <span>100% Authentic tickets guaranteed</span>
                                </div>
                                <div class="flex items-start space-x-2">
                                    <svg class="w-3 h-3 text-green-500 mt-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    <span>Secure payment processing</span>
                                </div>
                                <div class="flex items-start space-x-2">
                                    <svg class="w-3 h-3 text-green-500 mt-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    <span>Customer support available 24/7</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function ticketDetails() {
            return {
                isPurchasing: false,
                selectedOption: null,
                quantity: 1,
                seatPreference: '',
                specialRequests: '',
                acceptTerms: false,

                ticket: {
                    id: {{ request()->route('id') ?? 1 }},
                    event_title: 'Lakers vs Warriors',
                    sport: 'Basketball',
                    venue: 'Crypto.com Arena',
                    city: 'Los Angeles, CA',
                    address: '1111 S Figueroa St, Los Angeles, CA 90015',
                    date_time: 'December 15, 2024 at 7:30 PM',
                    duration: '3 hours',
                    league: 'NBA',
                    season: '2024-25 Regular Season',
                    capacity: 20000,
                    parking_info: 'Available on-site ($30)',
                    description: 'Watch two of the NBA\'s most exciting teams battle it out in this Western Conference matchup. Don\'t miss the action as the Lakers take on the Warriors in what promises to be an unforgettable night of basketball.',
                    status: 'available',
                    in_watchlist: false,
                    discount_percentage: 26,
                    image: null,
                    options: [
                        {
                            id: 1,
                            section: 'Lower Bowl 101',
                            row: 'A',
                            price: 145,
                            original_price: 180,
                            fees: 15,
                            quantity: 8,
                            guarantee: '100% Authentic'
                        },
                        {
                            id: 2,
                            section: 'Upper Level 300',
                            row: 'M',
                            price: 89,
                            original_price: 120,
                            fees: 12,
                            quantity: 12,
                            guarantee: '100% Authentic'
                        },
                        {
                            id: 3,
                            section: 'Nosebleeds 400',
                            row: null,
                            price: 45,
                            original_price: null,
                            fees: 8,
                            quantity: 25,
                            guarantee: '100% Authentic'
                        }
                    ]
                },

                similarEvents: [
                    {
                        id: 2,
                        event_title: 'Lakers vs Clippers',
                        venue: 'Crypto.com Arena',
                        date: 'Dec 22, 2024',
                        price: 125,
                        quantity: 15,
                        image: null
                    },
                    {
                        id: 3,
                        event_title: 'Warriors vs Kings',
                        venue: 'Chase Center',
                        date: 'Dec 28, 2024',
                        price: 98,
                        quantity: 8,
                        image: null
                    }
                ],

                get totalPrice() {
                    if (!this.selectedOption) return 0;
                    return (this.selectedOption.price + (this.selectedOption.fees || 0)) * this.quantity;
                },

                selectOption(option) {
                    this.selectedOption = option;
                    this.quantity = Math.min(this.quantity, option.quantity);
                },

                increaseQuantity() {
                    if (this.quantity < (this.selectedOption?.quantity || 1)) {
                        this.quantity++;
                    }
                },

                decreaseQuantity() {
                    if (this.quantity > 1) {
                        this.quantity--;
                    }
                },

                canPurchase() {
                    @if(auth()->user()->role === 'customer')
                        const hasSubscription = {{ auth()->user()->hasActiveSubscription() || auth()->user()->isInFreeTrial() ? 'true' : 'false' }};
                        const monthlyUsage = {{ auth()->user()->getMonthlyTicketUsage() ?? 0 }};
                        const monthlyLimit = {{ auth()->user()->getMonthlyTicketLimit() ?? 100 }};
                        
                        return hasSubscription && (monthlyUsage + this.quantity) <= monthlyLimit;
                    @elseif(auth()->user()->role === 'agent')
                        return true;
                    @else
                        return false;
                    @endif
                },

                async toggleWatchlist() {
                    try {
                        const method = this.ticket.in_watchlist ? 'DELETE' : 'POST';
                        const url = this.ticket.in_watchlist ? `/api/v1/watchlist/${this.ticket.id}` : '/api/v1/watchlist';
                        
                        const response = await fetch(url, {
                            method,
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: method === 'POST' ? JSON.stringify({ ticket_id: this.ticket.id }) : null
                        });

                        const data = await response.json();
                        
                        if (data.success) {
                            this.ticket.in_watchlist = !this.ticket.in_watchlist;
                            this.showToast(
                                this.ticket.in_watchlist ? 'Added to watchlist' : 'Removed from watchlist', 
                                'success'
                            );
                        } else {
                            this.showToast(data.message || 'Failed to update watchlist', 'error');
                        }
                    } catch (error) {
                        console.error('Watchlist error:', error);
                        this.showToast('An error occurred', 'error');
                    }
                },

                async purchaseTickets() {
                    if (!this.selectedOption || !this.acceptTerms || !this.canPurchase()) return;

                    this.isPurchasing = true;

                    try {
                        const response = await fetch('/api/v1/tickets/purchase', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                ticket_id: this.ticket.id,
                                option_id: this.selectedOption.id,
                                quantity: this.quantity,
                                seat_preference: this.seatPreference,
                                special_requests: this.specialRequests,
                                total_amount: this.totalPrice
                            })
                        });

                        const data = await response.json();
                        
                        if (data.success) {
                            // Redirect to purchase confirmation
                            window.location.href = data.redirect_url || '/tickets/purchase-success';
                        } else {
                            this.showToast(data.message || 'Purchase failed. Please try again.', 'error');
                        }
                    } catch (error) {
                        console.error('Purchase error:', error);
                        this.showToast('An error occurred during purchase', 'error');
                    } finally {
                        this.isPurchasing = false;
                    }
                },

                async createPriceAlert() {
                    try {
                        const response = await fetch('/api/v1/alerts', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                ticket_id: this.ticket.id,
                                target_price: this.selectedOption?.price || this.ticket.options[0].price,
                                alert_type: 'price_drop'
                            })
                        });

                        const data = await response.json();
                        
                        if (data.success) {
                            this.showToast('Price alert created successfully!', 'success');
                        } else {
                            this.showToast(data.message || 'Failed to create price alert', 'error');
                        }
                    } catch (error) {
                        console.error('Alert error:', error);
                        this.showToast('An error occurred', 'error');
                    }
                },

                showToast(message, type = 'info') {
                    const toast = document.createElement('div');
                    const bgColor = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500';
                    toast.className = `fixed bottom-4 right-4 ${bgColor} text-white px-6 py-3 rounded-lg shadow-lg z-50 transform translate-x-full transition-transform`;
                    toast.textContent = message;
                    
                    document.body.appendChild(toast);
                    
                    requestAnimationFrame(() => {
                        toast.classList.remove('translate-x-full');
                    });
                    
                    setTimeout(() => {
                        toast.classList.add('translate-x-full');
                        setTimeout(() => toast.remove(), 300);
                    }, 5000);
                }
            }
        }
    </script>
</x-app-layout>
