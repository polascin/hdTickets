<?php
// Simple dashboard test to check what's happening
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Test - HD Tickets</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 40px; 
            background: #f5f5f5;
        }
        .container { 
            max-width: 800px; 
            margin: 0 auto; 
            background: white; 
            padding: 30px; 
            border-radius: 10px; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .test { 
            margin: 20px 0; 
            padding: 15px; 
            background: #f8f9fa; 
            border-left: 4px solid #007bff; 
            border-radius: 5px;
        }
        .btn { 
            display: inline-block; 
            padding: 10px 20px; 
            background: #007bff; 
            color: white; 
            text-decoration: none; 
            border-radius: 5px; 
            margin: 5px;
        }
        .error { 
            background: #f8d7da; 
            border-left-color: #dc3545; 
            color: #721c24;
        }
        .success { 
            background: #d4edda; 
            border-left-color: #28a745; 
            color: #155724;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Dashboard Test - HD Tickets</h1>
        
        <div class="test">
            <h3>📊 Dashboard URL Test</h3>
            <p><strong>Test URL:</strong> <code>https://hdtickets.local/dashboard</code></p>
            
            <?php
            // Test dashboard endpoints
            $dashboardUrls = [
                'Dashboard (Main)' => 'https://hdtickets.local/dashboard',
                'Admin Dashboard' => 'https://hdtickets.local/admin/dashboard', 
                'Agent Dashboard' => 'https://hdtickets.local/agent-dashboard',
                'Customer Dashboard' => 'https://hdtickets.local/customer-dashboard',
                'Login Page' => 'https://hdtickets.local/login'
            ];
            
            foreach ($dashboardUrls as $name => $url) {
                echo "<div class='test'>";
                echo "<strong>{$name}:</strong><br>";
                
                // Use cURL to test the endpoint
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HEADER, true);
                curl_setopt($ch, CURLOPT_NOBODY, true);  // HEAD request
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); // Don't follow redirects
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $error = curl_error($ch);
                curl_close($ch);
                
                if ($error) {
                    echo "<span style='color: red;'>❌ Error: {$error}</span><br>";
                } else {
                    switch ($httpCode) {
                        case 200:
                            echo "<span style='color: green;'>✅ Status: {$httpCode} OK</span><br>";
                            break;
                        case 302:
                            // Extract redirect location
                            preg_match('/Location: (.+)\r?\n/i', $response, $matches);
                            $location = isset($matches[1]) ? trim($matches[1]) : 'Unknown';
                            echo "<span style='color: orange;'>➡️ Status: {$httpCode} Redirect to {$location}</span><br>";
                            break;
                        case 500:
                            echo "<span style='color: red;'>❌ Status: {$httpCode} Internal Server Error</span><br>";
                            break;
                        default:
                            echo "<span style='color: red;'>⚠️ Status: {$httpCode}</span><br>";
                    }
                }
                echo "</div>";
            }
            ?>
        </div>
        
        <div class="test">
            <h3>🔐 Prihlasovacie údaje</h3>
            <p>Na testovanie prihlásenia použite tieto účty:</p>
            <ul>
                <li><strong>Admin:</strong> admin@hdtickets.com / HDTickets2025!</li>
                <li><strong>Agent:</strong> agent@hdtickets.com / HDAgent2025!</li>
                <li><strong>Customer:</strong> customer@hdtickets.com / HDCustomer2025!</li>
            </ul>
        </div>
        
        <div class="test">
            <h3>🔍 Diagnostika</h3>
            <p>Ak vidíte HTTP 500 pri pristupovaní na dashboard:</p>
            <ol>
                <li>Najprv sa prihláste na <a href="/login" target="_blank">/login</a></li>
                <li>Potom skúste pristúpiť na dashboard</li>
                <li>Dashboard automaticky presmeruje podľa vašej role</li>
            </ol>
        </div>
        
        <div style="margin-top: 30px; text-align: center;">
            <a href="/" class="btn">🏠 Hlavná stránka</a>
            <a href="/login" class="btn">🔐 Prihlásiť sa</a>
            <a href="/dashboard" class="btn">📊 Dashboard</a>
            <a href="/test-status.php" class="btn">🧪 Status Test</a>
        </div>
        
        <div style="margin-top: 20px; text-align: center; color: #666; font-size: 12px;">
            Testované: <?php echo date('d.m.Y H:i:s'); ?>
        </div>
    </div>
</body>
</html>
