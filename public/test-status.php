<?php
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HD Tickets - Status Test</title>
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
        .status { 
            padding: 15px; 
            margin: 10px 0; 
            border-radius: 5px; 
        }
        .success { 
            background: #d4edda; 
            color: #155724; 
            border: 1px solid #c3e6cb; 
        }
        .info { 
            background: #d1ecf1; 
            color: #0c5460; 
            border: 1px solid #bee5eb; 
        }
        .warning { 
            background: #fff3cd; 
            color: #856404; 
            border: 1px solid #ffeaa7; 
        }
        h1 { 
            color: #333; 
            text-align: center; 
            border-bottom: 2px solid #007bff; 
            padding-bottom: 10px; 
        }
        .accounts { 
            margin-top: 30px; 
        }
        .account { 
            background: #f8f9fa; 
            padding: 15px; 
            margin: 10px 0; 
            border-radius: 5px; 
            border-left: 4px solid #007bff; 
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
        .btn:hover { 
            background: #0056b3; 
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎫 HD Tickets - Status Test</h1>
        
        <div class="status success">
            ✅ <strong>HD Tickets je funkčný!</strong> Stránka sa úspešne načítala.
        </div>
        
        <div class="status info">
            📅 <strong>Čas testu:</strong> <?php echo date('d.m.Y H:i:s'); ?>
        </div>
        
        <div class="status info">
            🌐 <strong>Server:</strong> <?php echo $_SERVER['HTTP_HOST'] ?? 'localhost'; ?>
        </div>
        
        <div class="status info">
            🔧 <strong>PHP verzia:</strong> <?php echo PHP_VERSION; ?>
        </div>
        
        <?php
        // Test database connection
        try {
            $pdo = new PDO('mysql:host=localhost;dbname=hdtickets', 'hdtickets', 'wH8n9H#5nG2K@7mP');
            $stmt = $pdo->query('SELECT COUNT(*) FROM users');
            $userCount = $stmt->fetchColumn();
            echo '<div class="status success">🗄️ <strong>Databáza:</strong> Pripojenie OK - ' . $userCount . ' používateľov v databáze</div>';
        } catch (Exception $e) {
            echo '<div class="status warning">⚠️ <strong>Databáza:</strong> Problém s pripojením - ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        ?>
        
        <div class="accounts">
            <h2>📧 Obnovené používateľské kontá</h2>
            
            <div class="account">
                <strong>👨‍💼 Administrátor</strong><br>
                Email: admin@hdtickets.com<br>
                Heslo: HDTickets2025!<br>
                Rola: Admin (plný prístup)
            </div>
            
            <div class="account">
                <strong>🎯 Agent</strong><br>
                Email: agent@hdtickets.com<br>
                Heslo: HDAgent2025!<br>
                Rola: Agent (výber a nákup lístkov)
            </div>
            
            <div class="account">
                <strong>👤 Zákazník</strong><br>
                Email: customer@hdtickets.com<br>
                Heslo: HDCustomer2025!<br>
                Rola: Customer (základný prístup)
            </div>
        </div>
        
        <div style="margin-top: 30px; text-align: center;">
            <a href="/" class="btn">🏠 Hlavná stránka</a>
            <a href="/login" class="btn">🔐 Prihlásiť sa</a>
            <a href="/register" class="btn">📝 Registrácia</a>
        </div>
        
        <div class="status warning" style="margin-top: 30px;">
            ⚠️ <strong>Bezpečnosť:</strong> Po prvom prihlásení si zmeňte predvolené heslá!
        </div>
        
        <div style="margin-top: 20px; text-align: center; color: #666; font-size: 12px;">
            HD Tickets System - Obnova účtov úspešná ✅
        </div>
    </div>
</body>
</html>
