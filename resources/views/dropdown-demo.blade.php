@extends('layouts.modern')
@section('title', 'Dropdown Components Demo')

@section('content')
  <div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
      <header class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Enhanced Dropdown Components</h1>
        <p class="text-lg text-gray-600 mt-2">Comprehensive dropdown and select component demonstrations</p>
      </header>

      <!-- Basic Enhanced Dropdown -->
      <section class="mb-12">
        <h2 class="text-2xl font-semibold text-gray-900 mb-4">Basic Enhanced Dropdown</h2>
        <div class="bg-white p-6 rounded-lg shadow border">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Standard Dropdown -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Standard Dropdown</label>
              <x-enhanced-dropdown width="w-64">
                <x-dropdown-item value="option1">
                  <x-slot:icon>
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                  </x-slot:icon>
                  First Option
                  <x-slot:description>This is a detailed description</x-slot:description>
                </x-dropdown-item>
                <x-dropdown-item value="option2">Second Option</x-dropdown-item>
                <x-dropdown-item divider="true" />
                <x-dropdown-item value="option3" dangerous="true">
                  <x-slot:icon>
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                      </path>
                    </svg>
                  </x-slot:icon>
                  Delete Item
                </x-dropdown-item>
              </x-enhanced-dropdown>
            </div>

            <!-- Searchable Dropdown -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Searchable Dropdown</label>
              <x-enhanced-dropdown searchable="true" placeholder="Search and select..." width="w-64">
                <x-dropdown-item value="apple">Apple</x-dropdown-item>
                <x-dropdown-item value="banana">Banana</x-dropdown-item>
                <x-dropdown-item value="cherry">Cherry</x-dropdown-item>
                <x-dropdown-item value="date">Date</x-dropdown-item>
                <x-dropdown-item value="elderberry">Elderberry</x-dropdown-item>
                <x-dropdown-item value="fig">Fig</x-dropdown-item>
                <x-dropdown-item value="grape">Grape</x-dropdown-item>
              </x-enhanced-dropdown>
            </div>
          </div>
        </div>
      </section>

      <!-- Multi-Select Component -->
      <section class="mb-12">
        <h2 class="text-2xl font-semibold text-gray-900 mb-4">Multi-Select Component</h2>
        <div class="bg-white p-6 rounded-lg shadow border">
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Basic Multi-Select -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Select Multiple Sports</label>
              <x-multi-select name="sports" placeholder="Choose your favorite sports..." :options="[
                  ['value' => 'football', 'text' => 'Football'],
                  ['value' => 'basketball', 'text' => 'Basketball'],
                  ['value' => 'baseball', 'text' => 'Baseball'],
                  ['value' => 'soccer', 'text' => 'Soccer'],
                  ['value' => 'tennis', 'text' => 'Tennis'],
                  ['value' => 'golf', 'text' => 'Golf'],
                  ['value' => 'hockey', 'text' => 'Hockey'],
                  ['value' => 'volleyball', 'text' => 'Volleyball'],
              ]"
                :selected="['football', 'basketball']" />
            </div>

            <!-- Limited Multi-Select -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Select Up to 3 Categories</label>
              <x-multi-select name="categories" placeholder="Choose categories (max 3)..." :maxSelections="3"
                :options="[
                    'technology',
                    'sports',
                    'entertainment',
                    'business',
                    'health',
                    'travel',
                    'food',
                    'fashion',
                    'education',
                ]" />
            </div>
          </div>
        </div>
      </section>

      <!-- Form Integration Examples -->
      <section class="mb-12">
        <h2 class="text-2xl font-semibold text-gray-900 mb-4">Form Integration</h2>
        <div class="bg-white p-6 rounded-lg shadow border">
          <form class="space-y-6">
            <!-- User Role Selection -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">User Role</label>
              <x-enhanced-dropdown placeholder="Select user role..." width="w-full">
                <x-dropdown-item value="admin" active="true">
                  <x-slot:icon>
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                      </path>
                    </svg>
                  </x-slot:icon>
                  Administrator
                  <x-slot:description>Full system access and management</x-slot:description>
                </x-dropdown-item>
                <x-dropdown-item value="agent">
                  <x-slot:icon>
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                  </x-slot:icon>
                  Agent
                  <x-slot:description>Moderate ticket access and user support</x-slot:description>
                </x-dropdown-item>
                <x-dropdown-item value="customer">
                  <x-slot:icon>
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z">
                      </path>
                    </svg>
                  </x-slot:icon>
                  Customer
                  <x-slot:description>Standard user with basic access</x-slot:description>
                </x-dropdown-item>
              </x-enhanced-dropdown>
            </div>

            <!-- Preferences Multi-Select -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Notification Preferences</label>
              <x-multi-select name="notifications" placeholder="Choose notification types..." :options="[
                  ['value' => 'email', 'text' => 'Email Notifications'],
                  ['value' => 'sms', 'text' => 'SMS Alerts'],
                  ['value' => 'push', 'text' => 'Push Notifications'],
                  ['value' => 'slack', 'text' => 'Slack Integration'],
                  ['value' => 'webhook', 'text' => 'Webhook Callbacks'],
              ]" />
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end space-x-4 pt-4">
              <button type="button"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                Cancel
              </button>
              <button type="submit"
                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                Save Settings
              </button>
            </div>
          </form>
        </div>
      </section>

      <!-- Error and Loading States -->
      <section class="mb-12">
        <h2 class="text-2xl font-semibold text-gray-900 mb-4">States & Validation</h2>
        <div class="bg-white p-6 rounded-lg shadow border">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Loading State -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Loading State</label>
              <x-enhanced-dropdown loading="true" placeholder="Loading options...">
              </x-enhanced-dropdown>
            </div>

            <!-- Error State -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Error State</label>
              <x-enhanced-dropdown error="true" errorMessage="Please select a valid option"
                placeholder="Select option...">
                <x-dropdown-item value="option1">Option 1</x-dropdown-item>
                <x-dropdown-item value="option2">Option 2</x-dropdown-item>
              </x-enhanced-dropdown>
            </div>

            <!-- Disabled State -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Disabled Multi-Select</label>
              <x-multi-select name="disabled" placeholder="This is disabled..." disabled="true" :options="['Option A', 'Option B', 'Option C']" />
            </div>
          </div>
        </div>
      </section>

      <!-- Accessibility Features -->
      <section class="mb-12">
        <h2 class="text-2xl font-semibold text-gray-900 mb-4">Accessibility Features</h2>
        <div class="bg-white p-6 rounded-lg shadow border">
          <div class="prose max-w-none">
            <h3>Keyboard Navigation</h3>
            <ul>
              <li><kbd>Tab</kbd> / <kbd>Shift+Tab</kbd> - Navigate between dropdowns</li>
              <li><kbd>Enter</kbd> / <kbd>Space</kbd> - Open/close dropdown</li>
              <li><kbd>Arrow Up</kbd> / <kbd>Arrow Down</kbd> - Navigate menu items</li>
              <li><kbd>Escape</kbd> - Close dropdown</li>
              <li><kbd>Type to search</kbd> - In searchable dropdowns</li>
            </ul>

            <h3>Screen Reader Support</h3>
            <ul>
              <li>Proper ARIA labels and roles</li>
              <li>Live region announcements for state changes</li>
              <li>Descriptive labels and instructions</li>
              <li>Focus management and restoration</li>
            </ul>

            <h3>Touch & Mobile Optimizations</h3>
            <ul>
              <li>44px minimum touch targets</li>
              <li>Responsive positioning and sizing</li>
              <li>Swipe gesture support</li>
              <li>Auto-zoom prevention on iOS</li>
            </ul>
          </div>
        </div>
      </section>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Event listeners for dropdown interactions
      document.addEventListener('dropdown-selected', function(event) {
        console.log('Dropdown selection:', event.detail);
      });

      document.addEventListener('multiselect-changed', function(event) {
        console.log('Multi-select changed:', event.detail);
      });

      // Form submission handler
      const forms = document.querySelectorAll('form');
      forms.forEach(form => {
        form.addEventListener('submit', function(event) {
          event.preventDefault();
          console.log('Form data would be submitted:', new FormData(form));
          alert('Form submission simulated! Check console for data.');
        });
      });
    });
  </script>

@endsection
