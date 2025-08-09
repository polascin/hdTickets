/**
 * HD Tickets - Customer Dashboard JavaScript Module
 * Comprehensive Sport Events Entry Tickets Monitoring System
 * 
 * Features:
 * - WebSocket connection for real-time ticket updates
 * - Chart.js integration for ticket price trends
 * - Countdown timers for event starts
 * - Notification system for price drops and availability
 * - Lazy loading for ticket images
 * - Progressive enhancement for non-JS users
 * - Touch gestures for mobile navigation
 */

class CustomerDashboard {
    constructor() {
        this.ws = null;
        this.charts = {};
        this.timers = new Map();
        this.notifications = [];
        this.touchStartX = 0;
        this.touchStartY = 0;
        this.lazyLoadObserver = null;
        
        this.init();
    }

    /**
     * Initialize the dashboard
     */
    init() {
        // Check for JavaScript support and enhance progressively
        document.body.classList.add('js-enabled');
        
        // Initialize all components
        this.initWebSocket();
        this.initCharts();
        this.initCountdowns();
        this.initNotificationSystem();
        this.initLazyLoading();
        this.initTouchGestures();
        this.initEventListeners();
        
        console.log('Customer Dashboard initialized');
    }

    /**
     * WebSocket Connection for Real-time Updates
     */
    initWebSocket() {
        const protocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
        const wsUrl = `${protocol}//${window.location.host}/ws/tickets`;
        
        try {
            this.ws = new WebSocket(wsUrl);
            
            this.ws.onopen = () => {
                console.log('WebSocket connected');
                this.showNotification('Connected to real-time updates', 'success');
            };
            
            this.ws.onmessage = (event) => {
                this.handleWebSocketMessage(JSON.parse(event.data));
            };
            
            this.ws.onclose = () => {
                console.log('WebSocket disconnected');
                this.showNotification('Disconnected from real-time updates', 'warning');
                // Attempt to reconnect after 3 seconds
                setTimeout(() => this.initWebSocket(), 3000);
            };
            
            this.ws.onerror = (error) => {
                console.error('WebSocket error:', error);
                this.showNotification('Connection error occurred', 'error');
            };
        } catch (error) {
            console.error('Failed to initialize WebSocket:', error);
        }
    }

    /**
     * Handle incoming WebSocket messages
     */
    handleWebSocketMessage(data) {
        switch (data.type) {
            case 'ticket_price_update':
                this.updateTicketPrice(data.ticketId, data.newPrice, data.oldPrice);
                break;
            case 'ticket_availability_update':
                this.updateTicketAvailability(data.ticketId, data.available);
                break;
            case 'new_ticket':
                this.addNewTicket(data.ticket);
                break;
            case 'price_trend_data':
                this.updatePriceChart(data.ticketId, data.trends);
                break;
            default:
                console.log('Unknown message type:', data.type);
        }
    }

    /**
     * Chart.js Integration for Price Trends
     */
    initCharts() {
        const chartElements = document.querySelectorAll('.price-trend-chart');
        
        chartElements.forEach(element => {
            const ticketId = element.dataset.ticketId;
            const ctx = element.getContext('2d');
            
            this.charts[ticketId] = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Price Trend',
                        data: [],
                        borderColor: '#007bff',
                        backgroundColor: 'rgba(0, 123, 255, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    scales: {
                        y: {
                            beginAtZero: false,
                            ticks: {
                                callback: function(value) {
                                    return '$' + value.toFixed(2);
                                }
                            }
                        },
                        x: {
                            type: 'time',
                            time: {
                                unit: 'hour',
                                displayFormats: {
                                    hour: 'MMM DD, HH:mm'
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Price: $' + context.parsed.y.toFixed(2);
                                }
                            }
                        },
                        legend: {
                            display: false
                        }
                    }
                }
            });
        });
    }

    /**
     * Update price chart with new data
     */
    updatePriceChart(ticketId, trends) {
        const chart = this.charts[ticketId];
        if (!chart) return;
        
        chart.data.labels = trends.map(t => new Date(t.timestamp));
        chart.data.datasets[0].data = trends.map(t => t.price);
        chart.update();
    }

    /**
     * Countdown Timers for Event Starts
     */
    initCountdowns() {
        const countdownElements = document.querySelectorAll('.event-countdown');
        
        countdownElements.forEach(element => {
            const eventDate = new Date(element.dataset.eventDate);
            const eventId = element.dataset.eventId;
            
            this.startCountdown(eventId, eventDate, element);
        });
    }

    /**
     * Start countdown timer for a specific event
     */
    startCountdown(eventId, eventDate, element) {
        const timer = setInterval(() => {
            const now = new Date().getTime();
            const distance = eventDate.getTime() - now;
            
            if (distance < 0) {
                clearInterval(timer);
                element.innerHTML = '<span class="event-started">Event Started</span>';
                this.timers.delete(eventId);
                return;
            }
            
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            element.innerHTML = `
                <div class="countdown-display">
                    <div class="countdown-item">
                        <span class="countdown-number">${days}</span>
                        <span class="countdown-label">Days</span>
                    </div>
                    <div class="countdown-item">
                        <span class="countdown-number">${hours}</span>
                        <span class="countdown-label">Hours</span>
                    </div>
                    <div class="countdown-item">
                        <span class="countdown-number">${minutes}</span>
                        <span class="countdown-label">Min</span>
                    </div>
                    <div class="countdown-item">
                        <span class="countdown-number">${seconds}</span>
                        <span class="countdown-label">Sec</span>
                    </div>
                </div>
            `;
        }, 1000);
        
        this.timers.set(eventId, timer);
    }

    /**
     * Notification System for Price Drops and Availability
     */
    initNotificationSystem() {
        // Create notification container if it doesn't exist
        if (!document.getElementById('notification-container')) {
            const container = document.createElement('div');
            container.id = 'notification-container';
            container.className = 'notification-container';
            document.body.appendChild(container);
        }
        
        // Request notification permission
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }
    }

    /**
     * Show notification to user
     */
    showNotification(message, type = 'info', persistent = false) {
        const notification = {
            id: Date.now(),
            message,
            type,
            persistent
        };
        
        this.notifications.push(notification);
        this.displayNotification(notification);
        
        // Show browser notification for important updates
        if ((type === 'success' || type === 'warning') && 'Notification' in window && Notification.permission === 'granted') {
            new Notification('HD Tickets Update', {
                body: message,
                icon: '/images/favicon.ico'
            });
        }
    }

    /**
     * Display notification in UI
     */
    displayNotification(notification) {
        const container = document.getElementById('notification-container');
        const element = document.createElement('div');
        element.className = `notification notification-${notification.type}`;
        element.innerHTML = `
            <div class="notification-content">
                <span class="notification-message">${notification.message}</span>
                <button class="notification-close" onclick="dashboard.dismissNotification(${notification.id})">×</button>
            </div>
        `;
        
        container.appendChild(element);
        
        // Auto-dismiss non-persistent notifications
        if (!notification.persistent) {
            setTimeout(() => this.dismissNotification(notification.id), 5000);
        }
    }

    /**
     * Dismiss notification
     */
    dismissNotification(id) {
        const notification = document.querySelector(`.notification[data-id="${id}"]`);
        if (notification) {
            notification.remove();
        }
        this.notifications = this.notifications.filter(n => n.id !== id);
    }

    /**
     * Update ticket price and show notification if needed
     */
    updateTicketPrice(ticketId, newPrice, oldPrice) {
        const ticketElement = document.querySelector(`[data-ticket-id="${ticketId}"]`);
        if (!ticketElement) return;
        
        const priceElement = ticketElement.querySelector('.ticket-price');
        if (priceElement) {
            priceElement.textContent = `$${newPrice.toFixed(2)}`;
            
            // Add animation class for price change
            if (newPrice < oldPrice) {
                priceElement.classList.add('price-drop');
                this.showNotification(`Price dropped for ticket #${ticketId}: $${newPrice.toFixed(2)}`, 'success');
            } else if (newPrice > oldPrice) {
                priceElement.classList.add('price-increase');
            }
            
            // Remove animation class after animation completes
            setTimeout(() => {
                priceElement.classList.remove('price-drop', 'price-increase');
            }, 2000);
        }
    }

    /**
     * Update ticket availability
     */
    updateTicketAvailability(ticketId, available) {
        const ticketElement = document.querySelector(`[data-ticket-id="${ticketId}"]`);
        if (!ticketElement) return;
        
        const availabilityElement = ticketElement.querySelector('.ticket-availability');
        if (availabilityElement) {
            availabilityElement.textContent = available ? 'Available' : 'Sold Out';
            availabilityElement.className = `ticket-availability ${available ? 'available' : 'sold-out'}`;
            
            if (available) {
                this.showNotification(`Ticket #${ticketId} is now available!`, 'success');
            }
        }
    }

    /**
     * Lazy Loading for Ticket Images
     */
    initLazyLoading() {
        if ('IntersectionObserver' in window) {
            this.lazyLoadObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        this.loadImage(entry.target);
                        this.lazyLoadObserver.unobserve(entry.target);
                    }
                });
            }, {
                rootMargin: '50px'
            });
            
            // Observe all lazy images
            document.querySelectorAll('img[data-src]').forEach(img => {
                this.lazyLoadObserver.observe(img);
            });
        } else {
            // Fallback for browsers without IntersectionObserver
            this.loadAllImages();
        }
    }

    /**
     * Load individual image
     */
    loadImage(img) {
        img.src = img.dataset.src;
        img.classList.add('loaded');
        img.onload = () => {
            img.classList.add('fade-in');
        };
    }

    /**
     * Fallback: Load all images immediately
     */
    loadAllImages() {
        document.querySelectorAll('img[data-src]').forEach(img => {
            this.loadImage(img);
        });
    }

    /**
     * Touch Gestures for Mobile Navigation
     */
    initTouchGestures() {
        const dashboardContainer = document.querySelector('.dashboard-container');
        if (!dashboardContainer) return;
        
        dashboardContainer.addEventListener('touchstart', (e) => {
            this.touchStartX = e.touches[0].clientX;
            this.touchStartY = e.touches[0].clientY;
        }, { passive: true });
        
        dashboardContainer.addEventListener('touchend', (e) => {
            if (!this.touchStartX || !this.touchStartY) return;
            
            const touchEndX = e.changedTouches[0].clientX;
            const touchEndY = e.changedTouches[0].clientY;
            
            const diffX = this.touchStartX - touchEndX;
            const diffY = this.touchStartY - touchEndY;
            
            // Horizontal swipe detection
            if (Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > 50) {
                if (diffX > 0) {
                    this.handleSwipeLeft();
                } else {
                    this.handleSwipeRight();
                }
            }
            
            // Vertical swipe detection
            if (Math.abs(diffY) > Math.abs(diffX) && Math.abs(diffY) > 50) {
                if (diffY > 0) {
                    this.handleSwipeUp();
                } else {
                    this.handleSwipeDown();
                }
            }
            
            this.touchStartX = 0;
            this.touchStartY = 0;
        }, { passive: true });
    }

    /**
     * Handle swipe gestures
     */
    handleSwipeLeft() {
        // Navigate to next page/section
        const nextButton = document.querySelector('.nav-next');
        if (nextButton && !nextButton.disabled) {
            nextButton.click();
        }
    }

    handleSwipeRight() {
        // Navigate to previous page/section
        const prevButton = document.querySelector('.nav-prev');
        if (prevButton && !prevButton.disabled) {
            prevButton.click();
        }
    }

    handleSwipeUp() {
        // Scroll to top or show filters
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    handleSwipeDown() {
        // Refresh data
        this.refreshDashboard();
    }

    /**
     * Initialize Event Listeners
     */
    initEventListeners() {
        // Filter controls
        document.addEventListener('change', (e) => {
            if (e.target.matches('.filter-control')) {
                this.handleFilterChange(e.target);
            }
        });
        
        // Search functionality
        const searchInput = document.querySelector('.ticket-search');
        if (searchInput) {
            let searchTimeout;
            searchInput.addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    this.handleSearch(e.target.value);
                }, 300);
            });
        }
        
        // Refresh button
        const refreshButton = document.querySelector('.refresh-dashboard');
        if (refreshButton) {
            refreshButton.addEventListener('click', () => {
                this.refreshDashboard();
            });
        }
        
        // Window events
        window.addEventListener('beforeunload', () => {
            this.cleanup();
        });
        
        window.addEventListener('online', () => {
            this.showNotification('Connection restored', 'success');
            this.initWebSocket();
        });
        
        window.addEventListener('offline', () => {
            this.showNotification('Connection lost', 'warning');
        });
    }

    /**
     * Handle filter changes
     */
    handleFilterChange(filterElement) {
        const filterType = filterElement.dataset.filter;
        const filterValue = filterElement.value;
        
        // Send filter update via WebSocket
        if (this.ws && this.ws.readyState === WebSocket.OPEN) {
            this.ws.send(JSON.stringify({
                type: 'filter_update',
                filter: filterType,
                value: filterValue
            }));
        }
        
        // Apply local filtering for immediate feedback
        this.applyLocalFilter(filterType, filterValue);
    }

    /**
     * Apply local filtering
     */
    applyLocalFilter(filterType, filterValue) {
        const tickets = document.querySelectorAll('.ticket-card');
        
        tickets.forEach(ticket => {
            const shouldShow = this.ticketMatchesFilter(ticket, filterType, filterValue);
            ticket.style.display = shouldShow ? 'block' : 'none';
        });
    }

    /**
     * Check if ticket matches filter
     */
    ticketMatchesFilter(ticket, filterType, filterValue) {
        if (!filterValue) return true;
        
        switch (filterType) {
            case 'sport':
                return ticket.dataset.sport === filterValue;
            case 'price':
                const price = parseFloat(ticket.dataset.price);
                const [min, max] = filterValue.split('-').map(parseFloat);
                return price >= min && (isNaN(max) || price <= max);
            case 'date':
                const eventDate = new Date(ticket.dataset.eventDate);
                const filterDate = new Date(filterValue);
                return eventDate.toDateString() === filterDate.toDateString();
            default:
                return true;
        }
    }

    /**
     * Handle search functionality
     */
    handleSearch(query) {
        const tickets = document.querySelectorAll('.ticket-card');
        const searchTerm = query.toLowerCase();
        
        tickets.forEach(ticket => {
            const title = ticket.querySelector('.ticket-title')?.textContent.toLowerCase() || '';
            const venue = ticket.querySelector('.ticket-venue')?.textContent.toLowerCase() || '';
            const sport = ticket.dataset.sport?.toLowerCase() || '';
            
            const matches = title.includes(searchTerm) || 
                          venue.includes(searchTerm) || 
                          sport.includes(searchTerm);
            
            ticket.style.display = matches ? 'block' : 'none';
        });
    }

    /**
     * Refresh dashboard data
     */
    refreshDashboard() {
        this.showNotification('Refreshing dashboard...', 'info');
        
        // Send refresh request via WebSocket
        if (this.ws && this.ws.readyState === WebSocket.OPEN) {
            this.ws.send(JSON.stringify({
                type: 'refresh_dashboard'
            }));
        }
        
        // Reload charts data
        Object.keys(this.charts).forEach(ticketId => {
            this.requestPriceTrendData(ticketId);
        });
    }

    /**
     * Request price trend data for a ticket
     */
    requestPriceTrendData(ticketId) {
        if (this.ws && this.ws.readyState === WebSocket.OPEN) {
            this.ws.send(JSON.stringify({
                type: 'get_price_trends',
                ticketId: ticketId
            }));
        }
    }

    /**
     * Add new ticket to dashboard
     */
    addNewTicket(ticket) {
        const ticketContainer = document.querySelector('.tickets-grid');
        if (!ticketContainer) return;
        
        const ticketElement = this.createTicketElement(ticket);
        ticketContainer.appendChild(ticketElement);
        
        // Initialize lazy loading for new images
        const images = ticketElement.querySelectorAll('img[data-src]');
        images.forEach(img => {
            if (this.lazyLoadObserver) {
                this.lazyLoadObserver.observe(img);
            } else {
                this.loadImage(img);
            }
        });
        
        // Initialize countdown for new ticket
        const countdown = ticketElement.querySelector('.event-countdown');
        if (countdown) {
            const eventDate = new Date(countdown.dataset.eventDate);
            const eventId = countdown.dataset.eventId;
            this.startCountdown(eventId, eventDate, countdown);
        }
        
        this.showNotification(`New ticket available: ${ticket.title}`, 'success');
    }

    /**
     * Create ticket element HTML
     */
    createTicketElement(ticket) {
        const element = document.createElement('div');
        element.className = 'ticket-card';
        element.dataset.ticketId = ticket.id;
        element.dataset.sport = ticket.sport;
        element.dataset.price = ticket.price;
        element.dataset.eventDate = ticket.eventDate;
        
        element.innerHTML = `
            <div class="ticket-image">
                <img data-src="${ticket.imageUrl}" alt="${ticket.title}" class="lazy-load">
            </div>
            <div class="ticket-info">
                <h3 class="ticket-title">${ticket.title}</h3>
                <p class="ticket-venue">${ticket.venue}</p>
                <div class="ticket-details">
                    <span class="ticket-price">$${ticket.price.toFixed(2)}</span>
                    <span class="ticket-availability ${ticket.available ? 'available' : 'sold-out'}">
                        ${ticket.available ? 'Available' : 'Sold Out'}
                    </span>
                </div>
                <div class="event-countdown" data-event-id="${ticket.id}" data-event-date="${ticket.eventDate}">
                    <!-- Countdown will be populated by JavaScript -->
                </div>
                <div class="price-chart-container">
                    <canvas class="price-trend-chart" data-ticket-id="${ticket.id}" width="300" height="150"></canvas>
                </div>
            </div>
        `;
        
        return element;
    }

    /**
     * Cleanup resources
     */
    cleanup() {
        // Clear all timers
        this.timers.forEach((timer) => {
            clearInterval(timer);
        });
        this.timers.clear();
        
        // Close WebSocket connection
        if (this.ws) {
            this.ws.close();
        }
        
        // Disconnect observers
        if (this.lazyLoadObserver) {
            this.lazyLoadObserver.disconnect();
        }
        
        // Destroy charts
        Object.values(this.charts).forEach(chart => {
            chart.destroy();
        });
        this.charts = {};
    }
}

// Initialize dashboard when DOM is ready
let dashboard;

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        dashboard = new CustomerDashboard();
    });
} else {
    dashboard = new CustomerDashboard();
}

// Progressive Enhancement: Fallback for non-JS users
// Remove 'no-js' class if it exists and add 'js' class
document.documentElement.classList.remove('no-js');
document.documentElement.classList.add('js');

// Export for global access
window.dashboard = dashboard;
