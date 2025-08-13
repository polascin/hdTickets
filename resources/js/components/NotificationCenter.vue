<template>
    <div class="notification-center" :class="{ 'mobile': isMobile }">
        <div class="notification-header">
            <h4 class="text-lg font-semibold text-gray-800 dark:text-white">Sports Ticket Alerts</h4>
            <div class="header-actions">
                <span v-if="unreadCount > 0" class="notification-badge">{{ unreadCount }}</span>
                <button @click="markAllAsRead" class="mark-all-read-btn" v-if="unreadCount > 0">
                    Mark All Read
                </button>
            </div>
        </div>
        
        <!-- Filter Tabs -->
        <div class="notification-filters">
            <button 
                v-for="filter in notificationFilters" 
                :key="filter.key"
                @click="setActiveFilter(filter.key)"
                :class="['filter-btn', { 'active': activeFilter === filter.key }]"
            >
                {{ filter.label }}
            </button>
        </div>
        
        <div class="notification-list">
            <div v-if="loading" class="loading-state">
                <p class="text-gray-600">Loading notifications...</p>
            </div>
            
            <div v-else-if="notifications.length > 0">
                <div
                    v-for="notification in notifications"
                    :key="notification.id"
                    :class="['notification-item', { 'unread': !notification.read_at }]"
                    @click="markAsRead(notification.id)"
                >
                    <div class="notification-content">
                        <div class="notification-title">{{ notification.data.title || 'Notification' }}</div>
                        <div class="notification-message">{{ notification.data.message || notification.data.body }}</div>
                        <div class="notification-time">{{ formatTime(notification.created_at) }}</div>
                    </div>
                    <div v-if="!notification.read_at" class="unread-indicator"></div>
                </div>
                
                <div v-if="hasMore" class="load-more">
                    <button @click="loadMore" :disabled="loadingMore" class="btn-load-more">
                        {{ loadingMore ? 'Loading...' : 'Load More' }}
                    </button>
                </div>
            </div>
            
            <div v-else class="empty-state">
                <p class="text-gray-600">No notifications at the moment</p>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, computed } from 'vue';
import { useWindowSize } from '@vueuse/core';
import axios from 'axios';

export default {
    name: 'NotificationCenter',
    setup() {
        const { width } = useWindowSize();
        const isMobile = computed(() => width.value < 768);
        
        const notifications = ref([]);
        const unreadCount = ref(0);
        const loading = ref(true);
        const loadingMore = ref(false);
        const hasMore = ref(false);
        const currentPage = ref(1);
        const activeFilter = ref('all');
        
        const notificationFilters = ref([
            { key: 'all', label: 'All' },
            { key: 'price_alerts', label: 'Price Alerts' },
            { key: 'availability', label: 'Availability' },
            { key: 'events', label: 'Events' },
            { key: 'watchlist', label: 'Watchlist' }
        ]);
        
        return {
            isMobile,
            notifications,
            unreadCount,
            loading,
            loadingMore,
            hasMore,
            currentPage,
            activeFilter,
            notificationFilters
        };
    },
    data() {
        return {};
    },
    mounted() {
        this.fetchNotifications();
        this.setupRealTimeUpdates();
    },
    methods: {
        async fetchNotifications(page = 1) {
            try {
                this.loading = page === 1;
                this.loadingMore = page > 1;
                
                const response = await axios.get(`/api/notifications?page=${page}`);
                const data = response.data;
                
                if (page === 1) {
                    this.notifications = data.data;
                } else {
                    this.notifications = [...this.notifications, ...data.data];
                }
                
                this.unreadCount = data.unread_count;
                this.hasMore = data.current_page < data.last_page;
                this.currentPage = data.current_page;
                
            } catch (error) {
                console.error('Error fetching notifications:', error);
                this.$emit('error', 'Failed to load notifications');
            } finally {
                this.loading = false;
                this.loadingMore = false;
            }
        },
        
        async markAsRead(notificationId) {
            try {
                await axios.patch(`/api/notifications/${notificationId}/read`);
                
                // Update local state
                const notification = this.notifications.find(n => n.id === notificationId);
                if (notification && !notification.read_at) {
                    notification.read_at = new Date().toISOString();
                    this.unreadCount = Math.max(0, this.unreadCount - 1);
                }
                
                this.$emit('notification-read', { notificationId, unreadCount: this.unreadCount });
                
            } catch (error) {
                console.error('Error marking notification as read:', error);
            }
        },
        
        async markAllAsRead() {
            try {
                await axios.patch('/api/notifications/mark-all-read');
                
                // Update local state
                this.notifications.forEach(notification => {
                    if (!notification.read_at) {
                        notification.read_at = new Date().toISOString();
                    }
                });
                
                this.unreadCount = 0;
                this.$emit('all-notifications-read');
                
            } catch (error) {
                console.error('Error marking all notifications as read:', error);
            }
        },
        
        loadMore() {
            if (this.hasMore && !this.loadingMore) {
                this.fetchNotifications(this.currentPage + 1);
            }
        },
        
        setupRealTimeUpdates() {
            // Listen for real-time notifications if Echo is available
            if (typeof window.Echo !== 'undefined' && window.Laravel && window.Laravel.user) {
                window.Echo.private(`user.${window.Laravel.user.id}`)
                    .notification((notification) => {
                        // Add new notification to the beginning of the list
                        this.notifications.unshift({
                            id: notification.id,
                            data: notification,
                            created_at: new Date().toISOString(),
                            read_at: null
                        });
                        
                        this.unreadCount++;
                        this.$emit('new-notification', { notification, unreadCount: this.unreadCount });
                    });
            }
        },
        
        setActiveFilter(filterKey) {
            this.activeFilter = filterKey;
            this.fetchNotifications(1);
        },
        
        formatTime(timestamp) {
            const date = new Date(timestamp);
            const now = new Date();
            const diffMs = now - date;
            const diffMins = Math.floor(diffMs / 60000);
            const diffHours = Math.floor(diffMins / 60);
            const diffDays = Math.floor(diffHours / 24);
            
            if (diffMins < 1) {
                return 'Just now';
            } else if (diffMins < 60) {
                return `${diffMins} minute${diffMins > 1 ? 's' : ''} ago`;
            } else if (diffHours < 24) {
                return `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`;
            } else if (diffDays < 7) {
                return `${diffDays} day${diffDays > 1 ? 's' : ''} ago`;
            } else {
                return date.toLocaleDateString();
            }
        }
    }
};
</script>

<style scoped>
.notification-center {
    max-width: 400px;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.notification-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    border-bottom: 1px solid #e5e7eb;
}

.notification-badge {
    background-color: #ef4444;
    color: white;
    border-radius: 9999px;
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
    font-weight: 600;
    min-width: 1.5rem;
    text-align: center;
}

.notification-list {
    max-height: 400px;
    overflow-y: auto;
}

.loading-state, .empty-state {
    padding: 2rem;
    text-align: center;
}

.notification-item {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 1rem;
    border-bottom: 1px solid #f3f4f6;
    cursor: pointer;
    transition: background-color 0.2s;
}

.notification-item:hover {
    background-color: #f9fafb;
}

.notification-item.unread {
    background-color: #eff6ff;
}

.notification-item.unread:hover {
    background-color: #dbeafe;
}

.notification-content {
    flex: 1;
}

.notification-title {
    font-weight: 600;
    color: #111827;
    margin-bottom: 0.25rem;
}

.notification-message {
    color: #6b7280;
    font-size: 0.875rem;
    margin-bottom: 0.5rem;
    line-height: 1.4;
}

.notification-time {
    font-size: 0.75rem;
    color: #9ca3af;
}

.unread-indicator {
    width: 0.5rem;
    height: 0.5rem;
    background-color: #3b82f6;
    border-radius: 50%;
    margin-top: 0.25rem;
    flex-shrink: 0;
}

.load-more {
    padding: 1rem;
    text-align: center;
    border-top: 1px solid #f3f4f6;
}

.btn-load-more {
    color: #3b82f6;
    background: none;
    border: none;
    cursor: pointer;
    font-weight: 500;
    font-size: 0.875rem;
}

.btn-load-more:hover:not(:disabled) {
    color: #2563eb;
}

.btn-load-more:disabled {
    color: #9ca3af;
    cursor: not-allowed;
}
</style>
