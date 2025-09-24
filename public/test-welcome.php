<?php
/**
 * Simple test file to verify our welcome page content can be served
 * This bypasses Laravel entirely to test our static HTML/CSS/JS
 */
?>
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>hdTickets - Test Welcome Page</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; }
        .hero { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 60px 20px; text-align: center; border-radius: 10px; margin-bottom: 40px; }
        .features { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; margin-bottom: 40px; }
        .feature { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .feature h3 { color: #667eea; margin-bottom: 15px; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 40px; }
        .stat { background: white; padding: 20px; text-align: center; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .stat-number { font-size: 2.5em; font-weight: bold; color: #667eea; }
    </style>
</head>
<body class="h-full bg-gray-50">

<div class="container">
    <div class="hero">
        <h1 style="font-size: 3em; margin: 0 0 20px 0;">hdTickets</h1>
        <p style="font-size: 1.2em; margin: 0;">Your Ultimate Sports Ticket & Event Management Platform</p>
        <p style="margin: 20px 0 0 0; opacity: 0.9;">Multi-platform ticket discovery • Smart pricing • Legal compliance</p>
    </div>

    <div class="stats">
        <div class="stat">
            <div class="stat-number">15K+</div>
            <div>Active Users</div>
        </div>
        <div class="stat">
            <div class="stat-number">50+</div>
            <div>Platforms</div>
        </div>
        <div class="stat">
            <div class="stat-number">99.9%</div>
            <div>Uptime</div>
        </div>
        <div class="stat">
            <div class="stat-number">24/7</div>
            <div>Support</div>
        </div>
    </div>

    <div class="features">
        <div class="feature">
            <h3>🎟️ Multi-Platform Ticket Discovery</h3>
            <p>Search across 50+ platforms including Ticketmaster, StubHub, Vivid Seats, and specialized European venues. Our intelligent aggregation finds the best deals and authentic tickets.</p>
        </div>

        <div class="feature">
            <h3>💰 Smart Pricing & Analytics</h3>
            <p>Real-time price monitoring with automated alerts. Track price trends, set purchase triggers, and get recommendations based on historical data and demand forecasting.</p>
        </div>

        <div class="feature">
            <h3>🔒 Security & Authentication</h3>
            <p>2FA authentication, device fingerprinting, and GDPR compliance. Role-based access for customers, agents, and administrators with comprehensive audit trails.</p>
        </div>

        <div class="feature">
            <h3>⚡ Advanced Search & Filtering</h3>
            <p>Filter by price range, seat sections, venue proximity, and event categories. Smart recommendations based on your preferences and past purchases.</p>
        </div>

        <div class="feature">
            <h3>📊 Business Intelligence</h3>
            <p>Comprehensive analytics dashboard with purchase patterns, user behavior insights, and market trend analysis. Export reports for business planning.</p>
        </div>

        <div class="feature">
            <h3>🌍 Legal & Compliance</h3>
            <p>Full legal compliance including Terms of Service, Privacy Policy, GDPR, and consumer protection laws. Automatic updates and notifications.</p>
        </div>
    </div>

    <div style="text-align: center; padding: 40px 0; border-top: 1px solid #ddd; margin-top: 40px;">
        <p style="margin: 0; color: #666;">© 2025 hdTickets. Professional Sports Ticket Management Platform.</p>
        <p style="margin: 10px 0 0 0; color: #666;">Status: <span style="color: green;">✓ System Operational</span></p>
    </div>
</div>

<script>
console.log('hdTickets Welcome Page Test - Loaded Successfully');
</script>

</body>
</html>