# HD Tickets Advanced Analytics System - Quick Start Guide

## 🚀 Immediate Access - System Ready!

The HD Tickets Advanced Analytics System is **fully operational** and ready for immediate use. Here's how to get started right now:

---

## 📊 Dashboard Access (Web Interface)

### **Access the Analytics Dashboard**
```
URL: https://your-domain.com/dashboard/analytics
Required: Admin or Agent role
```

### **Dashboard Features Available Now:**
- ✅ **Real-time Overview Metrics** - Key performance indicators
- ✅ **Platform Performance Charts** - Multi-platform comparison
- ✅ **Pricing Trends Analysis** - Historical price movements  
- ✅ **Event Popularity Tracking** - Trending events and recommendations
- ✅ **Anomaly Detection Alerts** - Real-time issue identification
- ✅ **Predictive Insights** - ML-powered forecasting
- ✅ **Interactive Filters** - Date, sport, platform, price range
- ✅ **Data Export** - CSV, PDF, JSON, XLSX formats

### **Quick Dashboard Tour:**
1. **Overview Cards** - Total events, tickets, avg prices, growth metrics
2. **Charts Section** - Interactive visualizations with Chart.js/D3.js
3. **Filter Panel** - Customize views by date range, sport, platform
4. **Export Button** - Download data in multiple formats
5. **Real-time Alerts** - Current anomalies and system notifications

---

## 🔌 API Access (Business Intelligence)

### **API Base URL**
```
https://your-domain.com/api/v1/bi/
```

### **Authentication Required**
All API endpoints require Bearer token authentication:

```bash
# Step 1: Login to get your API token
curl -X POST https://your-domain.com/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"your-email@domain.com","password":"your-password"}'

# Step 2: Use token in API calls
curl -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  https://your-domain.com/api/v1/bi/health
```

### **Ready-to-Use API Endpoints:**

#### **1. System Health Check**
```bash
GET /api/v1/bi/health
# Returns: System status, version, available endpoints, rate limits
```

#### **2. Analytics Overview**
```bash
GET /api/v1/bi/analytics/overview?sport=football&date_from=2024-01-01
# Returns: High-level KPIs, platform performance, pricing trends, event popularity
```

#### **3. Detailed Ticket Metrics**
```bash
GET /api/v1/bi/tickets/metrics?include_historical=true&price_min=50&price_max=500
# Returns: Ticket analysis, price distribution, sport breakdown, platform comparison
```

#### **4. Platform Performance**
```bash
GET /api/v1/bi/platforms/performance?include_metrics[]=pricing&include_metrics[]=volume
# Returns: Platform analytics with pricing, volume, trends, reliability metrics
```

#### **5. Competitive Intelligence**
```bash
GET /api/v1/bi/competitive/intelligence?analysis_type=pricing&include_recommendations=true
# Returns: Market analysis, price comparison, competitive positioning, business recommendations
```

#### **6. Predictive Insights**
```bash
GET /api/v1/bi/predictive/insights?prediction_type=price&horizon_days=30&sport=football
# Returns: ML-powered predictions for pricing, demand, event success, market trends
```

#### **7. Current Anomalies**
```bash
GET /api/v1/bi/anomalies/current?severity=high&category=price&limit=20
# Returns: Real-time anomalies with severity levels and detailed analysis
```

#### **8. Data Export**
```bash
POST /api/v1/bi/export/dataset
Content-Type: application/json

{
  "dataset": "tickets",
  "format": "csv",
  "date_from": "2024-01-01",
  "date_to": "2024-12-31",
  "fields": ["id", "event_name", "sport", "price", "venue"]
}
# Returns: Export ID and download URL for bulk data extraction
```

---

## 📈 Automated Reports

### **Configure Scheduled Reports**
```php
use App\Models\ScheduledReport;

// Create a daily analytics report
ScheduledReport::create([
    'name' => 'Daily Analytics Summary',
    'type' => 'daily_analytics',
    'frequency' => 'daily', // daily, weekly, monthly
    'format' => 'pdf',
    'recipients' => ['manager@hdtickets.com', 'analytics@hdtickets.com'],
    'filters' => [
        'include_charts' => true,
        'sports' => ['football', 'basketball'],
    ],
    'is_active' => true
]);
```

### **Manual Report Generation**
```bash
# Generate all due reports
php artisan reports:generate

# Generate specific report by ID
php artisan reports:generate --report-id=1

# Generate by report type
php artisan reports:generate --type=daily_analytics
```

---

## 🔍 Real-World Usage Examples

### **Example 1: Monitor Football Ticket Prices**
```javascript
// Dashboard: Filter for football tickets in the last 30 days
const filters = {
    sport: 'football',
    date_from: '2024-08-01',
    date_to: '2024-09-01',
    price_min: 50
};

// API: Get detailed metrics
fetch('/api/v1/bi/tickets/metrics?' + new URLSearchParams(filters), {
    headers: { 'Authorization': `Bearer ${token}` }
})
.then(response => response.json())
.then(data => console.log(data.data.summary));
```

### **Example 2: Competitive Analysis for Pricing Strategy**
```bash
# Get competitive pricing analysis with business recommendations
curl -H "Authorization: Bearer TOKEN" \
  "https://hdtickets.com/api/v1/bi/competitive/intelligence?analysis_type=pricing&include_recommendations=true&sport=football"

# Response includes:
# - Cross-platform price comparison  
# - Competitive advantages analysis
# - Strategic pricing recommendations
# - Market positioning insights
```

### **Example 3: Anomaly Detection for Price Monitoring**
```bash
# Monitor for critical price anomalies
curl -H "Authorization: Bearer TOKEN" \
  "https://hdtickets.com/api/v1/bi/anomalies/current?severity=critical&category=price"

# Get real-time alerts for:
# - Sudden price drops/spikes
# - Unusual volume changes
# - Platform-specific issues
# - Market disruptions
```

### **Example 4: Predictive Analytics for Business Planning**
```bash
# Get 30-day demand forecast for basketball events
curl -H "Authorization: Bearer TOKEN" \
  "https://hdtickets.com/api/v1/bi/predictive/insights?prediction_type=demand&sport=basketball&horizon_days=30"

# Returns:
# - Demand forecasts with confidence intervals
# - Price prediction models
# - Market trend analysis
# - Business recommendations
```

---

## 📊 Power BI / Tableau Integration

### **Power BI Data Source Setup**
```powerquery
let
    // Configure API connection
    BaseUrl = "https://hdtickets.com/api/v1/bi/",
    Token = "YOUR_API_TOKEN_HERE",
    
    // Get analytics overview
    Source = Json.Document(Web.Contents(
        BaseUrl & "analytics/overview",
        [
            Headers = [
                #"Authorization" = "Bearer " & Token,
                #"Content-Type" = "application/json"
            ]
        ]
    )),
    
    // Extract data
    Data = Source[data],
    OverviewMetrics = Data[overview_metrics]
in
    OverviewMetrics
```

### **Tableau REST API Connection**
```python
import requests
import pandas as pd

# API Configuration
base_url = "https://hdtickets.com/api/v1/bi/"
headers = {
    'Authorization': f'Bearer {api_token}',
    'Content-Type': 'application/json'
}

# Fetch platform performance data
response = requests.get(
    f"{base_url}platforms/performance",
    headers=headers,
    params={'include_metrics': ['pricing', 'volume', 'trends']}
)

# Convert to DataFrame for Tableau
data = response.json()['data']
df = pd.DataFrame(data['platforms'])
```

---

## 🛠️ Advanced Configuration

### **Environment Variables**
```bash
# Add to your .env file for optimal performance
ANALYTICS_CACHE_TTL=3600
PREDICTIVE_ANALYTICS_ENABLED=true
ANOMALY_DETECTION_ENABLED=true
BI_API_RATE_LIMIT=100
AUTOMATED_REPORTS_ENABLED=true
```

### **Performance Optimization**
```bash
# Clear caches for fresh data
php artisan cache:forget analytics*

# Optimize for large datasets
php artisan config:cache
php artisan route:cache

# Start background processing
php artisan horizon
```

### **Monitoring Commands**
```bash
# Check system health
php artisan route:list --path=analytics | wc -l

# View recent analytics activity
tail -f storage/logs/laravel.log | grep analytics

# Monitor API performance
php artisan horizon:status
```

---

## 🔐 Security & Access Control

### **Role Requirements**
- **Dashboard Access:** Admin or Agent role required
- **API Access:** Valid Bearer token + Admin/Agent role
- **Report Management:** Admin role for system-wide reports
- **Export Features:** Available to all authorized users

### **Rate Limits**
- **Standard Endpoints:** 100 requests/hour
- **Heavy Analytics:** 20 requests/hour
- **Data Export:** 5 requests/hour

### **Data Security**
- All API calls require authentication
- Export files automatically expire after 24 hours
- Sensitive data is never included in exports
- Complete audit logging for compliance

---

## 📞 Immediate Support

### **System Status Check**
```bash
# Verify all systems operational
curl -H "Authorization: Bearer TOKEN" https://hdtickets.com/api/v1/bi/health

# Expected response: {"success":true,"data":{"status":"healthy"}}
```

### **Common Quick Fixes**
```bash
# If dashboard loads slowly
php artisan cache:clear

# If API returns errors  
php artisan config:clear && php artisan route:clear

# If exports fail
chmod -R 755 storage/app/analytics/exports/
```

### **Log Monitoring**
```bash
# Watch for issues
tail -f storage/logs/laravel.log

# Analytics-specific logs
grep "analytics" storage/logs/laravel.log | tail -20
```

---

## 🎯 Quick Wins - Try These First!

### **1. Dashboard Overview (2 minutes)**
- Visit `/dashboard/analytics`
- Explore the overview metrics cards
- Try changing date filters
- Click on different chart sections

### **2. API Health Check (1 minute)**
```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
  https://your-domain.com/api/v1/bi/health
```

### **3. Export Sample Data (3 minutes)**
- Use dashboard export button
- Or API: POST to `/api/v1/bi/export/dataset`
- Download and examine the data

### **4. Set Up First Automated Report (5 minutes)**
```bash
php artisan tinker
>>> App\Models\ScheduledReport::create([
    'name' => 'Test Report',
    'type' => 'daily_analytics', 
    'frequency' => 'daily',
    'format' => 'pdf',
    'recipients' => ['your-email@domain.com'],
    'is_active' => true
]);
```

---

## 🚀 You're Ready!

The **HD Tickets Advanced Analytics System** is fully operational and ready to deliver powerful insights:

✅ **Dashboard:** Real-time visualization at `/dashboard/analytics`  
✅ **API:** 10 endpoints for programmatic access  
✅ **Reports:** Automated delivery system configured  
✅ **Export:** Multi-format data extraction ready  
✅ **Security:** Role-based access properly enforced  

**Start exploring your sports event ticket data with enterprise-level analytics!** 🎉

---

### 📈 Next Level Features to Explore:
1. **Competitive Intelligence** - Market positioning analysis
2. **Predictive Analytics** - ML-powered forecasting  
3. **Anomaly Detection** - Real-time issue identification
4. **Advanced Exports** - Custom data extraction
5. **API Integration** - Connect with external BI tools

The system is production-ready and scalable for your growing analytics needs!
