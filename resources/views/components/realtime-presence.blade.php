{{-- Real-Time Presence Component --}}
{{-- Shows online users, agents, and activity indicators with WebSocket integration --}}

<div x-data="realtimePresence()" x-init="init()" class="realtime-presence">
    {{-- Main Presence Widget --}}
    <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
        {{-- Header --}}
        <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                    <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                    Who's Online
                </h3>
                <div class="text-xs text-gray-500">
                    <span x-text="totalOnline"></span> online
                </div>
            </div>
        </div>

        {{-- Tabs --}}
        <div class="flex border-b border-gray-200">
            <button 
                @click="activeTab = 'all'"
                class="flex-1 px-4 py-2 text-sm font-medium transition-colors"
                :class="activeTab === 'all' ? 'bg-blue-50 text-blue-700 border-b-2 border-blue-500' : 'text-gray-500 hover:text-gray-700'"
            >
                All Users
                <span class="ml-1 px-2 py-0.5 text-xs rounded-full" 
                      :class="activeTab === 'all' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-600'"
                      x-text="allUsers.length">
                </span>
            </button>
            
            <button 
                @click="activeTab = 'agents'"
                class="flex-1 px-4 py-2 text-sm font-medium transition-colors"
                :class="activeTab === 'agents' ? 'bg-green-50 text-green-700 border-b-2 border-green-500' : 'text-gray-500 hover:text-gray-700'"
            >
                Support
                <span class="ml-1 px-2 py-0.5 text-xs rounded-full" 
                      :class="activeTab === 'agents' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'"
                      x-text="agents.length">
                </span>
            </button>
            
            <button 
                @click="activeTab = 'activity'"
                class="flex-1 px-4 py-2 text-sm font-medium transition-colors"
                :class="activeTab === 'activity' ? 'bg-purple-50 text-purple-700 border-b-2 border-purple-500' : 'text-gray-500 hover:text-gray-700'"
            >
                Activity
                <span class="ml-1 px-2 py-0.5 text-xs rounded-full" 
                      :class="activeTab === 'activity' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-600'"
                      x-text="recentActivity.length">
                </span>
            </button>
        </div>

        {{-- Content --}}
        <div class="max-h-80 overflow-y-auto">
            {{-- All Users Tab --}}
            <div x-show="activeTab === 'all'" class="divide-y divide-gray-100">
                <template x-for="user in allUsers.slice(0, maxVisibleUsers)" :key="user.id">
                    <div class="p-3 hover:bg-gray-50 transition-colors">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="relative">
                                    <img 
                                        :src="user.avatar || getDefaultAvatar(user.name)" 
                                        :alt="user.name"
                                        class="w-8 h-8 rounded-full bg-gray-200"
                                        @error="$event.target.src = getDefaultAvatar(user.name)"
                                    >
                                    <div 
                                        class="absolute -bottom-0.5 -right-0.5 w-3 h-3 rounded-full border-2 border-white"
                                        :class="getStatusColor(user.status)"
                                        :title="user.status"
                                    ></div>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                        <p class="text-sm font-medium text-gray-900 truncate" x-text="user.name"></p>
                                        <span x-show="user.role" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium" 
                                              :class="getRoleBadgeClass(user.role)" x-text="user.role"></span>
                                    </div>
                                    <div class="flex items-center gap-2 text-xs text-gray-500">
                                        <span x-text="user.currentPage || 'Dashboard'"></span>
                                        <span x-show="user.isTyping" class="text-blue-600 font-medium">• typing...</span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-xs text-gray-500" x-text="formatLastSeen(user.lastActivity)"></div>
                                <div x-show="user.isIdle" class="text-xs text-yellow-600">away</div>
                            </div>
                        </div>
                    </div>
                </template>
                
                {{-- Show More Button --}}
                <div x-show="allUsers.length > maxVisibleUsers" class="p-3 text-center border-t border-gray-100">
                    <button 
                        @click="showAllUsers()"
                        class="text-sm text-blue-600 hover:text-blue-800 font-medium"
                    >
                        Show <span x-text="allUsers.length - maxVisibleUsers"></span> more users
                    </button>
                </div>
                
                {{-- Empty State --}}
                <div x-show="allUsers.length === 0" class="p-8 text-center text-gray-500">
                    <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-.5a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                    <p class="text-sm">No users online</p>
                </div>
            </div>

            {{-- Support Agents Tab --}}
            <div x-show="activeTab === 'agents'" class="divide-y divide-gray-100">
                <template x-for="agent in agents" :key="agent.id">
                    <div class="p-3 hover:bg-gray-50 transition-colors">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="relative">
                                    <img 
                                        :src="agent.avatar || getDefaultAvatar(agent.name)" 
                                        :alt="agent.name"
                                        class="w-10 h-10 rounded-full bg-gray-200"
                                    >
                                    <div 
                                        class="absolute -bottom-0.5 -right-0.5 w-4 h-4 rounded-full border-2 border-white"
                                        :class="getAgentStatusColor(agent.status)"
                                    ></div>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                        <p class="text-sm font-medium text-gray-900" x-text="agent.name"></p>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Support
                                        </span>
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        <span x-show="agent.activeChats > 0">
                                            <span x-text="agent.activeChats"></span> active chat<span x-show="agent.activeChats !== 1">s</span>
                                        </span>
                                        <span x-show="agent.activeChats === 0">Available</span>
                                        <span x-show="agent.department"> • <span x-text="agent.department"></span></span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-xs" :class="getAgentAvailabilityColor(agent.availability)" x-text="agent.availability"></div>
                                <div x-show="agent.responseTime" class="text-xs text-gray-500">
                                    ~<span x-text="agent.responseTime"></span>min
                                </div>
                            </div>
                        </div>
                        
                        {{-- Agent Actions --}}
                        <div x-show="agent.canContact" class="mt-2 flex gap-2">
                            <button 
                                @click="startChat(agent.id)"
                                class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded hover:bg-blue-200 transition-colors"
                            >
                                💬 Chat
                            </button>
                            <button 
                                @click="requestCallback(agent.id)"
                                class="text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded hover:bg-gray-200 transition-colors"
                            >
                                📞 Callback
                            </button>
                        </div>
                    </div>
                </template>
                
                {{-- No Agents Online --}}
                <div x-show="agents.length === 0" class="p-8 text-center text-gray-500">
                    <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M12 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-sm">No support agents online</p>
                    <p class="text-xs text-gray-400 mt-1">We'll respond to your messages as soon as possible</p>
                </div>
            </div>

            {{-- Activity Tab --}}
            <div x-show="activeTab === 'activity'" class="divide-y divide-gray-100">
                <template x-for="activity in recentActivity.slice(0, 10)" :key="activity.id">
                    <div class="p-3 hover:bg-gray-50 transition-colors">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 mt-1">
                                <div 
                                    class="w-2 h-2 rounded-full"
                                    :class="getActivityTypeColor(activity.type)"
                                ></div>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm text-gray-900" x-html="activity.message"></p>
                                <div class="flex items-center gap-2 mt-1 text-xs text-gray-500">
                                    <span x-text="formatRelativeTime(activity.timestamp)"></span>
                                    <span x-show="activity.location">• <span x-text="activity.location"></span></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
                
                {{-- No Recent Activity --}}
                <div x-show="recentActivity.length === 0" class="p-8 text-center text-gray-500">
                    <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-sm">No recent activity</p>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="px-4 py-2 bg-gray-50 border-t border-gray-200 text-center">
            <div class="flex items-center justify-center gap-4 text-xs text-gray-500">
                <div class="flex items-center gap-1">
                    <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                    <span>Online</span>
                </div>
                <div class="flex items-center gap-1">
                    <div class="w-2 h-2 bg-yellow-500 rounded-full"></div>
                    <span>Away</span>
                </div>
                <div class="flex items-center gap-1">
                    <div class="w-2 h-2 bg-red-500 rounded-full"></div>
                    <span>Busy</span>
                </div>
                <div class="flex items-center gap-1">
                    <div class="w-2 h-2 bg-gray-400 rounded-full"></div>
                    <span>Offline</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Floating Presence Indicators (for specific pages) --}}
    <div x-show="showFloatingIndicators" class="fixed top-4 left-4 z-30">
        <div class="bg-black bg-opacity-75 text-white px-3 py-2 rounded-lg text-sm">
            <div class="flex items-center gap-2">
                <div class="flex -space-x-1">
                    <template x-for="user in visibleUsers.slice(0, 3)" :key="user.id">
                        <img 
                            :src="user.avatar || getDefaultAvatar(user.name)" 
                            :alt="user.name"
                            :title="user.name"
                            class="w-6 h-6 rounded-full border-2 border-black"
                        >
                    </template>
                </div>
                <span x-show="visibleUsers.length === 1" x-text="visibleUsers[0].name + ' is viewing this page'"></span>
                <span x-show="visibleUsers.length === 2" x-text="visibleUsers.length + ' people are viewing this page'"></span>
                <span x-show="visibleUsers.length > 2" x-text="visibleUsers.length + ' people are viewing this page'"></span>
            </div>
        </div>
    </div>
</div>

<script>
function realtimePresence() {
    return {
        // UI State
        activeTab: 'all',
        maxVisibleUsers: 8,
        showFloatingIndicators: false,
        
        // Data
        allUsers: [],
        agents: [],
        recentActivity: [],
        visibleUsers: [], // Users viewing the same page
        
        // Connection
        isConnected: false,
        presenceChannel: null,
        pageChannel: null,
        
        // Update intervals
        activityUpdateInterval: null,
        presenceCleanupInterval: null,
        
        init() {
            this.setupPresenceChannels();
            this.startPresenceUpdates();
            this.setupPageVisibility();
            
            // Determine if we should show floating indicators
            this.showFloatingIndicators = this.shouldShowFloatingIndicators();
            
            console.log('[Presence] Initialized');
        },
        
        setupPresenceChannels() {
            if (!window.Echo) {
                console.warn('[Presence] Echo not available');
                return;
            }
            
            // Global presence channel
            this.presenceChannel = window.Echo.join('platform.presence');
            
            this.presenceChannel
                .here((users) => {
                    this.allUsers = this.processUsers(users);
                    this.agents = this.filterAgents(users);
                    this.updateTotalCount();
                    console.log('[Presence] Initial users loaded:', users.length);
                })
                .joining((user) => {
                    this.addUser(user);
                    this.addActivity({
                        type: 'user_joined',
                        message: `<strong>${user.name}</strong> joined`,
                        timestamp: Date.now(),
                        userId: user.id
                    });
                    console.log('[Presence] User joined:', user.name);
                })
                .leaving((user) => {
                    this.removeUser(user);
                    this.addActivity({
                        type: 'user_left',
                        message: `<strong>${user.name}</strong> left`,
                        timestamp: Date.now(),
                        userId: user.id
                    });
                    console.log('[Presence] User left:', user.name);
                })
                .listenForWhisper('typing', (event) => {
                    this.handleUserTyping(event);
                })
                .listenForWhisper('page_change', (event) => {
                    this.handlePageChange(event);
                })
                .listenForWhisper('status_change', (event) => {
                    this.handleStatusChange(event);
                });
            
            // Page-specific presence (if applicable)
            const currentPage = this.getCurrentPageId();
            if (currentPage) {
                this.pageChannel = window.Echo.join(`page.${currentPage}`);
                
                this.pageChannel
                    .here((users) => {
                        this.visibleUsers = this.processUsers(users);
                    })
                    .joining((user) => {
                        this.visibleUsers.push(this.processUser(user));
                        this.updateFloatingIndicator();
                    })
                    .leaving((user) => {
                        this.visibleUsers = this.visibleUsers.filter(u => u.id !== user.id);
                        this.updateFloatingIndicator();
                    });
            }
        },
        
        startPresenceUpdates() {
            // Update user activity status periodically
            this.activityUpdateInterval = setInterval(() => {
                this.updateUserActivity();
                this.cleanupStaleActivity();
            }, 30000); // Every 30 seconds
            
            // Cleanup old presence data
            this.presenceCleanupInterval = setInterval(() => {
                this.cleanupStalePresence();
            }, 300000); // Every 5 minutes
        },
        
        setupPageVisibility() {
            // Handle page visibility changes
            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    this.updateUserStatus('away');
                } else {
                    this.updateUserStatus('online');
                }
            });
            
            // Handle page navigation
            window.addEventListener('beforeunload', () => {
                if (this.pageChannel) {
                    this.pageChannel.leave();
                }
            });
            
            // Track page changes in SPA
            this.watchRouteChanges();
        },
        
        processUsers(users) {
            return users.map(user => this.processUser(user));
        },
        
        processUser(user) {
            return {
                id: user.id,
                name: user.name,
                avatar: user.avatar,
                role: user.role,
                status: user.status || 'online',
                isIdle: user.last_activity && (Date.now() - new Date(user.last_activity).getTime()) > 600000, // 10 minutes
                isTyping: false,
                currentPage: user.current_page,
                lastActivity: user.last_activity,
                department: user.department,
                activeChats: user.active_chats || 0,
                availability: user.availability || 'available',
                responseTime: user.avg_response_time,
                canContact: user.role === 'agent' && user.availability === 'available'
            };
        },
        
        filterAgents(users) {
            return users
                .filter(user => user.role === 'agent' || user.role === 'admin')
                .map(user => this.processUser(user))
                .sort((a, b) => {
                    // Sort by availability first, then by active chats
                    if (a.availability === 'available' && b.availability !== 'available') return -1;
                    if (b.availability === 'available' && a.availability !== 'available') return 1;
                    return a.activeChats - b.activeChats;
                });
        },
        
        addUser(user) {
            const processedUser = this.processUser(user);
            
            // Add to all users if not already present
            const existingUserIndex = this.allUsers.findIndex(u => u.id === user.id);
            if (existingUserIndex === -1) {
                this.allUsers.push(processedUser);
            } else {
                this.allUsers[existingUserIndex] = processedUser;
            }
            
            // Add to agents if applicable
            if (user.role === 'agent' || user.role === 'admin') {
                const existingAgentIndex = this.agents.findIndex(a => a.id === user.id);
                if (existingAgentIndex === -1) {
                    this.agents.push(processedUser);
                } else {
                    this.agents[existingAgentIndex] = processedUser;
                }
                
                // Sort agents
                this.agents = this.agents.sort((a, b) => {
                    if (a.availability === 'available' && b.availability !== 'available') return -1;
                    if (b.availability === 'available' && a.availability !== 'available') return 1;
                    return a.activeChats - b.activeChats;
                });
            }
            
            this.updateTotalCount();
        },
        
        removeUser(user) {
            this.allUsers = this.allUsers.filter(u => u.id !== user.id);
            this.agents = this.agents.filter(a => a.id !== user.id);
            this.updateTotalCount();
        },
        
        handleUserTyping(event) {
            const user = this.allUsers.find(u => u.id === event.userId);
            if (user) {
                user.isTyping = event.isTyping;
                
                if (event.isTyping) {
                    // Auto-clear typing indicator after 3 seconds
                    setTimeout(() => {
                        user.isTyping = false;
                    }, 3000);
                }
            }
        },
        
        handlePageChange(event) {
            const user = this.allUsers.find(u => u.id === event.userId);
            if (user) {
                user.currentPage = event.page;
                
                this.addActivity({
                    type: 'page_change',
                    message: `<strong>${user.name}</strong> navigated to ${event.pageTitle || event.page}`,
                    timestamp: Date.now(),
                    userId: event.userId,
                    location: event.page
                });
            }
        },
        
        handleStatusChange(event) {
            const user = this.allUsers.find(u => u.id === event.userId);
            if (user) {
                user.status = event.status;
                user.availability = event.availability;
                
                // Update agent info if applicable
                const agent = this.agents.find(a => a.id === event.userId);
                if (agent) {
                    agent.status = event.status;
                    agent.availability = event.availability;
                    agent.activeChats = event.activeChats || agent.activeChats;
                }
            }
        },
        
        addActivity(activity) {
            activity.id = Date.now() + Math.random();
            this.recentActivity.unshift(activity);
            
            // Keep only last 50 activities
            if (this.recentActivity.length > 50) {
                this.recentActivity = this.recentActivity.slice(0, 50);
            }
        },
        
        updateUserActivity() {
            // Send heartbeat to update last activity
            if (this.presenceChannel) {
                this.presenceChannel.whisper('heartbeat', {
                    userId: this.getCurrentUserId(),
                    timestamp: Date.now(),
                    page: window.location.pathname
                });
            }
        },
        
        updateUserStatus(status) {
            if (this.presenceChannel) {
                this.presenceChannel.whisper('status_change', {
                    userId: this.getCurrentUserId(),
                    status: status,
                    timestamp: Date.now()
                });
            }
        },
        
        cleanupStaleActivity() {
            const cutoff = Date.now() - (24 * 60 * 60 * 1000); // 24 hours
            this.recentActivity = this.recentActivity.filter(activity => 
                activity.timestamp > cutoff
            );
        },
        
        cleanupStalePresence() {
            const cutoff = Date.now() - (15 * 60 * 1000); // 15 minutes
            this.allUsers = this.allUsers.filter(user => {
                if (!user.lastActivity) return true;
                return new Date(user.lastActivity).getTime() > cutoff;
            });
            
            this.agents = this.agents.filter(agent => {
                if (!agent.lastActivity) return true;
                return new Date(agent.lastActivity).getTime() > cutoff;
            });
            
            this.updateTotalCount();
        },
        
        updateTotalCount() {
            this.totalOnline = this.allUsers.length;
        },
        
        updateFloatingIndicator() {
            this.showFloatingIndicators = this.visibleUsers.length > 1 && this.shouldShowFloatingIndicators();
        },
        
        watchRouteChanges() {
            // Simple SPA route change detection
            let currentUrl = window.location.href;
            
            setInterval(() => {
                if (window.location.href !== currentUrl) {
                    currentUrl = window.location.href;
                    this.handleRouteChange();
                }
            }, 1000);
            
            // Also listen for popstate
            window.addEventListener('popstate', () => {
                this.handleRouteChange();
            });
        },
        
        handleRouteChange() {
            if (this.presenceChannel) {
                this.presenceChannel.whisper('page_change', {
                    userId: this.getCurrentUserId(),
                    page: window.location.pathname,
                    pageTitle: document.title,
                    timestamp: Date.now()
                });
            }
            
            // Update page-specific presence
            if (this.pageChannel) {
                this.pageChannel.leave();
            }
            
            const currentPage = this.getCurrentPageId();
            if (currentPage && window.Echo) {
                this.pageChannel = window.Echo.join(`page.${currentPage}`);
                this.setupPageChannelListeners();
            }
        },
        
        setupPageChannelListeners() {
            if (!this.pageChannel) return;
            
            this.pageChannel
                .here((users) => {
                    this.visibleUsers = this.processUsers(users);
                    this.updateFloatingIndicator();
                })
                .joining((user) => {
                    this.visibleUsers.push(this.processUser(user));
                    this.updateFloatingIndicator();
                })
                .leaving((user) => {
                    this.visibleUsers = this.visibleUsers.filter(u => u.id !== user.id);
                    this.updateFloatingIndicator();
                });
        },
        
        // UI Actions
        showAllUsers() {
            this.maxVisibleUsers = this.allUsers.length;
        },
        
        startChat(agentId) {
            // Trigger live chat with specific agent
            if (window.liveChatComponent) {
                window.liveChatComponent.startChatWithAgent(agentId);
            }
            
            // Or emit custom event
            document.dispatchEvent(new CustomEvent('start-agent-chat', {
                detail: { agentId }
            }));
        },
        
        requestCallback(agentId) {
            // Handle callback request
            document.dispatchEvent(new CustomEvent('request-callback', {
                detail: { agentId }
            }));
        },
        
        // Utility Methods
        getCurrentUserId() {
            return document.body.getAttribute('data-user-id');
        },
        
        getCurrentPageId() {
            // Extract page identifier for presence channel
            const path = window.location.pathname;
            if (path.includes('/tickets/')) {
                const ticketId = path.split('/tickets/')[1]?.split('/')[0];
                return ticketId ? `ticket-${ticketId}` : null;
            } else if (path.includes('/events/')) {
                const eventId = path.split('/events/')[1]?.split('/')[0];
                return eventId ? `event-${eventId}` : null;
            }
            
            // Default to page path
            return path.replace(/[^a-zA-Z0-9]/g, '-');
        },
        
        shouldShowFloatingIndicators() {
            // Show floating indicators on content pages, not on dashboard
            const path = window.location.pathname;
            return path.includes('/tickets/') || path.includes('/events/') || path.includes('/venues/');
        },
        
        getDefaultAvatar(name) {
            // Generate default avatar URL (e.g., using initials)
            const initials = name.split(' ').map(n => n[0]).join('').toUpperCase();
            return `https://ui-avatars.com/api/?name=${encodeURIComponent(initials)}&size=40&background=random`;
        },
        
        getStatusColor(status) {
            const colors = {
                online: 'bg-green-500',
                away: 'bg-yellow-500',
                busy: 'bg-red-500',
                offline: 'bg-gray-400'
            };
            return colors[status] || 'bg-gray-400';
        },
        
        getAgentStatusColor(status) {
            const colors = {
                online: 'bg-green-500',
                away: 'bg-yellow-500',
                busy: 'bg-red-500',
                offline: 'bg-gray-400'
            };
            return colors[status] || 'bg-green-500';
        },
        
        getAgentAvailabilityColor(availability) {
            const colors = {
                available: 'text-green-600',
                busy: 'text-red-600',
                away: 'text-yellow-600',
                offline: 'text-gray-500'
            };
            return colors[availability] || 'text-gray-500';
        },
        
        getRoleBadgeClass(role) {
            const classes = {
                admin: 'bg-red-100 text-red-800',
                agent: 'bg-green-100 text-green-800',
                customer: 'bg-blue-100 text-blue-800',
                scraper: 'bg-purple-100 text-purple-800'
            };
            return classes[role] || 'bg-gray-100 text-gray-800';
        },
        
        getActivityTypeColor(type) {
            const colors = {
                user_joined: 'bg-green-500',
                user_left: 'bg-red-500',
                page_change: 'bg-blue-500',
                status_change: 'bg-yellow-500',
                message_sent: 'bg-purple-500'
            };
            return colors[type] || 'bg-gray-500';
        },
        
        formatLastSeen(lastActivity) {
            if (!lastActivity) return 'now';
            
            const diff = Date.now() - new Date(lastActivity).getTime();
            const minutes = Math.floor(diff / 60000);
            const hours = Math.floor(diff / 3600000);
            
            if (minutes < 1) return 'now';
            if (minutes < 60) return `${minutes}m`;
            if (hours < 24) return `${hours}h`;
            
            return new Date(lastActivity).toLocaleDateString();
        },
        
        formatRelativeTime(timestamp) {
            const diff = Date.now() - timestamp;
            const minutes = Math.floor(diff / 60000);
            const hours = Math.floor(diff / 3600000);
            const days = Math.floor(diff / 86400000);
            
            if (minutes < 1) return 'just now';
            if (minutes < 60) return `${minutes}m ago`;
            if (hours < 24) return `${hours}h ago`;
            if (days < 7) return `${days}d ago`;
            
            return new Date(timestamp).toLocaleDateString();
        },
        
        // Cleanup
        destroy() {
            if (this.activityUpdateInterval) {
                clearInterval(this.activityUpdateInterval);
            }
            if (this.presenceCleanupInterval) {
                clearInterval(this.presenceCleanupInterval);
            }
            if (this.presenceChannel) {
                this.presenceChannel.leave();
            }
            if (this.pageChannel) {
                this.pageChannel.leave();
            }
        }
    };
}
</script>