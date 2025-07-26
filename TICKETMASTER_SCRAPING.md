# Ticketmaster Scraping Documentation

This document describes how to use the Ticketmaster scraping functionality integrated into the HD Tickets system.

## Overview

The Ticketmaster scraping system allows you to:
- Search for events on Ticketmaster
- Scrape detailed event information  
- Import events as tickets into your system
- Monitor scraping statistics

## Usage Examples

### Command Line
```bash
php artisan ticketmaster:scrape "concert" --limit=10
php artisan ticketmaster:scrape "basketball" --location="New York" --limit=20
php artisan ticketmaster:scrape "theater" --dry-run --limit=5
```

### API Endpoints
- POST /api/v1/ticketmaster/search - Search events
- POST /api/v1/ticketmaster/event-details - Get event details  
- POST /api/v1/ticketmaster/import - Import events as tickets (Agent/Admin)
- POST /api/v1/ticketmaster/import-urls - Import specific URLs (Agent/Admin)
- GET /api/v1/ticketmaster/stats - Get scraping statistics

## Features
- Smart data extraction with multiple CSS selectors
- Duplicate prevention
- Intelligent categorization and tagging
- Rate limiting and error handling
- Comprehensive logging and monitoring

## API Usage Example
```json
POST /api/v1/ticketmaster/search
{
    "keyword": "concert", 
    "location": "Los Angeles",
    "limit": 20
}
```
