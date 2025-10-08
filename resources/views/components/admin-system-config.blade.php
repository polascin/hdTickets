{{-- Admin System Configuration Interface --}}
{{-- Comprehensive system settings management for scraping sources, APIs, email templates, and platform configuration --}}

<div x-data="adminSystemConfig()" x-init="init()" class="admin-system-config">
    {{-- Header --}}
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                    <svg class="w-7 h-7 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"></path>
                    </svg>
                    System Configuration
                </h1>
                <p class="text-gray-600 mt-1">Manage platform settings, API configurations, and integrations</p>
            </div>
            <div class="flex items-center gap-3">
                <button 
                    @click="saveAllSettings()"
                    :disabled="isSaving"
                    class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 disabled:opacity-50 flex items-center gap-2"
                >
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M7.707 10.293a1 1 0 10-1.414 1.414l2 2a1 1 0 001.414 0l4-4a1 1 0 00-1.414-1.414L9 11.586l-1.293-1.293z"></path>
                    </svg>
                    <span x-show="!isSaving">Save All Changes</span>
                    <span x-show="isSaving">Saving...</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Configuration Tabs --}}
    <div class="bg-white rounded-lg shadow-md mb-6">
        <div class="border-b border-gray-200">
            <nav class="flex space-x-8 px-6" aria-label="Tabs">
                <button 
                    @click="activeTab = 'general'"
                    :class="activeTab === 'general' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="py-4 px-1 border-b-2 font-medium text-sm whitespace-nowrap"
                >
                    General Settings
                </button>
                <button 
                    @click="activeTab = 'scraping'"
                    :class="activeTab === 'scraping' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="py-4 px-1 border-b-2 font-medium text-sm whitespace-nowrap"
                >
                    Scraping Sources
                </button>
                <button 
                    @click="activeTab = 'api'"
                    :class="activeTab === 'api' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="py-4 px-1 border-b-2 font-medium text-sm whitespace-nowrap"
                >
                    API Configuration
                </button>
                <button 
                    @click="activeTab = 'email'"
                    :class="activeTab === 'email' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="py-4 px-1 border-b-2 font-medium text-sm whitespace-nowrap"
                >
                    Email Templates
                </button>
                <button 
                    @click="activeTab = 'notifications'"
                    :class="activeTab === 'notifications' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="py-4 px-1 border-b-2 font-medium text-sm whitespace-nowrap"
                >
                    Notifications
                </button>
                <button 
                    @click="activeTab = 'security'"
                    :class="activeTab === 'security' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="py-4 px-1 border-b-2 font-medium text-sm whitespace-nowrap"
                >
                    Security
                </button>
            </nav>
        </div>

        {{-- Tab Content --}}
        <div class="p-6">
            {{-- General Settings --}}
            <div x-show="activeTab === 'general'" class="space-y-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Platform Settings</h3>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {{-- Basic Settings --}}
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Platform Name</label>
                            <input 
                                type="text" 
                                x-model="settings.general.platform_name"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                placeholder="HD Tickets"
                            >
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Platform URL</label>
                            <input 
                                type="url" 
                                x-model="settings.general.platform_url"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                placeholder="https://hd-tickets.com"
                            >
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Support Email</label>
                            <input 
                                type="email" 
                                x-model="settings.general.support_email"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                placeholder="support@hd-tickets.com"
                            >
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Default Currency</label>
                            <select 
                                x-model="settings.general.default_currency"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            >
                                <option value="USD">USD - US Dollar</option>
                                <option value="EUR">EUR - Euro</option>
                                <option value="GBP">GBP - British Pound</option>
                                <option value="CAD">CAD - Canadian Dollar</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Timezone</label>
                            <select 
                                x-model="settings.general.timezone"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            >
                                <option value="America/New_York">Eastern Time (ET)</option>
                                <option value="America/Chicago">Central Time (CT)</option>
                                <option value="America/Denver">Mountain Time (MT)</option>
                                <option value="America/Los_Angeles">Pacific Time (PT)</option>
                                <option value="UTC">UTC</option>
                            </select>
                        </div>
                    </div>

                    {{-- Feature Toggles --}}
                    <div class="space-y-4">
                        <h4 class="font-medium text-gray-900">Feature Settings</h4>
                        
                        <div class="space-y-3">
                            <label class="flex items-center">
                                <input 
                                    type="checkbox" 
                                    x-model="settings.general.maintenance_mode"
                                    class="rounded border-gray-300 text-green-600 focus:ring-green-500"
                                >
                                <span class="ml-3 text-sm text-gray-700">
                                    Maintenance Mode
                                    <span class="block text-xs text-gray-500">Restrict access to platform for maintenance</span>
                                </span>
                            </label>

                            <label class="flex items-center">
                                <input 
                                    type="checkbox" 
                                    x-model="settings.general.user_registration"
                                    class="rounded border-gray-300 text-green-600 focus:ring-green-500"
                                >
                                <span class="ml-3 text-sm text-gray-700">
                                    Allow User Registration
                                    <span class="block text-xs text-gray-500">Enable new user signups</span>
                                </span>
                            </label>

                            <label class="flex items-center">
                                <input 
                                    type="checkbox" 
                                    x-model="settings.general.email_verification"
                                    class="rounded border-gray-300 text-green-600 focus:ring-green-500"
                                >
                                <span class="ml-3 text-sm text-gray-700">
                                    Require Email Verification
                                    <span class="block text-xs text-gray-500">Users must verify email before access</span>
                                </span>
                            </label>

                            <label class="flex items-center">
                                <input 
                                    type="checkbox" 
                                    x-model="settings.general.debug_mode"
                                    class="rounded border-gray-300 text-green-600 focus:ring-green-500"
                                >
                                <span class="ml-3 text-sm text-gray-700">
                                    Debug Mode
                                    <span class="block text-xs text-red-500">⚠️ Only enable for development</span>
                                </span>
                            </label>

                            <label class="flex items-center">
                                <input 
                                    type="checkbox" 
                                    x-model="settings.general.analytics_tracking"
                                    class="rounded border-gray-300 text-green-600 focus:ring-green-500"
                                >
                                <span class="ml-3 text-sm text-gray-700">
                                    Analytics Tracking
                                    <span class="block text-xs text-gray-500">Enable Google Analytics and user tracking</span>
                                </span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Scraping Sources --}}
            <div x-show="activeTab === 'scraping'" class="space-y-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">Ticket Scraping Sources</h3>
                    <button 
                        @click="addScrapingSource()"
                        class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 flex items-center gap-2"
                    >
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
                        </svg>
                        Add Source
                    </button>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <template x-for="(source, index) in settings.scraping.sources" :key="index">
                        <div class="border border-gray-200 rounded-lg p-4 space-y-4">
                            <div class="flex items-center justify-between">
                                <h4 class="font-medium text-gray-900">Source Configuration</h4>
                                <button 
                                    @click="removeScrapingSource(index)"
                                    class="text-red-600 hover:text-red-800 p-1"
                                >
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                    </svg>
                                </button>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Source Name</label>
                                <input 
                                    type="text" 
                                    x-model="source.name"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                    placeholder="e.g. StubHub, Vivid Seats"
                                >
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Base URL</label>
                                <input 
                                    type="url" 
                                    x-model="source.base_url"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                    placeholder="https://example.com"
                                >
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Rate Limit (req/min)</label>
                                    <input 
                                        type="number" 
                                        x-model="source.rate_limit"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                        min="1" max="1000"
                                    >
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Priority</label>
                                    <select 
                                        x-model="source.priority"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                    >
                                        <option value="high">High</option>
                                        <option value="medium">Medium</option>
                                        <option value="low">Low</option>
                                    </select>
                                </div>
                            </div>

                            <div class="flex items-center justify-between">
                                <label class="flex items-center">
                                    <input 
                                        type="checkbox" 
                                        x-model="source.enabled"
                                        class="rounded border-gray-300 text-green-600 focus:ring-green-500"
                                    >
                                    <span class="ml-2 text-sm text-gray-700">Active</span>
                                </label>
                                
                                <div class="flex items-center gap-2">
                                    <span class="text-xs" :class="source.status === 'online' ? 'text-green-600' : 'text-red-600'" x-text="source.status || 'unknown'"></span>
                                    <button 
                                        @click="testScrapingSource(index)"
                                        class="text-blue-600 hover:text-blue-800 text-sm"
                                    >
                                        Test Connection
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- API Configuration --}}
            <div x-show="activeTab === 'api'" class="space-y-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">API Keys and Services</h3>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {{-- Payment APIs --}}
                    <div class="border border-gray-200 rounded-lg p-4 space-y-4">
                        <h4 class="font-medium text-gray-900">Payment Services</h4>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Stripe Publishable Key</label>
                            <input 
                                type="text" 
                                x-model="settings.api.stripe.publishable_key"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                placeholder="pk_live_..."
                            >
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Stripe Secret Key</label>
                            <div class="relative">
                                <input 
                                    :type="showStripeSecret ? 'text' : 'password'"
                                    x-model="settings.api.stripe.secret_key"
                                    class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                    placeholder="sk_live_..."
                                >
                                <button 
                                    @click="showStripeSecret = !showStripeSecret"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center"
                                >
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="showStripeSecret ? 'M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21' : 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z'"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">PayPal Environment</label>
                            <select 
                                x-model="settings.api.paypal.environment"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            >
                                <option value="sandbox">Sandbox (Testing)</option>
                                <option value="production">Production</option>
                            </select>
                        </div>
                    </div>

                    {{-- External APIs --}}
                    <div class="border border-gray-200 rounded-lg p-4 space-y-4">
                        <h4 class="font-medium text-gray-900">External Services</h4>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Google Maps API Key</label>
                            <input 
                                type="text" 
                                x-model="settings.api.google_maps.api_key"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                placeholder="AIza..."
                            >
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">SendGrid API Key</label>
                            <input 
                                type="password" 
                                x-model="settings.api.sendgrid.api_key"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                placeholder="SG...."
                            >
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Twilio Account SID</label>
                            <input 
                                type="text" 
                                x-model="settings.api.twilio.account_sid"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                placeholder="AC..."
                            >
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Twilio Auth Token</label>
                            <input 
                                type="password" 
                                x-model="settings.api.twilio.auth_token"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                placeholder="Auth Token"
                            >
                        </div>
                    </div>
                </div>
            </div>

            {{-- Email Templates --}}
            <div x-show="activeTab === 'email'" class="space-y-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">Email Templates</h3>
                    <button 
                        @click="previewEmail()"
                        class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700"
                    >
                        Preview Template
                    </button>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {{-- Template List --}}
                    <div class="space-y-2">
                        <h4 class="font-medium text-gray-900">Templates</h4>
                        <div class="border border-gray-200 rounded-lg max-h-96 overflow-y-auto">
                            <template x-for="(template, key) in settings.email.templates" :key="key">
                                <button 
                                    @click="selectedEmailTemplate = key"
                                    :class="selectedEmailTemplate === key ? 'bg-green-50 border-green-200 text-green-700' : 'hover:bg-gray-50'"
                                    class="w-full text-left p-3 border-b border-gray-100 last:border-b-0"
                                >
                                    <div class="font-medium" x-text="template.name"></div>
                                    <div class="text-xs text-gray-500" x-text="template.subject"></div>
                                </button>
                            </template>
                        </div>
                    </div>

                    {{-- Template Editor --}}
                    <div x-show="selectedEmailTemplate" class="lg:col-span-2 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Template Name</label>
                            <input 
                                type="text" 
                                x-model="settings.email.templates[selectedEmailTemplate].name"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            >
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Subject Line</label>
                            <input 
                                type="text" 
                                x-model="settings.email.templates[selectedEmailTemplate].subject"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            >
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email Content</label>
                            <textarea 
                                x-model="settings.email.templates[selectedEmailTemplate].content"
                                rows="12"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent font-mono text-sm"
                                placeholder="Email HTML content..."
                            ></textarea>
                        </div>

                        <div class="text-xs text-gray-500">
                            <p class="mb-1">Available variables:</p>
                            <div class="flex flex-wrap gap-2">
                                <span class="bg-gray-100 px-2 py-1 rounded">{{user_name}}</span>
                                <span class="bg-gray-100 px-2 py-1 rounded">{{event_name}}</span>
                                <span class="bg-gray-100 px-2 py-1 rounded">{{ticket_price}}</span>
                                <span class="bg-gray-100 px-2 py-1 rounded">{{platform_url}}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Notifications --}}
            <div x-show="activeTab === 'notifications'" class="space-y-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Notification Settings</h3>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {{-- Email Notifications --}}
                    <div class="border border-gray-200 rounded-lg p-4 space-y-4">
                        <h4 class="font-medium text-gray-900">Email Notifications</h4>
                        
                        <div class="space-y-3">
                            <label class="flex items-center">
                                <input 
                                    type="checkbox" 
                                    x-model="settings.notifications.email.price_alerts"
                                    class="rounded border-gray-300 text-green-600 focus:ring-green-500"
                                >
                                <span class="ml-3 text-sm text-gray-700">Price Alert Notifications</span>
                            </label>

                            <label class="flex items-center">
                                <input 
                                    type="checkbox" 
                                    x-model="settings.notifications.email.booking_confirmations"
                                    class="rounded border-gray-300 text-green-600 focus:ring-green-500"
                                >
                                <span class="ml-3 text-sm text-gray-700">Booking Confirmations</span>
                            </label>

                            <label class="flex items-center">
                                <input 
                                    type="checkbox" 
                                    x-model="settings.notifications.email.account_updates"
                                    class="rounded border-gray-300 text-green-600 focus:ring-green-500"
                                >
                                <span class="ml-3 text-sm text-gray-700">Account Updates</span>
                            </label>

                            <label class="flex items-center">
                                <input 
                                    type="checkbox" 
                                    x-model="settings.notifications.email.marketing"
                                    class="rounded border-gray-300 text-green-600 focus:ring-green-500"
                                >
                                <span class="ml-3 text-sm text-gray-700">Marketing Emails</span>
                            </label>
                        </div>
                    </div>

                    {{-- Push Notifications --}}
                    <div class="border border-gray-200 rounded-lg p-4 space-y-4">
                        <h4 class="font-medium text-gray-900">Push Notifications</h4>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Firebase Server Key</label>
                            <input 
                                type="password" 
                                x-model="settings.notifications.push.firebase_key"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            >
                        </div>

                        <div class="space-y-3">
                            <label class="flex items-center">
                                <input 
                                    type="checkbox" 
                                    x-model="settings.notifications.push.price_drops"
                                    class="rounded border-gray-300 text-green-600 focus:ring-green-500"
                                >
                                <span class="ml-3 text-sm text-gray-700">Price Drop Alerts</span>
                            </label>

                            <label class="flex items-center">
                                <input 
                                    type="checkbox" 
                                    x-model="settings.notifications.push.new_events"
                                    class="rounded border-gray-300 text-green-600 focus:ring-green-500"
                                >
                                <span class="ml-3 text-sm text-gray-700">New Event Notifications</span>
                            </label>

                            <label class="flex items-center">
                                <input 
                                    type="checkbox" 
                                    x-model="settings.notifications.push.booking_updates"
                                    class="rounded border-gray-300 text-green-600 focus:ring-green-500"
                                >
                                <span class="ml-3 text-sm text-gray-700">Booking Updates</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Security Settings --}}
            <div x-show="activeTab === 'security'" class="space-y-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Security Configuration</h3>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {{-- Authentication --}}
                    <div class="border border-gray-200 rounded-lg p-4 space-y-4">
                        <h4 class="font-medium text-gray-900">Authentication</h4>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Session Timeout (minutes)</label>
                            <input 
                                type="number" 
                                x-model="settings.security.session_timeout"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                min="15" max="480"
                            >
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Password Minimum Length</label>
                            <input 
                                type="number" 
                                x-model="settings.security.password_min_length"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                min="6" max="50"
                            >
                        </div>

                        <div class="space-y-3">
                            <label class="flex items-center">
                                <input 
                                    type="checkbox" 
                                    x-model="settings.security.two_factor_auth"
                                    class="rounded border-gray-300 text-green-600 focus:ring-green-500"
                                >
                                <span class="ml-3 text-sm text-gray-700">Enable Two-Factor Authentication</span>
                            </label>

                            <label class="flex items-center">
                                <input 
                                    type="checkbox" 
                                    x-model="settings.security.login_attempts_limit"
                                    class="rounded border-gray-300 text-green-600 focus:ring-green-500"
                                >
                                <span class="ml-3 text-sm text-gray-700">Limit Login Attempts</span>
                            </label>

                            <label class="flex items-center">
                                <input 
                                    type="checkbox" 
                                    x-model="settings.security.password_requirements"
                                    class="rounded border-gray-300 text-green-600 focus:ring-green-500"
                                >
                                <span class="ml-3 text-sm text-gray-700">Enforce Strong Passwords</span>
                            </label>
                        </div>
                    </div>

                    {{-- API Security --}}
                    <div class="border border-gray-200 rounded-lg p-4 space-y-4">
                        <h4 class="font-medium text-gray-900">API Security</h4>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">API Rate Limit (per minute)</label>
                            <input 
                                type="number" 
                                x-model="settings.security.api_rate_limit"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                min="10" max="1000"
                            >
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Allowed Origins (CORS)</label>
                            <textarea 
                                x-model="settings.security.cors_origins"
                                rows="3"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                placeholder="https://app.hd-tickets.com&#10;https://mobile.hd-tickets.com"
                            ></textarea>
                        </div>

                        <div class="space-y-3">
                            <label class="flex items-center">
                                <input 
                                    type="checkbox" 
                                    x-model="settings.security.api_key_required"
                                    class="rounded border-gray-300 text-green-600 focus:ring-green-500"
                                >
                                <span class="ml-3 text-sm text-gray-700">Require API Keys</span>
                            </label>

                            <label class="flex items-center">
                                <input 
                                    type="checkbox" 
                                    x-model="settings.security.ssl_required"
                                    class="rounded border-gray-300 text-green-600 focus:ring-green-500"
                                >
                                <span class="ml-3 text-sm text-gray-700">Require SSL/HTTPS</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Save Status --}}
    <div x-show="saveStatus" class="fixed bottom-4 right-4 z-50">
        <div 
            class="bg-white rounded-lg shadow-lg border p-4 flex items-center gap-3 transition-all duration-300"
            :class="saveStatus === 'success' ? 'border-green-200' : saveStatus === 'error' ? 'border-red-200' : 'border-blue-200'"
        >
            <div x-show="saveStatus === 'saving'">
                <svg class="animate-spin w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
            <div x-show="saveStatus === 'success'">
                <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div x-show="saveStatus === 'error'">
                <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div>
                <div x-show="saveStatus === 'saving'" class="text-sm font-medium text-blue-900">Saving changes...</div>
                <div x-show="saveStatus === 'success'" class="text-sm font-medium text-green-900">Settings saved successfully!</div>
                <div x-show="saveStatus === 'error'" class="text-sm font-medium text-red-900">Failed to save settings</div>
            </div>
        </div>
    </div>
</div>

<script>
function adminSystemConfig() {
    return {
        // UI State
        activeTab: 'general',
        selectedEmailTemplate: 'welcome',
        saveStatus: null,
        isSaving: false,
        showStripeSecret: false,
        
        // Settings Data
        settings: {
            general: {
                platform_name: 'HD Tickets',
                platform_url: 'https://hd-tickets.com',
                support_email: 'support@hd-tickets.com',
                default_currency: 'USD',
                timezone: 'America/New_York',
                maintenance_mode: false,
                user_registration: true,
                email_verification: true,
                debug_mode: false,
                analytics_tracking: true
            },
            scraping: {
                sources: []
            },
            api: {
                stripe: {
                    publishable_key: '',
                    secret_key: ''
                },
                paypal: {
                    environment: 'sandbox'
                },
                google_maps: {
                    api_key: ''
                },
                sendgrid: {
                    api_key: ''
                },
                twilio: {
                    account_sid: '',
                    auth_token: ''
                }
            },
            email: {
                templates: {
                    welcome: {
                        name: 'Welcome Email',
                        subject: 'Welcome to {{platform_name}}!',
                        content: '<h1>Welcome {{user_name}}!</h1><p>Thanks for joining {{platform_name}}. Start exploring amazing sports events!</p>'
                    },
                    price_alert: {
                        name: 'Price Alert',
                        subject: 'Price Drop Alert: {{event_name}}',
                        content: '<h1>Great News!</h1><p>The price for {{event_name}} has dropped to {{ticket_price}}!</p>'
                    },
                    booking_confirmation: {
                        name: 'Booking Confirmation',
                        subject: 'Your booking confirmation for {{event_name}}',
                        content: '<h1>Booking Confirmed!</h1><p>Your tickets for {{event_name}} have been confirmed.</p>'
                    }
                }
            },
            notifications: {
                email: {
                    price_alerts: true,
                    booking_confirmations: true,
                    account_updates: true,
                    marketing: false
                },
                push: {
                    firebase_key: '',
                    price_drops: true,
                    new_events: true,
                    booking_updates: true
                }
            },
            security: {
                session_timeout: 60,
                password_min_length: 8,
                two_factor_auth: false,
                login_attempts_limit: true,
                password_requirements: true,
                api_rate_limit: 100,
                cors_origins: '',
                api_key_required: true,
                ssl_required: true
            }
        },
        
        init() {
            this.loadSettings();
            console.log('[AdminSystemConfig] Initialized');
        },
        
        async loadSettings() {
            try {
                const response = await fetch('/api/admin/settings');
                const data = await response.json();
                
                if (data.success) {
                    // Merge loaded settings with defaults
                    this.settings = { ...this.settings, ...data.settings };
                }
            } catch (error) {
                console.error('[AdminSystemConfig] Failed to load settings:', error);
                // Initialize with sample scraping sources
                this.initializeSampleData();
            }
        },
        
        initializeSampleData() {
            this.settings.scraping.sources = [
                {
                    name: 'StubHub',
                    base_url: 'https://www.stubhub.com',
                    rate_limit: 60,
                    priority: 'high',
                    enabled: true,
                    status: 'online'
                },
                {
                    name: 'Vivid Seats',
                    base_url: 'https://www.vividseats.com',
                    rate_limit: 120,
                    priority: 'high',
                    enabled: true,
                    status: 'online'
                },
                {
                    name: 'SeatGeek',
                    base_url: 'https://seatgeek.com',
                    rate_limit: 90,
                    priority: 'medium',
                    enabled: false,
                    status: 'offline'
                }
            ];
        },
        
        async saveAllSettings() {
            this.isSaving = true;
            this.saveStatus = 'saving';
            
            try {
                const response = await fetch('/api/admin/settings', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(this.settings)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.saveStatus = 'success';
                    setTimeout(() => {
                        this.saveStatus = null;
                    }, 3000);
                } else {
                    throw new Error(data.error || 'Save failed');
                }
            } catch (error) {
                console.error('[AdminSystemConfig] Save failed:', error);
                this.saveStatus = 'error';
                setTimeout(() => {
                    this.saveStatus = null;
                }, 3000);
            } finally {
                this.isSaving = false;
            }
        },
        
        addScrapingSource() {
            this.settings.scraping.sources.push({
                name: '',
                base_url: '',
                rate_limit: 60,
                priority: 'medium',
                enabled: true,
                status: 'unknown'
            });
        },
        
        removeScrapingSource(index) {
            if (confirm('Are you sure you want to remove this scraping source?')) {
                this.settings.scraping.sources.splice(index, 1);
            }
        },
        
        async testScrapingSource(index) {
            const source = this.settings.scraping.sources[index];
            
            if (!source.base_url) {
                alert('Please enter a base URL first');
                return;
            }
            
            source.status = 'testing';
            
            try {
                const response = await fetch('/api/admin/scraping/test', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        url: source.base_url,
                        rate_limit: source.rate_limit
                    })
                });
                
                const data = await response.json();
                source.status = data.success ? 'online' : 'offline';
                
                if (!data.success) {
                    alert(`Connection test failed: ${data.error}`);
                }
            } catch (error) {
                console.error('[AdminSystemConfig] Test failed:', error);
                source.status = 'offline';
                alert('Connection test failed: Network error');
            }
        },
        
        previewEmail() {
            const template = this.settings.email.templates[this.selectedEmailTemplate];
            if (!template) return;
            
            // Create a preview window
            const previewWindow = window.open('', '_blank', 'width=600,height=800');
            
            // Sample data for preview
            const sampleData = {
                user_name: 'John Doe',
                event_name: 'Lakers vs Warriors',
                ticket_price: '$125.00',
                platform_name: this.settings.general.platform_name,
                platform_url: this.settings.general.platform_url
            };
            
            let previewContent = template.content;
            let previewSubject = template.subject;
            
            // Replace template variables
            Object.keys(sampleData).forEach(key => {
                const regex = new RegExp(`{{${key}}}`, 'g');
                previewContent = previewContent.replace(regex, sampleData[key]);
                previewSubject = previewSubject.replace(regex, sampleData[key]);
            });
            
            previewWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Email Preview: ${template.name}</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; }
                        .header { background: #f0f0f0; padding: 10px; margin-bottom: 20px; border-radius: 5px; }
                        .content { max-width: 600px; }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <h2>Email Preview: ${template.name}</h2>
                        <p><strong>Subject:</strong> ${previewSubject}</p>
                    </div>
                    <div class="content">
                        ${previewContent}
                    </div>
                </body>
                </html>
            `);
        }
    };
}
</script>