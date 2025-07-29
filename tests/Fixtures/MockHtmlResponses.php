<?php

namespace Tests\Fixtures;

class MockHtmlResponses
{
    public static function getFunZoneSearchResults(): string
    {
        return '<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <title>FunZone - Vyhľadávanie eventov</title>
</head>
<body>
    <div class="search-results">
        <div class="event-card" data-event-id="123">
            <h3 class="event-title">Rock koncert - Bratislava</h3>
            <a href="/event/rock-koncert-bratislava-123" class="event-link">Zobraziť podrobnosti</a>
            <span class="date">15.01.2025 20:00</span>
            <span class="venue">Incheba Expo Arena</span>
            <span class="location">Bratislava</span>
            <div class="price-info">
                <span class="price">€35</span>
                <span class="price">€45</span>
            </div>
            <span class="category">koncert</span>
            <span class="available">120 lístkov dostupných</span>
        </div>
        
        <div class="event-card" data-event-id="456">
            <h3 class="event-title">Divadelné predstavenie - Hamlet</h3>
            <a href="/event/hamlet-divadlo-456" class="event-link">Zobraziť podrobnosti</a>
            <span class="date">18.01.2025 19:30</span>
            <span class="venue">Slovenské národné divadlo</span>
            <span class="location">Bratislava</span>
            <div class="price-info">
                <span class="price">€20</span>
                <span class="price">€30</span>
                <span class="price">€50</span>
            </div>
            <span class="category">divadlo</span>
            <span class="available">85 lístkov dostupných</span>
        </div>
        
        <div class="event-card" data-event-id="789">
            <h3 class="event-title">Jazz festival - Košice</h3>
            <a href="/event/jazz-festival-kosice-789" class="event-link">Zobraziť podrobnosti</a>
            <span class="date">25.01.2025 18:00</span>
            <span class="venue">Kultúrny park</span>
            <span class="location">Košice</span>
            <div class="price-info">
                <span class="price">€25</span>
                <span class="price">€40</span>
            </div>
            <span class="category">festival</span>
            <span class="status">dostupné</span>
        </div>
    </div>
</body>
</html>';
    }

    public static function getFunZoneEventDetails(): string
    {
        return '<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <title>Rock koncert - Bratislava | FunZone</title>
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Event",
        "name": "Rock koncert - Bratislava",
        "startDate": "2025-01-15T20:00:00+01:00",
        "location": {
            "@type": "Place",
            "name": "Incheba Expo Arena",
            "address": "Viedenská cesta 3-7, 851 01 Bratislava"
        },
        "offers": [
            {
                "@type": "Offer",
                "price": "35",
                "priceCurrency": "EUR",
                "name": "Štandardný lístok"
            },
            {
                "@type": "Offer", 
                "price": "45",
                "priceCurrency": "EUR",
                "name": "VIP lístok"
            }
        ]
    }
    </script>
</head>
<body>
    <main class="event-detail">
        <h1 class="event-title">Rock koncert - Bratislava</h1>
        
        <div class="event-info">
            <span class="event-date">15. január 2025, 20:00</span>
            <span class="venue">Incheba Expo Arena</span>
            <address class="location">Viedenská cesta 3-7, 851 01 Bratislava</address>
            <span class="organizer">Rock Productions s.r.o.</span>
            <span class="duration">Približne 3 hodiny s prestávkou</span>
        </div>
        
        <div class="description">
            <p>Jedinečný rockov koncert v srdci Bratislavy. Vystúpia najlepší slovenskí a českí rockoví umelci.</p>
            <p>Garantujeme nezabudnuteľný večer plný skvelej hudby a atmosféry.</p>
        </div>
        
        <div class="ticket-listings">
            <div class="ticket-listing">
                <span class="category">Štandardný vstup</span>
                <span class="price">€35</span>
                <span class="section">Stojisko</span>
                <span class="availability">95 lístkov</span>
            </div>
            
            <div class="ticket-listing">
                <span class="category">VIP vstup</span>
                <span class="price">€45</span>
                <span class="section">Sedadlá</span>
                <span class="availability">25 lístkov</span>
            </div>
        </div>
        
        <div class="venue-details">
            <h3>O mieste konania</h3>
            <p>Incheba Expo Arena je moderná hala s kapacitou 8000 návštevníkov.</p>
            <span class="capacity">8000</span>
            <div class="amenities">
                <span>Parkovisko</span>
                <span>Občerstvenie</span>
                <span>Bezbariérový prístup</span>
            </div>
        </div>
    </main>
</body>
</html>';
    }

    public static function getStubHubSearchResults(): string
    {
        return '<!DOCTYPE html>
<html>
<head>
    <title>Search Results | StubHub</title>
</head>
<body>
    <div class="search-results-container">
        <div class="EventCard" data-testid="event-card-12345">
            <h3 class="event-name">New York Yankees vs Boston Red Sox</h3>
            <a href="/event/yankees-vs-red-sox-12345">View Tickets</a>
            <div class="event-info">
                <span class="date">March 15, 2025 7:05 PM</span>
                <span class="venue">Yankee Stadium</span>
                <span class="location">Bronx, NY</span>
            </div>
            <div class="pricing">
                <span class="price">$45</span>
                <span class="price-label">Get in for</span>
            </div>
            <span class="ticket-count">1,250+ tickets available</span>
        </div>
        
        <div class="SearchResultCard" data-testid="event-card-67890">
            <h4 class="title">Hamilton - Broadway</h4>
            <a href="/event/hamilton-broadway-67890">View Tickets</a>
            <div class="event-info">
                <span class="event-date">March 20, 2025 8:00 PM</span>
                <span class="venue-name">Richard Rodgers Theatre</span>
                <span class="location">New York, NY</span>
            </div>
            <div class="pricing">
                <span class="price">$185</span>
                <span class="price-range">$185 - $450</span>
            </div>
            <span class="availability">350+ tickets from 15 sellers</span>
        </div>
        
        <div class="EventCard" data-testid="event-card-11111">
            <h3 class="event-name">Taylor Swift - Eras Tour</h3>
            <a href="/event/taylor-swift-eras-tour-11111">View Tickets</a>
            <div class="event-info">
                <span class="date">April 2, 2025 7:00 PM</span>
                <span class="venue">MetLife Stadium</span>
                <span class="location">East Rutherford, NJ</span>
            </div>
            <div class="pricing">
                <span class="price">$299</span>
                <span class="price-label">Starting at</span>
            </div>
            <span class="ticket-count">2,100+ tickets available</span>
        </div>
    </div>
</body>
</html>';
    }

    public static function getStubHubEventDetails(): string
    {
        return '<!DOCTYPE html>
<html>
<head>
    <title>New York Yankees vs Boston Red Sox | StubHub</title>
</head>
<body>
    <main class="event-page">
        <header class="event-header">
            <h1 class="event-title">New York Yankees vs Boston Red Sox</h1>
            <div class="event-meta">
                <span class="event-date">Saturday, March 15, 2025 at 7:05 PM</span>
                <span class="venue">Yankee Stadium</span>
                <address class="venue-address">1 E 161st St, Bronx, NY 10451</address>
            </div>
        </header>
        
        <section class="event-description">
            <p>Experience the legendary rivalry between the Yankees and Red Sox at the iconic Yankee Stadium.</p>
            <p>This classic matchup promises to deliver an unforgettable baseball experience.</p>
        </section>
        
        <section class="ticket-listings">
            <div class="listing" data-listing-id="listing-1">
                <div class="listing-info">
                    <span class="section">Field Box 020</span>
                    <span class="row">Row 8</span>
                    <span class="seats">Seats 1-2</span>
                </div>
                <span class="price">$125</span>
                <span class="total">$150 each (fees included)</span>
            </div>
            
            <div class="ticket-listing" data-listing-id="listing-2">
                <div class="listing-info">
                    <span class="section">Grandstand 420</span>
                    <span class="row">Row 12</span>
                    <span class="seats">Seats 15-16</span>
                </div>
                <span class="price">$75</span>
                <span class="total">$95 each (fees included)</span>
            </div>
            
            <div class="listing" data-listing-id="listing-3">
                <div class="listing-info">
                    <span class="section">Bleachers 203</span>
                    <span class="row">Row 20</span>
                    <span class="seats">Seats 8-9</span>
                </div>
                <span class="price">$45</span>
                <span class="total">$58 each (fees included)</span>
            </div>
        </section>
        
        <section class="venue-info">
            <h2>Yankee Stadium Information</h2>
            <p>Home of the New York Yankees since 2009, featuring modern amenities and classic baseball atmosphere.</p>
        </section>
    </main>
</body>
</html>';
    }

    public static function getViagogoSearchResults(): string
    {
        return '<!DOCTYPE html>
<html>
<head>
    <title>Concert Tickets | viagogo</title>
</head>
<body>
    <div class="search-results">
        <article class="event-card" data-event-id="viagogo-123">
            <h2 class="event-title">
                <a href="/concert-tickets/ed-sheeran-london">Ed Sheeran - London</a>
            </h2>
            <div class="event-details">
                <time class="event-date">2025-05-15 19:30</time>
                <span class="venue">The O2 Arena</span>
                <span class="city">London</span>
            </div>
            <div class="price-info">
                <span class="price-from">from £89</span>
                <span class="currency">GBP</span>
            </div>
            <span class="ticket-count">500+ tickets</span>
        </article>
        
        <article class="event-card" data-event-id="viagogo-456">
            <h2 class="event-title">
                <a href="/sport-tickets/manchester-united-vs-liverpool">Manchester United vs Liverpool</a>
            </h2>
            <div class="event-details">
                <time class="event-date">2025-04-20 17:30</time>
                <span class="venue">Old Trafford</span>
                <span class="city">Manchester</span>
            </div>
            <div class="price-info">
                <span class="price-from">from £145</span>
                <span class="price-range">£145 - £650</span>
            </div>
            <span class="ticket-count">1,200+ tickets</span>
        </article>
    </div>
</body>
</html>';
    }

    public static function getTickPickSearchResults(): string
    {
        return '<!DOCTYPE html>
<html>
<head>
    <title>Concert Tickets - No Fees | TickPick</title>
</head>
<body>
    <div class="search-container">
        <div class="event-listing" data-event="tickpick-789">
            <h3 class="listing-title">
                <a href="/buy-concert-tickets/the-weeknd-after-hours-tour">The Weeknd - After Hours Tour</a>
            </h3>
            <div class="event-info">
                <span class="event-date">Jun 10, 2025 8:00 PM</span>
                <span class="venue-name">Madison Square Garden</span>
                <span class="event-location">New York, NY</span>
            </div>
            <div class="pricing-info">
                <span class="lowest-price">$89</span>
                <span class="price-range">$89 - $399</span>
                <span class="no-fees">No Fees</span>
            </div>
            <span class="listing-count">450 listings</span>
        </div>
        
        <div class="event-listing" data-event="tickpick-101">
            <h3 class="listing-title">
                <a href="/buy-sports-tickets/los-angeles-lakers-vs-boston-celtics">Lakers vs Celtics</a>
            </h3>
            <div class="event-info">
                <span class="event-date">Mar 25, 2025 10:30 PM</span>
                <span class="venue-name">Crypto.com Arena</span>
                <span class="event-location">Los Angeles, CA</span>
            </div>
            <div class="pricing-info">
                <span class="lowest-price">$125</span>
                <span class="price-range">$125 - $850</span>
                <span class="no-fees">No Fees</span>
            </div>
            <span class="listing-count">1,100 listings</span>
        </div>
    </div>
</body>
</html>';
    }

    public static function getBotDetectionResponse(): string
    {
        return '<!DOCTYPE html>
<html>
<head>
    <title>Access Denied</title>
</head>
<body>
    <div class="cf-error-container">
        <h1>Sorry, you have been blocked</h1>
        <p>You are unable to access this website. Please verify you are human by completing the CAPTCHA below.</p>
        <div class="cf-captcha-container">
            <div class="captcha-challenge">
                <p>Please complete the security check to access stubhub.com</p>
            </div>
        </div>
        <p>If you believe you have been blocked in error, please contact our support team.</p>
        <div class="cloudflare-attribution">
            <p>Protected by Cloudflare</p>
        </div>
    </div>
</body>
</html>';
    }

    public static function getRateLimitResponse(): string
    {
        return '<!DOCTYPE html>
<html>
<head>
    <title>Rate Limit Exceeded</title>
</head>
<body>
    <div class="error-container">
        <h1>429 - Too Many Requests</h1>
        <p>You have exceeded the rate limit for this service.</p>
        <p>Please wait before making additional requests.</p>
        <p>Retry after: 300 seconds</p>
    </div>
</body>
</html>';
    }

    public static function getEmptySearchResults(): string
    {
        return '<!DOCTYPE html>
<html>
<head>
    <title>Search Results</title>
</head>
<body>
    <div class="search-results">
        <div class="no-results">
            <h2>No events found</h2>
            <p>Sorry, we couldn\'t find any events matching your search criteria.</p>
            <p>Try adjusting your search terms or browse our popular events.</p>
        </div>
    </div>
</body>
</html>';
    }

    public static function getJsonLdEventData(): string
    {
        return '<!DOCTYPE html>
<html>
<head>
    <title>Concert Event</title>
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Event",
        "name": "Rock Concert 2025",
        "description": "Amazing rock concert featuring top artists",
        "startDate": "2025-06-15T20:00:00+02:00",
        "endDate": "2025-06-15T23:30:00+02:00",
        "location": {
            "@type": "Place",
            "name": "Arena Stadium",
            "address": {
                "@type": "PostalAddress",
                "streetAddress": "123 Concert Street",
                "addressLocality": "Music City",
                "postalCode": "12345",
                "addressCountry": "US"
            }
        },
        "offers": [
            {
                "@type": "Offer",
                "name": "General Admission",
                "price": "75.00",
                "priceCurrency": "USD",
                "availability": "https://schema.org/InStock",
                "validFrom": "2025-01-01T00:00:00+02:00"
            },
            {
                "@type": "Offer",
                "name": "VIP Package",
                "price": "150.00",
                "priceCurrency": "USD",
                "availability": "https://schema.org/InStock",
                "validFrom": "2025-01-01T00:00:00+02:00"
            }
        ],
        "performer": {
            "@type": "MusicGroup",
            "name": "The Rock Band"
        },
        "organizer": {
            "@type": "Organization",
            "name": "Concert Productions Inc."
        }
    }
    </script>
</head>
<body>
    <div class="event-page">
        <h1>Rock Concert 2025</h1>
        <p>Join us for an unforgettable night of rock music!</p>
    </div>
</body>
</html>';
    }
}
