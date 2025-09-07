#!/bin/bash

# HD Tickets - Performance Monitoring Dashboard
# Description: Real-time performance monitoring with metrics collection and alerts
# Usage: ./scripts/performance-monitor.sh [--duration=60] [--interval=5] [--output=dashboard.html]
# Author: HD Tickets DevOps Team
# Version: 1.0.0

set -euo pipefail

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" &> /dev/null && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"
LOG_DIR="$PROJECT_DIR/storage/logs"
METRICS_DIR="$LOG_DIR/metrics"
TIMESTAMP=$(date '+%Y%m%d_%H%M%S')

# Default parameters
DURATION=60
INTERVAL=5
OUTPUT_FILE="$METRICS_DIR/performance-dashboard-$TIMESTAMP.html"
JSON_LOG="$METRICS_DIR/performance-metrics-$TIMESTAMP.json"
CONTINUOUS_MODE=false
ALERT_THRESHOLDS=true

# Performance thresholds
RESPONSE_TIME_WARNING=1.0
RESPONSE_TIME_CRITICAL=3.0
MEMORY_WARNING=80
MEMORY_CRITICAL=90
CPU_WARNING=80
CPU_CRITICAL=90
DISK_WARNING=80
DISK_CRITICAL=90

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Parse command line arguments
while [[ $# -gt 0 ]]; do
    case $1 in
        --duration=*)
            DURATION="${1#*=}"
            shift
            ;;
        --interval=*)
            INTERVAL="${1#*=}"
            shift
            ;;
        --output=*)
            OUTPUT_FILE="${1#*=}"
            shift
            ;;
        --continuous)
            CONTINUOUS_MODE=true
            shift
            ;;
        --no-alerts)
            ALERT_THRESHOLDS=false
            shift
            ;;
        --help|-h)
            echo "HD Tickets Performance Monitor"
            echo "Usage: $0 [options]"
            echo "Options:"
            echo "  --duration=N     Monitor for N seconds (default: 60)"
            echo "  --interval=N     Sample every N seconds (default: 5)"
            echo "  --output=FILE    Output HTML dashboard file"
            echo "  --continuous     Run continuously until stopped"
            echo "  --no-alerts      Disable alert notifications"
            exit 0
            ;;
        *)
            echo "Unknown option: $1"
            exit 1
            ;;
    esac
done

# Ensure directories exist
mkdir -p "$METRICS_DIR"

# Initialize metrics storage
METRICS_DATA=()

log_metric() {
    local timestamp="$1"
    local metric_name="$2"
    local metric_value="$3"
    local status="$4"
    
    local metric_json="{\"timestamp\":\"$timestamp\",\"metric\":\"$metric_name\",\"value\":\"$metric_value\",\"status\":\"$status\"}"
    METRICS_DATA+=("$metric_json")
    echo "$metric_json" >> "$JSON_LOG"
}

get_system_metrics() {
    local timestamp=$(date '+%Y-%m-%d %H:%M:%S')
    
    # CPU Usage
    local cpu_usage=$(top -bn1 | grep "Cpu(s)" | awk '{print $2}' | cut -d'%' -f1)
    local cpu_status="normal"
    if (( $(echo "$cpu_usage > $CPU_CRITICAL" | bc -l) )); then
        cpu_status="critical"
    elif (( $(echo "$cpu_usage > $CPU_WARNING" | bc -l) )); then
        cpu_status="warning"
    fi
    log_metric "$timestamp" "cpu_usage" "$cpu_usage" "$cpu_status"
    
    # Memory Usage
    local memory_info=$(free | awk 'NR==2{printf "%.1f", $3*100/$2 }')
    local memory_status="normal"
    if (( $(echo "$memory_info > $MEMORY_CRITICAL" | bc -l) )); then
        memory_status="critical"
    elif (( $(echo "$memory_info > $MEMORY_WARNING" | bc -l) )); then
        memory_status="warning"
    fi
    log_metric "$timestamp" "memory_usage" "$memory_info" "$memory_status"
    
    # Disk Usage
    local disk_usage=$(df -h "$PROJECT_DIR" | awk 'NR==2 {print $5}' | sed 's/%//')
    local disk_status="normal"
    if [[ $disk_usage -gt $DISK_CRITICAL ]]; then
        disk_status="critical"
    elif [[ $disk_usage -gt $DISK_WARNING ]]; then
        disk_status="warning"
    fi
    log_metric "$timestamp" "disk_usage" "$disk_usage" "$disk_status"
    
    # Load Average
    local load_avg=$(uptime | awk -F'load average:' '{print $2}' | awk '{print $1}' | sed 's/,//')
    log_metric "$timestamp" "load_average" "$load_avg" "normal"
}

get_application_metrics() {
    local timestamp=$(date '+%Y-%m-%d %H:%M:%S')
    
    cd "$PROJECT_DIR"
    
    # Test application response time
    local test_port=9001
    php artisan serve --host=localhost --port=$test_port --no-reload &
    local server_pid=$!
    
    sleep 2
    
    local response_time=$(curl -s -o /dev/null -w "%{time_total}" "http://localhost:$test_port" 2>/dev/null || echo "0")
    local http_code=$(curl -s -o /dev/null -w "%{http_code}" "http://localhost:$test_port" 2>/dev/null || echo "000")
    
    kill $server_pid 2>/dev/null || true
    wait $server_pid 2>/dev/null || true
    
    local response_status="normal"
    if (( $(echo "$response_time > $RESPONSE_TIME_CRITICAL" | bc -l) )); then
        response_status="critical"
    elif (( $(echo "$response_time > $RESPONSE_TIME_WARNING" | bc -l) )); then
        response_status="warning"
    fi
    
    log_metric "$timestamp" "response_time" "$response_time" "$response_status"
    log_metric "$timestamp" "http_status" "$http_code" $([ "$http_code" = "200" ] && echo "normal" || echo "critical")
}

get_database_metrics() {
    local timestamp=$(date '+%Y-%m-%d %H:%M:%S')
    
    cd "$PROJECT_DIR"
    
    # Database connection time
    local db_start=$(date +%s.%3N)
    if php artisan tinker --execute="DB::connection()->getPdo();" >/dev/null 2>&1; then
        local db_end=$(date +%s.%3N)
        local db_time=$(echo "$db_end - $db_start" | bc -l)
        log_metric "$timestamp" "db_connection_time" "$db_time" "normal"
        
        # Database size
        local db_size=$(php artisan tinker --execute="echo DB::select('SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb FROM information_schema.tables WHERE table_schema = database()')[0]->size_mb;" 2>/dev/null || echo "0")
        log_metric "$timestamp" "db_size_mb" "$db_size" "normal"
    else
        log_metric "$timestamp" "db_connection_time" "timeout" "critical"
    fi
}

get_redis_metrics() {
    local timestamp=$(date '+%Y-%m-%d %H:%M:%S')
    
    cd "$PROJECT_DIR"
    
    # Redis connection time
    local redis_start=$(date +%s.%3N)
    if php artisan tinker --execute="Redis::ping();" >/dev/null 2>&1; then
        local redis_end=$(date +%s.%3N)
        local redis_time=$(echo "$redis_end - $redis_start" | bc -l)
        log_metric "$timestamp" "redis_connection_time" "$redis_time" "normal"
        
        # Redis memory usage
        local redis_memory=$(redis-cli info memory 2>/dev/null | grep "used_memory:" | cut -d: -f2 | tr -d '\r' || echo "0")
        if [[ "$redis_memory" != "0" ]]; then
            local redis_memory_mb=$(echo "$redis_memory / 1024 / 1024" | bc -l | xargs printf "%.2f")
            log_metric "$timestamp" "redis_memory_mb" "$redis_memory_mb" "normal"
        fi
    else
        log_metric "$timestamp" "redis_connection_time" "timeout" "critical"
    fi
}

generate_html_dashboard() {
    local start_time="$1"
    local end_time="$2"
    local total_samples="$3"
    
    # Parse metrics data
    local cpu_data=""
    local memory_data=""
    local response_time_data=""
    local disk_data=""
    
    for metric in "${METRICS_DATA[@]}"; do
        local timestamp=$(echo "$metric" | jq -r '.timestamp')
        local metric_name=$(echo "$metric" | jq -r '.metric')
        local value=$(echo "$metric" | jq -r '.value')
        
        case $metric_name in
            "cpu_usage")
                cpu_data+="{x:'$timestamp',y:$value},"
                ;;
            "memory_usage")
                memory_data+="{x:'$timestamp',y:$value},"
                ;;
            "response_time")
                if [[ "$value" != "0" ]]; then
                    response_time_data+="{x:'$timestamp',y:$value},"
                fi
                ;;
            "disk_usage")
                disk_data+="{x:'$timestamp',y:$value},"
                ;;
        esac
    done
    
    # Generate HTML dashboard
    cat > "$OUTPUT_FILE" << EOF
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HD Tickets - Performance Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            margin: 0; 
            padding: 20px; 
            background-color: #f5f5f5; 
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        .metric-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .metric-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #333;
        }
        .chart-container {
            position: relative;
            height: 200px;
        }
        .summary-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #667eea;
        }
        .stat-label {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        .status-normal { color: #28a745; }
        .status-warning { color: #ffc107; }
        .status-critical { color: #dc3545; }
        .footer {
            margin-top: 30px;
            text-align: center;
            color: #666;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🚀 HD Tickets - Performance Dashboard</h1>
        <p>Monitoring Period: $start_time to $end_time (Total Samples: $total_samples)</p>
        <p>Generated: $(date '+%Y-%m-%d %H:%M:%S')</p>
    </div>

    <div class="metrics-grid">
        <div class="metric-card">
            <div class="metric-title">CPU Usage (%)</div>
            <div class="chart-container">
                <canvas id="cpuChart"></canvas>
            </div>
        </div>
        
        <div class="metric-card">
            <div class="metric-title">Memory Usage (%)</div>
            <div class="chart-container">
                <canvas id="memoryChart"></canvas>
            </div>
        </div>
        
        <div class="metric-card">
            <div class="metric-title">Application Response Time (seconds)</div>
            <div class="chart-container">
                <canvas id="responseChart"></canvas>
            </div>
        </div>
        
        <div class="metric-card">
            <div class="metric-title">Disk Usage (%)</div>
            <div class="chart-container">
                <canvas id="diskChart"></canvas>
            </div>
        </div>
    </div>

    <div class="summary-stats">
        <div class="stat-card">
            <div class="stat-value">$(hostname)</div>
            <div class="stat-label">Server Hostname</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">$(php -r 'echo PHP_VERSION;')</div>
            <div class="stat-label">PHP Version</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">$(cd "$PROJECT_DIR" && php artisan --version | cut -d' ' -f3)</div>
            <div class="stat-label">Laravel Version</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">$INTERVAL s</div>
            <div class="stat-label">Sample Interval</div>
        </div>
    </div>

    <div class="footer">
        <p>HD Tickets Performance Monitoring System | Generated by performance-monitor.sh</p>
        <p>Raw metrics available in: $(basename "$JSON_LOG")</p>
    </div>

    <script>
        // Chart configuration
        const chartConfig = {
            type: 'line',
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        type: 'time',
                        time: {
                            parser: 'YYYY-MM-DD HH:mm:ss'
                        }
                    },
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        };

        // CPU Chart
        new Chart(document.getElementById('cpuChart'), {
            ...chartConfig,
            data: {
                datasets: [{
                    label: 'CPU Usage',
                    data: [$cpu_data],
                    borderColor: '#dc3545',
                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                    tension: 0.4
                }]
            }
        });

        // Memory Chart
        new Chart(document.getElementById('memoryChart'), {
            ...chartConfig,
            data: {
                datasets: [{
                    label: 'Memory Usage',
                    data: [$memory_data],
                    borderColor: '#ffc107',
                    backgroundColor: 'rgba(255, 193, 7, 0.1)',
                    tension: 0.4
                }]
            }
        });

        // Response Time Chart
        new Chart(document.getElementById('responseChart'), {
            ...chartConfig,
            data: {
                datasets: [{
                    label: 'Response Time',
                    data: [$response_time_data],
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    tension: 0.4
                }]
            }
        });

        // Disk Chart
        new Chart(document.getElementById('diskChart'), {
            ...chartConfig,
            data: {
                datasets: [{
                    label: 'Disk Usage',
                    data: [$disk_data],
                    borderColor: '#6c757d',
                    backgroundColor: 'rgba(108, 117, 125, 0.1)',
                    tension: 0.4
                }]
            }
        });
    </script>
</body>
</html>
EOF
    
    echo -e "${GREEN}Performance dashboard generated: $OUTPUT_FILE${NC}"
}

display_real_time_stats() {
    clear
    echo -e "${BLUE}HD Tickets - Real-time Performance Monitor${NC}"
    echo "======================================================="
    echo "Monitoring started: $(date '+%Y-%m-%d %H:%M:%S')"
    echo "Sampling interval: ${INTERVAL}s"
    echo "Press Ctrl+C to stop and generate dashboard"
    echo ""
    
    local sample_count=0
    
    while true; do
        sample_count=$((sample_count + 1))
        
        echo -e "${YELLOW}Sample #$sample_count - $(date '+%H:%M:%S')${NC}"
        echo "----------------------------------------"
        
        get_system_metrics
        get_application_metrics
        get_database_metrics
        get_redis_metrics
        
        # Display current values
        local current_time=$(date '+%Y-%m-%d %H:%M:%S')
        echo "📊 System Metrics:"
        echo "  CPU: $(top -bn1 | grep "Cpu(s)" | awk '{print $2}' | cut -d'%' -f1)%"
        echo "  Memory: $(free | awk 'NR==2{printf "%.1f", $3*100/$2 }')%"
        echo "  Disk: $(df -h "$PROJECT_DIR" | awk 'NR==2 {print $5}')"
        echo ""
        
        # Check if we should stop (non-continuous mode)
        if [[ "$CONTINUOUS_MODE" == "false" ]] && [[ $sample_count -ge $((DURATION / INTERVAL)) ]]; then
            break
        fi
        
        sleep "$INTERVAL"
        
        # Clear previous output for next iteration
        if [[ "$CONTINUOUS_MODE" == "true" ]]; then
            tput cup 8 0
            tput ed
        fi
    done
}

cleanup() {
    echo -e "\n${YELLOW}Stopping monitoring and generating dashboard...${NC}"
    
    local end_time=$(date '+%Y-%m-%d %H:%M:%S')
    local total_samples=${#METRICS_DATA[@]}
    
    if [[ $total_samples -gt 0 ]]; then
        generate_html_dashboard "$(echo "${METRICS_DATA[0]}" | jq -r '.timestamp')" "$end_time" "$total_samples"
        
        echo -e "${GREEN}✅ Monitoring complete!${NC}"
        echo "📊 Dashboard: $OUTPUT_FILE"
        echo "📝 Raw data: $JSON_LOG"
        echo "📈 Total samples: $total_samples"
    else
        echo -e "${RED}❌ No metrics collected${NC}"
    fi
    
    exit 0
}

# Set up signal handlers
trap cleanup SIGINT SIGTERM

# Initialize JSON log
echo "[" > "$JSON_LOG"

# Main execution
echo -e "${BLUE}HD Tickets Performance Monitor Starting...${NC}"
echo "Duration: ${DURATION}s | Interval: ${INTERVAL}s | Output: $(basename "$OUTPUT_FILE")"
echo ""

START_TIME=$(date '+%Y-%m-%d %H:%M:%S')

if [[ "$CONTINUOUS_MODE" == "true" ]]; then
    echo -e "${YELLOW}Running in continuous mode (Ctrl+C to stop)${NC}"
fi

display_real_time_stats

# Clean up JSON log
sed -i '$ s/,$//' "$JSON_LOG"
echo "]" >> "$JSON_LOG"

cleanup
