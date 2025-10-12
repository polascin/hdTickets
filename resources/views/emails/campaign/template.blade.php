<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $content['subject'] }}</title>
    <style>
      * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
      }

      body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        line-height: 1.6;
        color: #333;
        background-color: #f4f4f4;
      }

      .email-container {
        max-width: 600px;
        margin: 0 auto;
        background-color: #ffffff;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
      }

      .header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 30px 20px;
        text-align: center;
      }

      .header h1 {
        font-size: 24px;
        font-weight: bold;
        margin-bottom: 10px;
      }

      .header p {
        font-size: 16px;
        opacity: 0.9;
      }

      .content {
        padding: 40px 30px;
      }

      .content h2 {
        color: #2c3e50;
        font-size: 22px;
        margin-bottom: 20px;
      }

      .content p {
        margin-bottom: 15px;
        font-size: 16px;
        line-height: 1.6;
      }

      .cta-section {
        text-align: center;
        margin: 30px 0;
      }

      .cta-button {
        display: inline-block;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 15px 30px;
        text-decoration: none;
        border-radius: 5px;
        font-weight: bold;
        font-size: 16px;
        transition: transform 0.2s;
      }

      .cta-button:hover {
        transform: translateY(-2px);
      }

      .features-section {
        background-color: #f8f9fa;
        padding: 30px;
        margin: 30px 0;
        border-radius: 8px;
      }

      .feature-item {
        margin-bottom: 15px;
        padding-left: 25px;
        position: relative;
      }

      .feature-item:before {
        content: "✓";
        position: absolute;
        left: 0;
        color: #28a745;
        font-weight: bold;
        font-size: 18px;
      }

      .stats-section {
        display: flex;
        justify-content: space-around;
        margin: 30px 0;
        text-align: center;
      }

      .stat-item {
        flex: 1;
        padding: 20px;
      }

      .stat-number {
        font-size: 32px;
        font-weight: bold;
        color: #667eea;
        display: block;
      }

      .stat-label {
        font-size: 14px;
        color: #666;
        margin-top: 5px;
      }

      .footer {
        background-color: #2c3e50;
        color: white;
        padding: 30px;
        text-align: center;
      }

      .footer p {
        margin-bottom: 10px;
        font-size: 14px;
      }

      .footer a {
        color: #3498db;
        text-decoration: none;
      }

      .social-links {
        margin: 20px 0;
      }

      .social-links a {
        display: inline-block;
        margin: 0 10px;
        padding: 10px;
        background-color: #34495e;
        color: white;
        text-decoration: none;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        line-height: 20px;
      }

      .unsubscribe {
        font-size: 12px;
        color: #95a5a6;
        margin-top: 20px;
      }

      @media (max-width: 600px) {
        .email-container {
          margin: 0;
          width: 100%;
        }

        .content {
          padding: 20px;
        }

        .stats-section {
          flex-direction: column;
        }

        .stat-item {
          margin-bottom: 20px;
        }
      }
    </style>
  </head>

  <body>
    <div class="email-container">
      <!-- Header -->
      <div class="header">
        <h1>🎟️ HD Tickets</h1>
        <p>Professional Sports Ticket Monitoring</p>
      </div>

      <!-- Main Content -->
      <div class="content">
        <h2>Hello {{ $user->name }}!</h2>

        {!! nl2br(e($content['body'])) !!}

        @if (isset($content['features']) && is_array($content['features']))
          <div class="features-section">
            <h3>What's Included:</h3>
            @foreach ($content['features'] as $feature)
              <div class="feature-item">{{ $feature }}</div>
            @endforeach
          </div>
        @endif

        @if (isset($content['stats']) && is_array($content['stats']))
          <div class="stats-section">
            @foreach ($content['stats'] as $stat)
              <div class="stat-item">
                <span class="stat-number">{{ $stat['number'] }}</span>
                <span class="stat-label">{{ $stat['label'] }}</span>
              </div>
            @endforeach
          </div>
        @endif

        @if (isset($content['action_url']) && isset($content['action_text']))
          <div class="cta-section">
            <a href="{{ $clickTrackingUrl }}?url={{ urlencode($content['action_url']) }}" class="cta-button">
              {{ $content['action_text'] }}
            </a>
          </div>
        @endif
      </div>

      <!-- Footer -->
      <div class="footer">
        <p><strong>HD Tickets - Professional Sports Ticket Monitoring</strong></p>
        <p>Monitor ticket prices, get instant alerts, automate purchases</p>

        <div class="social-links">
          <a href="#" title="Twitter">T</a>
          <a href="#" title="Facebook">F</a>
          <a href="#" title="LinkedIn">L</a>
          <a href="#" title="Instagram">I</a>
        </div>

        <p>
          <a href="mailto:support@hdtickets.com">Contact Support</a> |
          <a href="{{ url('/') }}">Visit Website</a> |
          <a href="{{ url('/privacy') }}">Privacy Policy</a>
        </p>

        <div class="unsubscribe">
          <p>
            You're receiving this email because you're subscribed to HD Tickets updates.<br>
            <a href="{{ $unsubscribeUrl }}">Unsubscribe</a> from future emails.
          </p>
        </div>
      </div>
    </div>

    <!-- Tracking Pixel -->
    <img src="{{ $trackingPixelUrl }}" width="1" height="1" style="display: none;" alt="">
  </body>

</html>
