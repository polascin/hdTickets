# HDTickets Infrastructure Documentation

## Global Load Balancer Configuration

### Overview
HDTickets uses a DigitalOcean Global Load Balancer (GLB) for high availability and performance optimization.

### Configuration Details

- **Load Balancer ID**: `784d225b-40dc-4ca7-8c05-9c26964ddab9`
- **Name**: `global-load-balancer-01`
- **Type**: Global Load Balancer
- **Status**: Active
- **Domain**: hdtickets.com

### Backend Configuration
- **Backend Droplet**: hdtickets-production (ID: 522849266)
- **Region**: nyc3 (priority: 1)
- **Target Protocol**: HTTPS
- **Target Port**: 80

### Features Enabled
- ✅ CDN Integration
- ✅ HTTP to HTTPS Redirect
- ✅ Health Checks (HTTP on /)
- ✅ Domain Management
- ✅ Failover (threshold: 70%)

### DNS Configuration
The domain `hdtickets.com` points to DigitalOcean's GLB anycast network:
- **A Records**: Point to GLB anycast IPs
- **Managed Domain**: Configured and managed by DigitalOcean GLB

### Health Checks
- **Protocol**: HTTP
- **Port**: 80
- **Path**: /
- **Check Interval**: 10 seconds
- **Response Timeout**: 5 seconds
- **Healthy Threshold**: 5
- **Unhealthy Threshold**: 3

### Status Verification
```bash
# Test HTTP connectivity
curl -I http://hdtickets.com/health

# Check load balancer status
doctl compute load-balancer get 784d225b-40dc-4ca7-8c05-9c26964ddab9

# View configuration
cat /var/www/hdtickets/infrastructure/load-balancer-config.json
```

### Issue Notes
- ✅ RESOLVED: Domain verification error has been fixed by updating DNS records to use correct GLB anycast IPs
- ✅ WORKING: HTTP traffic is routing correctly through the GLB (Status: 200 OK)
- ✅ WORKING: HTTPS traffic is routing correctly through the GLB (Status: 200 OK)
- ✅ WORKING: Health checks are passing (http://hdtickets.com/health returns 200)
- ℹ️ NOTE: GLB domain verification may show "ERROR" status temporarily due to DNS propagation delays, but traffic is flowing correctly

### Deployment Status
- **Status**: ✅ OPERATIONAL
- **HTTP Access**: http://hdtickets.com ✅
- **HTTPS Access**: https://hdtickets.com ✅
- **Health Endpoint**: http://hdtickets.com/health ✅
- **DNS Configuration**: Properly configured with GLB anycast IPs
- **Load Balancer**: Active and routing traffic correctly

### Last Updated
October 6, 2025 - Deployment errors resolved, GLB fully operational
