<x-unified-layout title="{{ $ticket->event_title }}" subtitle="{{ $ticket->venue }} • {{ $ticket->event_date->format('M j, Y') }}">
  <x-slot name="headerActions">
    <div class="flex items-center space-x-3">
      <!-- Share Button -->
      <button @click="shareTicket()" class="flex items-center bg-gray-100 text-gray-600 px-3 py-2 rounded-lg text-sm font-medium hover:bg-gray-200 transition">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"></path>
        </svg>
        Share
      </button>

      <!-- Watchlist Toggle -->
      <button @click="toggleWatchlist()" 
              :class="isWatched ? 'bg-red-100 text-red-600 border-red-200' : 'bg-blue-100 text-blue-600 border-blue-200'"
              class="flex items-center px-3 py-2 rounded-lg text-sm font-medium border hover:opacity-80 transition">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
        </svg>
        <span x-text="isWatched ? 'Watching' : 'Watch'">Watch</span>
      </button>
    </div>
  </x-slot>

  <div x-data="ticketDetails()" x-init="init()" class="space-y-8">
    
    <!-- Hero Section -->
    <div class="relative bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-600 rounded-lg overflow-hidden">
      <!-- Background Image -->
      <div class="absolute inset-0">
        <img src="{{ $ticket->image_url ?? asset('images/default-event.jpg') }}" 
             alt="{{ $ticket->event_title }}"
             class="w-full h-full object-cover opacity-20">
      </div>

      <div class="relative p-8">
        <div class="max-w-4xl mx-auto">
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
            <!-- Event Info -->
            <div class="lg:col-span-2 text-white">
              <div class="flex items-center space-x-3 mb-4">
                <x-ui.badge variant="primary" class="bg-white/20 text-white border-white/30">
                  {{ ucfirst($ticket->sport) }}
                </x-ui.badge>
                <div x-show="ticket.status" class="px-3 py-1 rounded-full text-xs font-medium"
                     :class="{
                       'bg-green-500 text-white': ticket.status === 'available',
                       'bg-red-500 text-white': ticket.status === 'limited',
                       'bg-orange-500 text-white': ticket.status === 'selling-fast'
                     }">
                  <span x-text="ticket.status?.replace('-', ' ')">{{ $ticket->availability_status }}</span>
                </div>
              </div>

              <h1 class="text-4xl font-bold mb-4">{{ $ticket->event_title }}</h1>
              
              <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 text-white/90">
                <div class="flex items-center">
                  <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                  </svg>
                  <div>
                    <div class="font-medium">{{ $ticket->venue }}</div>
                    <div class="text-sm opacity-75">{{ $ticket->city }}, {{ $ticket->state }}</div>
                  </div>
                </div>

                <div class="flex items-center">
                  <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                  </svg>
                  <div>
                    <div class="font-medium">{{ $ticket->event_date->format('M j, Y') }}</div>
                    <div class="text-sm opacity-75">{{ $ticket->event_date->format('l') }}</div>
                  </div>
                </div>

                <div class="flex items-center">
                  <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                  <div>
                    <div class="font-medium">{{ $ticket->event_time ?? 'TBD' }}</div>
                    <div class="text-sm opacity-75">Local Time</div>
                  </div>
                </div>
              </div>

              <!-- Quick Stats -->
              <div class="mt-8 grid grid-cols-3 gap-6">
                <div class="text-center">
                  <div class="text-2xl font-bold" x-text="formatCurrency(currentPrice)">{{ $ticket->formatted_price }}</div>
                  <div class="text-sm opacity-75">Starting Price</div>
                </div>
                <div class="text-center">
                  <div class="text-2xl font-bold" 
                       :class="priceChange >= 0 ? 'text-red-300' : 'text-green-300'"
                       x-text="priceChange >= 0 ? '+' + priceChange + '%' : priceChange + '%'">
                    {{ $ticket->price_change_24h }}%
                  </div>
                  <div class="text-sm opacity-75">24h Change</div>
                </div>
                <div class="text-center">
                  <div class="text-2xl font-bold">{{ $ticket->estimated_availability ?? 'Many' }}</div>
                  <div class="text-sm opacity-75">Available</div>
                </div>
              </div>
            </div>

            <!-- Purchase Card -->
            <div class="bg-white/10 backdrop-blur-md rounded-lg p-6 border border-white/20">
              <div class="text-center mb-6">
                <div class="text-3xl font-bold text-white mb-2" x-text="formatCurrency(currentPrice)">{{ $ticket->formatted_price }}</div>
                <div class="text-white/70 text-sm">Best available price</div>
              </div>

              <!-- Role-based Purchase Button -->
              @if(Auth::user()->role === 'customer')
                @if(Auth::user()->hasActiveSubscription() || Auth::user()->isWithinFreeTrial())
                  <button @click="initiatePurchase()" 
                          :disabled="purchaseLoading || ticket.status === 'sold-out'"
                          class="w-full bg-green-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition mb-4">
                    <span x-show="!purchaseLoading">Purchase Tickets</span>
                    <span x-show="purchaseLoading" class="flex items-center justify-center">
                      <svg class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                      </svg>
                      Processing...
                    </span>
                  </button>
                @else
                  <div class="bg-yellow-500/20 border border-yellow-500/30 rounded-lg p-4 mb-4">
                    <div class="text-yellow-200 text-sm text-center">
                      <svg class="w-5 h-5 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                      </svg>
                      <div class="font-medium">Subscription Required</div>
                      <div class="mt-1">Upgrade to purchase tickets</div>
                    </div>
                  </div>
                  <a href="{{ route('subscription.plans') }}" 
                     class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-blue-700 transition text-center block">
                    View Subscription Plans
                  </a>
                @endif
              @elseif(Auth::user()->role === 'agent')
                <button @click="initiatePurchase()" 
                        :disabled="purchaseLoading || ticket.status === 'sold-out'"
                        class="w-full bg-green-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition mb-4">
                  <span x-show="!purchaseLoading">Purchase Tickets (Unlimited)</span>
                  <span x-show="purchaseLoading" class="flex items-center justify-center">
                    <svg class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Processing...
                  </span>
                </button>
              @endif

              <!-- Price Alert Setup -->
              <button @click="showPriceAlertModal = true" 
                      class="w-full bg-white/10 text-white py-2 px-4 rounded-lg font-medium hover:bg-white/20 transition border border-white/20 text-sm">
                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4.343 12.344l1.414-1.414L6.5 11.5"></path>
                </svg>
                Set Price Alert
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <div class="lg:col-span-2 space-y-8">
        
        <!-- Price History Chart -->
        <x-ui.card>
          <x-ui.card-header title="Price History">
            <div class="flex items-center space-x-2">
              <button @click="priceHistoryRange = '7d'" 
                      :class="priceHistoryRange === '7d' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600'"
                      class="px-3 py-1 rounded text-sm">7D</button>
              <button @click="priceHistoryRange = '30d'" 
                      :class="priceHistoryRange === '30d' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600'"
                      class="px-3 py-1 rounded text-sm">30D</button>
              <button @click="priceHistoryRange = '90d'" 
                      :class="priceHistoryRange === '90d' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600'"
                      class="px-3 py-1 rounded text-sm">90D</button>
            </div>
          </x-ui.card-header>
          <x-ui.card-content>
            <div class="h-64">
              <canvas id="priceChart" class="w-full h-full"></canvas>
            </div>
            <div class="mt-4 grid grid-cols-3 gap-4 text-sm">
              <div class="text-center">
                <div class="font-bold text-lg text-green-600" x-text="formatCurrency(priceStats.lowest)">--</div>
                <div class="text-gray-500">Lowest Price</div>
              </div>
              <div class="text-center">
                <div class="font-bold text-lg text-gray-900" x-text="formatCurrency(priceStats.average)">--</div>
                <div class="text-gray-500">Average Price</div>
              </div>
              <div class="text-center">
                <div class="font-bold text-lg text-red-600" x-text="formatCurrency(priceStats.highest)">--</div>
                <div class="text-gray-500">Highest Price</div>
              </div>
            </div>
          </x-ui.card-content>
        </x-ui.card>

        <!-- Event Details -->
        <x-ui.card>
          <x-ui.card-header title="Event Details"></x-ui.card-header>
          <x-ui.card-content>
            <div class="space-y-6">
              <!-- Description -->
              @if($ticket->description)
                <div>
                  <h3 class="font-semibold text-gray-900 mb-2">About This Event</h3>
                  <p class="text-gray-700 leading-relaxed">{{ $ticket->description }}</p>
                </div>
              @endif

              <!-- Teams/Participants -->
              @if($ticket->teams)
                <div>
                  <h3 class="font-semibold text-gray-900 mb-3">Teams</h3>
                  <div class="flex items-center justify-center space-x-8">
                    @foreach($ticket->teams as $team)
                      <div class="text-center">
                        @if($team['logo'])
                          <img src="{{ $team['logo'] }}" alt="{{ $team['name'] }}" class="w-16 h-16 mx-auto mb-2 rounded-full">
                        @else
                          <div class="w-16 h-16 mx-auto mb-2 bg-gray-200 rounded-full flex items-center justify-center">
                            <span class="text-2xl font-bold text-gray-500">{{ substr($team['name'], 0, 1) }}</span>
                          </div>
                        @endif
                        <div class="font-medium text-gray-900">{{ $team['name'] }}</div>
                        @if($team['record'])
                          <div class="text-sm text-gray-500">{{ $team['record'] }}</div>
                        @endif
                      </div>
                      @if(!$loop->last)
                        <div class="text-2xl font-bold text-gray-400">VS</div>
                      @endif
                    @endforeach
                  </div>
                </div>
              @endif

              <!-- Venue Information -->
              <div>
                <h3 class="font-semibold text-gray-900 mb-3">Venue Information</h3>
                <div class="bg-gray-50 rounded-lg p-4">
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <div class="font-medium text-gray-900">{{ $ticket->venue }}</div>
                      <div class="text-gray-600">{{ $ticket->venue_address ?? 'Address not available' }}</div>
                      <div class="text-gray-600">{{ $ticket->city }}, {{ $ticket->state }}</div>
                    </div>
                    @if($ticket->venue_capacity)
                      <div>
                        <div class="text-sm text-gray-500">Capacity</div>
                        <div class="font-medium text-gray-900">{{ number_format($ticket->venue_capacity) }}</div>
                      </div>
                    @endif
                  </div>
                </div>
              </div>

              <!-- Important Information -->
              <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <h4 class="font-medium text-yellow-900 mb-2">⚠️ Important Information</h4>
                <ul class="text-sm text-yellow-800 space-y-1">
                  <li>• All ticket sales are final - no refunds or exchanges</li>
                  <li>• Prices may vary based on seat location and availability</li>
                  <li>• Event date and time subject to change</li>
                  <li>• Additional fees may apply during checkout</li>
                  <li>• Valid ID may be required for entry</li>
                </ul>
              </div>
            </div>
          </x-ui.card-content>
        </x-ui.card>
      </div>

      <!-- Sidebar -->
      <div class="space-y-6">
        
        <!-- Similar Events -->
        <x-ui.card>
          <x-ui.card-header title="Similar Events"></x-ui.card-header>
          <x-ui.card-content>
            <div class="space-y-4">
              <template x-for="similarEvent in similarEvents" :key="similarEvent.id">
                <div class="flex space-x-3 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition cursor-pointer"
                     @click="window.location.href = `/tickets/${similarEvent.id}`">
                  <img :src="similarEvent.image" :alt="similarEvent.title" 
                       class="w-12 h-12 rounded object-cover bg-gray-200">
                  <div class="flex-1 min-w-0">
                    <div class="font-medium text-gray-900 text-sm truncate" x-text="similarEvent.title"></div>
                    <div class="text-xs text-gray-500" x-text="similarEvent.venue"></div>
                    <div class="text-xs text-gray-500" x-text="formatDate(similarEvent.date)"></div>
                  </div>
                  <div class="text-right">
                    <div class="font-bold text-sm text-gray-900" x-text="formatCurrency(similarEvent.price)"></div>
                  </div>
                </div>
              </template>
            </div>
          </x-ui.card-content>
        </x-ui.card>

        <!-- Event Stats -->
        <x-ui.card>
          <x-ui.card-header title="Event Statistics"></x-ui.card-header>
          <x-ui.card-content>
            <div class="space-y-4">
              <div class="flex justify-between items-center">
                <span class="text-gray-600 text-sm">Views Today</span>
                <span class="font-medium text-gray-900" x-text="eventStats.viewsToday">{{ $ticket->views_today ?? '0' }}</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-gray-600 text-sm">Watching</span>
                <span class="font-medium text-gray-900" x-text="eventStats.watching">{{ $ticket->watchers_count ?? '0' }}</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-gray-600 text-sm">Price Alerts Set</span>
                <span class="font-medium text-gray-900" x-text="eventStats.alertsSet">{{ $ticket->alerts_count ?? '0' }}</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-gray-600 text-sm">Last Updated</span>
                <span class="font-medium text-gray-900 text-sm" x-text="formatTimeAgo('{{ $ticket->updated_at }}')">{{ $ticket->updated_at->diffForHumans() }}</span>
              </div>
            </div>
          </x-ui.card-content>
        </x-ui.card>

        <!-- Share Widget -->
        <x-ui.card>
          <x-ui.card-header title="Share This Event"></x-ui.card-header>
          <x-ui.card-content>
            <div class="grid grid-cols-2 gap-3">
              <button @click="shareOn('twitter')" class="flex items-center justify-center bg-blue-400 text-white py-2 px-3 rounded text-sm hover:bg-blue-500 transition">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                </svg>
                Twitter
              </button>
              
              <button @click="shareOn('facebook')" class="flex items-center justify-center bg-blue-600 text-white py-2 px-3 rounded text-sm hover:bg-blue-700 transition">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                </svg>
                Facebook
              </button>
              
              <button @click="copyLink()" class="col-span-2 flex items-center justify-center bg-gray-100 text-gray-700 py-2 px-3 rounded text-sm hover:bg-gray-200 transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                </svg>
                Copy Link
              </button>
            </div>
          </x-ui.card-content>
        </x-ui.card>
      </div>
    </div>

    <!-- Price Alert Modal -->
    <div x-show="showPriceAlertModal" x-cloak class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50" @click.self="showPriceAlertModal = false">
      <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-gray-900">Set Price Alert</h3>
          <p class="text-sm text-gray-600">Get notified when the price drops below your target</p>
        </div>
        <div class="px-6 py-4">
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Current Price</label>
              <div class="text-2xl font-bold text-gray-900" x-text="formatCurrency(currentPrice)"></div>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Alert When Price Drops To</label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">$</span>
                <input type="number" 
                       x-model="alertPrice"
                       :max="currentPrice"
                       step="0.01"
                       class="block w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                       placeholder="0.00">
              </div>
              <p class="text-xs text-gray-500 mt-1">Must be less than current price</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Notification Method</label>
              <div class="space-y-2">
                <label class="flex items-center">
                  <input type="checkbox" x-model="alertMethods" value="email" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                  <span class="ml-2 text-sm text-gray-700">Email notification</span>
                </label>
                @if(Auth::user()->phone_verified_at)
                  <label class="flex items-center">
                    <input type="checkbox" x-model="alertMethods" value="sms" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="ml-2 text-sm text-gray-700">SMS notification</span>
                  </label>
                @endif
              </div>
            </div>
          </div>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 flex justify-between">
          <button @click="showPriceAlertModal = false" class="text-gray-600 hover:text-gray-800 px-4 py-2 text-sm font-medium">
            Cancel
          </button>
          <button @click="createPriceAlert()" 
                  :disabled="!alertPrice || alertPrice >= currentPrice || alertMethods.length === 0"
                  class="bg-blue-600 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition">
            Create Alert
          </button>
        </div>
      </div>
    </div>
  </div>

  @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
      function ticketDetails() {
        return {
          // State
          isWatched: {{ $ticket->is_watched ? 'true' : 'false' }},
          purchaseLoading: false,
          showPriceAlertModal: false,
          priceHistoryRange: '30d',
          
          // Data
          ticket: @json($ticket),
          currentPrice: {{ $ticket->current_price }},
          priceChange: {{ $ticket->price_change_24h ?? 0 }},
          similarEvents: [],
          eventStats: {
            viewsToday: {{ $ticket->views_today ?? 0 }},
            watching: {{ $ticket->watchers_count ?? 0 }},
            alertsSet: {{ $ticket->alerts_count ?? 0 }}
          },
          priceStats: {
            lowest: 0,
            average: 0,
            highest: 0
          },
          
          // Price Alert
          alertPrice: '',
          alertMethods: ['email'],
          
          // Chart
          priceChart: null,

          async init() {
            this.loadSimilarEvents();
            this.loadPriceHistory();
            this.initializePriceChart();
            this.setupRealTimeUpdates();
          },

          async toggleWatchlist() {
            try {
              const response = await fetch(`/api/tickets/${this.ticket.id}/watchlist`, {
                method: this.isWatched ? 'DELETE' : 'POST',
                headers: {
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
              });

              if (response.ok) {
                this.isWatched = !this.isWatched;
                this.eventStats.watching += this.isWatched ? 1 : -1;
                this.showNotification(
                  this.isWatched ? 'Added to Watchlist' : 'Removed from Watchlist',
                  this.isWatched ? 'You\'ll receive price alerts for this event' : 'You\'ll no longer receive alerts',
                  'success'
                );
              }
            } catch (error) {
              this.showNotification('Error', 'Failed to update watchlist', 'error');
            }
          },

          async initiatePurchase() {
            this.purchaseLoading = true;
            
            try {
              // Redirect to purchase flow
              window.location.href = `/tickets/${this.ticket.id}/purchase`;
            } catch (error) {
              this.showNotification('Error', 'Failed to initiate purchase', 'error');
            } finally {
              this.purchaseLoading = false;
            }
          },

          async createPriceAlert() {
            try {
              const response = await fetch('/api/price-alerts', {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                  ticket_id: this.ticket.id,
                  target_price: this.alertPrice,
                  notification_methods: this.alertMethods
                })
              });

              const data = await response.json();

              if (data.success) {
                this.showPriceAlertModal = false;
                this.eventStats.alertsSet += 1;
                this.showNotification('Price Alert Created', 'You\'ll be notified when the price drops to $' + this.alertPrice, 'success');
              } else {
                this.showNotification('Error', data.message || 'Failed to create price alert', 'error');
              }
            } catch (error) {
              this.showNotification('Error', 'Failed to create price alert', 'error');
            }
          },

          async loadSimilarEvents() {
            try {
              const response = await fetch(`/api/tickets/${this.ticket.id}/similar`);
              const data = await response.json();
              this.similarEvents = data.similar_events || [];
            } catch (error) {
              console.error('Failed to load similar events:', error);
            }
          },

          async loadPriceHistory() {
            try {
              const response = await fetch(`/api/tickets/${this.ticket.id}/price-history?range=${this.priceHistoryRange}`);
              const data = await response.json();
              
              this.priceStats = data.stats || this.priceStats;
              this.updatePriceChart(data.history || []);
            } catch (error) {
              console.error('Failed to load price history:', error);
            }
          },

          initializePriceChart() {
            const ctx = document.getElementById('priceChart').getContext('2d');
            this.priceChart = new Chart(ctx, {
              type: 'line',
              data: {
                labels: [],
                datasets: [{
                  label: 'Price ($)',
                  data: [],
                  borderColor: '#3b82f6',
                  backgroundColor: 'rgba(59, 130, 246, 0.1)',
                  borderWidth: 2,
                  fill: true,
                  tension: 0.4
                }]
              },
              options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                  legend: {
                    display: false
                  }
                },
                scales: {
                  y: {
                    beginAtZero: false,
                    ticks: {
                      callback: function(value) {
                        return '$' + value.toLocaleString();
                      }
                    }
                  }
                },
                interaction: {
                  intersect: false,
                  mode: 'index'
                }
              }
            });
          },

          updatePriceChart(historyData) {
            if (this.priceChart && historyData.length > 0) {
              this.priceChart.data.labels = historyData.map(item => item.date);
              this.priceChart.data.datasets[0].data = historyData.map(item => item.price);
              this.priceChart.update();
            }
          },

          shareTicket() {
            if (navigator.share) {
              navigator.share({
                title: this.ticket.event_title,
                text: `Check out ${this.ticket.event_title} at ${this.ticket.venue}`,
                url: window.location.href
              });
            } else {
              this.copyLink();
            }
          },

          shareOn(platform) {
            const url = encodeURIComponent(window.location.href);
            const text = encodeURIComponent(`Check out ${this.ticket.event_title} at ${this.ticket.venue}`);
            
            let shareUrl;
            switch (platform) {
              case 'twitter':
                shareUrl = `https://twitter.com/intent/tweet?url=${url}&text=${text}`;
                break;
              case 'facebook':
                shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${url}`;
                break;
            }
            
            if (shareUrl) {
              window.open(shareUrl, '_blank', 'width=600,height=400');
            }
          },

          copyLink() {
            navigator.clipboard.writeText(window.location.href).then(() => {
              this.showNotification('Link Copied', 'Event link copied to clipboard', 'success');
            });
          },

          setupRealTimeUpdates() {
            if (window.Echo) {
              window.Echo.channel(`ticket.${this.ticket.id}`)
                .listen('TicketPriceUpdated', (e) => {
                  this.currentPrice = e.price;
                  this.priceChange = e.priceChange;
                })
                .listen('TicketAvailabilityUpdated', (e) => {
                  this.ticket.status = e.status;
                });
            }
          },

          formatCurrency(value) {
            return new Intl.NumberFormat('en-US', {
              style: 'currency',
              currency: 'USD',
              minimumFractionDigits: 0,
              maximumFractionDigits: 0
            }).format(value);
          },

          formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { 
              month: 'short', 
              day: 'numeric',
              year: 'numeric'
            });
          },

          formatTimeAgo(timestamp) {
            const date = new Date(timestamp);
            const now = new Date();
            const diffInHours = Math.floor((now - date) / (1000 * 60 * 60));
            
            if (diffInHours < 1) return 'Just now';
            if (diffInHours < 24) return `${diffInHours}h ago`;
            if (diffInHours < 168) return `${Math.floor(diffInHours / 24)}d ago`;
            return this.formatDate(timestamp);
          },

          showNotification(title, message, type = 'info') {
            if (window.hdTicketsFeedback) {
              window.hdTicketsFeedback[type](title, message);
            }
          }
        };
      }
    </script>
  @endpush
</x-unified-layout>
