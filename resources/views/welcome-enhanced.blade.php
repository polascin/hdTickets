<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description"
      content="HD Tickets - Professional Sports Event Ticket Monitoring & Automation Platform. Advanced scraping, real-time monitoring, and automated purchasing across multiple platforms.">
    <meta name="keywords"
      content="sports tickets, ticket monitoring, automated purchasing, ticketmaster scraping, stubhub, seatgeek, sports events">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>HD Tickets - Professional Sports Ticket Monitoring Platform</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet" />

    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
      :root {
        --primary-blue: #1e40af;
        --primary-blue-light: #3b82f6;
        --primary-purple: #8b5cf6;
        --accent-green: #10b981;
        --accent-red: #ef4444;
        --text-gray: #6b7280;
        --text-dark: #111827;
        --bg-light: #f8fafc;
        --bg-dark: #0f172a;
        --border-light: #e5e7eb;
        --shadow-light: rgba(0, 0, 0, 0.05);
        --shadow-medium: rgba(0, 0, 0, 0.1);
        --shadow-dark: rgba(0, 0, 0, 0.25);
      }

      * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
      }

      body {
        font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
        line-height: 1.6;
        color: var(--text-dark);
        background: linear-gradient(135deg, var(--primary-blue) 0%, var(--primary-blue-light) 50%, var(--primary-purple) 100%);
        min-height: 100vh;
      }

      .container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 20px;
      }

      /* Header Styles */
      .header {
        padding: 20px 0;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        position: relative;
      }

      .nav {
        display: flex;
        justify-content: space-between;
        align-items: center;
      }

      .logo {
        display: flex;
        align-items: center;
        color: white;
        font-size: 28px;
        font-weight: 800;
        text-decoration: none;
      }

      .logo i {
        margin-right: 12px;
        color: var(--accent-green);
      }

      .nav-links {
        display: flex;
        gap: 30px;
        align-items: center;
      }

      .nav-links a {
        color: rgba(255, 255, 255, 0.9);
        text-decoration: none;
        font-weight: 500;
        transition: all 0.3s ease;
        padding: 8px 16px;
        border-radius: 8px;
      }

      .nav-links a:hover {
        color: white;
        background: rgba(255, 255, 255, 0.1);
      }

      .btn {
        display: inline-flex;
        align-items: center;
        padding: 12px 24px;
        border-radius: 8px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
        font-size: 14px;
      }

      .btn-primary {
        background: white !important;
        color: var(--primary-blue) !important;
        font-weight: 700;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        border: 1px solid rgba(30, 64, 175, 0.2);
        text-decoration: none !important;
      }

      .btn-primary:hover {
        background: var(--bg-light) !important;
        color: var(--primary-blue) !important;
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        border-color: var(--primary-blue);
        text-decoration: none !important;
      }

      .btn-secondary {
        background: rgba(255, 255, 255, 0.1);
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.2);
      }

      .btn-secondary:hover {
        background: rgba(255, 255, 255, 0.2);
        transform: translateY(-2px);
      }

      /* Hero Section */
      .hero {
        padding: 80px 0 120px;
        text-align: center;
        color: white;
      }

      .hero h1 {
        font-size: 64px;
        font-weight: 800;
        margin-bottom: 24px;
        line-height: 1.1;
        background: linear-gradient(135deg, #ffffff 0%, #f1f5f9 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
      }

      .hero .subtitle {
        font-size: 24px;
        font-weight: 500;
        margin-bottom: 16px;
        opacity: 0.95;
      }

      .hero .description {
        font-size: 18px;
        opacity: 0.8;
        margin-bottom: 40px;
        max-width: 800px;
        margin-left: auto;
        margin-right: auto;
        line-height: 1.7;
      }

      .hero-buttons {
        display: flex;
        gap: 20px;
        justify-content: center;
        align-items: center;
        margin-bottom: 60px;
      }

      .hero-stats {
        display: flex;
        gap: 60px;
        justify-content: center;
        align-items: center;
        margin-top: 60px;
      }

      .stat-item {
        text-align: center;
      }

      .stat-number {
        font-size: 36px;
        font-weight: 800;
        display: block;
        color: var(--accent-green);
      }

      .stat-label {
        font-size: 14px;
        opacity: 0.8;
        margin-top: 4px;
      }

      /* Main Content Sections */
      .content-section {
        background: white;
        margin: 0 0 80px 0;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 20px 50px var(--shadow-medium);
      }

      .section-header {
        text-align: center;
        padding: 80px 40px 40px;
        background: linear-gradient(135deg, var(--bg-light) 0%, #ffffff 100%);
      }

      .section-title {
        font-size: 48px;
        font-weight: 800;
        color: var(--text-dark);
        margin-bottom: 16px;
      }

      .section-subtitle {
        font-size: 20px;
        color: var(--text-gray);
        max-width: 600px;
        margin: 0 auto;
      }

      /* Features Grid */
      .features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 40px;
        padding: 40px;
      }

      .feature-card {
        background: white;
        border-radius: 16px;
        padding: 40px;
        border: 1px solid var(--border-light);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
      }

      .feature-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px var(--shadow-medium);
        border-color: var(--primary-blue-light);
      }

      .feature-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--primary-blue) 0%, var(--primary-purple) 100%);
      }

      .feature-icon {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, var(--primary-blue) 0%, var(--primary-purple) 100%);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 24px;
      }

      .feature-icon i {
        font-size: 24px;
        color: white;
      }

      .feature-title {
        font-size: 24px;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 12px;
      }

      .feature-description {
        color: var(--text-gray);
        line-height: 1.7;
        margin-bottom: 20px;
      }

      .feature-highlights {
        list-style: none;
      }

      .feature-highlights li {
        color: var(--text-gray);
        margin-bottom: 8px;
        display: flex;
        align-items: center;
      }

      .feature-highlights li::before {
        content: '✓';
        color: var(--accent-green);
        font-weight: bold;
        margin-right: 12px;
        width: 16px;
      }

      /* Platform Integration Section */
      .platforms-section {
        background: linear-gradient(135deg, var(--bg-dark) 0%, #1e293b 100%);
        color: white;
        padding: 80px 40px;
      }

      .platforms-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 30px;
        margin-top: 60px;
      }

      .platform-card {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 16px;
        padding: 30px;
        text-align: center;
        border: 1px solid rgba(255, 255, 255, 0.1);
        transition: all 0.3s ease;
      }

      .platform-card:hover {
        background: rgba(255, 255, 255, 0.15);
        transform: translateY(-4px);
      }

      .platform-logo {
        font-size: 48px;
        margin-bottom: 20px;
        color: var(--accent-green);
      }

      .platform-name {
        font-size: 20px;
        font-weight: 600;
        margin-bottom: 12px;
      }

      .platform-description {
        opacity: 0.8;
        font-size: 14px;
      }

      /* Technology Stack */
      .tech-stack {
        padding: 80px 40px;
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
      }

      .tech-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 30px;
        margin-top: 60px;
      }

      .tech-item {
        text-align: center;
        padding: 30px;
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 12px var(--shadow-light);
        transition: all 0.3s ease;
      }

      .tech-item:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 25px var(--shadow-medium);
      }

      .tech-icon {
        font-size: 40px;
        margin-bottom: 16px;
        color: var(--primary-blue);
      }

      .tech-name {
        font-weight: 600;
        color: var(--text-dark);
      }

      /* Security Features */
      .security-section {
        background: linear-gradient(135deg, var(--primary-blue) 0%, var(--primary-purple) 100%);
        color: white;
        padding: 80px 40px;
      }

      .security-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 40px;
        margin-top: 60px;
      }

      .security-card {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 16px;
        padding: 40px;
        border: 1px solid rgba(255, 255, 255, 0.2);
      }

      .security-icon {
        font-size: 36px;
        margin-bottom: 20px;
        color: var(--accent-green);
      }

      .security-title {
        font-size: 20px;
        font-weight: 600;
        margin-bottom: 12px;
      }

      /* CTA Section */
      .cta-section {
        text-align: center;
        padding: 100px 40px;
        background: linear-gradient(135deg, var(--text-dark) 0%, #374151 100%);
        color: white;
      }

      .cta-title {
        font-size: 48px;
        font-weight: 800;
        margin-bottom: 24px;
      }

      .cta-description {
        font-size: 20px;
        opacity: 0.9;
        margin-bottom: 40px;
        max-width: 600px;
        margin-left: auto;
        margin-right: auto;
      }

      .cta-buttons {
        display: flex;
        gap: 20px;
        justify-content: center;
        align-items: center;
      }

      /* Footer */
      .footer {
        background: var(--text-dark);
        color: white;
        padding: 60px 40px 40px;
        text-align: center;
      }

      .footer-content {
        max-width: 800px;
        margin: 0 auto;
      }

      .footer-links {
        display: flex;
        gap: 30px;
        justify-content: center;
        margin-bottom: 30px;
      }

      .footer-links a {
        color: rgba(255, 255, 255, 0.7);
        text-decoration: none;
        transition: color 0.3s ease;
      }

      .footer-links a:hover {
        color: white;
      }

      .footer-text {
        opacity: 0.7;
        font-size: 14px;
      }

      /* Responsive Design */
      @media (max-width: 768px) {
        .hero h1 {
          font-size: 42px;
        }

        .hero .subtitle {
          font-size: 20px;
        }

        .hero-buttons {
          flex-direction: column;
          gap: 16px;
        }

        .hero-stats {
          flex-direction: column;
          gap: 30px;
        }

        .features-grid {
          grid-template-columns: 1fr;
        }

        .section-title {
          font-size: 36px;
        }

        .nav-links {
          display: none;
        }
      }

      @media (max-width: 480px) {
        .container {
          padding: 0 16px;
        }

        .hero {
          padding: 60px 0 80px;
        }

        .feature-card {
          padding: 30px;
        }

        .platforms-section,
        .tech-stack,
        .security-section,
        .cta-section {
          padding: 60px 20px;
        }
      }

      /* Animation */
      .animate-fade-in {
        animation: fadeIn 0.8s ease-out;
      }

      @keyframes fadeIn {
        from {
          opacity: 0;
          transform: translateY(30px);
        }

        to {
          opacity: 1;
          transform: translateY(0);
        }
      }

      /* Loading Animation */
      .loading-spinner {
        display: inline-block;
        width: 20px;
        height: 20px;
        border: 3px solid rgba(255, 255, 255, 0.3);
        border-radius: 50%;
        border-top-color: white;
        animation: spin 1s ease-in-out infinite;
        margin-right: 8px;
      }

      @keyframes spin {
        to {
          transform: rotate(360deg);
        }
      }
    </style>
  </head>

  <body>
    <!-- Header -->
    <header class="header">
      <div class="container">
        <nav class="nav">
          <a href="/" class="logo">
            <i class="fas fa-ticket-alt"></i>
            HD Tickets
          </a>
          <div class="nav-links">
            <a href="#features">Features</a>
            <a href="#platforms">Platforms</a>
            <a href="#technology">Technology</a>
            <a href="#security">Security</a>
            @guest
              <a href="{{ route('login') }}" class="btn btn-secondary">Login</a>
              <a href="{{ route('register') }}" class="btn btn-primary">Get Started</a>
            @else
              <a href="{{ route('dashboard') }}" class="btn btn-primary">Dashboard</a>
            @endguest
          </div>
        </nav>
      </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
      <div class="container animate-fade-in">
        <h1>Professional Sports Ticket Monitoring Platform</h1>
        <p class="subtitle">Advanced Scraping, Real-Time Monitoring & Automated Purchasing</p>
        <p class="description">
          HD Tickets provides enterprise-grade sports event ticket monitoring with AI-powered automation,
          multi-platform integration, and advanced analytics. Monitor availability, track prices, and
          automate purchases across Ticketmaster, StubHub, SeatGeek, and more.
        </p>

        <div class="hero-buttons">
          @guest
            <a href="{{ route('register') }}" class="btn btn-primary">
              <i class="fas fa-rocket"></i>&nbsp;&nbsp;Start Free Trial
            </a>
            <a href="#features" class="btn btn-secondary">
              <i class="fas fa-play"></i>&nbsp;&nbsp;View Features
            </a>
          @else
            <a href="{{ route('dashboard') }}" class="btn btn-primary">
              <i class="fas fa-tachometer-alt"></i>&nbsp;&nbsp;Go to Dashboard
            </a>
            <a href="#features" class="btn btn-secondary">
              <i class="fas fa-info-circle"></i>&nbsp;&nbsp;Learn More
            </a>
          @endguest
        </div>

        <div class="hero-stats">
          <div class="stat-item">
            <span class="stat-number">15+</span>
            <span class="stat-label">Integrated Platforms</span>
          </div>
          <div class="stat-item">
            <span class="stat-number">24/7</span>
            <span class="stat-label">Monitoring</span>
          </div>
          <div class="stat-item">
            <span class="stat-number">99.9%</span>
            <span class="stat-label">Uptime</span>
          </div>
          <div class="stat-item">
            <span class="stat-number">AI</span>
            <span class="stat-label">Powered</span>
          </div>
        </div>
      </div>
    </section>

    <!-- Core Features Section -->
    <section id="features" class="content-section">
      <div class="section-header">
        <h2 class="section-title">Comprehensive Backend Features</h2>
        <p class="section-subtitle">
          Built on enterprise-grade architecture with domain-driven design, advanced security, and real-time processing
        </p>
      </div>

      <div class="features-grid">
        <!-- Ticket Monitoring & Scraping -->
        <div class="feature-card">
          <div class="feature-icon">
            <i class="fas fa-search"></i>
          </div>
          <h3 class="feature-title">Real-Time Ticket Monitoring</h3>
          <p class="feature-description">
            Advanced web scraping and API integration across 15+ major ticketing platforms with intelligent
            availability detection and price tracking.
          </p>
          <ul class="feature-highlights">
            <li>Multi-platform scraping service with rotation</li>
            <li>Real-time availability notifications</li>
            <li>Historical price analysis and trends</li>
            <li>Custom monitoring criteria and filters</li>
            <li>Anti-detection and CAPTCHA handling</li>
          </ul>
        </div>

        <!-- Automated Purchase System -->
        <div class="feature-card">
          <div class="feature-icon">
            <i class="fas fa-robot"></i>
          </div>
          <h3 class="feature-title">AI-Powered Purchase Automation</h3>
          <p class="feature-description">
            Intelligent purchase decision engine with machine learning algorithms for optimal timing
            and automated checkout processes.
          </p>
          <ul class="feature-highlights">
            <li>Smart purchase decision algorithms</li>
            <li>Automated checkout with payment processing</li>
            <li>Purchase queue management system</li>
            <li>Risk assessment and validation</li>
            <li>Success rate optimization</li>
          </ul>
        </div>

        <!-- Multi-Platform Integration -->
        <div class="feature-card">
          <div class="feature-icon">
            <i class="fas fa-network-wired"></i>
          </div>
          <h3 class="feature-title">Multi-Platform Integration</h3>
          <p class="feature-description">
            Seamless integration with major ticketing platforms including Ticketmaster, StubHub,
            SeatGeek, and football club official stores.
          </p>
          <ul class="feature-highlights">
            <li>Ticketmaster API integration</li>
            <li>StubHub partnership connectivity</li>
            <li>SeatGeek marketplace access</li>
            <li>Football club store scrapers</li>
            <li>Custom platform adapters</li>
          </ul>
        </div>

        <!-- Advanced Security -->
        <div class="feature-card">
          <div class="feature-icon">
            <i class="fas fa-shield-alt"></i>
          </div>
          <h3 class="feature-title">Enterprise Security</h3>
          <p class="feature-description">
            Multi-layered security architecture with advanced authentication, encryption, and
            comprehensive audit logging.
          </p>
          <ul class="feature-highlights">
            <li>Two-factor authentication (2FA)</li>
            <li>Trusted device management</li>
            <li>End-to-end encryption</li>
            <li>Security incident monitoring</li>
            <li>Comprehensive audit trails</li>
          </ul>
        </div>

        <!-- Analytics & Business Intelligence -->
        <div class="feature-card">
          <div class="feature-icon">
            <i class="fas fa-chart-line"></i>
          </div>
          <h3 class="feature-title">Advanced Analytics Engine</h3>
          <p class="feature-description">
            Comprehensive business intelligence with user behavior analysis, market trends,
            and predictive analytics for optimal decision making.
          </p>
          <ul class="feature-highlights">
            <li>Real-time analytics dashboard</li>
            <li>User behavior tracking and insights</li>
            <li>Market trend analysis</li>
            <li>Performance metrics and KPIs</li>
            <li>Automated reporting system</li>
          </ul>
        </div>

        <!-- Role-Based Access Control -->
        <div class="feature-card">
          <div class="feature-icon">
            <i class="fas fa-users-cog"></i>
          </div>
          <h3 class="feature-title">Advanced RBAC System</h3>
          <p class="feature-description">
            Sophisticated role-based access control with hierarchical permissions,
            multi-tenant support, and granular resource management.
          </p>
          <ul class="feature-highlights">
            <li>Admin, Agent, Customer, Scraper roles</li>
            <li>Granular permission system</li>
            <li>Resource-level access control</li>
            <li>Subscription-based limitations</li>
            <li>User activity monitoring</li>
          </ul>
        </div>

        <!-- Recommendation Engine -->
        <div class="feature-card">
          <div class="feature-icon">
            <i class="fas fa-brain"></i>
          </div>
          <h3 class="feature-title">AI Recommendation Engine</h3>
          <p class="feature-description">
            Machine learning-powered recommendation system with behavioral analysis,
            collaborative filtering, and personalized suggestions.
          </p>
          <ul class="feature-highlights">
            <li>Personalized event recommendations</li>
            <li>Dynamic pricing optimization</li>
            <li>User preference learning</li>
            <li>Collaborative filtering algorithms</li>
            <li>A/B testing framework</li>
          </ul>
        </div>

        <!-- Legal & Compliance -->
        <div class="feature-card">
          <div class="feature-icon">
            <i class="fas fa-balance-scale"></i>
          </div>
          <h3 class="feature-title">Legal & Compliance Management</h3>
          <p class="feature-description">
            Comprehensive legal compliance framework with GDPR support, automated document
            management, and consumer protection compliance.
          </p>
          <ul class="feature-highlights">
            <li>GDPR compliance automation</li>
            <li>Terms of Service management</li>
            <li>Privacy policy enforcement</li>
            <li>Account deletion workflows</li>
            <li>Legal document versioning</li>
          </ul>
        </div>

        <!-- Performance Optimization -->
        <div class="feature-card">
          <div class="feature-icon">
            <i class="fas fa-tachometer-alt"></i>
          </div>
          <h3 class="feature-title">High-Performance Architecture</h3>
          <p class="feature-description">
            Optimized backend architecture with Redis caching, database optimization,
            queue processing, and real-time performance monitoring.
          </p>
          <ul class="feature-highlights">
            <li>Redis caching and session management</li>
            <li>Database query optimization</li>
            <li>Background job processing</li>
            <li>Real-time performance metrics</li>
            <li>Automated scaling capabilities</li>
          </ul>
        </div>
      </div>
    </section>

    <!-- Platform Integration Section -->
    <section id="platforms" class="platforms-section">
      <div class="container">
        <div class="section-header" style="background: transparent; color: white;">
          <h2 class="section-title" style="color: white;">Platform Integrations</h2>
          <p class="section-subtitle" style="color: rgba(255, 255, 255, 0.8);">
            Seamlessly integrated with major ticketing platforms and sports venues
          </p>
        </div>

        <div class="platforms-grid">
          <div class="platform-card">
            <div class="platform-logo">
              <i class="fas fa-ticket-alt"></i>
            </div>
            <h3 class="platform-name">Ticketmaster</h3>
            <p class="platform-description">Official API integration with real-time inventory access</p>
          </div>

          <div class="platform-card">
            <div class="platform-logo">
              <i class="fas fa-exchange-alt"></i>
            </div>
            <h3 class="platform-name">StubHub</h3>
            <p class="platform-description">Secondary market monitoring with price comparison</p>
          </div>

          <div class="platform-card">
            <div class="platform-logo">
              <i class="fas fa-chart-area"></i>
            </div>
            <h3 class="platform-name">SeatGeek</h3>
            <p class="platform-description">Marketplace integration with deal scoring</p>
          </div>

          <div class="platform-card">
            <div class="platform-logo">
              <i class="fas fa-futbol"></i>
            </div>
            <h3 class="platform-name">Football Club Stores</h3>
            <p class="platform-description">Direct integration with official team stores</p>
          </div>

          <div class="platform-card">
            <div class="platform-logo">
              <i class="fas fa-basketball-ball"></i>
            </div>
            <h3 class="platform-name">NBA Official</h3>
            <p class="platform-description">NBA team and venue official ticket sources</p>
          </div>

          <div class="platform-card">
            <div class="platform-logo">
              <i class="fas fa-baseball-ball"></i>
            </div>
            <h3 class="platform-name">MLB Venues</h3>
            <p class="platform-description">Major League Baseball stadium integrations</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Technology Stack Section -->
    <section id="technology" class="tech-stack">
      <div class="container">
        <div class="section-header">
          <h2 class="section-title">Enterprise Technology Stack</h2>
          <p class="section-subtitle">
            Built with modern, scalable technologies for maximum performance and reliability
          </p>
        </div>

        <div class="tech-grid">
          <div class="tech-item">
            <div class="tech-icon">
              <i class="fab fa-laravel"></i>
            </div>
            <div class="tech-name">Laravel 10</div>
          </div>

          <div class="tech-item">
            <div class="tech-icon">
              <i class="fab fa-php"></i>
            </div>
            <div class="tech-name">PHP 8.4</div>
          </div>

          <div class="tech-item">
            <div class="tech-icon">
              <i class="fas fa-database"></i>
            </div>
            <div class="tech-name">MySQL/MariaDB</div>
          </div>

          <div class="tech-item">
            <div class="tech-icon">
              <i class="fab fa-redis"></i>
            </div>
            <div class="tech-name">Redis Cache</div>
          </div>

          <div class="tech-item">
            <div class="tech-icon">
              <i class="fas fa-server"></i>
            </div>
            <div class="tech-name">Apache2</div>
          </div>

          <div class="tech-item">
            <div class="tech-icon">
              <i class="fab fa-ubuntu"></i>
            </div>
            <div class="tech-name">Ubuntu 24.04</div>
          </div>

          <div class="tech-item">
            <div class="tech-icon">
              <i class="fas fa-queue"></i>
            </div>
            <div class="tech-name">Queue Jobs</div>
          </div>

          <div class="tech-item">
            <div class="tech-icon">
              <i class="fas fa-bolt"></i>
            </div>
            <div class="tech-name">WebSockets</div>
          </div>
        </div>
      </div>
    </section>

    <!-- Security Features Section -->
    <section id="security" class="security-section">
      <div class="container">
        <div class="section-header" style="background: transparent;">
          <h2 class="section-title" style="color: white;">Enterprise Security</h2>
          <p class="section-subtitle" style="color: rgba(255, 255, 255, 0.8);">
            Multi-layered security architecture with advanced threat protection
          </p>
        </div>

        <div class="security-grid">
          <div class="security-card">
            <div class="security-icon">
              <i class="fas fa-user-shield"></i>
            </div>
            <h3 class="security-title">Advanced Authentication</h3>
            <p>Two-factor authentication, trusted devices, and biometric support with comprehensive login analytics.</p>
          </div>

          <div class="security-card">
            <div class="security-icon">
              <i class="fas fa-lock"></i>
            </div>
            <h3 class="security-title">Data Encryption</h3>
            <p>End-to-end encryption for all sensitive data with industry-standard AES-256 encryption protocols.</p>
          </div>

          <div class="security-card">
            <div class="security-icon">
              <i class="fas fa-eye"></i>
            </div>
            <h3 class="security-title">Security Monitoring</h3>
            <p>Real-time threat detection with automated incident response and comprehensive audit logging.</p>
          </div>

          <div class="security-card">
            <div class="security-icon">
              <i class="fas fa-shield-virus"></i>
            </div>
            <h3 class="security-title">Anti-Fraud Protection</h3>
            <p>Advanced fraud detection algorithms with machine learning-based risk assessment.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Call to Action Section -->
    <section class="cta-section">
      <div class="container">
        <h2 class="cta-title">Ready to Automate Your Ticket Monitoring?</h2>
        <p class="cta-description">
          Join thousands of users who trust HD Tickets for professional sports event monitoring
          and automated purchasing. Start your free trial today.
        </p>

        <div class="cta-buttons">
          @guest
            <a href="{{ route('register') }}" class="btn btn-primary">
              <i class="fas fa-rocket"></i>&nbsp;&nbsp;Start Free Trial
            </a>
            <a href="{{ route('login') }}" class="btn btn-secondary">
              <i class="fas fa-sign-in-alt"></i>&nbsp;&nbsp;Sign In
            </a>
          @else
            <a href="{{ route('dashboard') }}" class="btn btn-primary">
              <i class="fas fa-tachometer-alt"></i>&nbsp;&nbsp;Go to Dashboard
            </a>
            <a href="#features" class="btn btn-secondary">
              <i class="fas fa-info-circle"></i>&nbsp;&nbsp;Learn More
            </a>
          @endguest
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
      <div class="footer-content">
        <div class="footer-links">
          <a href="/legal/terms">Terms of Service</a>
          <a href="/legal/privacy">Privacy Policy</a>
          <a href="/legal/gdpr">GDPR Compliance</a>
          <a href="/health">System Status</a>
          <a href="mailto:support@hd-tickets.com">Support</a>
        </div>
        <p class="footer-text">
          © {{ date('Y') }} HD Tickets. Professional Sports Event Ticket Monitoring Platform.
          Built with enterprise-grade architecture for reliable, automated ticket acquisition.
        </p>
      </div>
    </footer>

    <!-- Scripts -->
    <script>
      // Smooth scrolling for navigation links
      document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
          e.preventDefault();
          const target = document.querySelector(this.getAttribute('href'));
          if (target) {
            target.scrollIntoView({
              behavior: 'smooth',
              block: 'start'
            });
          }
        });
      });

      // Add animation on scroll
      const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
      };

      const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.classList.add('animate-fade-in');
          }
        });
      }, observerOptions);

      // Observe all feature cards and sections
      document.querySelectorAll('.feature-card, .platform-card, .tech-item, .security-card').forEach(el => {
        observer.observe(el);
      });

      // Dynamic loading effect for buttons
      document.querySelectorAll('.btn').forEach(btn => {
        btn.addEventListener('click', function() {
          if (this.href && !this.href.includes('#')) {
            const originalContent = this.innerHTML;
            this.innerHTML = '<span class="loading-spinner"></span>Loading...';

            // Reset after navigation starts
            setTimeout(() => {
              this.innerHTML = originalContent;
            }, 500);
          }
        });
      });

      // Add active state to navigation
      window.addEventListener('scroll', () => {
        const sections = ['features', 'platforms', 'technology', 'security'];
        const scrollPos = window.scrollY + 100;

        sections.forEach(section => {
          const element = document.getElementById(section);
          const navLink = document.querySelector(`a[href="#${section}"]`);

          if (element && navLink) {
            const offsetTop = element.offsetTop;
            const offsetHeight = element.offsetHeight;

            if (scrollPos >= offsetTop && scrollPos < offsetTop + offsetHeight) {
              document.querySelectorAll('.nav-links a').forEach(link => {
                link.style.color = 'rgba(255, 255, 255, 0.9)';
              });
              navLink.style.color = 'white';
            }
          }
        });
      });
    </script>
  </body>

</html>
