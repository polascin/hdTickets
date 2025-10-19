<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::create('/login', 'GET');
$response = $kernel->handle($request);
$content = $response->getContent();
file_put_contents('storage/logs/login_dump.html', $content);
echo (strpos($content, 'Sign In') !== false) ? "FOUND\n" : "NOT_FOUND\n";
$kernel->terminate($request, $response);
