<?php
// Emergency access page to bypass Laravel completely
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HD Tickets - Emergency Access</title>
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
            margin: 0; 
            padding: 40px; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: white;
        }
        .container { 
            max-width: 900px; 
            margin: 0 auto; 
            background: rgba(255,255,255,0.95); 
            color: #333;
            padding: 40px; 
            border-radius: 15px; 
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        .status { 
            padding: 20px; 
            margin: 15px 0; 
            border-radius: 10px; 
            border-left: 5px solid;
        }
        .success { 
            background: #d4edda; 
            color: #155724; 
            border-left-color: #28a745;
        }
        .error { 
            background: #f8d7da; 
            color: #721c24; 
            border-left-color: #dc3545;
        }
        .warning { 
            background: #fff3cd; 
            color: #856404; 
            border-left-color: #ffc107;
        }
        .info { 
            background: #d1ecf1; 
            color: #0c5460; 
            border-left-color: #17a2b8;
        }
        h1 { 
            color: #333; 
            text-align: center; 
            margin-bottom: 30px;
            font-size: 2.5em;
        }
        .btn { 
            display: inline-block; 
            padding: 15px 30px; 
            background: #007bff; 
            color: white; 
            text-decoration: none; 
            border-radius: 8px; 
            margin: 10px 5px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn:hover { 
            background: #0056b3; 
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,123,255,0.3);
        }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #218838; }
        .btn-warning { background: #ffc107; color: #212529; }
        .btn-warning:hover { background: #e0a800; }
        .btn-danger { background: #dc3545; }
        .btn-danger:hover { background: #c82333; }
        .grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); 
            gap: 20px; 
            margin: 20px 0; 
        }
        .card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            border: 1px solid #dee2e6;
        }
        .emoji { font-size: 1.5em; margin-right: 10px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f1f3f4; font-weight: 600; }
        .footer { 
            text-align: center; 
            margin-top: 40px; 
            padding-top: 20px; 
            border-top: 1px solid #ddd; 
            color: #666; 
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><span class="emoji">🚨</span>HD Tickets - Emergency Access</h1>
        
        <div class="status error">
            <strong>⚠️ CRITICAL ALERT:</strong> You are seeing HTTP ERROR 500 in your browser. This emergency page bypasses Laravel completely to help diagnose the issue.
        </div>

        <?php
        // Test basic PHP functionality
        echo '<div class="status success">';
        echo '<strong><span class="emoji">✅</span> PHP is working!</strong> Version: ' . PHP_VERSION;
        echo '</div>';

        // Test server info
        echo '<div class="status info">';
        echo '<strong><span class="emoji">🖥️</span> Server Info:</strong><br>';
        echo 'Server: ' . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . '<br>';
        echo 'Host: ' . ($_SERVER['HTTP_HOST'] ?? 'Unknown') . '<br>';
        echo 'Request: ' . ($_SERVER['REQUEST_URI'] ?? 'Unknown') . '<br>';
        echo 'Time: ' . date('d.m.Y H:i:s');
        echo '</div>';

        // Test Laravel files
        $laravelFiles = [
            '../vendor/autoload.php' => 'Composer Autoloader',
            '../bootstrap/app.php' => 'Laravel Bootstrap',
            '../.env' => 'Environment File',
            '../config/app.php' => 'App Configuration',
        ];

        echo '<div class="card">';
        echo '<h3><span class="emoji">📁</span> Laravel Files Check</h3>';
        foreach ($laravelFiles as $file => $name) {
            $exists = file_exists($file);
            $status = $exists ? '<span style="color: green;">✅ Exists</span>' : '<span style="color: red;">❌ Missing</span>';
            echo "<div>{$name}: {$status}</div>";
        }
        echo '</div>';
        ?>

        <div class="grid">
            <div class="card">
                <h3><span class="emoji">🔐</span> Login Accounts</h3>
                <table>
                    <tr><th>Role</th><th>Email</th><th>Password</th></tr>
                    <tr><td>Admin</td><td>admin@hdtickets.com</td><td>HDTickets2025!</td></tr>
                    <tr><td>Agent</td><td>agent@hdtickets.com</td><td>HDAgent2025!</td></tr>
                    <tr><td>Customer</td><td>customer@hdtickets.com</td><td>HDCustomer2025!</td></tr>
                </table>
            </div>

            <div class="card">
                <h3><span class="emoji">🔧</span> Quick Fixes</h3>
                <p><strong>Try these solutions:</strong></p>
                <ol>
                    <li><strong>Clear browser cache:</strong> Ctrl+F5 or Cmd+Shift+R</li>
                    <li><strong>Try incognito mode</strong> to avoid cached errors</li>
                    <li><strong>Use different browser</strong> (Chrome, Firefox, Safari)</li>
                    <li><strong>Check if you're using HTTPS:</strong> https://hdtickets.local</li>
                </ol>
            </div>
        </div>

        <?php
        // Test database connection
        echo '<div class="card">';
        echo '<h3><span class="emoji">🗄️</span> Database Test</h3>';
        try {
            $pdo = new PDO('mysql:host=localhost;dbname=hdtickets', 'hdtickets', 'hdtickets');
            $stmt = $pdo->query('SELECT COUNT(*) FROM users');
            $count = $stmt->fetchColumn();
            echo '<div class="status success">✅ Database connected! Users count: ' . $count . '</div>';
            
            // Show user accounts
            $users = $pdo->query('SELECT email, role FROM users LIMIT 5')->fetchAll(PDO::FETCH_ASSOC);
            if ($users) {
                echo '<p><strong>Available accounts:</strong></p><ul>';
                foreach ($users as $user) {
                    echo '<li>' . htmlspecialchars($user['email']) . ' (' . htmlspecialchars($user['role']) . ')</li>';
                }
                echo '</ul>';
            }
        } catch (Exception $e) {
            echo '<div class="status error">❌ Database Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        echo '</div>';

        // Laravel test
        echo '<div class="card">';
        echo '<h3><span class="emoji">⚡</span> Laravel Test</h3>';
        try {
            require_once '../vendor/autoload.php';
            echo '<div class="status success">✅ Autoloader works</div>';
            
            $app = require_once '../bootstrap/app.php';
            echo '<div class="status success">✅ Laravel boots</div>';
            
            echo '<div class="status info">Laravel Version: ' . app()->version() . '</div>';
        } catch (Exception $e) {
            echo '<div class="status error">❌ Laravel Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
            echo '<div style="font-family: monospace; background: #f8f9fa; padding: 10px; margin: 10px 0; border-radius: 5px; font-size: 12px;">';
            echo 'File: ' . $e->getFile() . '<br>';
            echo 'Line: ' . $e->getLine() . '<br>';
            echo '</div>';
        }
        echo '</div>';
        ?>

        <div style="margin-top: 30px; text-align: center;">
            <h3><span class="emoji">🔗</span> Navigation Links</h3>
            <a href="/" class="btn btn-success">🏠 Try Homepage</a>
            <a href="/login" class="btn">🔐 Login Page</a>
            <a href="/dashboard" class="btn">📊 Dashboard</a>
            <a href="/php-test.php" class="btn btn-info">🧪 PHP Test</a>
            <a href="/test-status.php" class="btn btn-info">📋 Status Test</a>
        </div>

        <div class="status warning">
            <strong><span class="emoji">💡</span> IMPORTANT:</strong> If you can see this page but still get HTTP 500 on other pages, the issue is in Laravel, not the server itself.
        </div>

        <div class="footer">
            <p><strong>Emergency Access Page</strong> | Generated: <?php echo date('d.m.Y H:i:s'); ?></p>
            <p>If Laravel pages don't work but this page does, there's a configuration or code issue in Laravel.</p>
        </div>
    </div>
</body>
</html>
