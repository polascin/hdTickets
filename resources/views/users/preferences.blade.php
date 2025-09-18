@extends('layouts.app-v2')

@section('title', 'User Preferences')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">User Preferences</h1>
                    <p class="text-gray-600 mt-2">Customize your experience and notification settings</p>
                </div>
                <div class="flex space-x-3">
                    <button type="button" id="exportPreferences" class="btn btn-outline-secondary">
                        <i class="fas fa-download mr-2"></i>Export
                    </button>
                    <button type="button" id="resetPreferences" class="btn btn-outline-danger">
                        <i class="fas fa-undo mr-2"></i>Reset
                    </button>
                </div>
            </div>
        </div>

        <!-- Status Messages -->
        <div id="statusMessages" class="mb-6"></div>

        <!-- Presets Section -->
        @if(isset($presets) && $presets->count() > 0)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Setup</h2>
            <p class="text-gray-600 mb-4">Choose a preset to quickly configure your preferences:</p>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach($presets as $preset)
                <div class="preset-card border border-gray-200 rounded-lg p-4 hover:border-blue-300 cursor-pointer transition-colors"
                     data-preset-id="{{ $preset->id }}">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h3 class="font-medium text-gray-900">{{ $preset->name }}</h3>
                            <p class="text-sm text-gray-600 mt-1">{{ $preset->description }}</p>
                            @if($preset->is_system_preset)
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mt-2">
                                    System Preset
                                </span>
                            @endif
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary apply-preset" data-preset-id="{{ $preset->id }}">
                            Apply
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Preferences Form -->
        <form id="preferencesForm" class="space-y-6">
            @csrf

            <!-- Notification Settings -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-bell mr-2 text-blue-500"></i>
                        Notification Settings
                    </h2>
                    <p class="text-gray-600 mt-1">Configure how you receive notifications</p>
                </div>
                
                <div class="p-6 space-y-6">
                    <!-- Email Notifications -->
                    <div>
                        <h3 class="text-md font-medium text-gray-900 mb-3">Email Notifications</h3>
                        <div class="space-y-3">
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       name="notifications[email_enabled]" 
                                       value="1"
                                       data-category="notifications"
                                       data-key="email_enabled"
                                       data-type="boolean"
                                       class="preference-input rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                       {{ ($currentPreferences['notifications']['email_enabled'] ?? true) ? 'checked' : '' }}>
                                <span class="ml-2">Enable email notifications</span>
                            </label>
                            
                            <div class="ml-6 space-y-2" id="emailSettings">
                                <label class="flex items-center">
                                    <input type="checkbox" 
                                           name="notifications[email_ticket_assigned]" 
                                           value="1"
                                           data-category="notifications"
                                           data-key="email_ticket_assigned"
                                           data-type="boolean"
                                           class="preference-input rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                           {{ ($currentPreferences['notifications']['email_ticket_assigned'] ?? true) ? 'checked' : '' }}>
                                    <span class="ml-2">When a ticket is assigned to me</span>
                                </label>
                                
                                <label class="flex items-center">
                                    <input type="checkbox" 
                                           name="notifications[email_ticket_updated]" 
                                           value="1"
                                           data-category="notifications"
                                           data-key="email_ticket_updated"
                                           data-type="boolean"
                                           class="preference-input rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                           {{ ($currentPreferences['notifications']['email_ticket_updated'] ?? true) ? 'checked' : '' }}>
                                    <span class="ml-2">When a ticket is updated</span>
                                </label>
                                
                                <label class="flex items-center">
                                    <input type="checkbox" 
                                           name="notifications[email_ticket_closed]" 
                                           value="1"
                                           data-category="notifications"
                                           data-key="email_ticket_closed"
                                           data-type="boolean"
                                           class="preference-input rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                           {{ ($currentPreferences['notifications']['email_ticket_closed'] ?? false) ? 'checked' : '' }}>
                                    <span class="ml-2">When a ticket is closed</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Push Notifications -->
                    <div>
                        <h3 class="text-md font-medium text-gray-900 mb-3">Push Notifications</h3>
                        <div class="space-y-3">
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       name="notifications[push_enabled]" 
                                       value="1"
                                       data-category="notifications"
                                       data-key="push_enabled"
                                       data-type="boolean"
                                       class="preference-input rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                       {{ ($currentPreferences['notifications']['push_enabled'] ?? true) ? 'checked' : '' }}>
                                <span class="ml-2">Enable push notifications</span>
                            </label>
                            
                            <div class="ml-6 space-y-2" id="pushSettings">
                                <label class="flex items-center">
                                    <input type="checkbox" 
                                           name="notifications[push_ticket_assigned]" 
                                           value="1"
                                           data-category="notifications"
                                           data-key="push_ticket_assigned"
                                           data-type="boolean"
                                           class="preference-input rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                           {{ ($currentPreferences['notifications']['push_ticket_assigned'] ?? true) ? 'checked' : '' }}>
                                    <span class="ml-2">When a ticket is assigned to me</span>
                                </label>
                                
                                <label class="flex items-center">
                                    <input type="checkbox" 
                                           name="notifications[push_ticket_updated]" 
                                           value="1"
                                           data-category="notifications"
                                           data-key="push_ticket_updated"
                                           data-type="boolean"
                                           class="preference-input rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                           {{ ($currentPreferences['notifications']['push_ticket_updated'] ?? false) ? 'checked' : '' }}>
                                    <span class="ml-2">When a ticket is updated</span>
                                </label>
                                
                                <label class="flex items-center">
                                    <input type="checkbox" 
                                           name="notifications[push_ticket_closed]" 
                                           value="1"
                                           data-category="notifications"
                                           data-key="push_ticket_closed"
                                           data-type="boolean"
                                           class="preference-input rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                           {{ ($currentPreferences['notifications']['push_ticket_closed'] ?? false) ? 'checked' : '' }}>
                                    <span class="ml-2">When a ticket is closed</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- SMS Notifications -->
                    <div>
                        <h3 class="text-md font-medium text-gray-900 mb-3">SMS Notifications</h3>
                        <div class="space-y-3">
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       name="notifications[sms_enabled]" 
                                       value="1"
                                       data-category="notifications"
                                       data-key="sms_enabled"
                                       data-type="boolean"
                                       class="preference-input rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                       {{ ($currentPreferences['notifications']['sms_enabled'] ?? false) ? 'checked' : '' }}>
                                <span class="ml-2">Enable SMS notifications</span>
                            </label>
                            
                            <div class="ml-6">
                                <label class="flex items-center">
                                    <input type="checkbox" 
                                           name="notifications[sms_urgent_only]" 
                                           value="1"
                                           data-category="notifications"
                                           data-key="sms_urgent_only"
                                           data-type="boolean"
                                           class="preference-input rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                           {{ ($currentPreferences['notifications']['sms_urgent_only'] ?? true) ? 'checked' : '' }}>
                                    <span class="ml-2">Only for urgent tickets</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Display Preferences -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-palette mr-2 text-purple-500"></i>
                        Display Preferences
                    </h2>
                    <p class="text-gray-600 mt-1">Customize the look and feel of your interface</p>
                </div>
                
                <div class="p-6 space-y-6">
                    <!-- Theme Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Theme</label>
                        <div class="grid grid-cols-3 gap-4">
                            @foreach(['light' => 'Light', 'dark' => 'Dark', 'auto' => 'Auto'] as $value => $label)
                            <label class="flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:border-blue-300 transition-colors">
                                <input type="radio" 
                                       name="display[theme]" 
                                       value="{{ $value }}"
                                       data-category="display"
                                       data-key="theme"
                                       data-type="string"
                                       class="preference-input text-blue-600 focus:ring-blue-500"
                                       {{ ($currentPreferences['display']['theme'] ?? 'light') === $value ? 'checked' : '' }}>
                                <span class="ml-2">{{ $label }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Display Density -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Display Density</label>
                        <div class="grid grid-cols-3 gap-4">
                            @foreach(['compact' => 'Compact', 'comfortable' => 'Comfortable', 'spacious' => 'Spacious'] as $value => $label)
                            <label class="flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:border-blue-300 transition-colors">
                                <input type="radio" 
                                       name="display[density]" 
                                       value="{{ $value }}"
                                       data-category="display"
                                       data-key="density"
                                       data-type="string"
                                       class="preference-input text-blue-600 focus:ring-blue-500"
                                       {{ ($currentPreferences['display']['density'] ?? 'comfortable') === $value ? 'checked' : '' }}>
                                <span class="ml-2">{{ $label }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Language -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Language</label>
                        <select name="display[language]" 
                                data-category="display"
                                data-key="language"
                                data-type="string"
                                class="preference-input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @foreach($languages as $code => $name)
                                <option value="{{ $code }}" {{ ($currentPreferences['display']['language'] ?? 'en') === $code ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Timezone -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Timezone
                            <button type="button" id="detectTimezone" class="ml-2 text-sm text-blue-600 hover:text-blue-800">
                                (Auto-detect)
                            </button>
                        </label>
                        <select name="display[timezone]" 
                                data-category="display"
                                data-key="timezone"
                                data-type="string"
                                class="preference-input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @foreach($timezones as $region => $zoneList)
                                <optgroup label="{{ $region }}">
                                    @foreach($zoneList as $timezone => $displayName)
                                        <option value="{{ $timezone }}" {{ ($currentPreferences['display']['timezone'] ?? 'UTC') === $timezone ? 'selected' : '' }}>
                                            {{ $displayName }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>

                    <!-- Date/Time Format -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Date Format</label>
                            <select name="display[date_format]" 
                                    data-category="display"
                                    data-key="date_format"
                                    data-type="string"
                                    class="preference-input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                @foreach(['Y-m-d' => '2024-01-15', 'm/d/Y' => '01/15/2024', 'd/m/Y' => '15/01/2024', 'd-M-Y' => '15-Jan-2024'] as $format => $example)
                                    <option value="{{ $format }}" {{ ($currentPreferences['display']['date_format'] ?? 'Y-m-d') === $format ? 'selected' : '' }}>
                                        {{ $example }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Time Format</label>
                            <select name="display[time_format]" 
                                    data-category="display"
                                    data-key="time_format"
                                    data-type="string"
                                    class="preference-input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                @foreach(['H:i' => '24-hour (14:30)', 'g:i A' => '12-hour (2:30 PM)'] as $format => $example)
                                    <option value="{{ $format }}" {{ ($currentPreferences['display']['time_format'] ?? 'H:i') === $format ? 'selected' : '' }}>
                                        {{ $example }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Items per page -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Items per page</label>
                        <select name="display[items_per_page]" 
                                data-category="display"
                                data-key="items_per_page"
                                data-type="integer"
                                class="preference-input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @foreach([10, 25, 50, 100] as $value)
                                <option value="{{ $value }}" {{ ($currentPreferences['display']['items_per_page'] ?? 25) == $value ? 'selected' : '' }}>
                                    {{ $value }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Dashboard Preferences -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-tachometer-alt mr-2 text-green-500"></i>
                        Dashboard Preferences
                    </h2>
                    <p class="text-gray-600 mt-1">Configure your dashboard behavior and widgets</p>
                </div>
                
                <div class="p-6 space-y-6">
                    <!-- Auto Refresh -->
                    <div class="flex items-center justify-between">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Auto Refresh</label>
                            <p class="text-sm text-gray-500">Automatically refresh dashboard data</p>
                        </div>
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   name="dashboard[auto_refresh]" 
                                   value="1"
                                   data-category="dashboard"
                                   data-key="auto_refresh"
                                   data-type="boolean"
                                   class="preference-input rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                   {{ ($currentPreferences['dashboard']['auto_refresh'] ?? true) ? 'checked' : '' }}>
                        </label>
                    </div>

                    <!-- Refresh Interval -->
                    <div class="refresh-interval-setting" style="{{ ($currentPreferences['dashboard']['auto_refresh'] ?? true) ? '' : 'display: none;' }}">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Refresh Interval (seconds)</label>
                        <select name="dashboard[refresh_interval]" 
                                data-category="dashboard"
                                data-key="refresh_interval"
                                data-type="integer"
                                class="preference-input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @foreach([15, 30, 60, 120, 300] as $value)
                                <option value="{{ $value }}" {{ ($currentPreferences['dashboard']['refresh_interval'] ?? 30) == $value ? 'selected' : '' }}>
                                    {{ $value }} seconds
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Dashboard Widgets -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">Dashboard Widgets</label>
                        <div class="space-y-2">
                            @foreach(['show_stats_widgets' => 'Statistics Widgets', 'show_recent_tickets' => 'Recent Tickets', 'show_assigned_tickets' => 'Assigned Tickets'] as $key => $label)
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       name="dashboard[{{ $key }}]" 
                                       value="1"
                                       data-category="dashboard"
                                       data-key="{{ $key }}"
                                       data-type="boolean"
                                       class="preference-input rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                       {{ ($currentPreferences['dashboard'][$key] ?? true) ? 'checked' : '' }}>
                                <span class="ml-2">{{ $label }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Compact View -->
                    <div class="flex items-center justify-between">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Compact View</label>
                            <p class="text-sm text-gray-500">Use a more compact layout for dashboard widgets</p>
                        </div>
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   name="dashboard[compact_view]" 
                                   value="1"
                                   data-category="dashboard"
                                   data-key="compact_view"
                                   data-type="boolean"
                                   class="preference-input rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                   {{ ($currentPreferences['dashboard']['compact_view'] ?? false) ? 'checked' : '' }}>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Ticket Preferences -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-ticket-alt mr-2 text-orange-500"></i>
                        Ticket Preferences
                    </h2>
                    <p class="text-gray-600 mt-1">Configure your ticket handling preferences</p>
                </div>
                
                <div class="p-6 space-y-6">
                    <!-- Auto Assign -->
                    <div class="flex items-center justify-between">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Auto Assign to Self</label>
                            <p class="text-sm text-gray-500">Automatically assign tickets to yourself when you create them</p>
                        </div>
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   name="tickets[auto_assign_to_self]" 
                                   value="1"
                                   data-category="tickets"
                                   data-key="auto_assign_to_self"
                                   data-type="boolean"
                                   class="preference-input rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                   {{ ($currentPreferences['tickets']['auto_assign_to_self'] ?? false) ? 'checked' : '' }}>
                        </label>
                    </div>

                    <!-- Show Closed Tickets -->
                    <div class="flex items-center justify-between">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Show Closed Tickets</label>
                            <p class="text-sm text-gray-500">Include closed tickets in default ticket list views</p>
                        </div>
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   name="tickets[show_closed_tickets]" 
                                   value="1"
                                   data-category="tickets"
                                   data-key="show_closed_tickets"
                                   data-type="boolean"
                                   class="preference-input rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                   {{ ($currentPreferences['tickets']['show_closed_tickets'] ?? false) ? 'checked' : '' }}>
                        </label>
                    </div>

                    <!-- Default Priority -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Default Priority for New Tickets</label>
                        <select name="tickets[default_priority]" 
                                data-category="tickets"
                                data-key="default_priority"
                                data-type="string"
                                class="preference-input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @foreach(['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'urgent' => 'Urgent'] as $value => $label)
                                <option value="{{ $value }}" {{ ($currentPreferences['tickets']['default_priority'] ?? 'medium') === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Quick Actions -->
                    <div class="flex items-center justify-between">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Enable Quick Actions</label>
                            <p class="text-sm text-gray-500">Show quick action buttons on ticket cards</p>
                        </div>
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   name="tickets[enable_quick_actions]" 
                                   value="1"
                                   data-category="tickets"
                                   data-key="enable_quick_actions"
                                   data-type="boolean"
                                   class="preference-input rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                   {{ ($currentPreferences['tickets']['enable_quick_actions'] ?? true) ? 'checked' : '' }}>
                        </label>
                    </div>

                    <!-- Internal Notes -->
                    <div class="flex items-center justify-between">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Show Internal Notes</label>
                            <p class="text-sm text-gray-500">Display internal notes section by default</p>
                        </div>
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   name="tickets[show_internal_notes]" 
                                   value="1"
                                   data-category="tickets"
                                   data-key="show_internal_notes"
                                   data-type="boolean"
                                   class="preference-input rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                   {{ ($currentPreferences['tickets']['show_internal_notes'] ?? true) ? 'checked' : '' }}>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Save Button -->
            <div class="flex justify-end space-x-3">
                <button type="button" id="saveAllPreferences" class="btn btn-primary">
                    <i class="fas fa-save mr-2"></i>
                    Save All Preferences
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Include the UserPreferencesManager JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Initialize preferences manager
    if (typeof UserPreferencesManager !== 'undefined') {
        const preferencesManager = new UserPreferencesManager();
    } else {
        console.warn('UserPreferencesManager not loaded, falling back to basic functionality');
        
        // Basic fallback functionality
        document.querySelectorAll('.preference-input').forEach(input => {
            input.addEventListener('change', function() {
                // Debounced save
                clearTimeout(this.saveTimeout);
                this.saveTimeout = setTimeout(() => {
                    savePreference(this);
                }, 500);
            });
        });
        
        // Save single preference function
        function savePreference(input) {
            const category = input.dataset.category;
            const key = input.dataset.key;
            const dataType = input.dataset.type || 'string';
            let value = input.value;
            
            // Handle different input types
            if (input.type === 'checkbox') {
                value = input.checked;
            } else if (input.type === 'radio' && !input.checked) {
                return; // Skip unchecked radio buttons
            }
            
            // Convert value based on data type
            if (dataType === 'boolean') {
                value = Boolean(value);
            } else if (dataType === 'integer') {
                value = parseInt(value) || 0;
            }
            
            // Send AJAX request
            fetch('/user/preferences/update-single', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    category: category,
                    key: key,
                    value: value,
                    data_type: dataType
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage('Preference saved successfully', 'success');
                } else {
                    showMessage('Error saving preference: ' + (data.message || 'Unknown error'), 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('Error saving preference', 'error');
            });
        }
        
        // Show message function
        function showMessage(message, type) {
            const messagesContainer = document.getElementById('statusMessages');
            const messageElement = document.createElement('div');
            messageElement.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
            messageElement.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            messagesContainer.appendChild(messageElement);
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                messageElement.remove();
            }, 5000);
        }
    }
});
</script>
@endpush
