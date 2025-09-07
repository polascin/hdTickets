<x-unified-layout title="Notifications" subtitle="Manage your alerts, updates, and notification preferences">
  <x-slot name="headerActions">
    <div class="flex items-center space-x-3">
      <!-- Filter Buttons -->
      <div class="flex items-center space-x-2">
        <button @click="filterType = 'all'" 
                :class="filterType === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600'"
                class="px-3 py-2 rounded-lg text-sm font-medium hover:opacity-80 transition">
          All
        </button>
        <button @click="filterType = 'unread'" 
                :class="filterType === 'unread' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600'"
                class="px-3 py-2 rounded-lg text-sm font-medium hover:opacity-80 transition">
          Unread
        </button>
        <button @click="filterType = 'alerts'" 
                :class="filterType === 'alerts' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600'"
                class="px-3 py-2 rounded-lg text-sm font-medium hover:opacity-80 transition">
          Price Alerts
        </button>
      </div>

      <!-- Actions -->
      <div class="flex items-center space-x-2">
        <button @click="markAllAsRead()" 
                :disabled="!hasUnreadNotifications"
                class="text-blue-600 hover:text-blue-800 text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed">
          Mark All Read
        </button>
        <button @click="showPreferences = true" 
                class="flex items-center bg-gray-100 text-gray-700 px-3 py-2 rounded-lg text-sm font-medium hover:bg-gray-200 transition">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
          </svg>
          Settings
        </button>
      </div>
    </div>
  </x-slot>

  <div x-data="notificationCenter()" x-init="init()" class="space-y-6">
    
    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
      <x-ui.card>
        <x-ui.card-content class="p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5z"></path>
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm text-gray-600">Total Notifications</p>
              <p class="text-2xl font-bold text-gray-900" x-text="stats.total">0</p>
            </div>
          </div>
        </x-ui.card-content>
      </x-ui.card>

      <x-ui.card>
        <x-ui.card-content class="p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm text-gray-600">Unread</p>
              <p class="text-2xl font-bold text-red-600" x-text="stats.unread">0</p>
            </div>
          </div>
        </x-ui.card-content>
      </x-ui.card>

      <x-ui.card>
        <x-ui.card-content class="p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm text-gray-600">Price Alerts</p>
              <p class="text-2xl font-bold text-yellow-600" x-text="stats.priceAlerts">0</p>
            </div>
          </div>
        </x-ui.card-content>
      </x-ui.card>

      <x-ui.card>
        <x-ui.card-content class="p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm text-gray-600">This Week</p>
              <p class="text-2xl font-bold text-green-600" x-text="stats.thisWeek">0</p>
            </div>
          </div>
        </x-ui.card-content>
      </x-ui.card>
    </div>

    <!-- Notifications List -->
    <x-ui.card>
      <x-ui.card-header title="Recent Notifications">
        <div class="text-sm text-gray-500" x-text="`Showing ${filteredNotifications.length} of ${notifications.length} notifications`"></div>
      </x-ui.card-header>
      <x-ui.card-content class="p-0">
        <div class="divide-y divide-gray-200 max-h-96 overflow-y-auto">
          <template x-for="notification in paginatedNotifications" :key="notification.id">
            <div class="p-4 hover:bg-gray-50 transition cursor-pointer"
                 :class="{ 'bg-blue-50 border-l-4 border-blue-400': !notification.read_at }"
                 @click="markAsRead(notification)">
              <div class="flex items-start space-x-4">
                <!-- Notification Icon -->
                <div class="flex-shrink-0 mt-1">
                  <div :class="getNotificationIcon(notification.type).bgColor" class="w-10 h-10 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5" :class="getNotificationIcon(notification.type).textColor" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getNotificationIcon(notification.type).path"></path>
                    </svg>
                  </div>
                </div>

                <!-- Notification Content -->
                <div class="flex-1 min-w-0">
                  <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-gray-900" x-text="notification.title"></p>
                    <div class="flex items-center space-x-2">
                      <span class="text-xs text-gray-500" x-text="formatTimeAgo(notification.created_at)"></span>
                      <div x-show="!notification.read_at" class="w-2 h-2 bg-blue-600 rounded-full"></div>
                    </div>
                  </div>
                  
                  <p class="text-sm text-gray-600 mt-1" x-text="notification.message"></p>
                  
                  <!-- Action Buttons -->
                  <div class="mt-3 flex items-center space-x-3" x-show="notification.actions && notification.actions.length > 0">
                    <template x-for="action in notification.actions" :key="action.label">
                      <button @click="handleNotificationAction(notification, action, $event)"
                              :class="action.primary ? 'bg-blue-600 text-white hover:bg-blue-700' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                              class="px-3 py-1 rounded text-xs font-medium transition">
                        <span x-text="action.label"></span>
                      </button>
                    </template>
                  </div>

                  <!-- Related Data -->
                  <div class="mt-3" x-show="notification.data">
                    <!-- Price Alert Data -->
                    <div x-show="notification.type === 'price_alert'" class="flex items-center space-x-4 text-xs text-gray-600">
                      <span>Target: <span class="font-medium" x-text="formatCurrency(notification.data?.target_price)"></span></span>
                      <span>Current: <span class="font-medium text-green-600" x-text="formatCurrency(notification.data?.current_price)"></span></span>
                      <span x-show="notification.data?.savings" class="text-green-600 font-medium">
                        Saved <span x-text="formatCurrency(notification.data?.savings)"></span>
                      </span>
                    </div>

                    <!-- Event Update Data -->
                    <div x-show="notification.type === 'event_update'" class="text-xs text-gray-600">
                      <span x-text="notification.data?.event_name"></span>
                      <span x-show="notification.data?.venue"> • <span x-text="notification.data?.venue"></span></span>
                      <span x-show="notification.data?.date"> • <span x-text="formatDate(notification.data?.date)"></span></span>
                    </div>
                  </div>
                </div>

                <!-- Notification Actions Menu -->
                <div class="relative" x-data="{ open: false }">
                  <button @click="open = !open" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01"></path>
                    </svg>
                  </button>
                  
                  <div x-show="open" @click.away="open = false" x-cloak 
                       class="absolute right-0 mt-1 w-48 bg-white rounded-md shadow-lg z-10 border border-gray-200">
                    <div class="py-1">
                      <button @click="markAsRead(notification); open = false" 
                              x-show="!notification.read_at"
                              class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        Mark as read
                      </button>
                      <button @click="markAsUnread(notification); open = false" 
                              x-show="notification.read_at"
                              class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        Mark as unread
                      </button>
                      <button @click="deleteNotification(notification); open = false" 
                              class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                        Delete
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </template>

          <!-- Empty State -->
          <div x-show="filteredNotifications.length === 0" class="p-12 text-center">
            <svg class="mx-auto w-12 h-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4.343 12.344l1.414-1.414L6.5 11.5"></path>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No notifications</h3>
            <p class="text-gray-500" x-text="getEmptyStateMessage()"></p>
          </div>
        </div>

        <!-- Pagination -->
        <div x-show="totalPages > 1" class="px-4 py-3 border-t border-gray-200 flex items-center justify-between">
          <div class="text-sm text-gray-500">
            Showing <span x-text="(currentPage - 1) * perPage + 1"></span> to 
            <span x-text="Math.min(currentPage * perPage, filteredNotifications.length)"></span> of 
            <span x-text="filteredNotifications.length"></span> notifications
          </div>
          
          <div class="flex items-center space-x-2">
            <button @click="goToPage(currentPage - 1)" 
                    :disabled="currentPage === 1"
                    class="px-3 py-1 border border-gray-300 rounded text-sm hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
              Previous
            </button>
            
            <template x-for="page in getPaginationRange()" :key="page">
              <button @click="goToPage(page)" 
                      :class="page === currentPage ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'"
                      class="px-3 py-1 border border-gray-300 rounded text-sm">
                <span x-text="page"></span>
              </button>
            </template>
            
            <button @click="goToPage(currentPage + 1)" 
                    :disabled="currentPage === totalPages"
                    class="px-3 py-1 border border-gray-300 rounded text-sm hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
              Next
            </button>
          </div>
        </div>
      </x-ui.card-content>
    </x-ui.card>

    <!-- Notification Preferences Modal -->
    <div x-show="showPreferences" x-cloak class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50" @click.self="showPreferences = false">
      <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-gray-900">Notification Preferences</h3>
          <p class="text-sm text-gray-600">Manage how and when you receive notifications</p>
        </div>
        
        <div class="px-6 py-4">
          <div class="space-y-6">
            
            <!-- General Settings -->
            <div>
              <h4 class="text-base font-medium text-gray-900 mb-4">General Settings</h4>
              <div class="space-y-3">
                <label class="flex items-center justify-between">
                  <div>
                    <div class="text-sm font-medium text-gray-700">Browser Notifications</div>
                    <div class="text-xs text-gray-500">Receive real-time notifications in your browser</div>
                  </div>
                  <input type="checkbox" x-model="preferences.browser_notifications" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                </label>
                
                <label class="flex items-center justify-between">
                  <div>
                    <div class="text-sm font-medium text-gray-700">Sound Notifications</div>
                    <div class="text-xs text-gray-500">Play sound when receiving important alerts</div>
                  </div>
                  <input type="checkbox" x-model="preferences.sound_notifications" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                </label>
                
                <label class="flex items-center justify-between">
                  <div>
                    <div class="text-sm font-medium text-gray-700">Marketing Emails</div>
                    <div class="text-xs text-gray-500">Receive promotional offers and updates</div>
                  </div>
                  <input type="checkbox" x-model="preferences.marketing_emails" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                </label>
              </div>
            </div>

            <!-- Price Alerts -->
            <div>
              <h4 class="text-base font-medium text-gray-900 mb-4">Price Alerts</h4>
              <div class="space-y-3">
                <label class="flex items-center justify-between">
                  <div>
                    <div class="text-sm font-medium text-gray-700">Email Alerts</div>
                    <div class="text-xs text-gray-500">Send price drop alerts to your email</div>
                  </div>
                  <input type="checkbox" x-model="preferences.price_alerts_email" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                </label>
                
                <label class="flex items-center justify-between" x-show="hasVerifiedPhone">
                  <div>
                    <div class="text-sm font-medium text-gray-700">SMS Alerts</div>
                    <div class="text-xs text-gray-500">Send urgent price drops via SMS</div>
                  </div>
                  <input type="checkbox" x-model="preferences.price_alerts_sms" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                </label>
                
                <div class="mt-3">
                  <label class="block text-sm font-medium text-gray-700 mb-2">Alert Frequency</label>
                  <select x-model="preferences.alert_frequency" class="block w-full border border-gray-300 rounded-lg px-3 py-2">
                    <option value="immediate">Immediate</option>
                    <option value="hourly">Hourly Digest</option>
                    <option value="daily">Daily Digest</option>
                  </select>
                </div>
              </div>
            </div>

            <!-- Event Updates -->
            <div>
              <h4 class="text-base font-medium text-gray-900 mb-4">Event Updates</h4>
              <div class="space-y-3">
                <label class="flex items-center justify-between">
                  <div>
                    <div class="text-sm font-medium text-gray-700">Schedule Changes</div>
                    <div class="text-xs text-gray-500">Notify when event dates or times change</div>
                  </div>
                  <input type="checkbox" x-model="preferences.event_updates" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                </label>
                
                <label class="flex items-center justify-between">
                  <div>
                    <div class="text-sm font-medium text-gray-700">New Events</div>
                    <div class="text-xs text-gray-500">Notify about new events matching your interests</div>
                  </div>
                  <input type="checkbox" x-model="preferences.new_events" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                </label>
                
                <label class="flex items-center justify-between">
                  <div>
                    <div class="text-sm font-medium text-gray-700">Event Reminders</div>
                    <div class="text-xs text-gray-500">Remind me before purchased events</div>
                  </div>
                  <input type="checkbox" x-model="preferences.event_reminders" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                </label>
              </div>
            </div>

            <!-- Quiet Hours -->
            <div>
              <h4 class="text-base font-medium text-gray-900 mb-4">Quiet Hours</h4>
              <div class="space-y-3">
                <label class="flex items-center justify-between">
                  <div>
                    <div class="text-sm font-medium text-gray-700">Enable Quiet Hours</div>
                    <div class="text-xs text-gray-500">Pause non-urgent notifications during specified times</div>
                  </div>
                  <input type="checkbox" x-model="preferences.enable_quiet_hours" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                </label>
                
                <div x-show="preferences.enable_quiet_hours" class="grid grid-cols-2 gap-3">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Start Time</label>
                    <input type="time" x-model="preferences.quiet_hours_start" class="block w-full border border-gray-300 rounded-lg px-3 py-2">
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">End Time</label>
                    <input type="time" x-model="preferences.quiet_hours_end" class="block w-full border border-gray-300 rounded-lg px-3 py-2">
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <div class="px-6 py-4 border-t border-gray-200 flex justify-between">
          <button @click="showPreferences = false" class="text-gray-600 hover:text-gray-800 px-4 py-2 text-sm font-medium">
            Cancel
          </button>
          <button @click="savePreferences()" 
                  :disabled="savingPreferences"
                  class="bg-blue-600 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition">
            <span x-show="!savingPreferences">Save Preferences</span>
            <span x-show="savingPreferences" class="flex items-center">
              <svg class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Saving...
            </span>
          </button>
        </div>
      </div>
    </div>
  </div>

  @push('scripts')
    <script>
      function notificationCenter() {
        return {
          // State
          loading: true,
          filterType: 'all',
          showPreferences: false,
          savingPreferences: false,
          currentPage: 1,
          perPage: 10,

          // Data
          notifications: [],
          stats: {
            total: 0,
            unread: 0,
            priceAlerts: 0,
            thisWeek: 0
          },

          // Preferences
          preferences: {
            browser_notifications: true,
            sound_notifications: false,
            marketing_emails: false,
            price_alerts_email: true,
            price_alerts_sms: false,
            event_updates: true,
            new_events: true,
            event_reminders: true,
            enable_quiet_hours: false,
            quiet_hours_start: '22:00',
            quiet_hours_end: '08:00',
            alert_frequency: 'immediate'
          },

          hasVerifiedPhone: {{ Auth::user()->phone_verified_at ? 'true' : 'false' }},

          async init() {
            this.loading = true;
            await Promise.all([
              this.loadNotifications(),
              this.loadStats(),
              this.loadPreferences()
            ]);
            this.loading = false;
            this.setupRealTimeUpdates();
            this.requestNotificationPermission();
          },

          async loadNotifications() {
            try {
              const response = await fetch('/api/user/notifications');
              const data = await response.json();
              
              if (data.success) {
                this.notifications = data.notifications || [];
              }
            } catch (error) {
              console.error('Failed to load notifications:', error);
            }
          },

          async loadStats() {
            try {
              const response = await fetch('/api/user/notifications/stats');
              const data = await response.json();
              
              if (data.success) {
                this.stats = { ...this.stats, ...data.stats };
              }
            } catch (error) {
              console.error('Failed to load stats:', error);
            }
          },

          async loadPreferences() {
            try {
              const response = await fetch('/api/user/notification-preferences');
              const data = await response.json();
              
              if (data.success) {
                this.preferences = { ...this.preferences, ...data.preferences };
              }
            } catch (error) {
              console.error('Failed to load preferences:', error);
            }
          },

          get filteredNotifications() {
            switch (this.filterType) {
              case 'unread':
                return this.notifications.filter(n => !n.read_at);
              case 'alerts':
                return this.notifications.filter(n => n.type === 'price_alert');
              default:
                return this.notifications;
            }
          },

          get paginatedNotifications() {
            const start = (this.currentPage - 1) * this.perPage;
            const end = start + this.perPage;
            return this.filteredNotifications.slice(start, end);
          },

          get totalPages() {
            return Math.ceil(this.filteredNotifications.length / this.perPage);
          },

          get hasUnreadNotifications() {
            return this.notifications.some(n => !n.read_at);
          },

          async markAsRead(notification) {
            if (notification.read_at) return;

            try {
              const response = await fetch(`/api/user/notifications/${notification.id}/read`, {
                method: 'POST',
                headers: {
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
              });

              if (response.ok) {
                notification.read_at = new Date().toISOString();
                this.stats.unread = Math.max(0, this.stats.unread - 1);
              }
            } catch (error) {
              console.error('Failed to mark notification as read:', error);
            }
          },

          async markAsUnread(notification) {
            if (!notification.read_at) return;

            try {
              const response = await fetch(`/api/user/notifications/${notification.id}/unread`, {
                method: 'POST',
                headers: {
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
              });

              if (response.ok) {
                notification.read_at = null;
                this.stats.unread += 1;
              }
            } catch (error) {
              console.error('Failed to mark notification as unread:', error);
            }
          },

          async markAllAsRead() {
            try {
              const response = await fetch('/api/user/notifications/mark-all-read', {
                method: 'POST',
                headers: {
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
              });

              if (response.ok) {
                this.notifications.forEach(notification => {
                  if (!notification.read_at) {
                    notification.read_at = new Date().toISOString();
                  }
                });
                this.stats.unread = 0;
                this.showNotification('Success', 'All notifications marked as read', 'success');
              }
            } catch (error) {
              this.showNotification('Error', 'Failed to mark all notifications as read', 'error');
            }
          },

          async deleteNotification(notification) {
            if (!confirm('Are you sure you want to delete this notification?')) return;

            try {
              const response = await fetch(`/api/user/notifications/${notification.id}`, {
                method: 'DELETE',
                headers: {
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
              });

              if (response.ok) {
                const index = this.notifications.findIndex(n => n.id === notification.id);
                if (index !== -1) {
                  this.notifications.splice(index, 1);
                }
                this.stats.total = Math.max(0, this.stats.total - 1);
                if (!notification.read_at) {
                  this.stats.unread = Math.max(0, this.stats.unread - 1);
                }
              }
            } catch (error) {
              this.showNotification('Error', 'Failed to delete notification', 'error');
            }
          },

          handleNotificationAction(notification, action, event) {
            event.stopPropagation();
            
            switch (action.type) {
              case 'view_ticket':
                window.location.href = `/tickets/${action.ticket_id}`;
                break;
              case 'view_purchase':
                window.location.href = `/user/purchase-history/${action.purchase_id}`;
                break;
              case 'update_watchlist':
                window.location.href = '/user/watchlist';
                break;
              default:
                if (action.url) {
                  window.location.href = action.url;
                }
            }
          },

          async savePreferences() {
            this.savingPreferences = true;
            
            try {
              const response = await fetch('/api/user/notification-preferences', {
                method: 'PUT',
                headers: {
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(this.preferences)
              });

              const data = await response.json();

              if (data.success) {
                this.showPreferences = false;
                this.showNotification('Success', 'Notification preferences updated', 'success');
                
                // Update browser notification permission if enabled
                if (this.preferences.browser_notifications) {
                  this.requestNotificationPermission();
                }
              } else {
                this.showNotification('Error', data.message || 'Failed to update preferences', 'error');
              }
            } catch (error) {
              this.showNotification('Error', 'Failed to update preferences', 'error');
            } finally {
              this.savingPreferences = false;
            }
          },

          requestNotificationPermission() {
            if ('Notification' in window && this.preferences.browser_notifications) {
              if (Notification.permission === 'default') {
                Notification.requestPermission();
              }
            }
          },

          setupRealTimeUpdates() {
            if (window.Echo) {
              window.Echo.private(`user.${window.authUserId}.notifications`)
                .listen('NewNotification', (e) => {
                  this.notifications.unshift(e.notification);
                  this.stats.total += 1;
                  this.stats.unread += 1;
                  
                  // Show browser notification if enabled
                  if (this.preferences.browser_notifications && Notification.permission === 'granted') {
                    new Notification(e.notification.title, {
                      body: e.notification.message,
                      icon: '/favicon.ico'
                    });
                  }

                  // Play sound if enabled
                  if (this.preferences.sound_notifications) {
                    this.playNotificationSound();
                  }
                });
            }
          },

          playNotificationSound() {
            const audio = new Audio('/sounds/notification.mp3');
            audio.volume = 0.3;
            audio.play().catch(() => {
              // Sound play failed, ignore
            });
          },

          goToPage(page) {
            if (page >= 1 && page <= this.totalPages) {
              this.currentPage = page;
            }
          },

          getPaginationRange() {
            const current = this.currentPage;
            const total = this.totalPages;
            const range = [];
            
            let start = Math.max(1, current - 2);
            let end = Math.min(total, current + 2);
            
            for (let i = start; i <= end; i++) {
              range.push(i);
            }
            
            return range;
          },

          getNotificationIcon(type) {
            const icons = {
              price_alert: {
                bgColor: 'bg-yellow-100',
                textColor: 'text-yellow-600',
                path: 'M15 17h5l-5 5v-5z'
              },
              event_update: {
                bgColor: 'bg-blue-100',
                textColor: 'text-blue-600',
                path: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'
              },
              purchase_confirmation: {
                bgColor: 'bg-green-100',
                textColor: 'text-green-600',
                path: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'
              },
              system: {
                bgColor: 'bg-gray-100',
                textColor: 'text-gray-600',
                path: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
              }
            };
            return icons[type] || icons.system;
          },

          getEmptyStateMessage() {
            switch (this.filterType) {
              case 'unread':
                return 'No unread notifications';
              case 'alerts':
                return 'No price alerts yet';
              default:
                return 'No notifications yet';
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
              year: date.getFullYear() !== new Date().getFullYear() ? 'numeric' : undefined
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
