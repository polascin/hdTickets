# Manchester United Official App Platform Integration

**Date:** July 24, 2025  
**Version:** 2025.07.v4.0  
**Author:** Lubomir Polascin (Ľubomír Polaščín) aka Walter Csoelle  

## 🎯 Integration Summary

Successfully added and integrated the **Manchester United Official App** platform into the HD Tickets application as the 9th supported platform.

## 📋 Files Updated

### 1. Platform Configuration Files
- ✅ **config/platforms.php**
  - Added Manchester United to `display_order` array with order 9
  - Updated `ordered_keys` array to include `manchester_united`
  - Added platform configuration with key, name, and display_name

### 2. API Configuration Files  
- ✅ **config/ticket_apis.php**
  - Added comprehensive Manchester United API configuration
  - Configured web scraping settings with respectful rate limits
  - Added mobile app support configuration
  - Set up British English localization (en-GB)
  - Configured venue information for Old Trafford
  - Added event type filtering for various competitions
  - Set up ticket category management
  - Added to platform integration management
  - Added to user rotation settings
  - Added to load balancing with weight of 12
  - Placed in medium priority tier alongside SeatGeek and Viagogo

### 3. Service Layer Integration
- ✅ **app/Services/TicketApiManager.php**
  - Added ManchesterUnitedClient import
  - Added client initialization in `initializeClients()` method
  - Added Manchester United to `extractEventsFromResponse()` method
  - Added platform-specific status mapping for Manchester United events
  - Added comprehensive status handling for football-specific statuses

### 4. Client Implementation
- ✅ **app/Services/TicketApis/ManchesterUnitedClient.php** (Already Existing)
  - Updated with proper author and version annotations
  - Existing implementation supports web scraping of fixtures
  - Handles Old Trafford venue information
  - Processes match data and ticket availability

## 🔧 Technical Configuration

### Platform Details
- **Platform Key:** `manchester_united`
- **Display Name:** Manchester United Official App
- **Display Order:** 9 (after Bandsintown)
- **Priority Tier:** Medium (alongside SeatGeek and Viagogo)
- **Load Balancing Weight:** 12

### API Configuration
- **Base URL:** https://www.manutd.com
- **Mobile App URL:** https://www.manutd.com/en/tickets
- **Implementation Type:** Web Scraping
- **Rate Limits:** 2 requests/second, 200/hour, 2000/day
- **Currency:** GBP
- **Timezone:** Europe/London
- **Language:** British English (en-GB)

### Venue Information
- **Primary Venue:** Old Trafford
- **Capacity:** 74,879
- **Address:** Sir Matt Busby Way, Old Trafford, Manchester M16 0RA, UK

### Supported Event Types
- ✅ Premier League matches
- ✅ Champions League matches  
- ✅ Europa League matches
- ✅ FA Cup matches
- ✅ Carabao Cup matches
- ✅ Friendly matches
- ❌ Women's matches (separate ticketing system)
- ❌ Youth matches (different pricing/availability)

### Ticket Categories
- ✅ Members tickets
- ✅ General sale tickets
- ✅ Hospitality packages
- ❌ Season tickets (not available for general purchase)
- ❌ Away tickets (not sold through MUFC website)

### Mobile App Features
- ✅ App-specific API endpoints
- ✅ Authentication required
- ✅ Push notification support
- ✅ Fixtures API
- ✅ Tickets API
- ✅ Memberships API

## 🔄 Status Mapping

The Manchester United client includes comprehensive status mapping:

| Manchester United Status | HD Tickets Internal Status |
|-------------------------|---------------------------|
| `scheduled` | Available |
| `available` | Available |
| `on sale` | Available | 
| `members_only` | Available |
| `general_sale` | Available |
| `sold out` | Sold Out |
| `cancelled` | Not On Sale |
| `postponed` | Not On Sale |
| `rescheduled` | Not On Sale |
| `check_website` | Unknown |

## 🚀 Integration Benefits

1. **Official Source**: Direct access to Manchester United's official ticketing
2. **Comprehensive Coverage**: Supports all major competitions
3. **Mobile App Integration**: Ready for mobile app-specific features
4. **Respectful Scraping**: Conservative rate limits for official website
5. **British Localization**: Proper UK formatting and currency
6. **Venue Accuracy**: Correct Old Trafford capacity and details
7. **Competition Filtering**: Granular control over event types
8. **Status Intelligence**: Football-specific status handling

## 📊 Platform Statistics

- **Total Platforms Supported:** 9
- **Web Scraping Platforms:** 6 (Viagogo, TickPick, FunZone, Manchester United, plus StubHub/others with scraping options)
- **API-based Platforms:** 3 (Ticketmaster, SeatGeek, Eventbrite)
- **Sports-focused Platforms:** 2 (SeatGeek, Manchester United)
- **Regional Platforms:** 2 (FunZone for Slovakia, Manchester United for UK)

## 🎯 Usage Instructions

### Environment Configuration
To enable Manchester United integration, add to `.env`:
```env
MANCHESTER_UNITED_ENABLED=true
```

### Frontend Integration
The platform will automatically appear in platform selection dropdowns as:
**"Manchester United Official App"**

### API Usage
```php
// Search Manchester United fixtures
$apiManager = new TicketApiManager();
$results = $apiManager->searchEvents([
    'q' => 'Liverpool'  // Search for matches against Liverpool
], ['manchester_united']);

// Check if platform is available
if ($apiManager->isPlatformAvailable('manchester_united')) {
    // Platform is enabled and ready
}
```

## 📈 Performance Considerations

### Rate Limiting
- Very conservative rate limits to respect official website
- 2 requests per second maximum
- Built-in delays between requests
- Exponential backoff on failures

### Caching
- Utilizes application-wide caching system
- 5-minute cache TTL for scraping results
- Reduces load on Manchester United servers

### User Rotation
- Supports user rotation for scraping operations
- Priority users: customer, premium, agent
- No exclude patterns configured

## 🔐 Security & Compliance

- **Respectful Scraping**: Conservative rate limits
- **User Agent Rotation**: Multiple user agents including mobile
- **British English Headers**: Proper localization
- **No Authentication Bypass**: Respects membership requirements
- **Terms Compliance**: Only scrapes publicly available fixture data

## ✅ Integration Status

**Status: ✅ COMPLETE**

The Manchester United Official App platform has been successfully integrated into HD Tickets and is ready for use. All configuration files have been updated, the service layer properly handles the new platform, and the existing client implementation has been enhanced with proper documentation.

## 🔄 Next Steps

1. **Environment Setup**: Add `MANCHESTER_UNITED_ENABLED=true` to `.env` file
2. **Testing**: Test platform functionality with fixture searches
3. **Monitoring**: Monitor scraping performance and success rates
4. **Optimization**: Fine-tune rate limits based on usage patterns

---

**Manchester United Official App Integration: ✅ COMPLETE**

The platform is now fully integrated and available for ticket searches across Manchester United's official fixtures and events.
