<?php
// Simple PHP test to see if server is working
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <title>PHP Test - HD Tickets</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f0f0f0; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; }
        .status { padding: 15px; margin: 10px 0; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; }
        .info { background: #d1ecf1; color: #0c5460; }
        h1 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 PHP Test - HD Tickets</h1>
        
        <div class="status success">
            ✅ <strong>PHP funguje!</strong> Tento súbor sa úspešne načítal.
        </div>
        
        <div class="status info">
            <strong>PHP verzia:</strong> <?php echo PHP_VERSION; ?>
        </div>
        
        <div class="status info">
            <strong>Server:</strong> <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?>
        </div>
        
        <div class="status info">
            <strong>Čas:</strong> <?php echo date('d.m.Y H:i:s'); ?>
        </div>
        
        <div class="status info">
            <strong>Document Root:</strong> <?php echo $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown'; ?>
        </div>
        
        <div class="status info">
            <strong>Request URI:</strong> <?php echo $_SERVER['REQUEST_URI'] ?? 'Unknown'; ?>
        </div>
        
        <div class="status info">
            <strong>Host:</strong> <?php echo $_SERVER['HTTP_HOST'] ?? 'Unknown'; ?>
        </div>
        
        <?php
        // Test basic Laravel requirements
        $extensions = [
            'PDO', 'pdo_mysql', 'mbstring', 'openssl', 'tokenizer', 'xml', 'ctype', 'json', 'bcmath'
        ];
        
        echo '<div class="status info"><strong>Laravel Extensions:</strong><ul>';
        foreach ($extensions as $ext) {
            $loaded = extension_loaded($ext);
            echo '<li>' . $ext . ': ' . ($loaded ? '✅ OK' : '❌ Missing') . '</li>';
        }
        echo '</ul></div>';
        ?>
        
        <?php
        // Test file permissions
        $paths = [
            '/var/www/hdtickets/storage/logs/laravel.log',
            '/var/www/hdtickets/.env',
            '/var/www/hdtickets/public/index.php'
        ];
        
        echo '<div class="status info"><strong>Súbory a oprávnenia:</strong><ul>';
        foreach ($paths as $path) {
            $exists = file_exists($path);
            $readable = $exists ? is_readable($path) : false;
            echo '<li>' . basename($path) . ': ';
            if ($exists) {
                echo '✅ Existuje, ' . ($readable ? 'Čitateľný' : 'Nečitateľný');
            } else {
                echo '❌ Neexistuje';
            }
            echo '</li>';
        }
        echo '</ul></div>';
        ?>
        
        <div style="margin-top: 30px;">
            <h3>🔗 Test odkazy</h3>
            <a href="/" style="color: #007bff;">Hlavná stránka</a> | 
            <a href="/test-status.php" style="color: #007bff;">Status test</a> | 
            <a href="/dashboard-test.php" style="color: #007bff;">Dashboard test</a>
        </div>
        
        <div style="margin-top: 20px; text-align: center; color: #666; font-size: 12px;">
            Ak vidíte túto stránku, PHP server funguje správne!
        </div>
    </div>
</body>
</html>
