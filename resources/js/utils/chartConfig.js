/**
 * Chart.js Configuration Utilities for Sports Ticket System
 * Provides optimized chart configurations for ticket analytics
 */

import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    Title,
    Tooltip,
    Legend,
    ArcElement,
    TimeScale,
    Filler
} from 'chart.js';
import 'chartjs-adapter-date-fns';

// Register Chart.js components
ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    Title,
    Tooltip,
    Legend,
    ArcElement,
    TimeScale,
    Filler
);

// Sports-themed color palette
export const SPORTS_COLORS = {
    primary: '#0ea5e9',
    secondary: '#64748b',
    football: '#3b82f6',
    basketball: '#f97316',
    baseball: '#6b7280',
    tennis: '#22c55e',
    available: '#22c55e',
    sold: '#ef4444',
    pending: '#eab308',
    success: '#10b981',
    warning: '#f59e0b',
    danger: '#ef4444',
    info: '#3b82f6'
};

// Color variations for gradients and multi-series charts
export const COLOR_VARIANTS = {
    primary: ['#0ea5e9', '#38bdf8', '#7dd3fc', '#bae6fd'],
    football: ['#3b82f6', '#60a5fa', '#93c5fd', '#bfdbfe'],
    basketball: ['#f97316', '#fb923c', '#fdba74', '#fed7aa'],
    baseball: ['#6b7280', '#9ca3af', '#d1d5db', '#e5e7eb'],
    tennis: ['#22c55e', '#4ade80', '#86efac', '#bbf7d0'],
    status: ['#22c55e', '#eab308', '#ef4444']
};

/**
 * Base chart configuration
 */
export const BASE_CONFIG = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'top',
            labels: {
                usePointStyle: true,
                padding: 20,
                font: {
                    family: 'Inter, sans-serif',
                    size: 12
                }
            }
        },
        tooltip: {
            backgroundColor: 'rgba(0, 0, 0, 0.8)',
            titleFont: {
                family: 'Inter, sans-serif',
                size: 14,
                weight: 'bold'
            },
            bodyFont: {
                family: 'Inter, sans-serif',
                size: 12
            },
            cornerRadius: 8,
            displayColors: true,
            intersect: false,
            mode: 'index'
        }
    },
    scales: {
        x: {
            grid: {
                display: false
            },
            ticks: {
                font: {
                    family: 'Inter, sans-serif',
                    size: 11
                },
                color: '#64748b'
            }
        },
        y: {
            grid: {
                color: 'rgba(148, 163, 184, 0.1)'
            },
            ticks: {
                font: {
                    family: 'Inter, sans-serif',
                    size: 11
                },
                color: '#64748b'
            }
        }
    },
    elements: {
        point: {
            radius: 4,
            hoverRadius: 6
        },
        line: {
            tension: 0.1
        }
    }
};

/**
 * Ticket price history line chart configuration
 */
export function createPriceHistoryConfig(data, options = {}) {
    return {
        type: 'line',
        data: {
            labels: data.labels || [],
            datasets: [{
                label: 'Ticket Price',
                data: data.prices || [],
                borderColor: SPORTS_COLORS.primary,
                backgroundColor: `${SPORTS_COLORS.primary}20`,
                fill: true,
                tension: 0.1,
                pointBackgroundColor: SPORTS_COLORS.primary,
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2
            }]
        },
        options: {
            ...BASE_CONFIG,
            plugins: {
                ...BASE_CONFIG.plugins,
                title: {
                    display: true,
                    text: options.title || 'Ticket Price History',
                    font: {
                        family: 'Inter, sans-serif',
                        size: 16,
                        weight: 'bold'
                    }
                }
            },
            scales: {
                ...BASE_CONFIG.scales,
                x: {
                    ...BASE_CONFIG.scales.x,
                    type: 'time',
                    time: {
                        displayFormats: {
                            hour: 'HH:mm',
                            day: 'MMM dd',
                            week: 'MMM dd',
                            month: 'MMM yyyy'
                        }
                    }
                },
                y: {
                    ...BASE_CONFIG.scales.y,
                    ticks: {
                        ...BASE_CONFIG.scales.y.ticks,
                        callback: function(value) {
                            return '$' + value.toFixed(2);
                        }
                    }
                }
            }
        }
    };
}

/**
 * Ticket availability status doughnut chart configuration
 */
export function createAvailabilityStatusConfig(data, options = {}) {
    return {
        type: 'doughnut',
        data: {
            labels: ['Available', 'Sold', 'Pending'],
            datasets: [{
                data: [data.available || 0, data.sold || 0, data.pending || 0],
                backgroundColor: [
                    SPORTS_COLORS.available,
                    SPORTS_COLORS.sold,
                    SPORTS_COLORS.pending
                ],
                borderWidth: 0,
                hoverBorderWidth: 2,
                hoverBorderColor: '#ffffff'
            }]
        },
        options: {
            ...BASE_CONFIG,
            cutout: '60%',
            plugins: {
                ...BASE_CONFIG.plugins,
                title: {
                    display: true,
                    text: options.title || 'Ticket Availability Status',
                    font: {
                        family: 'Inter, sans-serif',
                        size: 16,
                        weight: 'bold'
                    }
                },
                legend: {
                    ...BASE_CONFIG.plugins.legend,
                    position: 'bottom'
                }
            }
        }
    };
}

/**
 * Sports category distribution bar chart configuration
 */
export function createSportsCategoryConfig(data, options = {}) {
    const colors = data.categories?.map(category => 
        SPORTS_COLORS[category.toLowerCase()] || SPORTS_COLORS.primary
    ) || [];

    return {
        type: 'bar',
        data: {
            labels: data.categories || [],
            datasets: [{
                label: 'Tickets',
                data: data.counts || [],
                backgroundColor: colors,
                borderColor: colors.map(color => color + '80'),
                borderWidth: 1,
                borderRadius: 4,
                borderSkipped: false
            }]
        },
        options: {
            ...BASE_CONFIG,
            plugins: {
                ...BASE_CONFIG.plugins,
                title: {
                    display: true,
                    text: options.title || 'Tickets by Sport Category',
                    font: {
                        family: 'Inter, sans-serif',
                        size: 16,
                        weight: 'bold'
                    }
                },
                legend: {
                    display: false
                }
            },
            scales: {
                ...BASE_CONFIG.scales,
                y: {
                    ...BASE_CONFIG.scales.y,
                    beginAtZero: true,
                    ticks: {
                        ...BASE_CONFIG.scales.y.ticks,
                        stepSize: 1
                    }
                }
            }
        }
    };
}

/**
 * Real-time monitoring line chart configuration
 */
export function createRealTimeMonitoringConfig(data, options = {}) {
    return {
        type: 'line',
        data: {
            labels: data.labels || [],
            datasets: data.datasets?.map((dataset, index) => ({
                label: dataset.label,
                data: dataset.data,
                borderColor: COLOR_VARIANTS.primary[index % COLOR_VARIANTS.primary.length],
                backgroundColor: COLOR_VARIANTS.primary[index % COLOR_VARIANTS.primary.length] + '20',
                fill: false,
                tension: 0.1,
                pointRadius: 2,
                pointHoverRadius: 4
            })) || []
        },
        options: {
            ...BASE_CONFIG,
            animation: {
                duration: 750,
                easing: 'easeInOutQuart'
            },
            plugins: {
                ...BASE_CONFIG.plugins,
                title: {
                    display: true,
                    text: options.title || 'Real-time Ticket Monitoring',
                    font: {
                        family: 'Inter, sans-serif',
                        size: 16,
                        weight: 'bold'
                    }
                }
            },
            scales: {
                ...BASE_CONFIG.scales,
                x: {
                    ...BASE_CONFIG.scales.x,
                    type: 'time',
                    time: {
                        unit: 'minute',
                        displayFormats: {
                            minute: 'HH:mm'
                        }
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    };
}

/**
 * Platform performance comparison chart configuration
 */
export function createPlatformPerformanceConfig(data, options = {}) {
    return {
        type: 'bar',
        data: {
            labels: data.platforms || [],
            datasets: [{
                label: 'Success Rate (%)',
                data: data.successRates || [],
                backgroundColor: COLOR_VARIANTS.primary[0],
                borderColor: COLOR_VARIANTS.primary[0],
                borderWidth: 1,
                yAxisID: 'y'
            }, {
                label: 'Response Time (ms)',
                data: data.responseTimes || [],
                backgroundColor: COLOR_VARIANTS.primary[1],
                borderColor: COLOR_VARIANTS.primary[1],
                borderWidth: 1,
                yAxisID: 'y1'
            }]
        },
        options: {
            ...BASE_CONFIG,
            plugins: {
                ...BASE_CONFIG.plugins,
                title: {
                    display: true,
                    text: options.title || 'Platform Performance Comparison',
                    font: {
                        family: 'Inter, sans-serif',
                        size: 16,
                        weight: 'bold'
                    }
                }
            },
            scales: {
                ...BASE_CONFIG.scales,
                y: {
                    ...BASE_CONFIG.scales.y,
                    type: 'linear',
                    display: true,
                    position: 'left',
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        ...BASE_CONFIG.scales.y.ticks,
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    beginAtZero: true,
                    grid: {
                        drawOnChartArea: false
                    },
                    ticks: {
                        font: {
                            family: 'Inter, sans-serif',
                            size: 11
                        },
                        color: '#64748b',
                        callback: function(value) {
                            return value + 'ms';
                        }
                    }
                }
            }
        }
    };
}

/**
 * Utility function to create gradient background
 */
export function createGradient(ctx, color1, color2) {
    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, color1);
    gradient.addColorStop(1, color2);
    return gradient;
}

/**
 * Utility function to format currency values
 */
export function formatCurrency(value, currency = 'USD') {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency
    }).format(value);
}

/**
 * Utility function to format time labels
 */
export function formatTimeLabel(date, format = 'short') {
    const options = format === 'short' 
        ? { hour: '2-digit', minute: '2-digit' }
        : { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
    
    return new Intl.DateTimeFormat('en-US', options).format(new Date(date));
}

/**
 * Export Chart.js instance for advanced usage
 */
export { ChartJS };
