@extends('layouts.app')

@section('title', 'Smart Alerts - HD Tickets')

@section('content')
  <div class="smart-alerts-dashboard" x-data="smartAlerts()" x-init="initializeAlerts()">
    <!-- Header Section -->
    <div class="bg-white border-b border-gray-200 px-4 py-6 sm:px-6">
      <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900 flex items-center">
              <svg class="w-8 h-8 text-blue-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                  d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                  clip-rule="evenodd"></path>
              </svg>
              Smart Alerts
            </h1>
            <p class="mt-1 text-sm text-gray-500">
              Intelligent ticket monitoring with multi-channel notifications
            </p>
          </div>

          <div class="flex items-center space-x-4">
            <!-- Templates dropdown -->
            <div class="relative" x-data="{ open: false }">
              <button @click="open = !open"
                class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                  </path>
                </svg>
                Templates
              </button>

              <div x-show="open" @click.away="open = false"
                class="absolute right-0 mt-2 w-64 bg-white rounded-md shadow-lg z-10">
                <div class="py-1">
                  <template x-for="template in templates" :key="template.name">
                    <button @click="useTemplate(template); open = false"
                      class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                      <div class="font-medium" x-text="template.name"></div>
                      <div class="text-xs text-gray-500" x-text="template.description"></div>
                    </button>
                  </template>
                </div>
              </div>
            </div>

            <!-- Create alert button -->
            <button @click="showCreateModal = true"
              class="inline-flex items-center px-4 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6">
                </path>
              </svg>
              Create Alert
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Stats Overview -->
    <div class="bg-gray-50 px-4 py-6 sm:px-6">
      <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <!-- Total Alerts -->
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd"
                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                        clip-rule="evenodd"></path>
                    </svg>
                  </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Total Alerts</dt>
                    <dd class="text-lg font-medium text-gray-900">{{ $stats['total_alerts'] }}</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <!-- Active Alerts -->
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd"></path>
                    </svg>
                  </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Active Alerts</dt>
                    <dd class="text-lg font-medium text-gray-900">{{ $stats['active_alerts'] }}</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <!-- Triggered Today -->
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                        clip-rule="evenodd"></path>
                    </svg>
                  </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Triggered Today</dt>
                    <dd class="text-lg font-medium text-gray-900">{{ $stats['triggered_today'] }}</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <!-- This Month -->
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd"
                        d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"
                        clip-rule="evenodd"></path>
                    </svg>
                  </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">This Month</dt>
                    <dd class="text-lg font-medium text-gray-900">{{ $stats['alerts_this_month'] }}</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

      <!-- Alerts List -->
      <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
          <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Your Smart Alerts</h3>

          <!-- Loading state -->
          <div x-show="loading" class="text-center py-8">
            <div
              class="inline-flex items-center px-4 py-2 font-semibold leading-6 text-sm shadow rounded-md text-gray-500 bg-white">
              <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg"
                fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                  stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
              </svg>
              Loading alerts...
            </div>
          </div>

          <!-- Alerts grid -->
          <div x-show="!loading" class="space-y-4">
            @foreach ($alerts as $alert)
              <div class="border border-gray-200 rounded-lg p-6 hover:shadow-lg transition-shadow duration-200"
                x-data="{ expanded: false }">
                <div class="flex items-start justify-between">
                  <div class="flex-1">
                    <div class="flex items-center">
                      <h4 class="text-lg font-semibold text-gray-900">{{ $alert->name }}</h4>
                      <span
                        class="ml-3 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize"
                        class="{{ $alert->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                        {{ $alert->is_active ? 'Active' : 'Inactive' }}
                      </span>
                      <span
                        class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize"
                        class="{{ $alert->priority === 'urgent'
                            ? 'bg-red-100 text-red-800'
                            : ($alert->priority === 'high'
                                ? 'bg-yellow-100 text-yellow-800'
                                : ($alert->priority === 'medium'
                                    ? 'bg-blue-100 text-blue-800'
                                    : 'bg-gray-100 text-gray-800')) }}">
                        {{ $alert->getPriorityLabel() }}
                      </span>
                    </div>

                    @if ($alert->description)
                      <p class="mt-2 text-sm text-gray-600">{{ $alert->description }}</p>
                    @endif

                    <div class="mt-3 flex items-center text-sm text-gray-500 space-x-6">
                      <span class="flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                          <path fill-rule="evenodd"
                            d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"
                            clip-rule="evenodd"></path>
                        </svg>
                        {{ $alert->getAlertTypeLabel() }}
                      </span>
                      <span class="flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                          <path fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                            clip-rule="evenodd"></path>
                        </svg>
                        {{ $alert->trigger_count }} triggers
                      </span>
                      <span class="flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                          <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                            clip-rule="evenodd"></path>
                        </svg>
                        @if ($alert->last_triggered_at)
                          Last: {{ $alert->last_triggered_at->diffForHumans() }}
                        @else
                          Never triggered
                        @endif
                      </span>
                    </div>

                    <div class="mt-3 flex items-center space-x-2">
                      @foreach ($alert->getNotificationChannelsLabels() as $channel)
                        <span
                          class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800">
                          {{ $channel }}
                        </span>
                      @endforeach
                    </div>
                  </div>

                  <div class="flex items-center space-x-2 ml-4">
                    <!-- Toggle button -->
                    <button @click="toggleAlert({{ $alert->id }})"
                      class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                      {{ $alert->is_active ? 'Disable' : 'Enable' }}
                    </button>

                    <!-- Edit button -->
                    <button @click="editAlert({{ $alert->id }})"
                      class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                      Edit
                    </button>

                    <!-- Delete button -->
                    <button @click="deleteAlert({{ $alert->id }})"
                      class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                      Delete
                    </button>
                  </div>
                </div>
              </div>
            @endforeach

            <!-- Empty state -->
            @if ($alerts->isEmpty())
              <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 17h5l-5 5v-5zM4 19h1a3 3 0 003-3V8a3 3 0 00-3-3H4a1 1 0 00-1 1v12a1 1 0 001 1z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No smart alerts yet</h3>
                <p class="mt-1 text-sm text-gray-500">Get started by creating your first intelligent ticket alert.</p>
                <div class="mt-6">
                  <button @click="showCreateModal = true"
                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Create Smart Alert
                  </button>
                </div>
              </div>
            @endif
          </div>

          <!-- Pagination -->
          @if ($alerts->hasPages())
            <div class="mt-6">
              {{ $alerts->links() }}
            </div>
          @endif
        </div>
      </div>
    </div>

    <!-- Create Alert Modal -->
    <div x-show="showCreateModal" x-cloak
      class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">Create Smart Alert</h3>
            <button @click="showCreateModal = false" class="text-gray-400 hover:text-gray-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>

          <form @submit.prevent="createAlert()" class="space-y-4">
            <!-- Alert Name -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Alert Name</label>
              <input type="text" x-model="newAlert.name" required
                class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
            </div>

            <!-- Alert Type -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Alert Type</label>
              <select x-model="newAlert.alert_type" required
                class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                <option value="">Select alert type</option>
                <option value="price_drop">Price Drop</option>
                <option value="availability">New Availability</option>
                <option value="instant_deal">Instant Deal</option>
                <option value="price_comparison">Price Comparison</option>
                <option value="venue_alert">Venue Alert</option>
                <option value="league_alert">League Alert</option>
                <option value="keyword_alert">Keyword Alert</option>
              </select>
            </div>

            <!-- Notification Channels -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Notification Channels</label>
              <div class="space-y-2">
                <label class="flex items-center">
                  <input type="checkbox" value="email" x-model="newAlert.notification_channels"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                  <span class="ml-2 text-sm text-gray-700">Email</span>
                </label>
                <label class="flex items-center">
                  <input type="checkbox" value="push" x-model="newAlert.notification_channels"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                  <span class="ml-2 text-sm text-gray-700">Push Notifications</span>
                </label>
                <label class="flex items-center">
                  <input type="checkbox" value="sms" x-model="newAlert.notification_channels"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                  <span class="ml-2 text-sm text-gray-700">SMS</span>
                </label>
              </div>
            </div>

            <!-- Priority -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Priority</label>
              <select x-model="newAlert.priority"
                class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
                <option value="urgent">Urgent</option>
              </select>
            </div>

            <!-- Submit buttons -->
            <div class="flex justify-end space-x-3 pt-4">
              <button type="button" @click="showCreateModal = false"
                class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Cancel
              </button>
              <button type="submit" :disabled="creating"
                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50">
                <span x-show="!creating">Create Alert</span>
                <span x-show="creating">Creating...</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script>
    function smartAlerts() {
      return {
        loading: false,
        creating: false,
        showCreateModal: false,
        templates: [],
        newAlert: {
          name: '',
          description: '',
          alert_type: '',
          trigger_conditions: {},
          notification_channels: ['email'],
          priority: 'medium',
          cooldown_minutes: 30,
          max_triggers_per_day: 10
        },

        async initializeAlerts() {
          await this.loadTemplates();
        },

        async loadTemplates() {
          try {
            const response = await fetch('/live-monitoring/alerts/templates');
            const data = await response.json();

            if (data.success) {
              this.templates = data.templates;
            }
          } catch (error) {
            console.error('Failed to load templates:', error);
          }
        },

        useTemplate(template) {
          this.newAlert = {
            ...template
          };
          this.showCreateModal = true;
        },

        async createAlert() {
          this.creating = true;

          try {
            const response = await fetch('/live-monitoring/alerts', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
              },
              body: JSON.stringify(this.newAlert)
            });

            const data = await response.json();

            if (data.success) {
              this.showCreateModal = false;
              this.resetNewAlert();
              location.reload(); // Refresh the page to show the new alert
            } else {
              alert('Failed to create alert: ' + data.message);
            }
          } catch (error) {
            console.error('Failed to create alert:', error);
            alert('Failed to create alert. Please try again.');
          } finally {
            this.creating = false;
          }
        },

        async toggleAlert(alertId) {
          try {
            const response = await fetch(`/live-monitoring/alerts/${alertId}/toggle`, {
              method: 'POST',
              headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
              }
            });

            const data = await response.json();

            if (data.success) {
              location.reload(); // Refresh to show updated status
            } else {
              alert('Failed to toggle alert: ' + data.message);
            }
          } catch (error) {
            console.error('Failed to toggle alert:', error);
            alert('Failed to toggle alert. Please try again.');
          }
        },

        async deleteAlert(alertId) {
          if (!confirm('Are you sure you want to delete this alert?')) {
            return;
          }

          try {
            const response = await fetch(`/live-monitoring/alerts/${alertId}`, {
              method: 'DELETE',
              headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
              }
            });

            const data = await response.json();

            if (data.success) {
              location.reload(); // Refresh to remove the deleted alert
            } else {
              alert('Failed to delete alert: ' + data.message);
            }
          } catch (error) {
            console.error('Failed to delete alert:', error);
            alert('Failed to delete alert. Please try again.');
          }
        },

        editAlert(alertId) {
          // For now, just redirect to a separate edit page
          // In a more complex implementation, you could show an edit modal
          window.location.href = `/live-monitoring/alerts/${alertId}/edit`;
        },

        resetNewAlert() {
          this.newAlert = {
            name: '',
            description: '',
            alert_type: '',
            trigger_conditions: {},
            notification_channels: ['email'],
            priority: 'medium',
            cooldown_minutes: 30,
            max_triggers_per_day: 10
          };
        }
      }
    }
  </script>
@endsection
