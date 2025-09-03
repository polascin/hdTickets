@extends('layouts.app')

@section('content')
  <div class="min-h-screen bg-gray-50" x-data="preferencesManager()">
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">User Preferences</h1>
            <p class="mt-2 text-gray-600">Customize your HD Tickets experience to match your preferences</p>
          </div>

          <div class="flex items-center space-x-3">
            <button type="button" @click="exportPreferences()"
              class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              Export
            </button>

            <button type="button" @click="resetPreferences()"
              class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
              Reset to Defaults
            </button>
          </div>
        </div>
      </div>

      <!-- Status Messages -->
      <div x-show="message" x-text="message"
        :class="messageType === 'success' ? 'bg-green-100 text-green-800 border-green-200' :
            'bg-red-100 text-red-800 border-red-200'"
        class="mb-6 p-4 rounded-md border transition-all duration-300"
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95"
        x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95">
      </div>

      <!-- Navigation Tabs -->
      <div class="mb-8">
        <nav class="flex space-x-8" aria-label="Preferences">
          <button @click="activeTab = 'notifications'"
            :class="activeTab === 'notifications' ? 'border-blue-500 text-blue-600' :
                'border-transparent text-gray-500 hover:text-gray-700'"
            class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 17h5l-5-5V9.5a6.5 6.5 0 10-13 0V12l-5 5h5a3 3 0 003 3z" />
            </svg>
            Notifications
          </button>
          <button @click="activeTab = 'display'"
            :class="activeTab === 'display' ? 'border-blue-500 text-blue-600' :
                'border-transparent text-gray-500 hover:text-gray-700'"
            class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
            Display & Theme
          </button>
          <button @click="activeTab = 'timezone'"
            :class="activeTab === 'timezone' ? 'border-blue-500 text-blue-600' :
                'border-transparent text-gray-500 hover:text-gray-700'"
            class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Timezone & Language
          </button>
          <button @click="activeTab = 'alerts'"
            :class="activeTab === 'alerts' ? 'border-blue-500 text-blue-600' :
                'border-transparent text-gray-500 hover:text-gray-700'"
            class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.99-.833-2.464 0L4.35 16.5c-.77.833.192 2.5 1.732 2.5z" />
            </svg>
            Alerts & Thresholds
          </button>
          <button @click="activeTab = 'sports'"
            :class="activeTab === 'sports' ? 'border-blue-500 text-blue-600' :
                'border-transparent text-gray-500 hover:text-gray-700'"
            class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Sports Preferences
          </button>
          <button @click="activeTab = 'performance'"
            :class="activeTab === 'performance' ? 'border-blue-500 text-blue-600' :
                'border-transparent text-gray-500 hover:text-gray-700'"
            class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
            Performance
          </button>
        </nav>
      </div>

      <!-- Content -->
      <div class="bg-white shadow-lg rounded-lg">
        <!-- Notification Settings Tab -->
        <div x-show="activeTab === 'notifications'" x-transition>
          <div class="p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">Notification Settings</h2>

            <!-- Notification Channels -->
            <div class="space-y-6">
              <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Notification Channels</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                  <!-- Email Notifications -->
                  <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                      <div class="flex items-center">
                        <svg class="w-6 h-6 text-blue-500 mr-3" fill="none" stroke="currentColor"
                          viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <div>
                          <label class="text-sm font-medium text-gray-900">Email</label>
                          <p class="text-xs text-gray-500">Receive email notifications</p>
                        </div>
                      </div>
                      <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" x-model="preferences.email_notifications"
                          @change="updatePreference('email_notifications', $event.target.checked)" class="sr-only peer">
                        <div
                          class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                        </div>
                      </label>
                    </div>
                  </div>

                  <!-- Push Notifications -->
                  <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                      <div class="flex items-center">
                        <svg class="w-6 h-6 text-green-500 mr-3" fill="none" stroke="currentColor"
                          viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        <div>
                          <label class="text-sm font-medium text-gray-900">Push</label>
                          <p class="text-xs text-gray-500">Browser push notifications</p>
                        </div>
                      </div>
                      <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" x-model="preferences.push_notifications"
                          @change="updatePreference('push_notifications', $event.target.checked)" class="sr-only peer">
                        <div
                          class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                        </div>
                      </label>
                    </div>
                  </div>

                  <!-- SMS Notifications -->
                  <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                      <div class="flex items-center">
                        <svg class="w-6 h-6 text-yellow-500 mr-3" fill="none" stroke="currentColor"
                          viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                        <div>
                          <label class="text-sm font-medium text-gray-900">SMS</label>
                          <p class="text-xs text-gray-500">Text message alerts</p>
                        </div>
                      </div>
                      <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" x-model="preferences.sms_notifications"
                          @change="updatePreference('sms_notifications', $event.target.checked)" class="sr-only peer">
                        <div
                          class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                        </div>
                      </label>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Notification Frequency -->
              <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Notification Frequency</h3>
                <div class="space-y-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Alert Frequency</label>
                    <select x-model="preferences.notification_frequency"
                      @change="updatePreference('notification_frequency', $event.target.value)"
                      class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                      <option value="immediate">Immediate</option>
                      <option value="hourly">Hourly Summary</option>
                      <option value="daily">Daily Digest</option>
                    </select>
                  </div>
                </div>
              </div>

              <!-- Quiet Hours -->
              <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Quiet Hours</h3>
                <div class="space-y-4">
                  <div class="flex items-center">
                    <label class="relative inline-flex items-center cursor-pointer">
                      <input type="checkbox" x-model="preferences.quiet_hours_enabled"
                        @change="updatePreference('quiet_hours_enabled', $event.target.checked)" class="sr-only peer">
                      <div
                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                      </div>
                    </label>
                    <span class="ml-3 text-sm font-medium text-gray-900">Enable quiet hours</span>
                  </div>

                  <div x-show="preferences.quiet_hours_enabled" class="grid grid-cols-2 gap-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700">Start Time</label>
                      <input type="time" x-model="preferences.quiet_hours_start"
                        @change="updatePreference('quiet_hours_start', $event.target.value)"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700">End Time</label>
                      <input type="time" x-model="preferences.quiet_hours_end"
                        @change="updatePreference('quiet_hours_end', $event.target.value)"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Display & Theme Tab -->
        <div x-show="activeTab === 'display'" x-transition>
          <div class="p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">Display & Theme Settings</h2>

            <div class="space-y-8">
              <!-- Theme Selection -->
              <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Theme</h3>
                <div class="grid grid-cols-3 gap-4">
                  @foreach ($themes as $themeKey => $themeData)
                    <div class="relative">
                      <input type="radio" name="theme" value="{{ $themeKey }}" x-model="preferences.theme"
                        @change="updatePreference('theme', '{{ $themeKey }}')" class="sr-only peer"
                        id="theme_{{ $themeKey }}">
                      <label for="theme_{{ $themeKey }}"
                        class="block p-4 border-2 border-gray-200 rounded-lg cursor-pointer peer-checked:border-blue-500 peer-checked:ring-2 peer-checked:ring-blue-200 hover:border-gray-300">
                        <div class="w-full h-16 rounded mb-3" style="background: {{ $themeData['preview'] }}"></div>
                        <div class="text-sm font-medium text-gray-900">{{ $themeData['name'] }}</div>
                        <div class="text-xs text-gray-500">{{ $themeData['description'] }}</div>
                      </label>
                    </div>
                  @endforeach
                </div>
              </div>

              <!-- Display Density -->
              <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Display Density</h3>
                <div class="space-y-3">
                  <div class="flex items-center">
                    <input type="radio" name="density" value="compact" x-model="preferences.display_density"
                      @change="updatePreference('display_density', 'compact')"
                      class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300" id="density_compact">
                    <label for="density_compact" class="ml-3 block text-sm font-medium text-gray-700">
                      Compact - More content, less spacing
                    </label>
                  </div>
                  <div class="flex items-center">
                    <input type="radio" name="density" value="comfortable" x-model="preferences.display_density"
                      @change="updatePreference('display_density', 'comfortable')"
                      class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300" id="density_comfortable">
                    <label for="density_comfortable" class="ml-3 block text-sm font-medium text-gray-700">
                      Comfortable - Balanced spacing (recommended)
                    </label>
                  </div>
                  <div class="flex items-center">
                    <input type="radio" name="density" value="spacious" x-model="preferences.display_density"
                      @change="updatePreference('display_density', 'spacious')"
                      class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300" id="density_spacious">
                    <label for="density_spacious" class="ml-3 block text-sm font-medium text-gray-700">
                      Spacious - More spacing, easier reading
                    </label>
                  </div>
                </div>
              </div>

              <!-- Interface Options -->
              <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Interface Options</h3>
                <div class="space-y-4">
                  <div class="flex items-center justify-between">
                    <div>
                      <label class="text-sm font-medium text-gray-900">Sidebar collapsed by default</label>
                      <p class="text-sm text-gray-500">Start with a collapsed sidebar for more content space</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                      <input type="checkbox" x-model="preferences.sidebar_collapsed"
                        @change="updatePreference('sidebar_collapsed', $event.target.checked)" class="sr-only peer">
                      <div
                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                      </div>
                    </label>
                  </div>

                  <div class="flex items-center justify-between">
                    <div>
                      <label class="text-sm font-medium text-gray-900">Show tooltips</label>
                      <p class="text-sm text-gray-500">Display helpful tooltips when hovering over elements</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                      <input type="checkbox" x-model="preferences.show_tooltips"
                        @change="updatePreference('show_tooltips', $event.target.checked)" class="sr-only peer">
                      <div
                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                      </div>
                    </label>
                  </div>

                  <div class="flex items-center justify-between">
                    <div>
                      <label class="text-sm font-medium text-gray-900">Enable animations</label>
                      <p class="text-sm text-gray-500">Use smooth animations and transitions</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                      <input type="checkbox" x-model="preferences.animation_enabled"
                        @change="updatePreference('animation_enabled', $event.target.checked)" class="sr-only peer">
                      <div
                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                      </div>
                    </label>
                  </div>

                  <div class="flex items-center justify-between">
                    <div>
                      <label class="text-sm font-medium text-gray-900">High contrast mode</label>
                      <p class="text-sm text-gray-500">Increase contrast for better accessibility</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                      <input type="checkbox" x-model="preferences.high_contrast"
                        @change="updatePreference('high_contrast', $event.target.checked)" class="sr-only peer">
                      <div
                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                      </div>
                    </label>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Timezone & Language Tab -->
        <div x-show="activeTab === 'timezone'" x-transition>
          <div class="p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">Timezone & Language Settings</h2>

            <div class="space-y-6">
              <!-- Timezone Selection -->
              <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Timezone</h3>
                <div class="flex items-center space-x-4 mb-4">
                  <button @click="detectTimezone()"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-blue-600 bg-blue-50 border border-blue-200 rounded-md hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Auto-detect my timezone
                  </button>
                  <span class="text-sm text-gray-500" x-text="'Current: ' + preferences.user_timezone"></span>
                </div>

                <select x-model="preferences.user_timezone" @change="updateTimezone($event.target.value)"
                  class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                  @foreach ($timezones as $tz => $display)
                    <option value="{{ $tz }}">{{ $display }}</option>
                  @endforeach
                </select>
              </div>

              <!-- Language Selection -->
              <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Language</h3>
                <select x-model="preferences.user_language" @change="updatePreference('language', $event.target.value)"
                  class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                  @foreach ($languages as $code => $name)
                    <option value="{{ $code }}">{{ $name }}</option>
                  @endforeach
                </select>
              </div>

              <!-- Currency Format -->
              <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Currency Format</h3>
                <select x-model="preferences.currency_format"
                  @change="updatePreference('currency_format', $event.target.value)"
                  class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                  <option value="USD">USD ($)</option>
                  <option value="EUR">EUR (€)</option>
                  <option value="GBP">GBP (£)</option>
                  <option value="CAD">CAD (C$)</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <!-- Alerts & Thresholds Tab -->
        <div x-show="activeTab === 'alerts'" x-transition>
          <div class="p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">Alert & Threshold Settings</h2>

            <div class="space-y-6">
              <!-- Alert Types -->
              <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Alert Types</h3>
                <div class="space-y-4">
                  <div class="flex items-center justify-between">
                    <div>
                      <label class="text-sm font-medium text-gray-900">Availability Alerts</label>
                      <p class="text-sm text-gray-500">Get notified when tickets become available</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                      <input type="checkbox" x-model="preferences.availability_alerts"
                        @change="updatePreference('availability_alerts', $event.target.checked)" class="sr-only peer">
                      <div
                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                      </div>
                    </label>
                  </div>

                  <div class="flex items-center justify-between">
                    <div>
                      <label class="text-sm font-medium text-gray-900">Price Alerts</label>
                      <p class="text-sm text-gray-500">Get notified about price changes</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                      <input type="checkbox" x-model="preferences.price_alerts"
                        @change="updatePreference('price_alerts', $event.target.checked)" class="sr-only peer">
                      <div
                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                      </div>
                    </label>
                  </div>

                  <div class="flex items-center justify-between">
                    <div>
                      <label class="text-sm font-medium text-gray-900">High Demand Alerts</label>
                      <p class="text-sm text-gray-500">Get notified about high-demand events</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                      <input type="checkbox" x-model="preferences.high_demand_alerts"
                        @change="updatePreference('high_demand_alerts', $event.target.checked)" class="sr-only peer">
                      <div
                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                      </div>
                    </label>
                  </div>
                </div>
              </div>

              <!-- Thresholds -->
              <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Alert Thresholds</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Price Drop Threshold (%)</label>
                    <input type="number" x-model="preferences.price_drop_threshold"
                      @input="updatePreference('price_drop_threshold', parseInt($event.target.value))" min="1"
                      max="50" step="1"
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <p class="mt-1 text-xs text-gray-500">Alert when price drops by this percentage</p>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Dashboard Refresh Interval
                      (seconds)</label>
                    <input type="number" x-model="preferences.dashboard_refresh_interval"
                      @input="updatePreference('dashboard_refresh_interval', parseInt($event.target.value))"
                      min="10" max="300" step="5"
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <p class="mt-1 text-xs text-gray-500">How often to refresh dashboard data</p>
                  </div>
                </div>
              </div>

              <!-- Escalation Settings -->
              <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Escalation Settings</h3>
                <div class="space-y-4">
                  <div class="flex items-center justify-between">
                    <div>
                      <label class="text-sm font-medium text-gray-900">Enable alert escalation</label>
                      <p class="text-sm text-gray-500">Escalate alerts if not acknowledged</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                      <input type="checkbox" x-model="preferences.escalation_enabled"
                        @change="updatePreference('escalation_enabled', $event.target.checked)" class="sr-only peer">
                      <div
                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                      </div>
                    </label>
                  </div>

                  <div x-show="preferences.escalation_enabled">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Escalation Delay (minutes)</label>
                    <input type="number" x-model="preferences.escalation_delay_minutes"
                      @input="updatePreference('escalation_delay_minutes', parseInt($event.target.value))"
                      min="1" max="60" step="1"
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <p class="mt-1 text-xs text-gray-500">Time to wait before escalating an unacknowledged alert</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Performance Tab -->
        <div x-show="activeTab === 'performance'" x-transition>
          <div class="p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">Performance Settings</h2>

            <div class="space-y-6">
              <!-- Dashboard Performance -->
              <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Dashboard Performance</h3>
                <div class="space-y-4">
                  <div class="flex items-center justify-between">
                    <div>
                      <label class="text-sm font-medium text-gray-900">Auto-refresh dashboard</label>
                      <p class="text-sm text-gray-500">Automatically refresh dashboard data</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                      <input type="checkbox" x-model="preferences.dashboard_auto_refresh"
                        @change="updatePreference('dashboard_auto_refresh', $event.target.checked)"
                        class="sr-only peer">
                      <div
                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                      </div>
                    </label>
                  </div>

                  <div class="flex items-center justify-between">
                    <div>
                      <label class="text-sm font-medium text-gray-900">Enable lazy loading</label>
                      <p class="text-sm text-gray-500">Load content as you scroll for better performance</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                      <input type="checkbox" x-model="preferences.lazy_loading_enabled"
                        @change="updatePreference('lazy_loading_enabled', $event.target.checked)" class="sr-only peer">
                      <div
                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                      </div>
                    </label>
                  </div>

                  <div class="flex items-center justify-between">
                    <div>
                      <label class="text-sm font-medium text-gray-900">Data compression</label>
                      <p class="text-sm text-gray-500">Compress data transfers to save bandwidth</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                      <input type="checkbox" x-model="preferences.data_compression"
                        @change="updatePreference('data_compression', $event.target.checked)" class="sr-only peer">
                      <div
                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                      </div>
                    </label>
                  </div>
                </div>
              </div>

              <!-- Bandwidth Optimization -->
              <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Bandwidth Optimization</h3>
                <div class="space-y-3">
                  <div class="flex items-center">
                    <input type="radio" name="bandwidth" value="auto" x-model="preferences.bandwidth_optimization"
                      @change="updatePreference('bandwidth_optimization', 'auto')"
                      class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300" id="bandwidth_auto">
                    <label for="bandwidth_auto" class="ml-3 block text-sm font-medium text-gray-700">
                      Auto - Adapt to connection speed
                    </label>
                  </div>
                  <div class="flex items-center">
                    <input type="radio" name="bandwidth" value="low" x-model="preferences.bandwidth_optimization"
                      @change="updatePreference('bandwidth_optimization', 'low')"
                      class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300" id="bandwidth_low">
                    <label for="bandwidth_low" class="ml-3 block text-sm font-medium text-gray-700">
                      Low bandwidth - Minimize data usage
                    </label>
                  </div>
                  <div class="flex items-center">
                    <input type="radio" name="bandwidth" value="high" x-model="preferences.bandwidth_optimization"
                      @change="updatePreference('bandwidth_optimization', 'high')"
                      class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300" id="bandwidth_high">
                    <label for="bandwidth_high" class="ml-3 block text-sm font-medium text-gray-700">
                      High bandwidth - Full quality, faster loading
                    </label>
                  </div>
                </div>
              </div>

              <!-- Advanced Options -->
              <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Advanced Options</h3>
                <div class="space-y-4">
                  <div class="flex items-center justify-between">
                    <div>
                      <label class="text-sm font-medium text-gray-900">Offline mode</label>
                      <p class="text-sm text-gray-500">Cache data for offline access (experimental)</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                      <input type="checkbox" x-model="preferences.offline_mode"
                        @change="updatePreference('offline_mode', $event.target.checked)" class="sr-only peer">
                      <div
                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                      </div>
                    </label>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Sports Preferences Tab -->
        <div x-show="activeTab === 'sports'" x-transition>
          <div class="p-6" x-data="sportsPreferencesManager()">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">Sports Event Ticket Preferences</h2>

            <div class="space-y-8">
              <!-- Favorite Sports Section -->
              <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Favorite Sports</h3>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                  <template x-for="(sport, key) in availableSports" :key="key">
                    <label
                      class="flex items-center p-3 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-300 transition-colors"
                      :class="selectedSports.includes(key) ? 'border-blue-500 bg-blue-50' : ''">
                      <input type="checkbox" :value="key" x-model="selectedSports"
                        @change="updateSelectedSports()" class="sr-only">
                      <div class="flex items-center">
                        <div class="flex-shrink-0">
                          <div class="w-8 h-8 rounded-full flex items-center justify-center"
                            :class="selectedSports.includes(key) ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-600'">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                              <path fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd" />
                            </svg>
                          </div>
                        </div>
                        <div class="ml-3">
                          <span class="text-sm font-medium text-gray-900" x-text="sport"></span>
                        </div>
                      </div>
                    </label>
                  </template>
                </div>
              </div>

              <!-- Favorite Teams Section -->
              <div>
                <div class="flex items-center justify-between mb-4">
                  <h3 class="text-lg font-medium text-gray-900">Favorite Teams</h3>
                  <button type="button" @click="showAddTeamModal = true"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-blue-600 bg-blue-50 border border-blue-200 rounded-md hover:bg-blue-100">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Team
                  </button>
                </div>

                <div class="space-y-3">
                  <template x-for="team in favoriteTeams" :key="team.id">
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                      <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0">
                          <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                            <span class="text-blue-600 font-semibold text-sm"
                              x-text="team.sport_type.charAt(0).toUpperCase()"></span>
                          </div>
                        </div>
                        <div>
                          <h4 class="font-medium text-gray-900"
                            x-text="team.team_city ? `${team.team_city} ${team.team_name}` : team.team_name"></h4>
                          <p class="text-sm text-gray-500"
                            x-text="`${team.sport_type} • ${team.league || 'Unknown League'}`"></p>
                        </div>
                      </div>
                      <div class="flex items-center space-x-3">
                        <!-- Priority Stars -->
                        <div class="flex items-center">
                          <template x-for="i in 5" :key="i">
                            <button @click="updateTeamPriority(team.id, i)" class="w-4 h-4 focus:outline-none"
                              :class="i <= team.priority ? 'text-yellow-400' : 'text-gray-300'">
                              <svg fill="currentColor" viewBox="0 0 20 20">
                                <path
                                  d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                              </svg>
                            </button>
                          </template>
                        </div>
                        <!-- Notifications Toggle -->
                        <div class="flex items-center space-x-2">
                          <div class="flex items-center space-x-1">
                            <button @click="toggleTeamAlert(team.id, 'email')"
                              :class="team.email_alerts ? 'text-blue-600' : 'text-gray-400'"
                              class="p-1 rounded hover:bg-gray-200">
                              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                              </svg>
                            </button>
                            <button @click="toggleTeamAlert(team.id, 'push')"
                              :class="team.push_alerts ? 'text-green-600' : 'text-gray-400'"
                              class="p-1 rounded hover:bg-gray-200">
                              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 2L3 9v5a1 1 0 001 1h3v-3h6v3h3a1 1 0 001-1V9l-7-7z" />
                              </svg>
                            </button>
                          </div>
                        </div>
                        <button @click="removeTeam(team.id)" class="text-red-500 hover:text-red-700">
                          <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                              d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                              clip-rule="evenodd" />
                          </svg>
                        </button>
                      </div>
                    </div>
                  </template>

                  <div x-show="favoriteTeams.length === 0" class="text-center py-8 text-gray-500">
                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor"
                      viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <p>No favorite teams added yet. Click "Add Team" to get started!</p>
                  </div>
                </div>
              </div>

              <!-- Favorite Venues Section -->
              <div>
                <div class="flex items-center justify-between mb-4">
                  <h3 class="text-lg font-medium text-gray-900">Favorite Venues</h3>
                  <button type="button" @click="showAddVenueModal = true"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-blue-600 bg-blue-50 border border-blue-200 rounded-md hover:bg-blue-100">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Venue
                  </button>
                </div>

                <div class="space-y-3">
                  <template x-for="venue in favoriteVenues" :key="venue.id">
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                      <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0">
                          <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                              <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                          </div>
                        </div>
                        <div>
                          <h4 class="font-medium text-gray-900" x-text="venue.venue_name"></h4>
                          <p class="text-sm text-gray-500"
                            x-text="`${venue.city}, ${venue.state_province || venue.country}`"></p>
                        </div>
                      </div>
                      <div class="flex items-center space-x-3">
                        <!-- Priority Stars -->
                        <div class="flex items-center">
                          <template x-for="i in 5" :key="i">
                            <button @click="updateVenuePriority(venue.id, i)" class="w-4 h-4 focus:outline-none"
                              :class="i <= venue.priority ? 'text-yellow-400' : 'text-gray-300'">
                              <svg fill="currentColor" viewBox="0 0 20 20">
                                <path
                                  d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                              </svg>
                            </button>
                          </template>
                        </div>
                        <!-- Notifications Toggle -->
                        <div class="flex items-center space-x-2">
                          <div class="flex items-center space-x-1">
                            <button @click="toggleVenueAlert(venue.id, 'email')"
                              :class="venue.email_alerts ? 'text-blue-600' : 'text-gray-400'"
                              class="p-1 rounded hover:bg-gray-200">
                              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                              </svg>
                            </button>
                            <button @click="toggleVenueAlert(venue.id, 'push')"
                              :class="venue.push_alerts ? 'text-green-600' : 'text-gray-400'"
                              class="p-1 rounded hover:bg-gray-200">
                              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 2L3 9v5a1 1 0 001 1h3v-3h6v3h3a1 1 0 001-1V9l-7-7z" />
                              </svg>
                            </button>
                          </div>
                        </div>
                        <button @click="removeVenue(venue.id)" class="text-red-500 hover:text-red-700">
                          <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                              d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                              clip-rule="evenodd" />
                          </svg>
                        </button>
                      </div>
                    </div>
                  </template>

                  <div x-show="favoriteVenues.length === 0" class="text-center py-8 text-gray-500">
                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor"
                      viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <p>No favorite venues added yet. Click "Add Venue" to get started!</p>
                  </div>
                </div>
              </div>

              <!-- Price Preferences Section -->
              <div>
                <div class="flex items-center justify-between mb-4">
                  <h3 class="text-lg font-medium text-gray-900">Price Threshold Alerts</h3>
                  <button type="button" @click="showAddPriceModal = true"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-blue-600 bg-blue-50 border border-blue-200 rounded-md hover:bg-blue-100">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Price Alert
                  </button>
                </div>

                <div class="space-y-4">
                  <template x-for="pref in pricePreferences" :key="pref.id">
                    <div class="border border-gray-200 rounded-lg p-4">
                      <div class="flex items-center justify-between mb-3">
                        <h4 class="font-medium text-gray-900" x-text="pref.preference_name"></h4>
                        <div class="flex items-center space-x-2">
                          <span x-show="pref.auto_purchase_enabled"
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            Auto-Purchase
                          </span>
                          <button @click="removePricePreference(pref.id)" class="text-red-500 hover:text-red-700">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                              <path fill-rule="evenodd"
                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                clip-rule="evenodd" />
                            </svg>
                          </button>
                        </div>
                      </div>
                      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-gray-600">
                        <div>
                          <span class="font-medium">Sport:</span> <span x-text="pref.sport_type || 'Any'"></span>
                        </div>
                        <div>
                          <span class="font-medium">Price Range:</span>
                          <span
                            x-text="pref.min_price ? `$${pref.min_price} - $${pref.max_price}` : `Up to $${pref.max_price}`"></span>
                        </div>
                        <div>
                          <span class="font-medium">Quantity:</span> <span x-text="pref.preferred_quantity"></span>
                        </div>
                      </div>
                      <div class="mt-2 flex items-center space-x-4">
                        <div class="flex items-center space-x-1">
                          <button @click="togglePriceAlert(pref.id, 'email')"
                            :class="pref.email_alerts ? 'text-blue-600' : 'text-gray-400'"
                            class="p-1 rounded hover:bg-gray-200">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                              <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                              <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                            </svg>
                          </button>
                          <button @click="togglePriceAlert(pref.id, 'push')"
                            :class="pref.push_alerts ? 'text-green-600' : 'text-gray-400'"
                            class="p-1 rounded hover:bg-gray-200">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                              <path d="M10 2L3 9v5a1 1 0 001 1h3v-3h6v3h3a1 1 0 001-1V9l-7-7z" />
                            </svg>
                          </button>
                        </div>
                        <span class="text-xs text-gray-500"
                          x-text="`${pref.alert_frequency} alerts • Drop threshold: ${pref.price_drop_threshold}%`"></span>
                      </div>
                    </div>
                  </template>

                  <div x-show="pricePreferences.length === 0" class="text-center py-8 text-gray-500">
                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor"
                      viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p>No price preferences configured yet. Click "Add Price Alert" to get started!</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Save Button -->
      <div class="mt-8 flex justify-end">
        <button type="button" @click="saveAllPreferences()" :disabled="saving"
          class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed">
          <svg x-show="saving" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg"
            fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
            </circle>
            <path class="opacity-75" fill="currentColor"
              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
            </path>
          </svg>
          <span x-show="!saving">Save All Preferences</span>
          <span x-show="saving">Saving...</span>
        </button>
      </div>
    </div>
  </div>

  <!-- Add Team Modal -->
  <div x-show="showAddTeamModal" x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50"
    @click.away="showAddTeamModal = false" x-data="teamModalManager()" style="display: none">
    <div class="bg-white rounded-lg p-6 w-full max-w-md">
      <h3 class="text-lg font-medium text-gray-900 mb-4">Add Favorite Team</h3>

      <form @submit.prevent="addTeam()">
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Sport Type</label>
            <select x-model="newTeam.sport_type" required
              class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
              <option value="">Select a sport</option>
              <template x-for="(sport, key) in availableSports" :key="key">
                <option :value="key" x-text="sport"></option>
              </template>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Team Name</label>
            <input type="text" x-model="newTeam.team_name" @input="searchTeams()"
              placeholder="Start typing team name..." required
              class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
            <!-- Search results dropdown -->
            <div x-show="teamSearchResults.length > 0"
              class="absolute z-10 w-full bg-white border border-gray-300 rounded-md shadow-lg mt-1">
              <template x-for="team in teamSearchResults.slice(0, 5)" :key="team.id">
                <div @click="selectTeam(team)" class="px-3 py-2 hover:bg-gray-100 cursor-pointer">
                  <div class="font-medium" x-text="team.name"></div>
                  <div class="text-sm text-gray-500" x-text="team.league + ' • ' + team.city"></div>
                </div>
              </template>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Team City (optional)</label>
            <input type="text" x-model="newTeam.team_city"
              class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">League</label>
            <input type="text" x-model="newTeam.league" required
              class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Priority (1-5 stars)</label>
            <div class="flex items-center space-x-1">
              <template x-for="i in 5" :key="i">
                <button type="button" @click="newTeam.priority = i" class="w-6 h-6 focus:outline-none"
                  :class="i <= newTeam.priority ? 'text-yellow-400' : 'text-gray-300'">
                  <svg fill="currentColor" viewBox="0 0 20 20">
                    <path
                      d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                  </svg>
                </button>
              </template>
            </div>
          </div>

          <div class="flex items-center space-x-4">
            <label class="flex items-center">
              <input type="checkbox" x-model="newTeam.email_alerts"
                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
              <span class="ml-2 text-sm text-gray-700">Email alerts</span>
            </label>
            <label class="flex items-center">
              <input type="checkbox" x-model="newTeam.push_alerts"
                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
              <span class="ml-2 text-sm text-gray-700">Push alerts</span>
            </label>
          </div>
        </div>

        <div class="flex justify-end space-x-3 mt-6">
          <button type="button" @click="showAddTeamModal = false"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
            Cancel
          </button>
          <button type="submit"
            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700">
            Add Team
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Add Venue Modal -->
  <div x-show="showAddVenueModal" x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50"
    @click.away="showAddVenueModal = false" x-data="venueModalManager()" style="display: none">
    <div class="bg-white rounded-lg p-6 w-full max-w-md">
      <h3 class="text-lg font-medium text-gray-900 mb-4">Add Favorite Venue</h3>

      <form @submit.prevent="addVenue()">
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Venue Name</label>
            <input type="text" x-model="newVenue.venue_name" @input="searchVenues()"
              placeholder="Start typing venue name..." required
              class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
            <!-- Search results dropdown -->
            <div x-show="venueSearchResults.length > 0"
              class="absolute z-10 w-full bg-white border border-gray-300 rounded-md shadow-lg mt-1">
              <template x-for="venue in venueSearchResults.slice(0, 5)" :key="venue.id">
                <div @click="selectVenue(venue)" class="px-3 py-2 hover:bg-gray-100 cursor-pointer">
                  <div class="font-medium" x-text="venue.name"></div>
                  <div class="text-sm text-gray-500" x-text="venue.city + ', ' + venue.state"></div>
                </div>
              </template>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
              <input type="text" x-model="newVenue.city" required
                class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">State/Province</label>
              <input type="text" x-model="newVenue.state_province"
                class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Country</label>
            <input type="text" x-model="newVenue.country" required
              class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Venue Type</label>
            <select x-model="newVenue.venue_type"
              class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
              <option value="stadium">Stadium</option>
              <option value="arena">Arena</option>
              <option value="ballpark">Ballpark</option>
              <option value="field">Field</option>
              <option value="court">Court</option>
              <option value="track">Track</option>
              <option value="other">Other</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Priority (1-5 stars)</label>
            <div class="flex items-center space-x-1">
              <template x-for="i in 5" :key="i">
                <button type="button" @click="newVenue.priority = i" class="w-6 h-6 focus:outline-none"
                  :class="i <= newVenue.priority ? 'text-yellow-400' : 'text-gray-300'">
                  <svg fill="currentColor" viewBox="0 0 20 20">
                    <path
                      d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                  </svg>
                </button>
              </template>
            </div>
          </div>

          <div class="flex items-center space-x-4">
            <label class="flex items-center">
              <input type="checkbox" x-model="newVenue.email_alerts"
                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
              <span class="ml-2 text-sm text-gray-700">Email alerts</span>
            </label>
            <label class="flex items-center">
              <input type="checkbox" x-model="newVenue.push_alerts"
                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
              <span class="ml-2 text-sm text-gray-700">Push alerts</span>
            </label>
          </div>
        </div>

        <div class="flex justify-end space-x-3 mt-6">
          <button type="button" @click="showAddVenueModal = false"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
            Cancel
          </button>
          <button type="submit"
            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700">
            Add Venue
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Add Price Preference Modal -->
  <div x-show="showAddPriceModal" x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50"
    @click.away="showAddPriceModal = false" x-data="priceModalManager()" style="display: none">
    <div class="bg-white rounded-lg p-6 w-full max-w-lg">
      <h3 class="text-lg font-medium text-gray-900 mb-4">Add Price Alert</h3>

      <form @submit.prevent="addPricePreference()">
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Alert Name</label>
            <input type="text" x-model="newPrice.preference_name" required placeholder="e.g., NBA Finals Under $200"
              class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Sport Type</label>
              <select x-model="newPrice.sport_type"
                class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                <option value="">Any Sport</option>
                <template x-for="(sport, key) in availableSports" :key="key">
                  <option :value="key" x-text="sport"></option>
                </template>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Event Category</label>
              <select x-model="newPrice.event_category"
                class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                <option value="">Any Category</option>
                <option value="regular_season">Regular Season</option>
                <option value="playoffs">Playoffs</option>
                <option value="championship">Championship</option>
                <option value="preseason">Preseason</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Min Price ($)</label>
              <input type="number" x-model="newPrice.min_price" min="0" step="0.01"
                class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Max Price ($)</label>
              <input type="number" x-model="newPrice.max_price" min="0" step="0.01" required
                class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Preferred Quantity</label>
              <input type="number" x-model="newPrice.preferred_quantity" min="1" max="20"
                value="2" class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Alert Frequency</label>
              <select x-model="newPrice.alert_frequency"
                class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                <option value="immediate">Immediate</option>
                <option value="hourly">Hourly</option>
                <option value="daily">Daily</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Price Drop Threshold (%)</label>
              <input type="number" x-model="newPrice.price_drop_threshold" min="1" max="50"
                value="10" class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Preferred Section</label>
              <input type="text" x-model="newPrice.preferred_section" placeholder="e.g., Lower Bowl, Upper Deck"
                class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
            </div>
          </div>

          <div class="flex items-center space-x-4">
            <label class="flex items-center">
              <input type="checkbox" x-model="newPrice.email_alerts"
                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
              <span class="ml-2 text-sm text-gray-700">Email alerts</span>
            </label>
            <label class="flex items-center">
              <input type="checkbox" x-model="newPrice.push_alerts"
                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
              <span class="ml-2 text-sm text-gray-700">Push alerts</span>
            </label>
            <label class="flex items-center">
              <input type="checkbox" x-model="newPrice.auto_purchase_enabled"
                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
              <span class="ml-2 text-sm text-gray-700">Auto-purchase</span>
            </label>
          </div>
        </div>

        <div class="flex justify-end space-x-3 mt-6">
          <button type="button" @click="showAddPriceModal = false"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
            Cancel
          </button>
          <button type="submit"
            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700">
            Add Price Alert
          </button>
        </div>
      </form>
    </div>
  </div>

  <script>
    function preferencesManager() {
      return {
        activeTab: 'notifications',
        saving: false,
        message: '',
        messageType: 'success',
        preferences: @json($preferences),

        init() {
          // Initialize preferences
          console.log('Preferences initialized:', this.preferences);
        },

        updatePreference(key, value) {
          this.preferences[key] = value;
          this.savePreference(key, value);
        },

        savePreference(key, value) {
          fetch('{{ route('preferences.update-single') }}', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
              },
              body: JSON.stringify({
                key: key,
                value: value,
                type: typeof value
              })
            })
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                this.showMessage('Preference saved successfully', 'success');
              } else {
                this.showMessage('Failed to save preference: ' + data.message, 'error');
              }
            })
            .catch(error => {
              console.error('Error:', error);
              this.showMessage('An error occurred while saving preference', 'error');
            });
        },

        updateTimezone(timezone) {
          fetch('{{ route('preferences.detect-timezone') }}', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
              },
              body: JSON.stringify({
                timezone: timezone
              })
            })
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                this.preferences.user_timezone = data.timezone;
                this.showMessage('Timezone updated successfully', 'success');
              } else {
                this.showMessage('Failed to update timezone: ' + data.message, 'error');
              }
            })
            .catch(error => {
              console.error('Error:', error);
              this.showMessage('An error occurred while updating timezone', 'error');
            });
        },

        detectTimezone() {
          const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
          this.updateTimezone(timezone);
        },

        saveAllPreferences() {
          this.saving = true;

          // Group preferences by category for batch update
          const categorizedPreferences = {
            notifications: {},
            display: {},
            alerts: {},
            performance: {}
          };

          // Categorize preferences
          Object.keys(this.preferences).forEach(key => {
            if (key.includes('notification') || key.includes('quiet_hours') || key.includes('sms') || key.includes(
                'email') || key.includes('push')) {
              categorizedPreferences.notifications[key] = this.preferences[key];
            } else if (key.includes('theme') || key.includes('display') || key.includes('sidebar') || key.includes(
                'animation') || key.includes('tooltip')) {
              categorizedPreferences.display[key] = this.preferences[key];
            } else if (key.includes('alert') || key.includes('threshold') || key.includes('escalation')) {
              categorizedPreferences.alerts[key] = this.preferences[key];
            } else if (key.includes('dashboard') || key.includes('lazy') || key.includes('compression') || key
              .includes('bandwidth')) {
              categorizedPreferences.performance[key] = this.preferences[key];
            }
          });

          fetch('{{ route('preferences.update') }}', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
              },
              body: JSON.stringify({
                preferences: categorizedPreferences
              })
            })
            .then(response => response.json())
            .then(data => {
              this.saving = false;
              if (data.success) {
                this.showMessage('All preferences saved successfully', 'success');
              } else {
                this.showMessage('Failed to save preferences: ' + data.message, 'error');
              }
            })
            .catch(error => {
              this.saving = false;
              console.error('Error:', error);
              this.showMessage('An error occurred while saving preferences', 'error');
            });
        },

        resetPreferences() {
          if (!confirm(
              'Are you sure you want to reset all preferences to their default values? This action cannot be undone.')) {
            return;
          }

          fetch('{{ route('preferences.reset') }}', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
              }
            })
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                this.preferences = data.preferences;
                this.showMessage('Preferences reset to defaults successfully', 'success');
              } else {
                this.showMessage('Failed to reset preferences: ' + data.message, 'error');
              }
            })
            .catch(error => {
              console.error('Error:', error);
              this.showMessage('An error occurred while resetting preferences', 'error');
            });
        },

        exportPreferences() {
          fetch('{{ route('preferences.export') }}', {
              method: 'GET',
              headers: {
                'Accept': 'application/json'
              }
            })
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                const blob = new Blob([JSON.stringify(data.data, null, 2)], {
                  type: 'application/json'
                });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'hd-tickets-preferences.json';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
                this.showMessage('Preferences exported successfully', 'success');
              } else {
                this.showMessage('Failed to export preferences', 'error');
              }
            })
            .catch(error => {
              console.error('Error:', error);
              this.showMessage('An error occurred while exporting preferences', 'error');
            });
        },

        showMessage(message, type) {
          this.message = message;
          this.messageType = type;
          setTimeout(() => {
            this.message = '';
          }, 5000);
        }
      }
    }

    // Sports Preferences Manager
    function sportsPreferencesManager() {
      return {
        availableSports: {
          'football': 'Football',
          'basketball': 'Basketball',
          'baseball': 'Baseball',
          'hockey': 'Hockey',
          'soccer': 'Soccer',
          'tennis': 'Tennis',
          'golf': 'Golf',
          'boxing': 'Boxing',
          'mma': 'Mixed Martial Arts',
          'wrestling': 'Wrestling',
          'auto_racing': 'Auto Racing',
          'other': 'Other'
        },
        selectedSports: [],
        favoriteTeams: [],
        favoriteVenues: [],
        pricePreferences: [],
        showAddTeamModal: false,
        showAddVenueModal: false,
        showAddPriceModal: false,

        init() {
          this.loadSportsData();
        },

        loadSportsData() {
          fetch('{{ route('preferences.sports.get') }}', {
              method: 'GET',
              headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
              }
            })
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                this.favoriteTeams = data.data.favoriteTeams || [];
                this.favoriteVenues = data.data.favoriteVenues || [];
                this.pricePreferences = data.data.pricePreferences || [];
                this.selectedSports = data.data.selectedSports || [];
              }
            })
            .catch(error => {
              console.error('Error loading sports data:', error);
            });
        },

        updateSelectedSports() {
          fetch('{{ route('preferences.sports.update-selected') }}', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
              },
              body: JSON.stringify({
                sports: this.selectedSports
              })
            })
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                console.log('Selected sports updated successfully');
              } else {
                console.error('Failed to update selected sports');
              }
            })
            .catch(error => {
              console.error('Error updating selected sports:', error);
            });
        },

        updateTeamPriority(teamId, priority) {
          const team = this.favoriteTeams.find(t => t.id === teamId);
          if (team) {
            team.priority = priority;
            this.updateTeamPreference(teamId, {
              priority: priority
            });
          }
        },

        updateVenuePriority(venueId, priority) {
          const venue = this.favoriteVenues.find(v => v.id === venueId);
          if (venue) {
            venue.priority = priority;
            this.updateVenuePreference(venueId, {
              priority: priority
            });
          }
        },

        toggleTeamAlert(teamId, alertType) {
          const team = this.favoriteTeams.find(t => t.id === teamId);
          if (team) {
            const fieldName = alertType + '_alerts';
            team[fieldName] = !team[fieldName];
            this.updateTeamPreference(teamId, {
              [fieldName]: team[fieldName]
            });
          }
        },

        toggleVenueAlert(venueId, alertType) {
          const venue = this.favoriteVenues.find(v => v.id === venueId);
          if (venue) {
            const fieldName = alertType + '_alerts';
            venue[fieldName] = !venue[fieldName];
            this.updateVenuePreference(venueId, {
              [fieldName]: venue[fieldName]
            });
          }
        },

        togglePriceAlert(priceId, alertType) {
          const pref = this.pricePreferences.find(p => p.id === priceId);
          if (pref) {
            const fieldName = alertType + '_alerts';
            pref[fieldName] = !pref[fieldName];
            this.updatePricePreference(priceId, {
              [fieldName]: pref[fieldName]
            });
          }
        },

        updateTeamPreference(teamId, data) {
          fetch(`{{ route('preferences.teams.update', '') }}/${teamId}`, {
              method: 'PUT',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
              },
              body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
              if (!data.success) {
                console.error('Failed to update team preference');
              }
            })
            .catch(error => {
              console.error('Error updating team preference:', error);
            });
        },

        updateVenuePreference(venueId, data) {
          fetch(`{{ route('preferences.venues.update', '') }}/${venueId}`, {
              method: 'PUT',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
              },
              body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
              if (!data.success) {
                console.error('Failed to update venue preference');
              }
            })
            .catch(error => {
              console.error('Error updating venue preference:', error);
            });
        },

        updatePricePreference(priceId, data) {
          fetch(`{{ route('preferences.prices.update', '') }}/${priceId}`, {
              method: 'PUT',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
              },
              body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
              if (!data.success) {
                console.error('Failed to update price preference');
              }
            })
            .catch(error => {
              console.error('Error updating price preference:', error);
            });
        },

        removeTeam(teamId) {
          if (!confirm('Are you sure you want to remove this favorite team?')) {
            return;
          }

          fetch(`{{ route('preferences.teams.destroy', '') }}/${teamId}`, {
              method: 'DELETE',
              headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
              }
            })
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                this.favoriteTeams = this.favoriteTeams.filter(t => t.id !== teamId);
              } else {
                console.error('Failed to remove team');
              }
            })
            .catch(error => {
              console.error('Error removing team:', error);
            });
        },

        removeVenue(venueId) {
          if (!confirm('Are you sure you want to remove this favorite venue?')) {
            return;
          }

          fetch(`{{ route('preferences.venues.destroy', '') }}/${venueId}`, {
              method: 'DELETE',
              headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
              }
            })
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                this.favoriteVenues = this.favoriteVenues.filter(v => v.id !== venueId);
              } else {
                console.error('Failed to remove venue');
              }
            })
            .catch(error => {
              console.error('Error removing venue:', error);
            });
        },

        removePricePreference(priceId) {
          if (!confirm('Are you sure you want to remove this price preference?')) {
            return;
          }

          fetch(`{{ route('preferences.prices.destroy', '') }}/${priceId}`, {
              method: 'DELETE',
              headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
              }
            })
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                this.pricePreferences = this.pricePreferences.filter(p => p.id !== priceId);
              } else {
                console.error('Failed to remove price preference');
              }
            })
            .catch(error => {
              console.error('Error removing price preference:', error);
            });
        }
      }
    }

    // Team Modal Manager
    function teamModalManager() {
      return {
        newTeam: {
          sport_type: '',
          team_name: '',
          team_city: '',
          league: '',
          priority: 3,
          email_alerts: true,
          push_alerts: false
        },
        teamSearchResults: [],
        availableSports: {
          'football': 'Football',
          'basketball': 'Basketball',
          'baseball': 'Baseball',
          'hockey': 'Hockey',
          'soccer': 'Soccer',
          'tennis': 'Tennis',
          'golf': 'Golf',
          'other': 'Other'
        },

        searchTeams() {
          if (this.newTeam.team_name.length < 2) {
            this.teamSearchResults = [];
            return;
          }

          fetch(
              `{{ route('preferences.teams.search') }}?q=${encodeURIComponent(this.newTeam.team_name)}&sport=${this.newTeam.sport_type}`, {
                method: 'GET',
                headers: {
                  'Accept': 'application/json'
                }
              })
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                this.teamSearchResults = data.data || [];
              } else {
                this.teamSearchResults = [];
              }
            })
            .catch(error => {
              console.error('Error searching teams:', error);
              this.teamSearchResults = [];
            });
        },

        selectTeam(team) {
          this.newTeam.team_name = team.name;
          this.newTeam.team_city = team.city || '';
          this.newTeam.league = team.league || '';
          this.teamSearchResults = [];
        },

        addTeam() {
          fetch('{{ route('preferences.teams.store') }}', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
              },
              body: JSON.stringify(this.newTeam)
            })
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                // Reset form
                this.newTeam = {
                  sport_type: '',
                  team_name: '',
                  team_city: '',
                  league: '',
                  priority: 3,
                  email_alerts: true,
                  push_alerts: false
                };
                this.showAddTeamModal = false;
                // Reload sports data
                this.$parent.loadSportsData();
              } else {
                alert('Failed to add team: ' + (data.message || 'Unknown error'));
              }
            })
            .catch(error => {
              console.error('Error adding team:', error);
              alert('An error occurred while adding the team');
            });
        }
      }
    }

    // Venue Modal Manager
    function venueModalManager() {
      return {
        newVenue: {
          venue_name: '',
          city: '',
          state_province: '',
          country: 'United States',
          venue_type: 'stadium',
          priority: 3,
          email_alerts: true,
          push_alerts: false
        },
        venueSearchResults: [],

        searchVenues() {
          if (this.newVenue.venue_name.length < 2) {
            this.venueSearchResults = [];
            return;
          }

          fetch(`{{ route('preferences.venues.search') }}?q=${encodeURIComponent(this.newVenue.venue_name)}`, {
              method: 'GET',
              headers: {
                'Accept': 'application/json'
              }
            })
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                this.venueSearchResults = data.data || [];
              } else {
                this.venueSearchResults = [];
              }
            })
            .catch(error => {
              console.error('Error searching venues:', error);
              this.venueSearchResults = [];
            });
        },

        selectVenue(venue) {
          this.newVenue.venue_name = venue.name;
          this.newVenue.city = venue.city || '';
          this.newVenue.state_province = venue.state || '';
          this.newVenue.country = venue.country || 'United States';
          this.venueSearchResults = [];
        },

        addVenue() {
          fetch('{{ route('preferences.venues.store') }}', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
              },
              body: JSON.stringify(this.newVenue)
            })
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                // Reset form
                this.newVenue = {
                  venue_name: '',
                  city: '',
                  state_province: '',
                  country: 'United States',
                  venue_type: 'stadium',
                  priority: 3,
                  email_alerts: true,
                  push_alerts: false
                };
                this.showAddVenueModal = false;
                // Reload sports data
                this.$parent.loadSportsData();
              } else {
                alert('Failed to add venue: ' + (data.message || 'Unknown error'));
              }
            })
            .catch(error => {
              console.error('Error adding venue:', error);
              alert('An error occurred while adding the venue');
            });
        }
      }
    }

    // Price Modal Manager
    function priceModalManager() {
      return {
        newPrice: {
          preference_name: '',
          sport_type: '',
          event_category: '',
          min_price: null,
          max_price: null,
          preferred_quantity: 2,
          alert_frequency: 'immediate',
          price_drop_threshold: 10,
          preferred_section: '',
          email_alerts: true,
          push_alerts: false,
          auto_purchase_enabled: false
        },
        availableSports: {
          'football': 'Football',
          'basketball': 'Basketball',
          'baseball': 'Baseball',
          'hockey': 'Hockey',
          'soccer': 'Soccer',
          'tennis': 'Tennis',
          'golf': 'Golf',
          'other': 'Other'
        },

        addPricePreference() {
          fetch('{{ route('preferences.prices.store') }}', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
              },
              body: JSON.stringify(this.newPrice)
            })
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                // Reset form
                this.newPrice = {
                  preference_name: '',
                  sport_type: '',
                  event_category: '',
                  min_price: null,
                  max_price: null,
                  preferred_quantity: 2,
                  alert_frequency: 'immediate',
                  price_drop_threshold: 10,
                  preferred_section: '',
                  email_alerts: true,
                  push_alerts: false,
                  auto_purchase_enabled: false
                };
                this.showAddPriceModal = false;
                // Reload sports data
                this.$parent.loadSportsData();
              } else {
                alert('Failed to add price preference: ' + (data.message || 'Unknown error'));
              }
            })
            .catch(error => {
              console.error('Error adding price preference:', error);
              alert('An error occurred while adding the price preference');
            });
        }
      }
    }
  </script>

  <!-- CSS with timestamp to prevent caching -->
  <link rel="stylesheet" href="{{ asset('css/preferences.css') }}">

  <style>
    /* Additional inline styles for preferences */
    .peer:checked~.after\\:translate-x-full:after {
      --tw-translate-x: 100%;
      transform: translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y));
    }

    .transition-all {
      transition-duration: 200ms;
    }

    /* Loading state styles */
    @keyframes spin {
      from {
        transform: rotate(0deg);
      }

      to {
        transform: rotate(360deg);
      }
    }

    .animate-spin {
      animation: spin 1s linear infinite;
    }

    /* Custom focus styles */
    .focus\\:ring-blue-300:focus {
      --tw-ring-color: rgb(147 197 253);
    }

    .focus\\:ring-4:focus {
      --tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);
      --tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(4px + var(--tw-ring-offset-width)) var(--tw-ring-color);
      box-shadow: var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow, 0 0 #0000);
    }
  </style>
@endsection
