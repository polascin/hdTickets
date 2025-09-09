<x-app-layout>
    <div class="min-h-screen bg-gray-50">
        {{-- Header --}}
        <div class="bg-white shadow-sm border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Account Settings</h1>
                        <p class="mt-1 text-sm text-gray-500">Manage your account preferences and security settings</p>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ auth()->user()->role === 'agent' ? 'purple' : (auth()->user()->role === 'admin' ? 'gray' : 'indigo') }}-100 text-{{ auth()->user()->role === 'agent' ? 'purple' : (auth()->user()->role === 'admin' ? 'gray' : 'indigo') }}-800">
                            {{ ucfirst(auth()->user()->role) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="accountSettings()">
            <div class="grid lg:grid-cols-4 gap-8">
                {{-- Settings Navigation --}}
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-2xl shadow-lg p-1">
                        <nav class="space-y-1">
                            <button @click="activeTab = 'profile'" 
                                    :class="activeTab === 'profile' ? 'bg-indigo-600 text-white' : 'text-gray-700 hover:bg-gray-100'"
                                    class="w-full flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-colors">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                Profile
                            </button>
                            <button @click="activeTab = 'security'" 
                                    :class="activeTab === 'security' ? 'bg-indigo-600 text-white' : 'text-gray-700 hover:bg-gray-100'"
                                    class="w-full flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-colors">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                Security
                            </button>
                            <button @click="activeTab = 'notifications'" 
                                    :class="activeTab === 'notifications' ? 'bg-indigo-600 text-white' : 'text-gray-700 hover:bg-gray-100'"
                                    class="w-full flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-colors">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4 19l1-7H3l1 7zM15 3H4l11 4v12z"/>
                                </svg>
                                Notifications
                            </button>
                            @if(auth()->user()->role === 'customer')
                                <button @click="activeTab = 'subscription'" 
                                        :class="activeTab === 'subscription' ? 'bg-indigo-600 text-white' : 'text-gray-700 hover:bg-gray-100'"
                                        class="w-full flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-colors">
                                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                    </svg>
                                    Subscription
                                </button>
                            @endif
                            <button @click="activeTab = 'privacy'" 
                                    :class="activeTab === 'privacy' ? 'bg-indigo-600 text-white' : 'text-gray-700 hover:bg-gray-100'"
                                    class="w-full flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-colors">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                                Privacy
                            </button>
                            <button @click="activeTab = 'danger'" 
                                    :class="activeTab === 'danger' ? 'bg-red-600 text-white' : 'text-gray-700 hover:bg-gray-100'"
                                    class="w-full flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-colors">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.124 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                </svg>
                                Account
                            </button>
                        </nav>
                    </div>
                </div>

                {{-- Settings Content --}}
                <div class="lg:col-span-3">
                    {{-- Profile Settings --}}
                    <div x-show="activeTab === 'profile'" class="space-y-6">
                        <div class="bg-white rounded-2xl shadow-lg p-6">
                            <h2 class="text-xl font-semibold text-gray-900 mb-6">Profile Information</h2>
                            
                            <form @submit.prevent="updateProfile()" class="space-y-6">
                                {{-- Avatar Upload --}}
                                <div class="flex items-center space-x-6">
                                    <div class="relative">
                                        <img class="w-24 h-24 rounded-full object-cover border-4 border-white shadow-lg" 
                                             :src="profileData.avatar || '/images/default-avatar.png'" 
                                             :alt="profileData.name + ' avatar'">
                                        <label for="avatar-upload" class="absolute bottom-0 right-0 bg-indigo-600 text-white rounded-full p-2 cursor-pointer hover:bg-indigo-700 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                        </label>
                                        <input type="file" id="avatar-upload" class="hidden" accept="image/*" @change="handleAvatarUpload($event)">
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-medium text-gray-900" x-text="profileData.name">{{ auth()->user()->name }}</h3>
                                        <p class="text-sm text-gray-500" x-text="profileData.email">{{ auth()->user()->email }}</p>
                                        <p class="text-xs text-gray-400 mt-1">Click the camera icon to change your profile picture</p>
                                    </div>
                                </div>

                                {{-- Personal Information --}}
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                                        <input type="text" 
                                               id="first_name" 
                                               x-model="profileData.firstName"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                               required>
                                    </div>
                                    <div>
                                        <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                                        <input type="text" 
                                               id="last_name" 
                                               x-model="profileData.lastName"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                               required>
                                    </div>
                                </div>

                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                                    <input type="email" 
                                           id="email" 
                                           x-model="profileData.email"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                           required>
                                    <p class="text-xs text-gray-500 mt-1">We'll send a verification email if you change your address</p>
                                </div>

                                <div>
                                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number (Optional)</label>
                                    <input type="tel" 
                                           id="phone" 
                                           x-model="profileData.phone"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                           placeholder="+1 (555) 123-4567">
                                </div>

                                <div>
                                    <label for="bio" class="block text-sm font-medium text-gray-700 mb-1">Bio (Optional)</label>
                                    <textarea id="bio" 
                                              x-model="profileData.bio"
                                              rows="3"
                                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                              placeholder="Tell us a bit about yourself..."></textarea>
                                </div>

                                <div class="flex justify-end">
                                    <button type="submit" 
                                            :disabled="isUpdatingProfile"
                                            class="bg-indigo-600 text-white py-2 px-6 rounded-lg font-medium hover:bg-indigo-700 transition-colors disabled:opacity-50">
                                        <span x-show="!isUpdatingProfile">Save Changes</span>
                                        <span x-show="isUpdatingProfile">Saving...</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Security Settings --}}
                    <div x-show="activeTab === 'security'" class="space-y-6">
                        {{-- Password Change --}}
                        <div class="bg-white rounded-2xl shadow-lg p-6">
                            <h2 class="text-xl font-semibold text-gray-900 mb-6">Change Password</h2>
                            
                            <form @submit.prevent="updatePassword()" class="space-y-4">
                                <div>
                                    <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                                    <input type="password" 
                                           id="current_password" 
                                           x-model="passwordData.currentPassword"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                           required>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                                        <input type="password" 
                                               id="new_password" 
                                               x-model="passwordData.newPassword"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                               required>
                                    </div>
                                    <div>
                                        <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                                        <input type="password" 
                                               id="confirm_password" 
                                               x-model="passwordData.confirmPassword"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                               required>
                                    </div>
                                </div>

                                <div class="flex justify-end">
                                    <button type="submit" 
                                            :disabled="isUpdatingPassword"
                                            class="bg-indigo-600 text-white py-2 px-6 rounded-lg font-medium hover:bg-indigo-700 transition-colors disabled:opacity-50">
                                        <span x-show="!isUpdatingPassword">Update Password</span>
                                        <span x-show="isUpdatingPassword">Updating...</span>
                                    </button>
                                </div>
                            </form>
                        </div>

                        {{-- Two-Factor Authentication --}}
                        <div class="bg-white rounded-2xl shadow-lg p-6">
                            <div class="flex items-center justify-between mb-6">
                                <div>
                                    <h2 class="text-xl font-semibold text-gray-900">Two-Factor Authentication</h2>
                                    <p class="text-sm text-gray-500 mt-1">Add an extra layer of security to your account</p>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <span class="text-sm" :class="twoFactorEnabled ? 'text-green-600' : 'text-gray-500'" x-text="twoFactorEnabled ? 'Enabled' : 'Disabled'"></span>
                                    <button @click="toggleTwoFactor()" 
                                            :class="twoFactorEnabled ? 'bg-green-600 hover:bg-green-700' : 'bg-gray-300 hover:bg-gray-400'"
                                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors">
                                        <span :class="twoFactorEnabled ? 'translate-x-6' : 'translate-x-1'" 
                                              class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"></span>
                                    </button>
                                </div>
                            </div>

                            <div x-show="twoFactorEnabled" class="space-y-4">
                                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                    <div class="flex items-start space-x-3">
                                        <svg class="w-5 h-5 text-green-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        <div>
                                            <p class="text-sm font-medium text-green-800">Two-Factor Authentication is active</p>
                                            <p class="text-sm text-green-700 mt-1">Your account is protected with 2FA using your authenticator app</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex space-x-3">
                                    <button @click="showRecoveryCodes()" 
                                            class="bg-gray-100 text-gray-700 py-2 px-4 rounded-lg font-medium hover:bg-gray-200 transition-colors">
                                        View Recovery Codes
                                    </button>
                                    <button @click="regenerateRecoveryCodes()" 
                                            class="bg-yellow-100 text-yellow-700 py-2 px-4 rounded-lg font-medium hover:bg-yellow-200 transition-colors">
                                        Generate New Codes
                                    </button>
                                </div>
                            </div>

                            <div x-show="!twoFactorEnabled" class="text-center py-8">
                                <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Enable Two-Factor Authentication</h3>
                                <p class="text-gray-600 mb-4">Protect your account with an additional security layer using your mobile device</p>
                                <button @click="window.location.href='/auth/2fa/setup'" 
                                        class="bg-indigo-600 text-white py-2 px-6 rounded-lg font-medium hover:bg-indigo-700 transition-colors">
                                    Set Up 2FA
                                </button>
                            </div>
                        </div>

                        {{-- Active Sessions --}}
                        <div class="bg-white rounded-2xl shadow-lg p-6">
                            <h2 class="text-xl font-semibold text-gray-900 mb-6">Active Sessions</h2>
                            
                            <div class="space-y-4">
                                <template x-for="session in activeSessions" :key="session.id">
                                    <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                                        <div class="flex items-center space-x-4">
                                            <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-900" x-text="session.device"></p>
                                                <p class="text-sm text-gray-500" x-text="session.location + ' • ' + session.last_active"></p>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <span x-show="session.is_current" class="px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full">Current</span>
                                            <button x-show="!session.is_current" @click="terminateSession(session.id)" 
                                                    class="text-red-600 hover:text-red-700 text-sm font-medium">
                                                Terminate
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <div class="mt-6 pt-6 border-t border-gray-200">
                                <button @click="terminateAllSessions()" 
                                        class="bg-red-100 text-red-700 py-2 px-4 rounded-lg font-medium hover:bg-red-200 transition-colors">
                                    Terminate All Other Sessions
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Notification Settings --}}
                    <div x-show="activeTab === 'notifications'" class="space-y-6">
                        <div class="bg-white rounded-2xl shadow-lg p-6">
                            <h2 class="text-xl font-semibold text-gray-900 mb-6">Notification Preferences</h2>
                            
                            <div class="space-y-6">
                                {{-- Email Notifications --}}
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Email Notifications</h3>
                                    <div class="space-y-4">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="font-medium text-gray-900">Price Drop Alerts</p>
                                                <p class="text-sm text-gray-500">Get notified when ticket prices drop for your watchlisted events</p>
                                            </div>
                                            <button @click="toggleNotification('email_price_drops')" 
                                                    :class="notifications.email.priceDrops ? 'bg-indigo-600' : 'bg-gray-300'"
                                                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors">
                                                <span :class="notifications.email.priceDrops ? 'translate-x-6' : 'translate-x-1'" 
                                                      class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"></span>
                                            </button>
                                        </div>

                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="font-medium text-gray-900">New Events</p>
                                                <p class="text-sm text-gray-500">Get notified about new events for your favorite teams</p>
                                            </div>
                                            <button @click="toggleNotification('email_new_events')" 
                                                    :class="notifications.email.newEvents ? 'bg-indigo-600' : 'bg-gray-300'"
                                                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors">
                                                <span :class="notifications.email.newEvents ? 'translate-x-6' : 'translate-x-1'" 
                                                      class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"></span>
                                            </button>
                                        </div>

                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="font-medium text-gray-900">Purchase Confirmations</p>
                                                <p class="text-sm text-gray-500">Receive receipts and confirmations for ticket purchases</p>
                                            </div>
                                            <button @click="toggleNotification('email_purchases')" 
                                                    :class="notifications.email.purchases ? 'bg-indigo-600' : 'bg-gray-300'"
                                                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors">
                                                <span :class="notifications.email.purchases ? 'translate-x-6' : 'translate-x-1'" 
                                                      class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"></span>
                                            </button>
                                        </div>

                                        @if(auth()->user()->role === 'customer')
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <p class="font-medium text-gray-900">Subscription Updates</p>
                                                    <p class="text-sm text-gray-500">Billing reminders and subscription status changes</p>
                                                </div>
                                                <button @click="toggleNotification('email_subscription')" 
                                                        :class="notifications.email.subscription ? 'bg-indigo-600' : 'bg-gray-300'"
                                                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors">
                                                    <span :class="notifications.email.subscription ? 'translate-x-6' : 'translate-x-1'" 
                                                          class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"></span>
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                {{-- Push Notifications --}}
                                <div class="border-t border-gray-200 pt-6">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Push Notifications</h3>
                                    <div class="space-y-4">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="font-medium text-gray-900">Real-time Price Alerts</p>
                                                <p class="text-sm text-gray-500">Instant notifications for significant price changes</p>
                                            </div>
                                            <button @click="toggleNotification('push_price_alerts')" 
                                                    :class="notifications.push.priceAlerts ? 'bg-indigo-600' : 'bg-gray-300'"
                                                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors">
                                                <span :class="notifications.push.priceAlerts ? 'translate-x-6' : 'translate-x-1'" 
                                                      class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"></span>
                                            </button>
                                        </div>

                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="font-medium text-gray-900">Sale Reminders</p>
                                                <p class="text-sm text-gray-500">Reminders when tickets you're watching go on sale</p>
                                            </div>
                                            <button @click="toggleNotification('push_sale_reminders')" 
                                                    :class="notifications.push.saleReminders ? 'bg-indigo-600' : 'bg-gray-300'"
                                                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors">
                                                <span :class="notifications.push.saleReminders ? 'translate-x-6' : 'translate-x-1'" 
                                                      class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"></span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 pt-6 border-t border-gray-200 flex justify-end">
                                <button @click="saveNotificationSettings()" 
                                        :disabled="isSavingNotifications"
                                        class="bg-indigo-600 text-white py-2 px-6 rounded-lg font-medium hover:bg-indigo-700 transition-colors disabled:opacity-50">
                                    <span x-show="!isSavingNotifications">Save Preferences</span>
                                    <span x-show="isSavingNotifications">Saving...</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Subscription Tab --}}
                    @if(auth()->user()->role === 'customer')
                        <div x-show="activeTab === 'subscription'" class="space-y-6">
                            <div class="bg-white rounded-2xl shadow-lg p-6">
                                <div class="flex items-center justify-between mb-6">
                                    <h2 class="text-xl font-semibold text-gray-900">Subscription Overview</h2>
                                    <a href="{{ route('subscriptions.dashboard') }}" 
                                       class="bg-indigo-600 text-white py-2 px-4 rounded-lg font-medium hover:bg-indigo-700 transition-colors">
                                        Manage Subscription
                                    </a>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div class="text-center">
                                        <div class="text-3xl font-bold text-gray-900">{{ auth()->user()->getMonthlyTicketUsage() ?? 0 }}</div>
                                        <div class="text-sm text-gray-500">Tickets Used This Month</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-3xl font-bold text-green-600">{{ auth()->user()->getMonthlyTicketLimit() - auth()->user()->getMonthlyTicketUsage() ?? 100 }}</div>
                                        <div class="text-sm text-gray-500">Remaining Tickets</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-3xl font-bold text-indigo-600">${{ auth()->user()->subscription->monthly_fee ?? '29.99' }}</div>
                                        <div class="text-sm text-gray-500">Monthly Fee</div>
                                    </div>
                                </div>

                                @if(auth()->user()->subscription)
                                    <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm text-gray-600">Next billing date:</span>
                                            <span class="font-medium text-gray-900">{{ auth()->user()->subscription->next_billing_date?->format('M j, Y') ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- Privacy Settings --}}
                    <div x-show="activeTab === 'privacy'" class="space-y-6">
                        <div class="bg-white rounded-2xl shadow-lg p-6">
                            <h2 class="text-xl font-semibold text-gray-900 mb-6">Privacy Controls</h2>
                            
                            <div class="space-y-6">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-medium text-gray-900">Profile Visibility</p>
                                        <p class="text-sm text-gray-500">Make your profile visible to other users</p>
                                    </div>
                                    <button @click="togglePrivacySetting('profile_visible')" 
                                            :class="privacySettings.profileVisible ? 'bg-indigo-600' : 'bg-gray-300'"
                                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors">
                                        <span :class="privacySettings.profileVisible ? 'translate-x-6' : 'translate-x-1'" 
                                              class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"></span>
                                    </button>
                                </div>

                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-medium text-gray-900">Analytics & Performance</p>
                                        <p class="text-sm text-gray-500">Allow us to collect anonymous usage data to improve the service</p>
                                    </div>
                                    <button @click="togglePrivacySetting('analytics_enabled')" 
                                            :class="privacySettings.analyticsEnabled ? 'bg-indigo-600' : 'bg-gray-300'"
                                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors">
                                        <span :class="privacySettings.analyticsEnabled ? 'translate-x-6' : 'translate-x-1'" 
                                              class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"></span>
                                    </button>
                                </div>

                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-medium text-gray-900">Marketing Communications</p>
                                        <p class="text-sm text-gray-500">Receive promotional emails about new features and offers</p>
                                    </div>
                                    <button @click="togglePrivacySetting('marketing_emails')" 
                                            :class="privacySettings.marketingEmails ? 'bg-indigo-600' : 'bg-gray-300'"
                                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors">
                                        <span :class="privacySettings.marketingEmails ? 'translate-x-6' : 'translate-x-1'" 
                                              class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"></span>
                                    </button>
                                </div>
                            </div>

                            <div class="mt-8 pt-6 border-t border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Data Management</h3>
                                <div class="space-y-3">
                                    <button @click="requestDataExport()" 
                                            class="w-full bg-gray-100 text-gray-700 py-3 px-4 rounded-lg font-medium hover:bg-gray-200 transition-colors text-left">
                                        Download My Data
                                    </button>
                                    <p class="text-xs text-gray-500 px-4">Request a copy of all data associated with your account</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Danger Zone --}}
                    <div x-show="activeTab === 'danger'" class="space-y-6">
                        <div class="bg-white rounded-2xl shadow-lg p-6 border-2 border-red-200">
                            <h2 class="text-xl font-semibold text-red-900 mb-6">Danger Zone</h2>
                            
                            <div class="space-y-6">
                                {{-- Deactivate Account --}}
                                <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
                                    <h3 class="font-medium text-red-900 mb-2">Deactivate Account</h3>
                                    <p class="text-sm text-red-700 mb-4">Temporarily deactivate your account. You can reactivate it anytime by logging in again.</p>
                                    <button @click="showDeactivateModal = true" 
                                            class="bg-red-100 text-red-700 py-2 px-4 rounded-lg font-medium hover:bg-red-200 transition-colors">
                                        Deactivate Account
                                    </button>
                                </div>

                                {{-- Delete Account --}}
                                <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
                                    <h3 class="font-medium text-red-900 mb-2">Delete Account</h3>
                                    <p class="text-sm text-red-700 mb-4">Permanently delete your account and all associated data. This action cannot be undone.</p>
                                    <button @click="showDeleteModal = true" 
                                            class="bg-red-600 text-white py-2 px-4 rounded-lg font-medium hover:bg-red-700 transition-colors">
                                        Delete Account
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Modals --}}
            {{-- Account Deactivation Modal --}}
            <div x-show="showDeactivateModal" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                <div class="bg-white rounded-2xl max-w-md w-full p-6" @click.stop>
                    <div class="text-center mb-6">
                        <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.124 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Deactivate Account?</h3>
                        <p class="text-gray-600">Your account will be temporarily deactivated. You can reactivate it by logging in again.</p>
                    </div>

                    <div class="flex space-x-3">
                        <button @click="showDeactivateModal = false" 
                                class="flex-1 bg-gray-100 text-gray-700 py-3 rounded-lg font-medium hover:bg-gray-200 transition-colors">
                            Cancel
                        </button>
                        <button @click="deactivateAccount()" 
                                :disabled="isDeactivating"
                                class="flex-1 bg-yellow-600 text-white py-3 rounded-lg font-medium hover:bg-yellow-700 transition-colors disabled:opacity-50">
                            <span x-show="!isDeactivating">Deactivate</span>
                            <span x-show="isDeactivating">Deactivating...</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Account Deletion Modal --}}
            <div x-show="showDeleteModal" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                <div class="bg-white rounded-2xl max-w-md w-full p-6" @click.stop>
                    <div class="text-center mb-6">
                        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Delete Account?</h3>
                        <p class="text-gray-600">This will permanently delete your account and all associated data. This action cannot be undone.</p>
                    </div>

                    <div class="mb-6">
                        <label for="delete_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                            Type "DELETE" to confirm:
                        </label>
                        <input type="text" 
                               id="delete_confirmation" 
                               x-model="deleteConfirmation"
                               class="w-full px-3 py-2 border border-red-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                               placeholder="DELETE">
                    </div>

                    <div class="flex space-x-3">
                        <button @click="showDeleteModal = false; deleteConfirmation = ''" 
                                class="flex-1 bg-gray-100 text-gray-700 py-3 rounded-lg font-medium hover:bg-gray-200 transition-colors">
                            Cancel
                        </button>
                        <button @click="deleteAccount()" 
                                :disabled="deleteConfirmation !== 'DELETE' || isDeleting"
                                class="flex-1 bg-red-600 text-white py-3 rounded-lg font-medium hover:bg-red-700 transition-colors disabled:opacity-50">
                            <span x-show="!isDeleting">Delete Account</span>
                            <span x-show="isDeleting">Deleting...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function accountSettings() {
            return {
                activeTab: 'profile',
                showDeactivateModal: false,
                showDeleteModal: false,
                deleteConfirmation: '',
                isUpdatingProfile: false,
                isUpdatingPassword: false,
                isSavingNotifications: false,
                isDeactivating: false,
                isDeleting: false,
                twoFactorEnabled: {{ auth()->user()->two_factor_enabled ?? 'false' }},

                profileData: {
                    name: '{{ auth()->user()->name ?? "" }}',
                    firstName: '{{ auth()->user()->first_name ?? "" }}',
                    lastName: '{{ auth()->user()->last_name ?? "" }}',
                    email: '{{ auth()->user()->email ?? "" }}',
                    phone: '{{ auth()->user()->phone ?? "" }}',
                    bio: '{{ auth()->user()->bio ?? "" }}',
                    avatar: '{{ auth()->user()->avatar ?? "" }}'
                },

                passwordData: {
                    currentPassword: '',
                    newPassword: '',
                    confirmPassword: ''
                },

                notifications: {
                    email: {
                        priceDrops: true,
                        newEvents: true,
                        purchases: true,
                        subscription: true
                    },
                    push: {
                        priceAlerts: false,
                        saleReminders: true
                    }
                },

                privacySettings: {
                    profileVisible: true,
                    analyticsEnabled: true,
                    marketingEmails: false
                },

                activeSessions: [
                    {
                        id: 1,
                        device: 'Chrome on Windows',
                        location: 'New York, NY',
                        last_active: '2 minutes ago',
                        is_current: true
                    },
                    {
                        id: 2,
                        device: 'Safari on iPhone',
                        location: 'New York, NY',
                        last_active: '1 hour ago',
                        is_current: false
                    }
                ],

                async updateProfile() {
                    this.isUpdatingProfile = true;
                    
                    try {
                        const response = await fetch('/api/v1/user/profile', {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify(this.profileData)
                        });

                        const data = await response.json();
                        
                        if (data.success) {
                            this.showToast('Profile updated successfully!', 'success');
                        } else {
                            this.showToast(data.message || 'Failed to update profile', 'error');
                        }
                    } catch (error) {
                        console.error('Error updating profile:', error);
                        this.showToast('An error occurred while updating your profile', 'error');
                    } finally {
                        this.isUpdatingProfile = false;
                    }
                },

                async updatePassword() {
                    if (this.passwordData.newPassword !== this.passwordData.confirmPassword) {
                        this.showToast('New passwords do not match', 'error');
                        return;
                    }

                    this.isUpdatingPassword = true;
                    
                    try {
                        const response = await fetch('/api/v1/user/password', {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify(this.passwordData)
                        });

                        const data = await response.json();
                        
                        if (data.success) {
                            this.showToast('Password updated successfully!', 'success');
                            this.passwordData = {
                                currentPassword: '',
                                newPassword: '',
                                confirmPassword: ''
                            };
                        } else {
                            this.showToast(data.message || 'Failed to update password', 'error');
                        }
                    } catch (error) {
                        console.error('Error updating password:', error);
                        this.showToast('An error occurred while updating your password', 'error');
                    } finally {
                        this.isUpdatingPassword = false;
                    }
                },

                toggleTwoFactor() {
                    if (this.twoFactorEnabled) {
                        this.disableTwoFactor();
                    } else {
                        window.location.href = '/auth/2fa/setup';
                    }
                },

                async disableTwoFactor() {
                    if (!confirm('Are you sure you want to disable two-factor authentication? This will make your account less secure.')) {
                        return;
                    }

                    try {
                        const response = await fetch('/api/v1/user/2fa/disable', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });

                        const data = await response.json();
                        
                        if (data.success) {
                            this.twoFactorEnabled = false;
                            this.showToast('Two-factor authentication disabled', 'success');
                        } else {
                            this.showToast(data.message || 'Failed to disable 2FA', 'error');
                        }
                    } catch (error) {
                        console.error('Error disabling 2FA:', error);
                        this.showToast('An error occurred', 'error');
                    }
                },

                toggleNotification(type) {
                    const parts = type.split('_');
                    const category = parts[0]; // email or push
                    const setting = parts.slice(1).join('_'); // priceDrops, newEvents, etc.
                    
                    // Convert snake_case to camelCase
                    const camelSetting = setting.replace(/_([a-z])/g, (g) => g[1].toUpperCase());
                    
                    this.notifications[category][camelSetting] = !this.notifications[category][camelSetting];
                },

                async saveNotificationSettings() {
                    this.isSavingNotifications = true;
                    
                    try {
                        const response = await fetch('/api/v1/user/notifications', {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify(this.notifications)
                        });

                        const data = await response.json();
                        
                        if (data.success) {
                            this.showToast('Notification preferences saved!', 'success');
                        } else {
                            this.showToast(data.message || 'Failed to save preferences', 'error');
                        }
                    } catch (error) {
                        console.error('Error saving notifications:', error);
                        this.showToast('An error occurred', 'error');
                    } finally {
                        this.isSavingNotifications = false;
                    }
                },

                togglePrivacySetting(setting) {
                    this.privacySettings[setting] = !this.privacySettings[setting];
                },

                async requestDataExport() {
                    try {
                        const response = await fetch('/api/v1/user/data-export', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });

                        const data = await response.json();
                        
                        if (data.success) {
                            this.showToast('Data export requested. You will receive an email when ready.', 'success');
                        } else {
                            this.showToast(data.message || 'Failed to request data export', 'error');
                        }
                    } catch (error) {
                        console.error('Error requesting data export:', error);
                        this.showToast('An error occurred', 'error');
                    }
                },

                async deactivateAccount() {
                    this.isDeactivating = true;
                    
                    try {
                        const response = await fetch('/api/v1/user/deactivate', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });

                        const data = await response.json();
                        
                        if (data.success) {
                            window.location.href = '/auth/logout';
                        } else {
                            this.showToast(data.message || 'Failed to deactivate account', 'error');
                        }
                    } catch (error) {
                        console.error('Error deactivating account:', error);
                        this.showToast('An error occurred', 'error');
                    } finally {
                        this.isDeactivating = false;
                        this.showDeactivateModal = false;
                    }
                },

                async deleteAccount() {
                    this.isDeleting = true;
                    
                    try {
                        const response = await fetch('/api/v1/user/delete', {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                confirmation: this.deleteConfirmation
                            })
                        });

                        const data = await response.json();
                        
                        if (data.success) {
                            window.location.href = '/';
                        } else {
                            this.showToast(data.message || 'Failed to delete account', 'error');
                        }
                    } catch (error) {
                        console.error('Error deleting account:', error);
                        this.showToast('An error occurred', 'error');
                    } finally {
                        this.isDeleting = false;
                        this.showDeleteModal = false;
                        this.deleteConfirmation = '';
                    }
                },

                terminateSession(sessionId) {
                    // Remove session from list
                    this.activeSessions = this.activeSessions.filter(s => s.id !== sessionId);
                    this.showToast('Session terminated', 'success');
                },

                terminateAllSessions() {
                    if (!confirm('This will log you out from all other devices. Continue?')) return;
                    
                    // Keep only current session
                    this.activeSessions = this.activeSessions.filter(s => s.is_current);
                    this.showToast('All other sessions terminated', 'success');
                },

                handleAvatarUpload(event) {
                    const file = event.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            this.profileData.avatar = e.target.result;
                        };
                        reader.readAsDataURL(file);
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
