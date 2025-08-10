<?php
// Complete recovery script - bypasses all Laravel issues
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Simple login functionality
if ($_POST['action'] === 'login') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Simple hardcoded login for emergency access
    $accounts = [
        'admin@hdtickets.com' => 'HDTickets2025!',
        'agent@hdtickets.com' => 'HDAgent2025!',
        'customer@hdtickets.com' => 'HDCustomer2025!'
    ];
    
    if (isset($accounts[$email]) && $accounts[$email] === $password) {
        $_SESSION['logged_in'] = true;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_role'] = explode('@', $email)[0]; // admin, agent, customer
        header('Location: /recovery.php');
        exit;
    } else {
        $login_error = 'Invalid credentials';
    }
}

if ($_POST['action'] === 'logout') {
    session_destroy();
    header('Location: /recovery.php');
    exit;
}

$is_logged_in = $_SESSION['logged_in'] ?? false;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HD Tickets - Recovery Portal</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container { 
            background: rgba(255,255,255,0.1); 
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            max-width: 500px;
            width: 90%;
            border: 1px solid rgba(255,255,255,0.2);
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
        }
        .logo { 
            text-align: center; 
            font-size: 2.5em; 
            font-weight: 700; 
            margin-bottom: 20px;
            background: linear-gradient(45deg, #ff6b6b, #4ecdc4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .subtitle { 
            text-align: center; 
            margin-bottom: 30px; 
            opacity: 0.8;
            font-size: 1.1em;
        }
        .form-group { 
            margin-bottom: 20px; 
        }
        label { 
            display: block; 
            margin-bottom: 8px; 
            font-weight: 500;
            color: rgba(255,255,255,0.9);
        }
        input { 
            width: 100%; 
            padding: 15px; 
            border: none; 
            border-radius: 10px; 
            background: rgba(255,255,255,0.2);
            color: white;
            font-size: 16px;
            backdrop-filter: blur(5px);
        }
        input::placeholder { 
            color: rgba(255,255,255,0.6); 
        }
        input:focus { 
            outline: none; 
            background: rgba(255,255,255,0.3);
            box-shadow: 0 0 20px rgba(255,255,255,0.2);
        }
        .btn { 
            width: 100%; 
            padding: 15px; 
            border: none; 
            border-radius: 10px; 
            background: linear-gradient(45deg, #ff6b6b, #4ecdc4);
            color: white; 
            font-size: 16px; 
            font-weight: 600;
            cursor: pointer; 
            transition: all 0.3s ease;
            margin-top: 10px;
        }
        .btn:hover { 
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        .btn-secondary { 
            background: rgba(255,255,255,0.2); 
            backdrop-filter: blur(10px);
        }
        .error { 
            background: rgba(255,107,107,0.2); 
            color: #ff6b6b; 
            padding: 15px; 
            border-radius: 10px; 
            margin-bottom: 20px;
            border: 1px solid rgba(255,107,107,0.3);
        }
        .success { 
            background: rgba(78,205,196,0.2); 
            color: #4ecdc4; 
            padding: 15px; 
            border-radius: 10px; 
            margin-bottom: 20px;
            border: 1px solid rgba(78,205,196,0.3);
        }
        .dashboard { 
            text-align: center; 
        }
        .user-info { 
            background: rgba(255,255,255,0.1); 
            padding: 20px; 
            border-radius: 10px; 
            margin-bottom: 20px;
        }
        .links { 
            display: grid; 
            gap: 10px; 
            margin-top: 20px; 
        }
        .link-btn { 
            display: block; 
            text-decoration: none; 
            color: white; 
            background: rgba(255,255,255,0.1); 
            padding: 12px 20px; 
            border-radius: 8px; 
            transition: all 0.3s ease;
            text-align: center;
        }
        .link-btn:hover { 
            background: rgba(255,255,255,0.2); 
            transform: translateY(-2px);
        }
        .accounts { 
            margin-top: 30px; 
            font-size: 14px; 
            opacity: 0.7; 
        }
        .accounts h4 { 
            margin-bottom: 10px; 
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">🎫 HD Tickets</div>
        <div class="subtitle">Emergency Recovery Portal</div>
        
        <?php if (!$is_logged_in): ?>
            
            <?php if (isset($login_error)): ?>
                <div class="error">❌ <?= htmlspecialchars($login_error) ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <input type="hidden" name="action" value="login">
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                </div>
                
                <button type="submit" class="btn">🔐 Emergency Login</button>
            </form>
            
            <div class="accounts">
                <h4>🔑 Available Accounts:</h4>
                <div>Admin: admin@hdtickets.com / HDTickets2025!</div>
                <div>Agent: agent@hdtickets.com / HDAgent2025!</div>
                <div>Customer: customer@hdtickets.com / HDCustomer2025!</div>
            </div>
            
        <?php else: ?>
            
            <div class="success">✅ Emergency login successful!</div>
            
            <div class="user-info">
                <h3>👤 Logged in as:</h3>
                <div><strong>Email:</strong> <?= htmlspecialchars($_SESSION['user_email']) ?></div>
                <div><strong>Role:</strong> <?= ucfirst(htmlspecialchars($_SESSION['user_role'])) ?></div>
            </div>
            
            <div class="dashboard">
                <h3>🚀 Recovery Dashboard</h3>
                <p>You are now logged in via emergency recovery. Use the links below to access the system:</p>
            </div>
            
            <div class="links">
                <a href="/" class="link-btn">🏠 Try Main Homepage</a>
                <a href="/login" class="link-btn">🔐 Laravel Login Page</a>
                <a href="/dashboard" class="link-btn">📊 Laravel Dashboard</a>
                <a href="/emergency.php" class="link-btn">🚨 Emergency Diagnostics</a>
                <a href="/php-test.php" class="link-btn">🧪 PHP System Test</a>
            </div>
            
            <form method="POST" style="margin-top: 20px;">
                <input type="hidden" name="action" value="logout">
                <button type="submit" class="btn btn-secondary">🚪 Logout</button>
            </form>
            
        <?php endif; ?>
        
        <div style="text-align: center; margin-top: 30px; opacity: 0.6; font-size: 12px;">
            Emergency Recovery Portal | <?= date('d.m.Y H:i:s') ?>
        </div>
    </div>
</body>
</html>
