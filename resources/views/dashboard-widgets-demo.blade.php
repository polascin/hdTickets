<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>HD Tickets - Interactive Dashboard Widgets Demo</title>
    
    <!-- Core Dashboard CSS -->
    <link href="{{ asset('css/customer-dashboard.css') }}?v={{ time() }}" rel="stylesheet">
    <!-- Interactive Widgets CSS -->
    <link href="{{ asset('css/dashboard-widgets.css') }}?v={{ time() }}" rel="stylesheet">
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }
        
        .demo-header {
            text-align: center;
            margin-bottom: 40px;
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        .demo-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 12px;
            background: linear-gradient(135deg, #10b981, #3b82f6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .demo-subtitle {
            font-size: 1.25rem;
            color: #6b7280;
            margin-bottom: 0;
        }
        
        .widgets-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 24px;
            margin-bottom: 40px;
        }
        
        .widget-section {
            background: white;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .widget-section:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }
        
        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .section-icon {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, #10b981, #3b82f6);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 16px;
        }
        
        .actions-section {
            grid-column: 1 / -1;
        }
        
        .demo-actions {
            display: flex;
            gap: 16px;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 40px;
        }
        
        @media (max-width: 768px) {
            .widgets-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }
            
            .demo-title {
                font-size: 2rem;
            }
            
            .widget-section {
                padding: 16px;
            }
        }
    </style>
</head>
<body>
    <!-- Demo Header -->
    <div class="demo-header">
        <h1 class="demo-title">Interactive Dashboard Widgets</h1>
        <p class="demo-subtitle">Sports Events Entry Tickets Monitoring System</p>
    </div>

    <!-- Widgets Grid -->
    <div class="widgets-grid">
        
        <!-- Circular Progress Indicators -->
        <div class="widget-section">
            <h2 class="section-title">
                <div class="section-icon">
                    <i class="fas fa-chart-pie"></i>
                </div>
                Purchase Queue Status
            </h2>
            <div class="dashboard-widget">
                <div class="widget-header">
                    <div>
                        <h3 class="widget-title">Queue Progress</h3>
                        <p class="widget-subtitle">Real-time purchase queue status</p>
                    </div>
                    <button class="widget-action">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
                <div class="queue-status">
                    <div class="queue-item">
                        <div class="circular-progress" data-value="75" data-max="100" data-label="Active">
                            <svg class="progress-circle active" viewBox="0 0 120 120">
                                <circle class="progress-circle-bg" cx="60" cy="60" r="54"></circle>
                                <circle class="progress-circle-fill" cx="60" cy="60" r="54"></circle>
                            </svg>
                            <div class="progress-label">
                                <div class="progress-value">75</div>
                                <div class="progress-text">Active</div>
                            </div>
                        </div>
                    </div>
                    <div class="queue-item">
                        <div class="circular-progress" data-value="45" data-max="100" data-label="Pending">
                            <svg class="progress-circle" viewBox="0 0 120 120">
                                <circle class="progress-circle-bg" cx="60" cy="60" r="54"></circle>
                                <circle class="progress-circle-fill warning" cx="60" cy="60" r="54"></circle>
                            </svg>
                            <div class="progress-label">
                                <div class="progress-value">45</div>
                                <div class="progress-text">Pending</div>
                            </div>
                        </div>
                    </div>
                    <div class="queue-item">
                        <div class="circular-progress" data-value="92" data-max="100" data-label="Failed">
                            <svg class="progress-circle" viewBox="0 0 120 120">
                                <circle class="progress-circle-bg" cx="60" cy="60" r="54"></circle>
                                <circle class="progress-circle-fill error" cx="60" cy="60" r="54"></circle>
                            </svg>
                            <div class="progress-label">
                                <div class="progress-value">92</div>
                                <div class="progress-text">Failed</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Heat Map Calendar -->
        <div class="widget-section">
            <h2 class="section-title">
                <div class="section-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                Event Density Calendar
            </h2>
            <div class="dashboard-widget">
                <div class="widget-header">
                    <div>
                        <h3 class="widget-title">Event Heat Map</h3>
                        <p class="widget-subtitle">Click on days to see events</p>
                    </div>
                    <button class="widget-action">
                        <i class="fas fa-expand"></i>
                    </button>
                </div>
                <div class="heat-map-calendar" id="event-heatmap">
                    <!-- Calendar days will be generated by JavaScript -->
                </div>
                <div class="heat-map-legend">
                    <div class="legend-item">
                        <div class="legend-color" style="background: #f3f4f6;"></div>
                        <span>No events</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background: #06b6d4;"></div>
                        <span>Low</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background: #f59e0b;"></div>
                        <span>Medium</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background: #ef4444;"></div>
                        <span>High</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Interactive Seat Map -->
        <div class="widget-section">
            <h2 class="section-title">
                <div class="section-icon">
                    <i class="fas fa-couch"></i>
                </div>
                Seat Map Preview
            </h2>
            <div class="dashboard-widget">
                <div class="widget-header">
                    <div>
                        <h3 class="widget-title">Stadium Layout</h3>
                        <p class="widget-subtitle">Click seats to select</p>
                    </div>
                    <button class="widget-action">
                        <i class="fas fa-maximize"></i>
                    </button>
                </div>
                <div class="seat-map" id="venue-seatmap">
                    <!-- Seat map will be generated by JavaScript -->
                </div>
            </div>
        </div>

        <!-- Price Comparison Chart -->
        <div class="widget-section">
            <h2 class="section-title">
                <div class="section-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                Price Comparison
            </h2>
            <div class="dashboard-widget">
                <div class="widget-header">
                    <div>
                        <h3 class="widget-title">Platform Prices</h3>
                        <p class="widget-subtitle">Compare across platforms</p>
                    </div>
                    <button class="widget-action">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
                <div class="price-comparison" id="price-comparison-widget">
                    <!-- Price comparison will be generated by JavaScript -->
                </div>
            </div>
        </div>

        <!-- Alert Management Dashboard -->
        <div class="widget-section">
            <h2 class="section-title">
                <div class="section-icon">
                    <i class="fas fa-bell"></i>
                </div>
                Alert Management
            </h2>
            <div class="dashboard-widget">
                <div class="widget-header">
                    <div>
                        <h3 class="widget-title">Active Alerts</h3>
                        <p class="widget-subtitle">Manage your notifications</p>
                    </div>
                    <button class="widget-action">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
                <div class="alert-dashboard" id="alert-management-widget">
                    <!-- Alert dashboard will be generated by JavaScript -->
                </div>
            </div>
        </div>

        <!-- Quick Action Buttons -->
        <div class="widget-section actions-section">
            <h2 class="section-title">
                <div class="section-icon">
                    <i class="fas fa-bolt"></i>
                </div>
                Quick Actions with Haptic Feedback
            </h2>
            <div class="dashboard-widget">
                <div class="widget-header">
                    <div>
                        <h3 class="widget-title">Action Center</h3>
                        <p class="widget-subtitle">Touch-optimized controls</p>
                    </div>
                </div>
                <div class="button-group">
                    <button class="haptic-button" data-action="search" data-haptic-intensity="light">
                        <div class="haptic-button-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <div class="haptic-button-text">
                            <div class="haptic-button-label">Search Tickets</div>
                            <div class="haptic-button-description">Find events and venues</div>
                        </div>
                    </button>
                    
                    <button class="haptic-button success" data-action="alerts" data-haptic-intensity="medium">
                        <div class="haptic-button-icon">
                            <i class="fas fa-bell"></i>
                        </div>
                        <div class="haptic-button-text">
                            <div class="haptic-button-label">My Alerts</div>
                            <div class="haptic-button-description">Manage notifications</div>
                        </div>
                    </button>
                    
                    <button class="haptic-button warning" data-action="purchase" data-haptic-intensity="heavy">
                        <div class="haptic-button-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="haptic-button-text">
                            <div class="haptic-button-label">Purchase Queue</div>
                            <div class="haptic-button-description">View buying status</div>
                        </div>
                    </button>
                    
                    <button class="haptic-button secondary" data-action="refresh" data-haptic-intensity="light">
                        <div class="haptic-button-icon">
                            <i class="fas fa-sync-alt"></i>
                        </div>
                        <div class="haptic-button-text">
                            <div class="haptic-button-label">Refresh Data</div>
                            <div class="haptic-button-description">Update all widgets</div>
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Demo Actions -->
    <div class="demo-actions">
        <button class="haptic-button" onclick="simulateDataUpdate()">
            <div class="haptic-button-icon">
                <i class="fas fa-play"></i>
            </div>
            <div class="haptic-button-text">
                <div class="haptic-button-label">Simulate Updates</div>
                <div class="haptic-button-description">Show real-time changes</div>
            </div>
        </button>
        
        <button class="haptic-button secondary" onclick="toggleDemoMode()">
            <div class="haptic-button-icon">
                <i class="fas fa-magic"></i>
            </div>
            <div class="haptic-button-text">
                <div class="haptic-button-label">Demo Mode</div>
                <div class="haptic-button-description">Auto-animate widgets</div>
            </div>
        </button>
        
        <button class="haptic-button error" onclick="resetAllWidgets()">
            <div class="haptic-button-icon">
                <i class="fas fa-redo"></i>
            </div>
            <div class="haptic-button-text">
                <div class="haptic-button-label">Reset Demo</div>
                <div class="haptic-button-description">Restore defaults</div>
            </div>
        </button>
    </div>

    <!-- Floating Action Button -->
    <button class="fab-button" data-action="refresh" title="Refresh All Widgets">
        <i class="fas fa-sync-alt"></i>
    </button>

    <!-- JavaScript -->
    <!-- Core dashboard functionality -->
    <script src="{{ asset('js/customer-dashboard.js') }}?v={{ time() }}"></script>
    <!-- Interactive widgets -->
    <script src="{{ asset('js/dashboard-widgets.js') }}?v={{ time() }}"></script>
    
    <!-- Demo-specific JavaScript -->
    <script>
        // Demo configuration
        let demoMode = false;
        let demoInterval = null;

        // WebSocket configuration for demo
        window.websocketConfig = {
            url: 'ws://localhost:6001',
            key: 'demo-key',
            auth: {
                userId: 1,
                token: '{{ csrf_token() }}'
            }
        };

        /**
         * Simulate real-time data updates
         */
        function simulateDataUpdate() {
            console.log('Simulating data updates...');
            
            // Update progress indicators with random values
            const progressWidgets = Object.keys(window.dashboardWidgets.widgets).filter(id => id.startsWith('progress-'));
            progressWidgets.forEach(widgetId => {
                const newValue = Math.floor(Math.random() * 100);
                const label = ['Active', 'Pending', 'Failed'][Math.floor(Math.random() * 3)];
                window.dashboardWidgets.updateCircularProgress(widgetId, newValue, 100, label);
            });
            
            // Trigger haptic feedback
            window.dashboardWidgets.triggerHapticFeedback('medium');
            
            // Show notification
            showNotification('Data updated successfully!', 'success');
        }

        /**
         * Toggle demo mode for continuous updates
         */
        function toggleDemoMode() {
            demoMode = !demoMode;
            
            if (demoMode) {
                console.log('Demo mode enabled');
                startDemoMode();
                showNotification('Demo mode enabled - widgets will auto-update', 'info');
            } else {
                console.log('Demo mode disabled');
                stopDemoMode();
                showNotification('Demo mode disabled', 'info');
            }
            
            // Update button text
            const button = event.target.closest('.haptic-button');
            const label = button.querySelector('.haptic-button-label');
            label.textContent = demoMode ? 'Stop Demo' : 'Demo Mode';
        }

        /**
         * Start demo mode with automatic updates
         */
        function startDemoMode() {
            demoInterval = setInterval(() => {
                simulateDataUpdate();
                
                // Random chance to simulate other events
                if (Math.random() > 0.7) {
                    simulateHeatMapUpdate();
                }
                
                if (Math.random() > 0.8) {
                    simulatePriceUpdate();
                }
                
                if (Math.random() > 0.9) {
                    simulateAlertTrigger();
                }
            }, 3000); // Update every 3 seconds
        }

        /**
         * Stop demo mode
         */
        function stopDemoMode() {
            if (demoInterval) {
                clearInterval(demoInterval);
                demoInterval = null;
            }
        }

        /**
         * Reset all widgets to default state
         */
        function resetAllWidgets() {
            console.log('Resetting all widgets...');
            
            // Stop demo mode if running
            if (demoMode) {
                toggleDemoMode();
            }
            
            // Reload the page to reset everything
            if (confirm('This will reload the page and reset all widgets. Continue?')) {
                window.location.reload();
            }
        }

        /**
         * Simulate heat map updates
         */
        function simulateHeatMapUpdate() {
            const heatMapWidgets = Object.keys(window.dashboardWidgets.widgets).filter(id => id.startsWith('heatmap-'));
            heatMapWidgets.forEach(widgetId => {
                window.dashboardWidgets.generateMockHeatMapData(widgetId);
            });
        }

        /**
         * Simulate price updates
         */
        function simulatePriceUpdate() {
            const priceWidgets = Object.keys(window.dashboardWidgets.widgets).filter(id => id.startsWith('price-'));
            priceWidgets.forEach(widgetId => {
                window.dashboardWidgets.generateMockPriceData(widgetId);
            });
        }

        /**
         * Simulate alert triggers
         */
        function simulateAlertTrigger() {
            showNotification('Price Alert: Arsenal vs Chelsea tickets dropped to $110!', 'warning');
            window.dashboardWidgets.triggerHapticFeedback('heavy');
        }

        /**
         * Show demo notification
         */
        function showNotification(message, type = 'info') {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = `demo-notification ${type}`;
            notification.innerHTML = `
                <div class="notification-content">
                    <i class="fas fa-${type === 'success' ? 'check' : type === 'warning' ? 'exclamation' : type === 'error' ? 'times' : 'info'}-circle"></i>
                    <span>${message}</span>
                </div>
            `;
            
            // Add styles
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: ${type === 'success' ? '#059669' : type === 'warning' ? '#f59e0b' : type === 'error' ? '#ef4444' : '#06b6d4'};
                color: white;
                padding: 16px 20px;
                border-radius: 8px;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
                z-index: 1000;
                animation: slideIn 0.3s ease-out;
                max-width: 400px;
            `;
            
            document.body.appendChild(notification);
            
            // Remove after 3 seconds
            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease-in';
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 300);
            }, 3000);
        }

        // Add notification animations
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            
            @keyframes slideOut {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(100%);
                    opacity: 0;
                }
            }
            
            .demo-notification .notification-content {
                display: flex;
                align-items: center;
                gap: 12px;
            }
        `;
        document.head.appendChild(style);

        // Initialize demo when page loads
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Dashboard Widgets Demo initialized');
            
            // Show welcome notification
            setTimeout(() => {
                showNotification('Interactive Dashboard Widgets loaded successfully!', 'success');
            }, 1000);
        });

        // Handle page visibility changes
        document.addEventListener('visibilitychange', function() {
            if (document.hidden && demoMode) {
                stopDemoMode();
            } else if (!document.hidden && demoMode) {
                startDemoMode();
            }
        });

        // Cleanup on page unload
        window.addEventListener('beforeunload', function() {
            if (demoInterval) {
                clearInterval(demoInterval);
            }
        });
    </script>
</body>
</html>
