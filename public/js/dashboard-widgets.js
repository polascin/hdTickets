/**
 * HD Tickets - Interactive Dashboard Widgets JavaScript
 * Sports Events Entry Tickets Monitoring System
 * Version: 1.0.0
 * 
 * Features:
 * - Circular progress indicators for purchase queue status
 * - Heat map calendar showing event density
 * - Interactive seat map previews
 * - Price comparison charts across platforms
 * - Alert management dashboard with toggle controls
 * - Quick action buttons with haptic feedback
 */

class DashboardWidgets {
    constructor() {
        this.widgets = {};
        this.charts = {};
        this.heatMapData = {};
        this.seatMapData = {};
        this.priceData = {};
        this.alerts = {};
        
        // Haptic feedback support detection
        this.hasHapticFeedback = 'vibrate' in navigator;
        
        this.init();
    }

    /**
     * Initialize all dashboard widgets
     */
    init() {
        console.log('Initializing Dashboard Widgets...');
        
        // Initialize all widget types
        this.initCircularProgress();
        this.initHeatMapCalendar();
        this.initSeatMap();
        this.initPriceComparison();
        this.initAlertDashboard();
        this.initHapticButtons();
        
        // Set up real-time updates
        this.setupRealTimeUpdates();
        
        // Set up event listeners
        this.setupEventListeners();
        
        console.log('Dashboard Widgets initialized successfully');
    }

    /**
     * Circular Progress Indicators for Purchase Queue Status
     */
    initCircularProgress() {
        const progressElements = document.querySelectorAll('.circular-progress');
        
        progressElements.forEach((element, index) => {
            const progressId = `progress-${index}`;
            this.widgets[progressId] = {
                element: element,
                circle: element.querySelector('.progress-circle-fill'),
                label: element.querySelector('.progress-value'),
                text: element.querySelector('.progress-text'),
                value: 0,
                target: 0,
                animationId: null
            };
            
            // Initialize with data attributes
            const initialValue = parseInt(element.dataset.value) || 0;
            const maxValue = parseInt(element.dataset.max) || 100;
            const label = element.dataset.label || 'Progress';
            
            this.updateCircularProgress(progressId, initialValue, maxValue, label);
        });
    }

    /**
     * Update circular progress indicator
     */
    updateCircularProgress(progressId, value, max = 100, label = 'Progress') {
        const widget = this.widgets[progressId];
        if (!widget) return;

        const percentage = Math.min(Math.max((value / max) * 100, 0), 100);
        const circumference = 2 * Math.PI * 60; // radius = 60
        const strokeDasharray = `${(percentage / 100) * circumference} ${circumference}`;
        
        // Animate the progress
        if (widget.animationId) {
            cancelAnimationFrame(widget.animationId);
        }
        
        this.animateProgress(widget, strokeDasharray, value, label, percentage);
    }

    /**
     * Animate progress indicator with modern easing
     */
    animateProgress(widget, targetStrokeDasharray, targetValue, label, percentage) {
        const startValue = widget.value || 0;
        const valueChange = targetValue - startValue;
        const duration = 800; // Reduced to 0.8 seconds for snappier feel
        const startTime = performance.now();
        
        const animate = (currentTime) => {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            // Modern cubic-bezier easing (ease-out-quart)
            const eased = 1 - Math.pow(1 - progress, 4);
            
            const currentValue = Math.round(startValue + (valueChange * eased));
            const currentPercentage = (currentValue / 100) * 100;
            const circumference = 2 * Math.PI * 60;
            const currentStrokeDasharray = `${(currentPercentage / 100) * circumference} ${circumference}`;
            
            widget.circle.style.strokeDasharray = currentStrokeDasharray;
            widget.circle.style.transition = 'stroke 0.3s ease';
            widget.label.textContent = currentValue;
            widget.text.textContent = label;
            widget.value = currentValue;
            
            // Smooth color transitions based on percentage
            widget.circle.classList.remove('warning', 'error', 'success');
            if (percentage > 80) {
                widget.circle.classList.add('error');
            } else if (percentage > 60) {
                widget.circle.classList.add('warning');
            } else if (percentage > 30) {
                widget.circle.classList.add('success');
            }
            
            if (progress < 1) {
                widget.animationId = requestAnimationFrame(animate);
            }
        };
        
        widget.animationId = requestAnimationFrame(animate);
    }

    /**
     * Heat Map Calendar for Event Density
     */
    initHeatMapCalendar() {
        const heatMapElements = document.querySelectorAll('.heat-map-calendar');
        
        heatMapElements.forEach((element, index) => {
            const heatMapId = `heatmap-${index}`;
            this.widgets[heatMapId] = {
                element: element,
                days: [],
                data: {}
            };
            
            this.generateCalendarDays(heatMapId);
            this.loadHeatMapData(heatMapId);
        });
    }

    /**
     * Generate calendar days
     */
    generateCalendarDays(heatMapId) {
        const widget = this.widgets[heatMapId];
        const today = new Date();
        const currentMonth = today.getMonth();
        const currentYear = today.getFullYear();
        
        // Clear existing content
        widget.element.innerHTML = '';
        
        // Get first day of month and days in month
        const firstDay = new Date(currentYear, currentMonth, 1);
        const lastDay = new Date(currentYear, currentMonth + 1, 0);
        const daysInMonth = lastDay.getDate();
        const startingDayOfWeek = firstDay.getDay();
        
        // Add empty cells for days before the first day of the month
        for (let i = 0; i < startingDayOfWeek; i++) {
            const emptyDay = document.createElement('div');
            emptyDay.className = 'calendar-day empty';
            widget.element.appendChild(emptyDay);
        }
        
        // Add days of the month
        for (let day = 1; day <= daysInMonth; day++) {
            const dayElement = document.createElement('div');
            dayElement.className = 'calendar-day';
            dayElement.textContent = day;
            dayElement.dataset.date = `${currentYear}-${String(currentMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            
            // Mark today
            if (day === today.getDate()) {
                dayElement.classList.add('today');
            }
            
            // Add tooltip
            const tooltip = document.createElement('div');
            tooltip.className = 'event-tooltip';
            tooltip.textContent = 'Loading...';
            dayElement.appendChild(tooltip);
            
            // Add event listeners
            dayElement.addEventListener('mouseenter', (e) => this.showDateTooltip(e, heatMapId));
            dayElement.addEventListener('mouseleave', (e) => this.hideDateTooltip(e));
            dayElement.addEventListener('click', (e) => this.handleDateClick(e, heatMapId));
            
            widget.element.appendChild(dayElement);
            widget.days.push(dayElement);
        }
    }

    /**
     * Load heat map data
     */
    async loadHeatMapData(heatMapId) {
        try {
            // Simulate API call - replace with actual endpoint
            const response = await fetch('/api/event-density');
            const data = await response.json();
            
            this.heatMapData[heatMapId] = data;
            this.updateHeatMapDisplay(heatMapId);
        } catch (error) {
            console.error('Failed to load heat map data:', error);
            // Use mock data for demonstration
            this.generateMockHeatMapData(heatMapId);
        }
    }

    /**
     * Generate mock heat map data
     */
    generateMockHeatMapData(heatMapId) {
        const widget = this.widgets[heatMapId];
        const mockData = {};
        
        widget.days.forEach(day => {
            const date = day.dataset.date;
            if (date) {
                const eventCount = Math.floor(Math.random() * 10);
                mockData[date] = {
                    count: eventCount,
                    events: this.generateMockEvents(eventCount),
                    density: eventCount > 6 ? 'high' : eventCount > 3 ? 'medium' : eventCount > 0 ? 'low' : 'none'
                };
            }
        });
        
        this.heatMapData[heatMapId] = mockData;
        this.updateHeatMapDisplay(heatMapId);
    }

    /**
     * Generate mock events
     */
    generateMockEvents(count) {
        const mockEvents = [
            'Football vs Arsenal', 'Basketball Championship', 'Tennis Open',
            'Baseball World Series', 'Soccer Premier League', 'Hockey Finals',
            'Cricket Test Match', 'Golf Tournament', 'Racing Grand Prix',
            'Boxing Championship'
        ];
        
        return Array(count).fill(null).map((_, i) => 
            mockEvents[Math.floor(Math.random() * mockEvents.length)]
        );
    }

    /**
     * Update heat map display
     */
    updateHeatMapDisplay(heatMapId) {
        const widget = this.widgets[heatMapId];
        const data = this.heatMapData[heatMapId];
        
        widget.days.forEach(day => {
            const date = day.dataset.date;
            if (date && data[date]) {
                const dayData = data[date];
                
                // Remove existing classes
                day.classList.remove('has-events', 'low-density', 'medium-density', 'high-density');
                
                // Add appropriate class based on density
                if (dayData.count > 0) {
                    day.classList.add('has-events', `${dayData.density}-density`);
                }
                
                // Update tooltip
                const tooltip = day.querySelector('.event-tooltip');
                if (tooltip) {
                    if (dayData.count === 0) {
                        tooltip.textContent = 'No events';
                    } else {
                        tooltip.textContent = `${dayData.count} event${dayData.count > 1 ? 's' : ''}`;
                    }
                }
            }
        });
    }

    /**
     * Show date tooltip
     */
    showDateTooltip(event, heatMapId) {
        const dayElement = event.target;
        const date = dayElement.dataset.date;
        const data = this.heatMapData[heatMapId];
        
        if (date && data[date]) {
            const dayData = data[date];
            const tooltip = dayElement.querySelector('.event-tooltip');
            
            if (dayData.count === 0) {
                tooltip.textContent = 'No events scheduled';
            } else {
                tooltip.textContent = `${dayData.count} event${dayData.count > 1 ? 's' : ''}: ${dayData.events.slice(0, 2).join(', ')}${dayData.events.length > 2 ? '...' : ''}`;
            }
        }
    }

    /**
     * Hide date tooltip
     */
    hideDateTooltip(event) {
        // Tooltip hiding is handled by CSS
    }

    /**
     * Handle date click
     */
    handleDateClick(event, heatMapId) {
        const dayElement = event.target;
        const date = dayElement.dataset.date;
        const data = this.heatMapData[heatMapId];
        
        if (date && data[date] && data[date].count > 0) {
            this.triggerHapticFeedback('light');
            this.showEventDetails(date, data[date]);
        }
    }

    /**
     * Show event details
     */
    showEventDetails(date, dayData) {
        // This would typically open a modal or navigate to a details page
        console.log(`Events for ${date}:`, dayData.events);
        alert(`Events for ${date}:\n${dayData.events.join('\n')}`);
    }

    /**
     * Interactive Seat Map Previews
     */
    initSeatMap() {
        const seatMapElements = document.querySelectorAll('.seat-map');
        
        seatMapElements.forEach((element, index) => {
            const seatMapId = `seatmap-${index}`;
            this.widgets[seatMapId] = {
                element: element,
                seats: [],
                selectedSeats: [],
                seatLayout: {}
            };
            
            this.generateSeatMap(seatMapId);
            this.loadSeatMapData(seatMapId);
        });
    }

    /**
     * Generate seat map
     */
    generateSeatMap(seatMapId) {
        const widget = this.widgets[seatMapId];
        const seatContainer = widget.element;
        
        // Clear existing seats (except stage label)
        const existingSeats = seatContainer.querySelectorAll('.seat-section');
        existingSeats.forEach(section => section.remove());
        
        // Generate sections
        const sections = [
            { name: 'VIP', rows: 3, seatsPerRow: 8, type: 'premium' },
            { name: 'Main Floor', rows: 8, seatsPerRow: 12, type: 'available' },
            { name: 'Balcony', rows: 5, seatsPerRow: 10, type: 'available' }
        ];
        
        sections.forEach(section => {
            const sectionElement = document.createElement('div');
            sectionElement.className = 'seat-section';
            sectionElement.dataset.section = section.name;
            
            for (let row = 0; row < section.rows; row++) {
                const rowElement = document.createElement('div');
                rowElement.className = 'seat-row';
                
                for (let seatNum = 0; seatNum < section.seatsPerRow; seatNum++) {
                    const seat = document.createElement('div');
                    const seatId = `${section.name}-${row + 1}-${seatNum + 1}`;
                    
                    seat.className = `seat ${section.type}`;
                    seat.dataset.seatId = seatId;
                    seat.dataset.section = section.name;
                    seat.dataset.row = row + 1;
                    seat.dataset.seat = seatNum + 1;
                    
                    // Random occupancy for demo
                    if (Math.random() < 0.3) {
                        seat.classList.remove('available', 'premium');
                        seat.classList.add('occupied');
                    }
                    
                    // Add tooltip
                    const tooltip = document.createElement('div');
                    tooltip.className = 'seat-tooltip';
                    tooltip.textContent = `${section.name} Row ${row + 1} Seat ${seatNum + 1}`;
                    seat.appendChild(tooltip);
                    
                    // Add event listeners
                    seat.addEventListener('click', (e) => this.handleSeatClick(e, seatMapId));
                    seat.addEventListener('mouseenter', (e) => this.showSeatTooltip(e));
                    seat.addEventListener('mouseleave', (e) => this.hideSeatTooltip(e));
                    
                    rowElement.appendChild(seat);
                    widget.seats.push(seat);
                }
                
                sectionElement.appendChild(rowElement);
            }
            
            seatContainer.appendChild(sectionElement);
        });
        
        // Add seat legend
        this.addSeatLegend(seatContainer);
    }

    /**
     * Add seat legend
     */
    addSeatLegend(container) {
        const legend = document.createElement('div');
        legend.className = 'seat-legend';
        
        const legendItems = [
            { class: 'available', label: 'Available' },
            { class: 'occupied', label: 'Occupied' },
            { class: 'premium', label: 'Premium' },
            { class: 'selected', label: 'Selected' }
        ];
        
        legendItems.forEach(item => {
            const legendItem = document.createElement('div');
            legendItem.className = 'seat-legend-item';
            
            const color = document.createElement('div');
            color.className = `seat-legend-color seat ${item.class}`;
            
            const label = document.createElement('span');
            label.textContent = item.label;
            
            legendItem.appendChild(color);
            legendItem.appendChild(label);
            legend.appendChild(legendItem);
        });
        
        container.appendChild(legend);
    }

    /**
     * Handle seat click
     */
    handleSeatClick(event, seatMapId) {
        const seat = event.target.closest('.seat');
        const widget = this.widgets[seatMapId];
        
        if (seat.classList.contains('occupied')) {
            this.triggerHapticFeedback('medium');
            return;
        }
        
        this.triggerHapticFeedback('light');
        
        if (seat.classList.contains('selected')) {
            seat.classList.remove('selected');
            widget.selectedSeats = widget.selectedSeats.filter(s => s !== seat.dataset.seatId);
        } else {
            seat.classList.add('selected');
            widget.selectedSeats.push(seat.dataset.seatId);
        }
        
        this.updateSelectedSeatsInfo(seatMapId);
    }

    /**
     * Update selected seats info
     */
    updateSelectedSeatsInfo(seatMapId) {
        const widget = this.widgets[seatMapId];
        console.log(`Selected seats: ${widget.selectedSeats.join(', ')}`);
        
        // Emit custom event for other parts of the app
        document.dispatchEvent(new CustomEvent('seatsSelected', {
            detail: {
                seatMapId: seatMapId,
                selectedSeats: widget.selectedSeats
            }
        }));
    }

    /**
     * Show seat tooltip
     */
    showSeatTooltip(event) {
        const seat = event.target.closest('.seat');
        const tooltip = seat.querySelector('.seat-tooltip');
        
        if (seat.classList.contains('occupied')) {
            tooltip.textContent = 'Seat unavailable';
        } else if (seat.classList.contains('premium')) {
            tooltip.textContent += ' - Premium $150';
        } else {
            tooltip.textContent += ' - $75';
        }
    }

    /**
     * Hide seat tooltip
     */
    hideSeatTooltip(event) {
        // Tooltip hiding is handled by CSS
    }

    /**
     * Load seat map data
     */
    async loadSeatMapData(seatMapId) {
        try {
            // Simulate API call
            const response = await fetch('/api/seat-availability');
            const data = await response.json();
            
            this.seatMapData[seatMapId] = data;
            this.updateSeatAvailability(seatMapId);
        } catch (error) {
            console.error('Failed to load seat map data:', error);
        }
    }

    /**
     * Update seat availability
     */
    updateSeatAvailability(seatMapId) {
        const widget = this.widgets[seatMapId];
        const data = this.seatMapData[seatMapId];
        
        if (!data) return;
        
        widget.seats.forEach(seat => {
            const seatId = seat.dataset.seatId;
            if (data[seatId]) {
                const seatData = data[seatId];
                seat.classList.toggle('occupied', !seatData.available);
                seat.classList.toggle('available', seatData.available);
                seat.classList.toggle('premium', seatData.type === 'premium');
            }
        });
    }

    /**
     * Price Comparison Charts
     */
    initPriceComparison() {
        const priceElements = document.querySelectorAll('.price-comparison');
        
        priceElements.forEach((element, index) => {
            const priceId = `price-${index}`;
            this.widgets[priceId] = {
                element: element,
                platforms: [],
                bestPrice: null
            };
            
            this.loadPriceData(priceId);
        });
    }

    /**
     * Load price data
     */
    async loadPriceData(priceId) {
        try {
            // Simulate API call
            const response = await fetch('/api/price-comparison');
            const data = await response.json();
            
            this.priceData[priceId] = data;
            this.updatePriceDisplay(priceId);
        } catch (error) {
            console.error('Failed to load price data:', error);
            this.generateMockPriceData(priceId);
        }
    }

    /**
     * Generate mock price data
     */
    generateMockPriceData(priceId) {
        const platforms = [
            { name: 'Ticketmaster', logo: '/images/ticketmaster.png', basePrice: 85 },
            { name: 'StubHub', logo: '/images/stubhub.png', basePrice: 92 },
            { name: 'Vivid Seats', logo: '/images/vividseats.png', basePrice: 78 },
            { name: 'SeatGeek', logo: '/images/seatgeek.png', basePrice: 88 },
            { name: 'Viagogo', logo: '/images/viagogo.png', basePrice: 95 }
        ];
        
        const mockData = platforms.map(platform => ({
            ...platform,
            currentPrice: platform.basePrice + (Math.random() * 20 - 10),
            previousPrice: platform.basePrice,
            availability: Math.random() > 0.2,
            lastUpdated: new Date()
        }));
        
        this.priceData[priceId] = mockData;
        this.updatePriceDisplay(priceId);
    }

    /**
     * Update price display
     */
    updatePriceDisplay(priceId) {
        const widget = this.widgets[priceId];
        const data = this.priceData[priceId];
        
        // Clear existing content
        widget.element.innerHTML = '';
        
        // Find best price
        const availablePrices = data.filter(p => p.availability);
        const bestPrice = availablePrices.reduce((min, p) => 
            p.currentPrice < min.currentPrice ? p : min
        , availablePrices[0]);
        
        widget.bestPrice = bestPrice;
        
        // Create platform price elements
        data.forEach(platform => {
            const platformElement = this.createPlatformPriceElement(platform, platform === bestPrice);
            widget.element.appendChild(platformElement);
            widget.platforms.push(platformElement);
        });
    }

    /**
     * Create platform price element
     */
    createPlatformPriceElement(platform, isBestPrice = false) {
        const element = document.createElement('div');
        element.className = `platform-price ${isBestPrice ? 'best-price' : ''}`;
        element.dataset.platform = platform.name;
        
        const priceChange = platform.currentPrice - platform.previousPrice;
        const changeClass = priceChange > 0 ? 'positive' : priceChange < 0 ? 'negative' : '';
        const changeSymbol = priceChange > 0 ? '↑' : priceChange < 0 ? '↓' : '';
        
        element.innerHTML = `
            <div class="platform-info">
                <img src="${platform.logo}" alt="${platform.name}" class="platform-logo" 
                     onerror="this.style.display='none'">
                <div>
                    <div class="platform-name">${platform.name}</div>
                    <div class="platform-details">${platform.availability ? 'Available' : 'Sold Out'}</div>
                </div>
            </div>
            <div class="platform-price-value">
                <div class="current-price">$${platform.currentPrice.toFixed(2)}</div>
                ${Math.abs(priceChange) > 0.01 ? `
                    <div class="price-change ${changeClass}">
                        ${changeSymbol} $${Math.abs(priceChange).toFixed(2)}
                    </div>
                ` : ''}
                <svg class="price-mini-chart" viewBox="0 0 60 20">
                    <path class="price-trend-line" d="M5,15 Q15,${Math.random() * 15 + 2} 25,${Math.random() * 15 + 2} T55,${Math.random() * 15 + 2}"></path>
                </svg>
            </div>
        `;
        
        // Add click handler
        element.addEventListener('click', () => {
            this.triggerHapticFeedback('light');
            this.handlePlatformClick(platform);
        });
        
        return element;
    }

    /**
     * Handle platform click
     */
    handlePlatformClick(platform) {
        if (platform.availability) {
            console.log(`Opening ${platform.name} for purchase`);
            // This would typically open the platform's purchase page
        } else {
            alert(`${platform.name} is currently sold out`);
        }
    }

    /**
     * Alert Management Dashboard
     */
    initAlertDashboard() {
        const alertElements = document.querySelectorAll('.alert-dashboard');
        
        alertElements.forEach((element, index) => {
            const alertId = `alerts-${index}`;
            this.widgets[alertId] = {
                element: element,
                alerts: []
            };
            
            this.loadAlerts(alertId);
        });
    }

    /**
     * Load alerts
     */
    async loadAlerts(alertId) {
        try {
            const response = await fetch('/api/user-alerts');
            const data = await response.json();
            
            this.alerts[alertId] = data;
            this.updateAlertDisplay(alertId);
        } catch (error) {
            console.error('Failed to load alerts:', error);
            this.generateMockAlerts(alertId);
        }
    }

    /**
     * Generate mock alerts
     */
    generateMockAlerts(alertId) {
        const mockAlerts = [
            {
                id: 1,
                title: 'Arsenal vs Chelsea - Price Alert',
                description: 'Notify when tickets drop below $120',
                conditions: ['Price < $120', 'Category: Premier League'],
                active: true,
                triggered: false
            },
            {
                id: 2,
                title: 'Lakers vs Warriors - Availability Alert',
                description: 'Notify when tickets become available',
                conditions: ['Any availability', 'Section: Lower Bowl'],
                active: true,
                triggered: true
            },
            {
                id: 3,
                title: 'Wimbledon Finals - General Alert',
                description: 'Any ticket updates for Wimbledon',
                conditions: ['All events', 'Tournament: Wimbledon'],
                active: false,
                triggered: false
            }
        ];
        
        this.alerts[alertId] = mockAlerts;
        this.updateAlertDisplay(alertId);
    }

    /**
     * Update alert display
     */
    updateAlertDisplay(alertId) {
        const widget = this.widgets[alertId];
        const alerts = this.alerts[alertId];
        
        widget.element.innerHTML = '';
        
        alerts.forEach(alert => {
            const alertElement = this.createAlertElement(alert, alertId);
            widget.element.appendChild(alertElement);
            widget.alerts.push(alertElement);
        });
    }

    /**
     * Create alert element
     */
    createAlertElement(alert, alertId) {
        const element = document.createElement('div');
        element.className = `alert-item ${alert.active ? 'active' : ''} ${alert.triggered ? 'triggered' : ''}`;
        element.dataset.alertId = alert.id;
        
        element.innerHTML = `
            <div class="alert-info">
                <div class="alert-title">${alert.title}</div>
                <div class="alert-description">${alert.description}</div>
                <div class="alert-conditions">
                    ${alert.conditions.map(condition => 
                        `<span class="alert-condition">${condition}</span>`
                    ).join('')}
                </div>
            </div>
            <div class="alert-controls">
                <button class="toggle-switch ${alert.active ? 'active' : ''}" 
                        data-alert-id="${alert.id}" 
                        aria-label="Toggle alert ${alert.active ? 'off' : 'on'}">
                    <span class="sr-only">Toggle alert</span>
                </button>
                <button class="alert-action" data-action="edit" data-alert-id="${alert.id}">Edit</button>
                <button class="alert-action danger" data-action="delete" data-alert-id="${alert.id}">Delete</button>
            </div>
        `;
        
        // Add event listeners
        const toggleSwitch = element.querySelector('.toggle-switch');
        toggleSwitch.addEventListener('click', (e) => this.handleAlertToggle(e, alertId));
        
        const actionButtons = element.querySelectorAll('.alert-action');
        actionButtons.forEach(button => {
            button.addEventListener('click', (e) => this.handleAlertAction(e, alertId));
        });
        
        return element;
    }

    /**
     * Handle alert toggle
     */
    handleAlertToggle(event, alertId) {
        const button = event.target;
        const alertIdValue = parseInt(button.dataset.alertId);
        const alert = this.alerts[alertId].find(a => a.id === alertIdValue);
        
        if (alert) {
            alert.active = !alert.active;
            button.classList.toggle('active', alert.active);
            button.closest('.alert-item').classList.toggle('active', alert.active);
            
            this.triggerHapticFeedback('light');
            
            // Update on server
            this.updateAlertOnServer(alertIdValue, { active: alert.active });
        }
    }

    /**
     * Handle alert action
     */
    handleAlertAction(event, alertId) {
        const button = event.target;
        const action = button.dataset.action;
        const alertIdValue = parseInt(button.dataset.alertId);
        
        this.triggerHapticFeedback('medium');
        
        switch (action) {
            case 'edit':
                this.editAlert(alertIdValue, alertId);
                break;
            case 'delete':
                this.deleteAlert(alertIdValue, alertId);
                break;
        }
    }

    /**
     * Edit alert
     */
    editAlert(alertIdValue, alertId) {
        console.log(`Editing alert ${alertIdValue}`);
        // This would typically open a modal or form for editing
    }

    /**
     * Delete alert
     */
    deleteAlert(alertIdValue, alertId) {
        if (confirm('Are you sure you want to delete this alert?')) {
            this.alerts[alertId] = this.alerts[alertId].filter(a => a.id !== alertIdValue);
            this.updateAlertDisplay(alertId);
            this.deleteAlertOnServer(alertIdValue);
        }
    }

    /**
     * Update alert on server
     */
    async updateAlertOnServer(alertId, updates) {
        try {
            await fetch(`/api/alerts/${alertId}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                },
                body: JSON.stringify(updates)
            });
        } catch (error) {
            console.error('Failed to update alert:', error);
        }
    }

    /**
     * Delete alert on server
     */
    async deleteAlertOnServer(alertId) {
        try {
            await fetch(`/api/alerts/${alertId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                }
            });
        } catch (error) {
            console.error('Failed to delete alert:', error);
        }
    }

    /**
     * Quick Action Buttons with Haptic Feedback
     */
    initHapticButtons() {
        const hapticButtons = document.querySelectorAll('.haptic-button, .fab-button');
        
        hapticButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                const intensity = button.dataset.hapticIntensity || 'medium';
                this.triggerHapticFeedback(intensity);
                this.handleHapticButtonClick(e);
            });
            
            // Add visual feedback
            button.addEventListener('mousedown', () => {
                button.style.transform = 'scale(0.95)';
            });
            
            button.addEventListener('mouseup', () => {
                button.style.transform = '';
            });
            
            button.addEventListener('mouseleave', () => {
                button.style.transform = '';
            });
        });
    }

    /**
     * Trigger haptic feedback
     */
    triggerHapticFeedback(intensity = 'medium') {
        if (!this.hasHapticFeedback) return;
        
        const patterns = {
            light: 50,
            medium: 100,
            heavy: 200
        };
        
        navigator.vibrate(patterns[intensity] || patterns.medium);
    }

    /**
     * Handle haptic button click
     */
    handleHapticButtonClick(event) {
        const button = event.target.closest('.haptic-button, .fab-button');
        const action = button.dataset.action;
        
        console.log(`Haptic button clicked: ${action}`);
        
        // Handle specific actions
        switch (action) {
            case 'refresh':
                this.refreshAllData();
                break;
            case 'search':
                this.openSearch();
                break;
            case 'alerts':
                this.openAlerts();
                break;
            case 'purchase':
                this.openPurchase();
                break;
            default:
                // Handle custom actions via data attributes or href
                if (button.href) {
                    window.location.href = button.href;
                }
        }
    }

    /**
     * Refresh all data
     */
    async refreshAllData() {
        console.log('Refreshing all widget data...');
        
        // Show loading states
        Object.keys(this.widgets).forEach(widgetId => {
            const widget = this.widgets[widgetId];
            widget.element.classList.add('widget-loading');
        });
        
        // Refresh all widget types
        await Promise.all([
            this.refreshCircularProgress(),
            this.refreshHeatMapData(),
            this.refreshSeatMapData(),
            this.refreshPriceData(),
            this.refreshAlertData()
        ]);
        
        // Remove loading states
        setTimeout(() => {
            Object.keys(this.widgets).forEach(widgetId => {
                const widget = this.widgets[widgetId];
                widget.element.classList.remove('widget-loading');
            });
        }, 500);
    }

    /**
     * Refresh methods for each widget type
     */
    async refreshCircularProgress() {
        // Simulate new progress data
        Object.keys(this.widgets).forEach(widgetId => {
            if (widgetId.startsWith('progress-')) {
                const newValue = Math.floor(Math.random() * 100);
                this.updateCircularProgress(widgetId, newValue);
            }
        });
    }

    async refreshHeatMapData() {
        Object.keys(this.widgets).forEach(async widgetId => {
            if (widgetId.startsWith('heatmap-')) {
                await this.loadHeatMapData(widgetId);
            }
        });
    }

    async refreshSeatMapData() {
        Object.keys(this.widgets).forEach(async widgetId => {
            if (widgetId.startsWith('seatmap-')) {
                await this.loadSeatMapData(widgetId);
            }
        });
    }

    async refreshPriceData() {
        Object.keys(this.widgets).forEach(async widgetId => {
            if (widgetId.startsWith('price-')) {
                await this.loadPriceData(widgetId);
            }
        });
    }

    async refreshAlertData() {
        Object.keys(this.widgets).forEach(async widgetId => {
            if (widgetId.startsWith('alerts-')) {
                await this.loadAlerts(widgetId);
            }
        });
    }

    /**
     * Open search
     */
    openSearch() {
        // Implement search functionality
        const searchInput = document.querySelector('.ticket-search');
        if (searchInput) {
            searchInput.focus();
        }
    }

    /**
     * Open alerts
     */
    openAlerts() {
        // Navigate to alerts page or open alerts modal
        window.location.href = '/alerts';
    }

    /**
     * Open purchase
     */
    openPurchase() {
        // Navigate to purchase page
        window.location.href = '/purchase';
    }

    /**
     * Setup real-time updates
     */
    setupRealTimeUpdates() {
        // Connect to WebSocket for real-time updates
        if (window.websocketConfig && window.websocketConfig.url) {
            this.connectWebSocket();
        }
        
        // Set up periodic updates as fallback
        setInterval(() => {
            this.updateWidgetData();
        }, 30000); // Update every 30 seconds
    }

    /**
     * Connect to WebSocket
     */
    connectWebSocket() {
        try {
            this.ws = new WebSocket(window.websocketConfig.url);
            
            this.ws.onopen = () => {
                console.log('Widget WebSocket connected');
            };
            
            this.ws.onmessage = (event) => {
                const data = JSON.parse(event.data);
                this.handleWebSocketMessage(data);
            };
            
            this.ws.onclose = () => {
                console.log('Widget WebSocket disconnected');
                // Attempt to reconnect after 3 seconds
                setTimeout(() => this.connectWebSocket(), 3000);
            };
            
        } catch (error) {
            console.error('Failed to connect to WebSocket:', error);
        }
    }

    /**
     * Handle WebSocket messages
     */
    handleWebSocketMessage(data) {
        switch (data.type) {
            case 'queue_update':
                this.updateQueueProgress(data.queueId, data.progress, data.total);
                break;
            case 'event_density_update':
                this.updateHeatMapWithNewEvent(data.date, data.eventCount);
                break;
            case 'seat_availability_update':
                this.updateSeatAvailability(data.seatMapId, data.seats);
                break;
            case 'price_update':
                this.updatePlatformPrice(data.platform, data.newPrice);
                break;
            case 'alert_triggered':
                this.handleAlertTriggered(data.alertId);
                break;
        }
    }

    /**
     * Update queue progress from WebSocket
     */
    updateQueueProgress(queueId, progress, total) {
        const progressWidgets = Object.keys(this.widgets).filter(id => id.startsWith('progress-'));
        progressWidgets.forEach(widgetId => {
            this.updateCircularProgress(widgetId, progress, total, 'Queue Progress');
        });
    }

    /**
     * Update widget data periodically
     */
    async updateWidgetData() {
        // Only update if page is visible to save resources
        if (document.hidden) return;
        
        // Randomly update some widgets to simulate real-time changes
        if (Math.random() > 0.7) {
            await this.refreshPriceData();
        }
        
        if (Math.random() > 0.8) {
            await this.refreshSeatMapData();
        }
    }

    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Handle window resize for responsive updates
        window.addEventListener('resize', () => {
            this.handleResize();
        });
        
        // Handle visibility change to pause/resume updates
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.pauseUpdates();
            } else {
                this.resumeUpdates();
            }
        });
    }

    /**
     * Handle window resize
     */
    handleResize() {
        // Update responsive widgets
        Object.keys(this.widgets).forEach(widgetId => {
            const widget = this.widgets[widgetId];
            if (widgetId.startsWith('heatmap-')) {
                this.updateHeatMapDisplay(widgetId);
            }
        });
    }

    /**
     * Pause updates when page is hidden
     */
    pauseUpdates() {
        console.log('Pausing widget updates');
        this.updatesPaused = true;
    }

    /**
     * Resume updates when page becomes visible
     */
    resumeUpdates() {
        console.log('Resuming widget updates');
        this.updatesPaused = false;
        this.updateWidgetData();
    }

    /**
     * Cleanup resources
     */
    cleanup() {
        // Close WebSocket connection
        if (this.ws) {
            this.ws.close();
        }
        
        // Cancel any running animations
        Object.keys(this.widgets).forEach(widgetId => {
            const widget = this.widgets[widgetId];
            if (widget.animationId) {
                cancelAnimationFrame(widget.animationId);
            }
        });
        
        // Clear charts
        Object.values(this.charts).forEach(chart => {
            if (chart.destroy) {
                chart.destroy();
            }
        });
    }
}

// Initialize dashboard widgets when DOM is ready
let dashboardWidgets;

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        dashboardWidgets = new DashboardWidgets();
    });
} else {
    dashboardWidgets = new DashboardWidgets();
}

// Handle page unload cleanup
window.addEventListener('beforeunload', () => {
    if (dashboardWidgets) {
        dashboardWidgets.cleanup();
    }
});

// Export for global access
window.dashboardWidgets = dashboardWidgets;
