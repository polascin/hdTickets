<!DOCTYPE html>
<html>
<head>
    <title>🚀 NEW DASHBOARD TEST</title>
    <style>
        body { 
            font-family: Arial; 
            background: linear-gradient(135deg, #3b82f6, #8b5cf6); 
            color: white; 
            padding: 40px; 
            text-align: center; 
        }
        .container { 
            background: rgba(255,255,255,0.1); 
            backdrop-filter: blur(10px); 
            padding: 40px; 
            border-radius: 20px; 
            margin: 0 auto; 
            max-width: 800px; 
        }
        .success { 
            font-size: 24px; 
            margin-bottom: 20px; 
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎉 NEW HD TICKETS DASHBOARD LOADED!</h1>
        <div class="success">✅ The new dashboard implementation is working!</div>
        <p><strong>Time:</strong> {{ now()->format('Y-m-d H:i:s') }}</p>
        <p><strong>User:</strong> {{ Auth::user()->email ?? 'Not logged in' }}</p>
        <p><strong>Role:</strong> {{ Auth::user()->role ?? 'Unknown' }}</p>
        <p><strong>View:</strong> dashboard.test-new</p>
        <p><strong>Controller:</strong> EnhancedDashboardController</p>
        
        <hr style="margin: 30px 0; border: 1px solid rgba(255,255,255,0.3);">
        
        <h2>🧪 Testing Data:</h2>
        @if(isset($statistics))
            <p><strong>Available Tickets:</strong> {{ $statistics['available_tickets'] ?? 'N/A' }}</p>
            <p><strong>New Today:</strong> {{ $statistics['new_today'] ?? 'N/A' }}</p>
        @else
            <p><em>No statistics data passed to view</em></p>
        @endif
        
        <p style="margin-top: 30px; font-size: 18px;">
            <a href="{{ route('dashboard.customer') }}" 
               style="color: #fbbf24; text-decoration: underline;">
                → Go to Full Dashboard
            </a>
        </p>
    </div>
</body>
</html>