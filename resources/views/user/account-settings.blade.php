<x-unified-layout title="Account Settings" subtitle="Manage your profile, security, and preferences">
  <x-slot name="headerActions">
    <div class="flex items-center space-x-3">
      <!-- Settings Navigation -->
      <div class="flex items-center space-x-2">
        <button @click="activeTab = 'profile'" 
                :class="activeTab === 'profile' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600'"
                class="px-3 py-2 rounded-lg text-sm font-medium hover:opacity-80 transition">
          Profile
        </button>
        <button @click="activeTab = 'security'" 
                :class="activeTab === 'security' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600'"
                class="px-3 py-2 rounded-lg text-sm font-medium hover:opacity-80 transition">
          Security
        </button>
        <button @click="activeTab = 'preferences'" 
                :class="activeTab === 'preferences' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600'"
                class="px-3 py-2 rounded-lg text-sm font-medium hover:opacity-80 transition">
          Preferences
        </button>
        @if(Auth::user()->role === 'customer')
          <button @click="activeTab = 'subscription'" 
                  :class="activeTab === 'subscription' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600'"
                  class="px-3 py-2 rounded-lg text-sm font-medium hover:opacity-80 transition">
            Subscription
          </button>
        @endif
      </div>
    </div>
  </x-slot>

  <div x-data="accountSettings()" x-init="init()" class="space-y-8">
    
    <!-- Profile Settings -->
    <div x-show="activeTab === 'profile'" x-cloak>
      <x-ui.card>
        <x-ui.card-header title="Profile Information">
          <div class="text-sm text-gray-500">Update your personal information and preferences</div>
        </x-ui.card-header>
        <x-ui.card-content>
          <form @submit.prevent="updateProfile()" class="space-y-6">
            
            <!-- Profile Photo -->
            <div class="flex items-center space-x-6">
              <div class="flex-shrink-0">
                <img :src="profile.avatar_url || '/images/default-avatar.png'" 
                     alt="Profile photo"
                     class="w-20 h-20 rounded-full object-cover bg-gray-200">
              </div>
              <div>
                <h3 class="text-sm font-medium text-gray-900">Profile Photo</h3>
                <p class="text-xs text-gray-500 mb-2">JPG, GIF or PNG. 1MB max.</p>
                <div class="flex items-center space-x-3">
                  <input type="file" 
                         @change="handleAvatarUpload($event)"
                         accept="image/*"
                         class="hidden" 
                         ref="avatarInput">
                  <button type="button" 
                          @click="$refs.avatarInput.click()"
                          class="bg-white border border-gray-300 rounded-md px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Change Photo
                  </button>
                  <button type="button" 
                          @click="removeAvatar()"
                          x-show="profile.avatar_url"
                          class="text-sm text-red-600 hover:text-red-800">
                    Remove
                  </button>
                </div>
              </div>
            </div>

            <!-- Basic Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                <input type="text" 
                       x-model="profile.first_name"
                       class="block w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                <input type="text" 
                       x-model="profile.last_name"
                       class="block w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                <input type="email" 
                       x-model="profile.email"
                       class="block w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <div x-show="!profile.email_verified_at" class="mt-2 flex items-center text-sm">
                  <svg class="w-4 h-4 text-yellow-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                  </svg>
                  <span class="text-yellow-700">Email not verified</span>
                  <button type="button" 
                          @click="resendEmailVerification()"
                          class="ml-2 text-blue-600 hover:text-blue-800 font-medium">
                    Resend
                  </button>
                </div>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                <input type="tel" 
                       x-model="profile.phone"
                       placeholder="+1 (555) 123-4567"
                       class="block w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <div x-show="profile.phone && !profile.phone_verified_at" class="mt-2 flex items-center text-sm">
                  <svg class="w-4 h-4 text-yellow-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                  </svg>
                  <span class="text-yellow-700">Phone not verified</span>
                  <button type="button" 
                          @click="verifyPhone()"
                          class="ml-2 text-blue-600 hover:text-blue-800 font-medium">
                    Verify
                  </button>
                </div>
              </div>
            </div>

            <!-- Address Information -->
            <div class="space-y-4">
              <h3 class="text-lg font-medium text-gray-900">Address Information</h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                  <label class="block text-sm font-medium text-gray-700 mb-2">Street Address</label>
                  <input type="text" 
                         x-model="profile.address"
                         class="block w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">City</label>
                  <input type="text" 
                         x-model="profile.city"
                         class="block w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">State/Province</label>
                  <input type="text" 
                         x-model="profile.state"
                         class="block w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">ZIP/Postal Code</label>
                  <input type="text" 
                         x-model="profile.zip"
                         class="block w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Country</label>
                  <select x-model="profile.country" class="block w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="US">United States</option>
                    <option value="CA">Canada</option>
                    <option value="UK">United Kingdom</option>
                    <!-- Add more countries as needed -->
                  </select>
                </div>
              </div>
            </div>

            <!-- Sports Preferences -->
            <div class="space-y-4">
              <h3 class="text-lg font-medium text-gray-900">Sports Preferences</h3>
              <p class="text-sm text-gray-600">Select your favorite sports to receive personalized recommendations</p>
              <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                <template x-for="sport in availableSports" :key="sport.slug">
                  <label class="flex items-center space-x-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                    <input type="checkbox" 
                           :value="sport.slug"
                           x-model="profile.favorite_sports"
                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <div>
                      <div class="font-medium text-gray-900" x-text="sport.icon + ' ' + sport.name"></div>
                    </div>
                  </label>
                </template>
              </div>
            </div>

            <!-- Save Button -->
            <div class="flex justify-end">
              <button type="submit" 
                      :disabled="updatingProfile"
                      class="bg-blue-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition">
                <span x-show="!updatingProfile">Save Changes</span>
                <span x-show="updatingProfile" class="flex items-center">
                  <svg class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Saving...
                </span>
              </button>
            </div>
          </form>
        </x-ui.card-content>
      </x-ui.card>
    </div>

    <!-- Security Settings -->
    <div x-show="activeTab === 'security'" x-cloak class="space-y-6">
      
      <!-- Password Change -->
      <x-ui.card>
        <x-ui.card-header title="Change Password">
          <div class="text-sm text-gray-500">Ensure your account stays secure</div>
        </x-ui.card-header>
        <x-ui.card-content>
          <form @submit.prevent="updatePassword()" class="space-y-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Current Password</label>
              <input type="password" 
                     x-model="security.current_password"
                     class="block w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
              <input type="password" 
                     x-model="security.new_password"
                     class="block w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
              <!-- Password Strength Indicator -->
              <div class="mt-2">
                <div class="flex space-x-1">
                  <div class="h-1 flex-1 rounded" :class="getPasswordStrength(security.new_password) >= 1 ? 'bg-red-500' : 'bg-gray-200'"></div>
                  <div class="h-1 flex-1 rounded" :class="getPasswordStrength(security.new_password) >= 2 ? 'bg-yellow-500' : 'bg-gray-200'"></div>
                  <div class="h-1 flex-1 rounded" :class="getPasswordStrength(security.new_password) >= 3 ? 'bg-green-500' : 'bg-gray-200'"></div>
                </div>
                <p class="text-xs text-gray-600 mt-1">
                  Password strength: 
                  <span x-text="getPasswordStrengthText(security.new_password)"></span>
                </p>
              </div>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
              <input type="password" 
                     x-model="security.confirm_password"
                     class="block w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
              <div x-show="security.new_password && security.confirm_password && security.new_password !== security.confirm_password" 
                   class="mt-2 text-sm text-red-600">
                Passwords do not match
              </div>
            </div>

            <div class="flex justify-end">
              <button type="submit" 
                      :disabled="updatingPassword || !isPasswordValid"
                      class="bg-blue-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition">
                <span x-show="!updatingPassword">Update Password</span>
                <span x-show="updatingPassword" class="flex items-center">
                  <svg class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Updating...
                </span>
              </button>
            </div>
          </form>
        </x-ui.card-content>
      </x-ui.card>

      <!-- Two-Factor Authentication -->
      <x-ui.card>
        <x-ui.card-header title="Two-Factor Authentication">
          <div class="text-sm text-gray-500">Add an extra layer of security to your account</div>
        </x-ui.card-header>
        <x-ui.card-content>
          <div class="space-y-4">
            <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div :class="security.two_factor_enabled ? 'bg-green-100' : 'bg-gray-100'" class="w-10 h-10 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5" :class="security.two_factor_enabled ? 'text-green-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                  </div>
                </div>
                <div class="ml-4">
                  <h3 class="font-medium text-gray-900">Authenticator App (TOTP)</h3>
                  <p class="text-sm text-gray-600">
                    <span x-show="security.two_factor_enabled" class="text-green-600">Enabled</span>
                    <span x-show="!security.two_factor_enabled">Use an authenticator app to generate verification codes</span>
                  </p>
                </div>
              </div>
              <div class="flex items-center space-x-2">
                <button @click="security.two_factor_enabled ? disable2FA() : enable2FA()" 
                        :class="security.two_factor_enabled ? 'bg-red-100 text-red-600 hover:bg-red-200' : 'bg-blue-600 text-white hover:bg-blue-700'"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition">
                  <span x-text="security.two_factor_enabled ? 'Disable' : 'Enable'"></span>
                </button>
              </div>
            </div>

            <!-- Recovery Codes -->
            <div x-show="security.two_factor_enabled" class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
              <h4 class="font-medium text-yellow-900 mb-2">Recovery Codes</h4>
              <p class="text-sm text-yellow-800 mb-3">
                Store these recovery codes in a safe place. They can be used to access your account if you lose your authenticator device.
              </p>
              <div class="flex items-center space-x-3">
                <button @click="viewRecoveryCodes()" class="text-sm text-yellow-700 hover:text-yellow-900 font-medium">
                  View Codes
                </button>
                <button @click="regenerateRecoveryCodes()" class="text-sm text-yellow-700 hover:text-yellow-900 font-medium">
                  Regenerate
                </button>
              </div>
            </div>
          </div>
        </x-ui.card-content>
      </x-ui.card>

      <!-- Login Sessions -->
      <x-ui.card>
        <x-ui.card-header title="Active Sessions">
          <div class="text-sm text-gray-500">Manage devices that are logged into your account</div>
        </x-ui.card-header>
        <x-ui.card-content>
          <div class="space-y-4">
            <template x-for="session in security.active_sessions" :key="session.id">
              <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                <div class="flex items-center">
                  <div class="flex-shrink-0">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                  </div>
                  <div class="ml-4">
                    <h3 class="font-medium text-gray-900" x-text="session.device"></h3>
                    <p class="text-sm text-gray-600">
                      <span x-text="session.location"></span> • 
                      <span x-text="formatDate(session.last_active)"></span>
                      <span x-show="session.is_current" class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        Current Session
                      </span>
                    </p>
                  </div>
                </div>
                <button x-show="!session.is_current" 
                        @click="revokeSession(session.id)"
                        class="text-red-600 hover:text-red-800 text-sm font-medium">
                  Revoke
                </button>
              </div>
            </template>
            
            <div class="text-center">
              <button @click="revokeAllSessions()" class="text-red-600 hover:text-red-800 text-sm font-medium">
                Revoke All Other Sessions
              </button>
            </div>
          </div>
        </x-ui.card-content>
      </x-ui.card>
    </div>

    <!-- Preferences -->
    <div x-show="activeTab === 'preferences'" x-cloak>
      <x-ui.card>
        <x-ui.card-header title="Application Preferences">
          <div class="text-sm text-gray-500">Customize your HD Tickets experience</div>
        </x-ui.card-header>
        <x-ui.card-content>
          <form @submit.prevent="updatePreferences()" class="space-y-6">
            
            <!-- Theme Preferences -->
            <div>
              <h3 class="text-lg font-medium text-gray-900 mb-4">Appearance</h3>
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Theme</label>
                  <select x-model="preferences.theme" class="block w-full border border-gray-300 rounded-lg px-3 py-2">
                    <option value="light">Light</option>
                    <option value="dark">Dark</option>
                    <option value="system">System Default</option>
                  </select>
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Language</label>
                  <select x-model="preferences.language" class="block w-full border border-gray-300 rounded-lg px-3 py-2">
                    <option value="en">English</option>
                    <option value="es">Spanish</option>
                    <option value="fr">French</option>
                  </select>
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Time Zone</label>
                  <select x-model="preferences.timezone" class="block w-full border border-gray-300 rounded-lg px-3 py-2">
                    <option value="America/New_York">Eastern Time</option>
                    <option value="America/Chicago">Central Time</option>
                    <option value="America/Denver">Mountain Time</option>
                    <option value="America/Los_Angeles">Pacific Time</option>
                  </select>
                </div>
              </div>
            </div>

            <!-- Default Settings -->
            <div>
              <h3 class="text-lg font-medium text-gray-900 mb-4">Default Settings</h3>
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Default Search Location</label>
                  <input type="text" 
                         x-model="preferences.default_location"
                         placeholder="e.g., New York, NY"
                         class="block w-full border border-gray-300 rounded-lg px-3 py-2">
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Default Price Range</label>
                  <div class="grid grid-cols-2 gap-3">
                    <input type="number" 
                           x-model="preferences.default_price_min"
                           placeholder="Min price"
                           class="block w-full border border-gray-300 rounded-lg px-3 py-2">
                    <input type="number" 
                           x-model="preferences.default_price_max"
                           placeholder="Max price"
                           class="block w-full border border-gray-300 rounded-lg px-3 py-2">
                  </div>
                </div>
                
                <div>
                  <label class="flex items-center">
                    <input type="checkbox" x-model="preferences.auto_refresh" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="ml-2 text-sm text-gray-700">Auto-refresh ticket prices</span>
                  </label>
                </div>
                
                <div>
                  <label class="flex items-center">
                    <input type="checkbox" x-model="preferences.location_services" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="ml-2 text-sm text-gray-700">Use location services for nearby events</span>
                  </label>
                </div>
              </div>
            </div>

            <!-- Privacy Settings -->
            <div>
              <h3 class="text-lg font-medium text-gray-900 mb-4">Privacy</h3>
              <div class="space-y-4">
                <div>
                  <label class="flex items-center">
                    <input type="checkbox" x-model="preferences.analytics_tracking" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="ml-2 text-sm text-gray-700">Allow analytics tracking to improve the service</span>
                  </label>
                </div>
                
                <div>
                  <label class="flex items-center">
                    <input type="checkbox" x-model="preferences.personalized_recommendations" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="ml-2 text-sm text-gray-700">Show personalized event recommendations</span>
                  </label>
                </div>
                
                <div>
                  <label class="flex items-center">
                    <input type="checkbox" x-model="preferences.social_features" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="ml-2 text-sm text-gray-700">Enable social sharing features</span>
                  </label>
                </div>
              </div>
            </div>

            <!-- Save Button -->
            <div class="flex justify-end">
              <button type="submit" 
                      :disabled="updatingPreferences"
                      class="bg-blue-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition">
                <span x-show="!updatingPreferences">Save Preferences</span>
                <span x-show="updatingPreferences" class="flex items-center">
                  <svg class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Saving...
                </span>
              </button>
            </div>
          </form>
        </x-ui.card-content>
      </x-ui.card>
    </div>

    @if(Auth::user()->role === 'customer')
    <!-- Subscription Settings -->
    <div x-show="activeTab === 'subscription'" x-cloak>
      <div class="space-y-6">
        
        <!-- Current Subscription -->
        <x-ui.card>
          <x-ui.card-header title="Current Subscription">
            <div class="text-sm text-gray-500">Manage your HD Tickets subscription</div>
          </x-ui.card-header>
          <x-ui.card-content>
            <div class="space-y-4">
              <div class="flex items-center justify-between p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <div>
                  <h3 class="font-medium text-blue-900" x-text="subscription.plan_name || 'Free Trial'">Premium Plan</h3>
                  <p class="text-sm text-blue-700" x-text="subscription.description">
                    100 tickets per month • Priority support • Advanced features
                  </p>
                  <p class="text-sm text-blue-600 mt-1" x-show="subscription.trial_ends_at">
                    Trial ends: <span x-text="formatDate(subscription.trial_ends_at)"></span>
                  </p>
                  <p class="text-sm text-blue-600 mt-1" x-show="subscription.next_billing_date">
                    Next billing: <span x-text="formatDate(subscription.next_billing_date)"></span>
                  </p>
                </div>
                <div class="text-right">
                  <div class="text-2xl font-bold text-blue-900" x-text="formatCurrency(subscription.amount || 0)">$29.99</div>
                  <div class="text-sm text-blue-700">per month</div>
                </div>
              </div>

              <!-- Usage Stats -->
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="text-center">
                  <div class="text-2xl font-bold text-gray-900" x-text="subscription.tickets_used || 0">25</div>
                  <div class="text-sm text-gray-600">Tickets Used</div>
                  <div class="text-xs text-gray-500" x-text="'of ' + (subscription.ticket_limit || 100) + ' this month'">of 100 this month</div>
                </div>
                <div class="text-center">
                  <div class="text-2xl font-bold text-green-600" x-text="formatCurrency(subscription.total_savings || 0)">$125</div>
                  <div class="text-sm text-gray-600">Total Savings</div>
                  <div class="text-xs text-gray-500">since subscription started</div>
                </div>
                <div class="text-center">
                  <div class="text-2xl font-bold text-purple-600" x-text="subscription.alerts_triggered || 0">12</div>
                  <div class="text-sm text-gray-600">Alerts Triggered</div>
                  <div class="text-xs text-gray-500">this month</div>
                </div>
              </div>

              <!-- Action Buttons -->
              <div class="flex items-center space-x-3">
                <button @click="upgradeSubscription()" 
                        x-show="!subscription.is_premium"
                        class="bg-blue-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-blue-700 transition">
                  Upgrade Plan
                </button>
                <button @click="manageSubscription()" 
                        class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg font-medium hover:bg-gray-200 transition">
                  Manage Subscription
                </button>
                <button @click="cancelSubscription()" 
                        x-show="subscription.status === 'active'"
                        class="text-red-600 hover:text-red-800 px-4 py-2 font-medium">
                  Cancel Subscription
                </button>
              </div>
            </div>
          </x-ui.card-content>
        </x-ui.card>

        <!-- Billing History -->
        <x-ui.card>
          <x-ui.card-header title="Billing History">
            <div class="text-sm text-gray-500">View your payment history and download receipts</div>
          </x-ui.card-header>
          <x-ui.card-content class="p-0">
            <div class="divide-y divide-gray-200">
              <template x-for="invoice in subscription.billing_history" :key="invoice.id">
                <div class="p-4 flex items-center justify-between hover:bg-gray-50">
                  <div>
                    <div class="font-medium text-gray-900" x-text="formatDate(invoice.date)"></div>
                    <div class="text-sm text-gray-600" x-text="invoice.description">Monthly subscription</div>
                  </div>
                  <div class="flex items-center space-x-4">
                    <div class="text-right">
                      <div class="font-medium text-gray-900" x-text="formatCurrency(invoice.amount)">$29.99</div>
                      <div class="text-sm" :class="invoice.status === 'paid' ? 'text-green-600' : 'text-red-600'" x-text="invoice.status">Paid</div>
                    </div>
                    <button @click="downloadInvoice(invoice.id)" class="text-blue-600 hover:text-blue-800 text-sm">
                      Download
                    </button>
                  </div>
                </div>
              </template>
              
              <div x-show="!subscription.billing_history || subscription.billing_history.length === 0" class="p-8 text-center text-gray-500">
                No billing history available
              </div>
            </div>
          </x-ui.card-content>
        </x-ui.card>
      </div>
    </div>
    @endif

    <!-- Danger Zone -->
    <x-ui.card class="border-red-200">
      <x-ui.card-header title="Danger Zone" class="text-red-900">
        <div class="text-sm text-red-600">Irreversible and destructive actions</div>
      </x-ui.card-header>
      <x-ui.card-content>
        <div class="space-y-4">
          <div class="flex items-center justify-between p-4 border border-red-200 rounded-lg">
            <div>
              <h3 class="font-medium text-red-900">Export Account Data</h3>
              <p class="text-sm text-red-700">Download a copy of all your account data and activity</p>
            </div>
            <button @click="exportAccountData()" class="bg-red-100 text-red-700 px-4 py-2 rounded-lg font-medium hover:bg-red-200 transition">
              Request Export
            </button>
          </div>
          
          <div class="flex items-center justify-between p-4 border border-red-200 rounded-lg">
            <div>
              <h3 class="font-medium text-red-900">Delete Account</h3>
              <p class="text-sm text-red-700">Permanently delete your account and all associated data</p>
            </div>
            <button @click="showDeleteConfirmation = true" class="bg-red-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-red-700 transition">
              Delete Account
            </button>
          </div>
        </div>
      </x-ui.card-content>
    </x-ui.card>

    <!-- Delete Account Confirmation Modal -->
    <div x-show="showDeleteConfirmation" x-cloak class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50" @click.self="showDeleteConfirmation = false">
      <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-red-900">Delete Account</h3>
          <p class="text-sm text-red-700">This action cannot be undone</p>
        </div>
        <div class="px-6 py-4">
          <div class="space-y-4">
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
              <h4 class="font-medium text-red-900 mb-2">What will be deleted:</h4>
              <ul class="text-sm text-red-700 space-y-1">
                <li>• Your profile and personal information</li>
                <li>• All purchase history and tickets</li>
                <li>• Watchlist and price alerts</li>
                <li>• Account preferences and settings</li>
                <li>• All analytics and activity data</li>
              </ul>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Type "DELETE" to confirm account deletion:
              </label>
              <input type="text" 
                     x-model="deleteConfirmation"
                     placeholder="DELETE"
                     class="block w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent">
            </div>
          </div>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 flex justify-between">
          <button @click="showDeleteConfirmation = false" class="text-gray-600 hover:text-gray-800 px-4 py-2 text-sm font-medium">
            Cancel
          </button>
          <button @click="deleteAccount()" 
                  :disabled="deleteConfirmation !== 'DELETE' || deletingAccount"
                  class="bg-red-600 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed transition">
            <span x-show="!deletingAccount">Delete Account</span>
            <span x-show="deletingAccount" class="flex items-center">
              <svg class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Deleting...
            </span>
          </button>
        </div>
      </div>
    </div>
  </div>

  @push('scripts')
    <script>
      function accountSettings() {
        return {
          // State
          activeTab: 'profile',
          updatingProfile: false,
          updatingPassword: false,
          updatingPreferences: false,
          showDeleteConfirmation: false,
          deleteConfirmation: '',
          deletingAccount: false,

          // Data
          profile: @json(Auth::user()),
          security: {
            current_password: '',
            new_password: '',
            confirm_password: '',
            two_factor_enabled: {{ Auth::user()->two_factor_secret ? 'true' : 'false' }},
            active_sessions: []
          },
          preferences: {
            theme: 'light',
            language: 'en',
            timezone: 'America/New_York',
            default_location: '',
            default_price_min: '',
            default_price_max: '',
            auto_refresh: true,
            location_services: false,
            analytics_tracking: true,
            personalized_recommendations: true,
            social_features: true
          },
          subscription: {},
          availableSports: [
            { slug: 'football', name: 'Football', icon: '🏈' },
            { slug: 'basketball', name: 'Basketball', icon: '🏀' },
            { slug: 'baseball', name: 'Baseball', icon: '⚾' },
            { slug: 'hockey', name: 'Hockey', icon: '🏒' },
            { slug: 'soccer', name: 'Soccer', icon: '⚽' },
            { slug: 'tennis', name: 'Tennis', icon: '🎾' }
          ],

          async init() {
            await Promise.all([
              this.loadProfile(),
              this.loadSecuritySettings(),
              this.loadPreferences(),
              this.loadSubscription()
            ]);
          },

          async loadProfile() {
            try {
              const response = await fetch('/api/user/profile');
              const data = await response.json();
              
              if (data.success) {
                this.profile = { ...this.profile, ...data.profile };
              }
            } catch (error) {
              console.error('Failed to load profile:', error);
            }
          },

          async loadSecuritySettings() {
            try {
              const response = await fetch('/api/user/security');
              const data = await response.json();
              
              if (data.success) {
                this.security = { ...this.security, ...data.security };
              }
            } catch (error) {
              console.error('Failed to load security settings:', error);
            }
          },

          async loadPreferences() {
            try {
              const response = await fetch('/api/user/preferences');
              const data = await response.json();
              
              if (data.success) {
                this.preferences = { ...this.preferences, ...data.preferences };
              }
            } catch (error) {
              console.error('Failed to load preferences:', error);
            }
          },

          async loadSubscription() {
            if ('{{ Auth::user()->role }}' !== 'customer') return;
            
            try {
              const response = await fetch('/api/user/subscription');
              const data = await response.json();
              
              if (data.success) {
                this.subscription = data.subscription || {};
              }
            } catch (error) {
              console.error('Failed to load subscription:', error);
            }
          },

          async updateProfile() {
            this.updatingProfile = true;
            
            try {
              const response = await fetch('/api/user/profile', {
                method: 'PUT',
                headers: {
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(this.profile)
              });

              const data = await response.json();

              if (data.success) {
                this.showNotification('Success', 'Profile updated successfully', 'success');
              } else {
                this.showNotification('Error', data.message || 'Failed to update profile', 'error');
              }
            } catch (error) {
              this.showNotification('Error', 'Failed to update profile', 'error');
            } finally {
              this.updatingProfile = false;
            }
          },

          async updatePassword() {
            this.updatingPassword = true;
            
            try {
              const response = await fetch('/api/user/password', {
                method: 'PUT',
                headers: {
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                  current_password: this.security.current_password,
                  new_password: this.security.new_password,
                  confirm_password: this.security.confirm_password
                })
              });

              const data = await response.json();

              if (data.success) {
                this.security.current_password = '';
                this.security.new_password = '';
                this.security.confirm_password = '';
                this.showNotification('Success', 'Password updated successfully', 'success');
              } else {
                this.showNotification('Error', data.message || 'Failed to update password', 'error');
              }
            } catch (error) {
              this.showNotification('Error', 'Failed to update password', 'error');
            } finally {
              this.updatingPassword = false;
            }
          },

          async updatePreferences() {
            this.updatingPreferences = true;
            
            try {
              const response = await fetch('/api/user/preferences', {
                method: 'PUT',
                headers: {
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(this.preferences)
              });

              const data = await response.json();

              if (data.success) {
                this.showNotification('Success', 'Preferences updated successfully', 'success');
              } else {
                this.showNotification('Error', data.message || 'Failed to update preferences', 'error');
              }
            } catch (error) {
              this.showNotification('Error', 'Failed to update preferences', 'error');
            } finally {
              this.updatingPreferences = false;
            }
          },

          async handleAvatarUpload(event) {
            const file = event.target.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('avatar', file);

            try {
              const response = await fetch('/api/user/avatar', {
                method: 'POST',
                headers: {
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: formData
              });

              const data = await response.json();

              if (data.success) {
                this.profile.avatar_url = data.avatar_url;
                this.showNotification('Success', 'Profile photo updated', 'success');
              } else {
                this.showNotification('Error', data.message || 'Failed to upload photo', 'error');
              }
            } catch (error) {
              this.showNotification('Error', 'Failed to upload photo', 'error');
            }
          },

          async deleteAccount() {
            this.deletingAccount = true;
            
            try {
              const response = await fetch('/api/user/account', {
                method: 'DELETE',
                headers: {
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                  confirmation: this.deleteConfirmation
                })
              });

              const data = await response.json();

              if (data.success) {
                this.showNotification('Success', 'Account deleted successfully', 'success');
                setTimeout(() => {
                  window.location.href = '/';
                }, 2000);
              } else {
                this.showNotification('Error', data.message || 'Failed to delete account', 'error');
              }
            } catch (error) {
              this.showNotification('Error', 'Failed to delete account', 'error');
            } finally {
              this.deletingAccount = false;
            }
          },

          getPasswordStrength(password) {
            if (!password) return 0;
            let strength = 0;
            if (password.length >= 8) strength++;
            if (/[A-Z]/.test(password) && /[a-z]/.test(password)) strength++;
            if (/[0-9]/.test(password) && /[^A-Za-z0-9]/.test(password)) strength++;
            return strength;
          },

          getPasswordStrengthText(password) {
            const strength = this.getPasswordStrength(password);
            const texts = ['Weak', 'Fair', 'Good', 'Strong'];
            return texts[strength] || 'Very Weak';
          },

          get isPasswordValid() {
            return this.security.new_password && 
                   this.security.confirm_password && 
                   this.security.new_password === this.security.confirm_password &&
                   this.getPasswordStrength(this.security.new_password) >= 2;
          },

          formatCurrency(value) {
            return new Intl.NumberFormat('en-US', {
              style: 'currency',
              currency: 'USD',
              minimumFractionDigits: 2
            }).format(value);
          },

          formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { 
              month: 'long', 
              day: 'numeric',
              year: 'numeric'
            });
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
