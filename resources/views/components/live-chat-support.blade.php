{{-- Live Chat Support Component --}}
{{-- Real-time customer support chat with agent presence and file sharing --}}

<div x-data="liveChatSupport()" x-init="init()" class="live-chat-support">
    {{-- Chat Trigger Button (when minimized) --}}
    <div 
        x-show="!isChatOpen && showChatTrigger"
        class="fixed bottom-6 right-6 z-40"
    >
        <button 
            @click="openChat()"
            class="bg-blue-600 hover:bg-blue-700 text-white rounded-full p-4 shadow-lg transition-all duration-300 group relative"
        >
            {{-- Notification Badge --}}
            <div 
                x-show="unreadCount > 0" 
                class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-6 h-6 flex items-center justify-center font-bold animate-bounce"
                x-text="unreadCount > 9 ? '9+' : unreadCount"
            ></div>
            
            {{-- Agent Online Indicator --}}
            <div 
                x-show="isAgentOnline"
                class="absolute -top-0.5 -left-0.5 w-4 h-4 bg-green-500 rounded-full border-2 border-white animate-pulse"
            ></div>
            
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd"></path>
            </svg>
        </button>
    </div>

    {{-- Main Chat Interface --}}
    <div 
        x-show="isChatOpen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="transform translate-y-full opacity-0"
        x-transition:enter-end="transform translate-y-0 opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="transform translate-y-0 opacity-100"
        x-transition:leave-end="transform translate-y-full opacity-0"
        class="fixed bottom-6 right-6 w-96 h-[600px] bg-white rounded-lg shadow-2xl border border-gray-200 flex flex-col z-50"
        style="max-height: calc(100vh - 3rem);"
    >
        {{-- Chat Header --}}
        <div class="flex items-center justify-between p-4 border-b border-gray-200 bg-blue-600 text-white rounded-t-lg">
            <div class="flex items-center gap-3">
                <div class="relative">
                    <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 13V5a2 2 0 00-2-2H4a2 2 0 00-2 2v8a2 2 0 002 2h3l3 3 3-3h3a2 2 0 002-2zM5 7a1 1 0 011-1h8a1 1 0 110 2H6a1 1 0 01-1-1zm1 3a1 1 0 100 2h3a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div 
                        class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full border-2 border-white"
                        :class="isAgentOnline ? 'bg-green-400' : 'bg-gray-400'"
                    ></div>
                </div>
                <div>
                    <h3 class="font-semibold text-sm">HD Tickets Support</h3>
                    <p class="text-xs opacity-90">
                        <span x-show="isAgentOnline">
                            <span x-show="agentInfo.name" x-text="agentInfo.name"></span>
                            <span x-show="!agentInfo.name">Agent online</span> • 
                            <span x-show="agentInfo.isTyping">typing...</span>
                            <span x-show="!agentInfo.isTyping">Available</span>
                        </span>
                        <span x-show="!isAgentOnline">We'll respond soon</span>
                    </p>
                </div>
            </div>
            
            <div class="flex items-center gap-2">
                {{-- Minimize Button --}}
                <button 
                    @click="minimizeChat()"
                    class="p-1 rounded hover:bg-blue-500 transition-colors"
                >
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                    </svg>
                </button>
                
                {{-- Close Button --}}
                <button 
                    @click="closeChat()"
                    class="p-1 rounded hover:bg-blue-500 transition-colors"
                >
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Chat Messages --}}
        <div 
            x-ref="messagesContainer"
            class="flex-1 p-4 space-y-4 overflow-y-auto bg-gray-50"
            style="scrollbar-width: thin;"
        >
            {{-- Welcome Message --}}
            <div x-show="messages.length === 0" class="text-center py-8">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 13V5a2 2 0 00-2-2H4a2 2 0 00-2 2v8a2 2 0 002 2h3l3 3 3-3h3a2 2 0 002-2zM5 7a1 1 0 011-1h8a1 1 0 110 2H6a1 1 0 01-1-1zm1 3a1 1 0 100 2h3a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <h4 class="text-lg font-semibold text-gray-900 mb-2">How can we help?</h4>
                <p class="text-sm text-gray-600">Send us a message and we'll get back to you as soon as possible.</p>
            </div>

            {{-- Messages --}}
            <template x-for="message in messages" :key="message.id">
                <div class="flex" :class="message.sender === 'user' ? 'justify-end' : 'justify-start'">
                    <div class="max-w-xs lg:max-w-md">
                        {{-- Message Bubble --}}
                        <div 
                            class="px-4 py-2 rounded-lg text-sm"
                            :class="message.sender === 'user' 
                                ? 'bg-blue-600 text-white rounded-br-none' 
                                : 'bg-white border border-gray-200 text-gray-900 rounded-bl-none shadow-sm'"
                        >
                            {{-- Message Content --}}
                            <div x-show="message.type === 'text'" x-text="message.content"></div>
                            
                            {{-- Image Message --}}
                            <div x-show="message.type === 'image'" class="space-y-2">
                                <img :src="message.content" :alt="message.alt || 'Shared image'" class="rounded max-w-full h-auto">
                                <p x-show="message.caption" class="text-xs" x-text="message.caption"></p>
                            </div>
                            
                            {{-- File Message --}}
                            <div x-show="message.type === 'file'" class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                                <span x-text="message.fileName"></span>
                                <span class="text-xs opacity-75" x-text="formatFileSize(message.fileSize)"></span>
                            </div>
                            
                            {{-- System Message --}}
                            <div x-show="message.type === 'system'" class="text-center text-xs opacity-75" x-text="message.content"></div>
                        </div>
                        
                        {{-- Message Metadata --}}
                        <div 
                            class="flex items-center gap-1 mt-1 text-xs text-gray-500"
                            :class="message.sender === 'user' ? 'justify-end' : 'justify-start'"
                        >
                            <span x-text="formatMessageTime(message.timestamp)"></span>
                            <span x-show="message.sender === 'user' && message.status">
                                <svg x-show="message.status === 'sent'" class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <svg x-show="message.status === 'delivered'" class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <svg x-show="message.status === 'read'" class="w-3 h-3 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </span>
                        </div>
                    </div>
                </div>
            </template>
            
            {{-- Typing Indicator --}}
            <div x-show="agentInfo.isTyping" class="flex justify-start">
                <div class="bg-white border border-gray-200 rounded-lg px-4 py-2 rounded-bl-none shadow-sm">
                    <div class="flex space-x-1">
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></div>
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- File Upload Preview --}}
        <div x-show="filePreview" class="px-4 py-2 bg-blue-50 border-t border-gray-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="text-sm text-blue-800" x-text="filePreview?.name"></span>
                    <span class="text-xs text-blue-600" x-text="formatFileSize(filePreview?.size)"></span>
                </div>
                <button @click="cancelFileUpload()" class="text-blue-600 hover:text-blue-800">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Message Input --}}
        <div class="p-4 border-t border-gray-200 bg-white rounded-b-lg">
            <div class="flex items-end gap-2">
                {{-- File Upload Button --}}
                <div class="relative">
                    <input 
                        type="file" 
                        x-ref="fileInput"
                        @change="handleFileSelect($event)"
                        accept="image/*,.pdf,.doc,.docx,.txt"
                        class="hidden"
                    >
                    <button 
                        @click="$refs.fileInput.click()"
                        class="p-2 text-gray-400 hover:text-gray-600 transition-colors"
                        :disabled="isUploading"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                        </svg>
                    </button>
                </div>

                {{-- Message Input --}}
                <div class="flex-1">
                    <textarea
                        x-model="currentMessage"
                        x-ref="messageInput"
                        @keydown.enter.prevent="handleEnterKey($event)"
                        @input="handleTyping()"
                        @focus="markMessagesAsRead()"
                        placeholder="Type your message..."
                        rows="1"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg resize-none focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        style="max-height: 100px;"
                        :disabled="isUploading"
                    ></textarea>
                </div>

                {{-- Send Button --}}
                <button 
                    @click="sendMessage()"
                    :disabled="!currentMessage.trim() && !filePreview || isUploading"
                    class="p-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors"
                >
                    <svg x-show="!isUploading" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"></path>
                    </svg>
                    <svg x-show="isUploading" class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                </button>
            </div>
            
            {{-- Quick Actions --}}
            <div x-show="showQuickActions && messages.length === 0" class="mt-3 flex flex-wrap gap-2">
                <button 
                    @click="sendQuickMessage('I need help with ticket pricing')"
                    class="px-3 py-1 text-xs bg-gray-100 text-gray-700 rounded-full hover:bg-gray-200 transition-colors"
                >
                    💰 Ticket pricing
                </button>
                <button 
                    @click="sendQuickMessage('I have a question about my order')"
                    class="px-3 py-1 text-xs bg-gray-100 text-gray-700 rounded-full hover:bg-gray-200 transition-colors"
                >
                    📦 My order
                </button>
                <button 
                    @click="sendQuickMessage('I need help finding tickets')"
                    class="px-3 py-1 text-xs bg-gray-100 text-gray-700 rounded-full hover:bg-gray-200 transition-colors"
                >
                    🎫 Find tickets
                </button>
                <button 
                    @click="sendQuickMessage('Technical support needed')"
                    class="px-3 py-1 text-xs bg-gray-100 text-gray-700 rounded-full hover:bg-gray-200 transition-colors"
                >
                    🔧 Technical
                </button>
            </div>
        </div>
    </div>

    {{-- Chat History Modal --}}
    <div 
        x-show="showChatHistory"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 overflow-y-auto"
    >
        <div class="flex min-h-screen items-end justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showChatHistory = false"></div>
            
            <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Chat History</h3>
                    <div class="max-h-96 overflow-y-auto space-y-3">
                        <template x-for="session in chatHistory" :key="session.id">
                            <div class="p-3 border rounded-lg hover:bg-gray-50 cursor-pointer" @click="loadChatSession(session)">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900" x-text="session.subject || 'General Support'"></p>
                                        <p class="text-xs text-gray-500" x-text="session.lastMessage"></p>
                                    </div>
                                    <span class="text-xs text-gray-400" x-text="formatDate(session.updatedAt)"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
                <div class="mt-5 sm:mt-6">
                    <button 
                        @click="showChatHistory = false"
                        class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition-colors"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function liveChatSupport() {
    return {
        // Chat state
        isChatOpen: false,
        showChatTrigger: true,
        showChatHistory: false,
        showQuickActions: true,
        currentSessionId: null,
        
        // Connection state
        isConnected: false,
        isAgentOnline: false,
        agentInfo: {
            name: '',
            avatar: '',
            isTyping: false
        },
        
        // Messages
        messages: [],
        currentMessage: '',
        unreadCount: 0,
        lastTypingTime: 0,
        typingTimeout: null,
        
        // File handling
        filePreview: null,
        isUploading: false,
        maxFileSize: 10 * 1024 * 1024, // 10MB
        
        // Chat history
        chatHistory: [],
        
        init() {
            this.setupWebSocketListeners();
            this.loadChatState();
            this.checkAgentPresence();
            
            // Initialize page visibility handling
            this.setupVisibilityHandling();
            
            console.log('[LiveChat] Initialized');
        },
        
        setupWebSocketListeners() {
            if (!window.Echo) {
                console.warn('[LiveChat] Echo not available');
                return;
            }
            
            // Subscribe to user's private channel for chat messages
            const userId = document.body.getAttribute('data-user-id');
            if (userId) {
                const userChannel = window.Echo.private(`user.${userId}`);
                
                userChannel
                    .listen('ChatMessageReceived', (event) => this.handleIncomingMessage(event))
                    .listen('AgentJoinedChat', (event) => this.handleAgentJoined(event))
                    .listen('AgentLeftChat', (event) => this.handleAgentLeft(event))
                    .listen('AgentTyping', (event) => this.handleAgentTyping(event))
                    .listen('MessageStatusUpdated', (event) => this.handleMessageStatusUpdate(event));
            }
            
            // Subscribe to general support presence channel
            const presenceChannel = window.Echo.join('support.presence');
            
            presenceChannel
                .here((users) => {
                    this.isAgentOnline = users.some(user => user.role === 'agent');
                })
                .joining((user) => {
                    if (user.role === 'agent') {
                        this.isAgentOnline = true;
                        this.agentInfo = {
                            name: user.name,
                            avatar: user.avatar,
                            isTyping: false
                        };
                    }
                })
                .leaving((user) => {
                    if (user.role === 'agent') {
                        this.isAgentOnline = false;
                        this.agentInfo.isTyping = false;
                    }
                });
            
            // Connection status
            document.addEventListener('echo:connected', () => {
                this.isConnected = true;
                this.checkAgentPresence();
            });
            
            document.addEventListener('echo:disconnected', () => {
                this.isConnected = false;
                this.isAgentOnline = false;
            });
        },
        
        setupVisibilityHandling() {
            document.addEventListener('visibilitychange', () => {
                if (!document.hidden && this.isChatOpen) {
                    this.markMessagesAsRead();
                }
            });
        },
        
        loadChatState() {
            // Load from localStorage
            const savedState = localStorage.getItem('hdtickets_chat_state');
            if (savedState) {
                try {
                    const state = JSON.parse(savedState);
                    this.currentSessionId = state.sessionId;
                    this.messages = state.messages || [];
                    this.chatHistory = state.history || [];
                    
                    // Update unread count
                    this.updateUnreadCount();
                } catch (error) {
                    console.error('[LiveChat] Failed to load chat state:', error);
                }
            }
        },
        
        saveChatState() {
            const state = {
                sessionId: this.currentSessionId,
                messages: this.messages,
                history: this.chatHistory,
                timestamp: Date.now()
            };
            
            try {
                localStorage.setItem('hdtickets_chat_state', JSON.stringify(state));
            } catch (error) {
                console.error('[LiveChat] Failed to save chat state:', error);
            }
        },
        
        openChat() {
            this.isChatOpen = true;
            this.showChatTrigger = false;
            
            this.$nextTick(() => {
                this.scrollToBottom();
                if (this.$refs.messageInput) {
                    this.$refs.messageInput.focus();
                }
                this.markMessagesAsRead();
            });
            
            // Start new session if none exists
            if (!this.currentSessionId) {
                this.startNewChatSession();
            }
        },
        
        minimizeChat() {
            this.isChatOpen = false;
            this.showChatTrigger = true;
        },
        
        closeChat() {
            this.isChatOpen = false;
            this.showChatTrigger = true;
            
            // Save current session to history if it has messages
            if (this.messages.length > 0) {
                this.saveSessionToHistory();
            }
        },
        
        startNewChatSession() {
            this.currentSessionId = 'chat_' + Date.now();
            
            // Add welcome message
            this.addMessage({
                id: 'welcome_' + Date.now(),
                type: 'system',
                content: 'Chat session started',
                timestamp: Date.now(),
                sender: 'system'
            });
            
            // Notify server about new chat session
            this.sendToServer('chat_session_started', {
                sessionId: this.currentSessionId
            });
        },
        
        sendMessage() {
            const messageText = this.currentMessage.trim();
            
            if (!messageText && !this.filePreview) return;
            
            let message;
            
            if (this.filePreview) {
                // File message
                message = {
                    id: 'msg_' + Date.now(),
                    type: 'file',
                    content: this.filePreview.url,
                    fileName: this.filePreview.name,
                    fileSize: this.filePreview.size,
                    caption: messageText,
                    timestamp: Date.now(),
                    sender: 'user',
                    status: 'sending'
                };
                
                this.uploadFile(this.filePreview, message);
            } else {
                // Text message
                message = {
                    id: 'msg_' + Date.now(),
                    type: 'text',
                    content: messageText,
                    timestamp: Date.now(),
                    sender: 'user',
                    status: 'sending'
                };
                
                this.sendToServer('chat_message', message);
            }
            
            this.addMessage(message);
            this.currentMessage = '';
            this.filePreview = null;
            
            this.$nextTick(() => {
                this.scrollToBottom();
                this.adjustTextareaHeight();
            });
        },
        
        sendQuickMessage(text) {
            this.currentMessage = text;
            this.sendMessage();
            this.showQuickActions = false;
        },
        
        handleIncomingMessage(event) {
            const message = {
                id: event.id,
                type: event.type,
                content: event.content,
                timestamp: event.timestamp,
                sender: 'agent',
                senderName: event.senderName,
                senderAvatar: event.senderAvatar
            };
            
            this.addMessage(message);
            
            // Update agent info
            if (event.senderName) {
                this.agentInfo.name = event.senderName;
                this.agentInfo.avatar = event.senderAvatar;
            }
            
            // Increment unread count if chat is not visible
            if (!this.isChatOpen || document.hidden) {
                this.unreadCount++;
                
                // Show browser notification
                this.showNotification('New message', event.content, event.senderAvatar);
            } else {
                // Mark as read immediately
                this.markMessageAsRead(message.id);
            }
            
            this.scrollToBottom();
        },
        
        handleAgentJoined(event) {
            this.isAgentOnline = true;
            this.agentInfo = {
                name: event.agentName,
                avatar: event.agentAvatar,
                isTyping: false
            };
            
            this.addMessage({
                id: 'agent_joined_' + Date.now(),
                type: 'system',
                content: `${event.agentName} joined the chat`,
                timestamp: Date.now(),
                sender: 'system'
            });
        },
        
        handleAgentLeft(event) {
            this.agentInfo.isTyping = false;
            
            this.addMessage({
                id: 'agent_left_' + Date.now(),
                type: 'system',
                content: `${event.agentName} left the chat`,
                timestamp: Date.now(),
                sender: 'system'
            });
        },
        
        handleAgentTyping(event) {
            this.agentInfo.isTyping = event.isTyping;
            
            // Auto-hide typing indicator after 3 seconds
            if (event.isTyping) {
                setTimeout(() => {
                    this.agentInfo.isTyping = false;
                }, 3000);
                
                this.scrollToBottom();
            }
        },
        
        handleMessageStatusUpdate(event) {
            const message = this.messages.find(m => m.id === event.messageId);
            if (message) {
                message.status = event.status;
            }
        },
        
        handleTyping() {
            const now = Date.now();
            this.lastTypingTime = now;
            
            // Send typing indicator
            this.sendToServer('user_typing', {
                sessionId: this.currentSessionId,
                isTyping: true
            });
            
            // Stop typing after 3 seconds of inactivity
            if (this.typingTimeout) {
                clearTimeout(this.typingTimeout);
            }
            
            this.typingTimeout = setTimeout(() => {
                if (Date.now() - this.lastTypingTime >= 2900) {
                    this.sendToServer('user_typing', {
                        sessionId: this.currentSessionId,
                        isTyping: false
                    });
                }
            }, 3000);
        },
        
        handleEnterKey(event) {
            if (event.shiftKey) {
                // Allow new line with Shift+Enter
                return;
            } else {
                // Send message with Enter
                this.sendMessage();
            }
        },
        
        handleFileSelect(event) {
            const file = event.target.files[0];
            if (!file) return;
            
            // Validate file size
            if (file.size > this.maxFileSize) {
                alert(`File size must be less than ${this.formatFileSize(this.maxFileSize)}`);
                return;
            }
            
            // Create preview
            this.filePreview = {
                name: file.name,
                size: file.size,
                type: file.type,
                url: URL.createObjectURL(file),
                file: file
            };
        },
        
        cancelFileUpload() {
            if (this.filePreview) {
                URL.revokeObjectURL(this.filePreview.url);
                this.filePreview = null;
            }
        },
        
        async uploadFile(fileData, message) {
            this.isUploading = true;
            
            try {
                const formData = new FormData();
                formData.append('file', fileData.file);
                formData.append('sessionId', this.currentSessionId);
                formData.append('messageId', message.id);
                
                const response = await fetch('/api/chat/upload-file', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    message.content = result.fileUrl;
                    message.status = 'sent';
                } else {
                    message.status = 'failed';
                    console.error('[LiveChat] File upload failed:', result.error);
                }
            } catch (error) {
                message.status = 'failed';
                console.error('[LiveChat] File upload error:', error);
            } finally {
                this.isUploading = false;
                URL.revokeObjectURL(fileData.url);
            }
        },
        
        addMessage(message) {
            this.messages.push(message);
            this.saveChatState();
        },
        
        sendToServer(eventType, data) {
            // Send to Laravel backend via HTTP or WebSocket
            if (window.Echo && this.isConnected) {
                // Use WebSocket if available
                window.Echo.private(`user.${document.body.getAttribute('data-user-id')}`)
                    .whisper(eventType, data);
            } else {
                // Fallback to HTTP
                fetch('/api/chat/events', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        event: eventType,
                        data: data
                    })
                }).catch(error => {
                    console.error('[LiveChat] Failed to send to server:', error);
                });
            }
        },
        
        markMessagesAsRead() {
            const unreadMessages = this.messages.filter(m => 
                m.sender === 'agent' && m.status !== 'read'
            );
            
            unreadMessages.forEach(message => {
                this.markMessageAsRead(message.id);
            });
            
            this.unreadCount = 0;
        },
        
        markMessageAsRead(messageId) {
            this.sendToServer('message_read', {
                messageId: messageId,
                sessionId: this.currentSessionId
            });
        },
        
        updateUnreadCount() {
            this.unreadCount = this.messages.filter(m => 
                m.sender === 'agent' && m.status !== 'read'
            ).length;
        },
        
        scrollToBottom() {
            this.$nextTick(() => {
                if (this.$refs.messagesContainer) {
                    this.$refs.messagesContainer.scrollTop = this.$refs.messagesContainer.scrollHeight;
                }
            });
        },
        
        adjustTextareaHeight() {
            const textarea = this.$refs.messageInput;
            if (textarea) {
                textarea.style.height = 'auto';
                textarea.style.height = Math.min(textarea.scrollHeight, 100) + 'px';
            }
        },
        
        saveSessionToHistory() {
            if (this.messages.length === 0) return;
            
            const session = {
                id: this.currentSessionId,
                subject: this.extractSubject(),
                messages: [...this.messages],
                lastMessage: this.messages[this.messages.length - 1].content,
                createdAt: this.messages[0].timestamp,
                updatedAt: Date.now()
            };
            
            // Add to history (keep only last 10)
            this.chatHistory.unshift(session);
            this.chatHistory = this.chatHistory.slice(0, 10);
            
            this.saveChatState();
        },
        
        extractSubject() {
            const firstUserMessage = this.messages.find(m => 
                m.sender === 'user' && m.type === 'text' && m.content.length > 10
            );
            
            return firstUserMessage ? 
                firstUserMessage.content.substring(0, 50) + (firstUserMessage.content.length > 50 ? '...' : '') :
                'General Support';
        },
        
        loadChatSession(session) {
            this.messages = [...session.messages];
            this.currentSessionId = session.id;
            this.showChatHistory = false;
            this.openChat();
        },
        
        checkAgentPresence() {
            // Check if agents are online
            fetch('/api/chat/agent-status')
                .then(response => response.json())
                .then(data => {
                    this.isAgentOnline = data.agentsOnline > 0;
                })
                .catch(error => {
                    console.error('[LiveChat] Failed to check agent presence:', error);
                });
        },
        
        showNotification(title, body, icon) {
            if ('Notification' in window && Notification.permission === 'granted') {
                const notification = new Notification(title, {
                    body: body.substring(0, 100),
                    icon: icon || '/favicon.ico',
                    tag: 'hdtickets-chat'
                });
                
                notification.onclick = () => {
                    window.focus();
                    this.openChat();
                    notification.close();
                };
                
                setTimeout(() => notification.close(), 5000);
            } else if ('Notification' in window && Notification.permission === 'default') {
                Notification.requestPermission();
            }
        },
        
        // Utility methods
        formatMessageTime(timestamp) {
            const date = new Date(timestamp);
            const now = new Date();
            
            if (date.toDateString() === now.toDateString()) {
                return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            } else {
                return date.toLocaleDateString([], { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
            }
        },
        
        formatDate(timestamp) {
            return new Date(timestamp).toLocaleDateString();
        },
        
        formatFileSize(bytes) {
            if (bytes === 0) return '0B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + sizes[i];
        }
    };
}
</script>