<?php declare(strict_types=1);

namespace Deployer;

require 'recipe/laravel.php';

// Project name
set('application', 'HD Tickets');

// Project repository
set('repository', 'https://github.com/polascin/hdTickets.git');

// Git tty
set('git_tty', TRUE);

// Deployment path - adjust to your server
set('deploy_path', '/var/www/hdtickets');

// Number of releases to keep
set('keep_releases', 5);

// Default branch
set('branch', 'main');

// Composer options
set('composer_options', '--verbose --prefer-dist --no-progress --no-interaction --optimize-autoloader --no-dev');

// Node.js & NPM options
set('bin/npm', 'npm');
set('bin/node', 'node');

// Shared files/folders between releases
set('shared_files', [
    '.env',
    'storage/oauth-private.key',
    'storage/oauth-public.key',
]);

set('shared_dirs', [
    'storage',
    'bootstrap/cache',
    'node_modules',
]);

// Writable directories
set('writable_dirs', [
    'bootstrap/cache',
    'storage',
    'storage/app',
    'storage/app/public',
    'storage/framework',
    'storage/framework/cache',
    'storage/framework/cache/data',
    'storage/framework/sessions',
    'storage/framework/testing',
    'storage/framework/views',
    'storage/logs',
    'storage/quality',
]);

// Laravel specific settings
set('laravel_version', function () {
    $result = run('{{bin/php}} {{release_path}}/artisan --version');
    preg_match_all('/(\d+\.?)+/', $result, $matches);

    return $matches[0][0] ?? 11;
});

// Allow host level configuration via environment for CI/CD flexibility.
$productionHost = getenv('DEPLOY_HOST') ?: 'hd-tickets.com';
$productionUser = getenv('DEPLOY_USER') ?: 'deploy';
$productionPath = getenv('DEPLOY_PATH') ?: '/var/www/hdtickets';
$productionPhpBinary = getenv('DEPLOY_PHP_BINARY') ?: '/usr/bin/php8.3';

// Production host configuration
host('production')
    ->hostname($productionHost)
    ->user($productionUser)
    ->set('deploy_path', $productionPath)
    ->set('labels', ['stage' => 'production'])
    ->set('branch', 'main')
    ->set('http_user', 'www-data')
    ->set('composer_options', '--no-dev --prefer-dist --no-interaction --optimize-autoloader')
    ->addSshOption('UserKnownHostsFile', '/dev/null')
    ->addSshOption('StrictHostKeyChecking', 'no')
    ->set('bin/php', $productionPhpBinary);

// Custom tasks for Laravel deployment

// Task: Upload built assets from CI
task('deploy:upload-assets', function () {
    if (test('[ -d public/build ]')) {
        upload('public/build/', '{{release_path}}/public/build/');
        writeln('<info>✓</info> Built assets uploaded from CI');
    } else {
        writeln('<comment>!</comment> No built assets found - assets should be built in CI');
    }
})->desc('Upload pre-built assets from CI');

// Task: Generate app key if it doesn't exist
task('artisan:key:generate', function () {
    // Check if .env exists and has APP_KEY
    if (test('[ -f {{deploy_path}}/shared/.env ]')) {
        $hasKey = run('grep -q "^APP_KEY=.\+" {{deploy_path}}/shared/.env && echo "yes" || echo "no"');
        if (trim($hasKey) === 'no') {
            run('{{bin/php}} {{release_path}}/artisan key:generate --force');
            writeln('<info>✓</info> Application key generated');
        } else {
            writeln('<info>✓</info> Application key already exists');
        }
    } else {
        writeln('<comment>!</comment> .env file not found, skipping key generation');
    }
})->desc('Generate application key');

// Task: Generate Passport keys if they don't exist
task('artisan:passport:keys', function () {
    if (!test('[ -f {{deploy_path}}/shared/storage/oauth-private.key ]')) {
        run('{{bin/php}} {{release_path}}/artisan passport:keys --force');
        writeln('<info>✓</info> Passport keys generated');
    } else {
        writeln('<info>✓</info> Passport keys already exist');
    }
})->desc('Generate Passport OAuth keys');

// Task: Create storage link
task('artisan:storage:link', function () {
    if (test('[ ! -L {{release_path}}/public/storage ]')) {
        run('{{bin/php}} {{release_path}}/artisan storage:link');
        writeln('<info>✓</info> Storage link created');
    } else {
        writeln('<info>✓</info> Storage link already exists');
    }
})->desc('Create storage symbolic link');

// Task: Cache Laravel configuration
task('artisan:cache:all', function () {
    run('{{bin/php}} {{release_path}}/artisan config:cache');
    // Skip route:cache due to closure routes in web.php
    // run('{{bin/php}} {{release_path}}/artisan route:cache');
    run('{{bin/php}} {{release_path}}/artisan view:cache');
    run('{{bin/php}} {{release_path}}/artisan event:cache');
    writeln('<info>✓</info> Caches updated (routes skipped)');
})->desc('Cache configurations, views, and events');

// Task: Gracefully restart Horizon
task('artisan:horizon:terminate', function () {
    $horizonStatus = run('sudo systemctl is-active hdtickets-horizon || echo "inactive"');
    if (trim($horizonStatus) === 'active') {
        run('{{bin/php}} {{current_path}}/artisan horizon:terminate');
        writeln('<info>✓</info> Horizon workers terminated gracefully');
    } else {
        writeln('<comment>!</comment> Horizon service not running');
    }
})->desc('Gracefully terminate Horizon workers');

// Task: Reload PHP-FPM
task('php-fpm:reload', function () {
    run('sudo systemctl reload php8.3-fpm');
    writeln('<info>✓</info> PHP-FPM reloaded');
})->desc('Reload PHP-FPM service');

// Task: Restart Horizon service
task('horizon:restart', function () {
    run('sudo systemctl restart hdtickets-horizon');
    sleep(2); // Give it time to start
    $status = run('sudo systemctl is-active hdtickets-horizon');
    if (trim($status) === 'active') {
        writeln('<info>✓</info> Horizon service restarted successfully');
    } else {
        writeln('<error>✗</error> Failed to restart Horizon service');
    }
})->desc('Restart Horizon systemd service');

// Task: Health check
task('health:check', function () {
    $url = get('url', 'https://hd-tickets.com');
    $healthUrl = rtrim($url, '/') . '/health';

    // Wait a moment for the deployment to settle
    sleep(3);

    $response = runLocally("curl -s -o /dev/null -w '%{http_code}' --max-time 30 --insecure $healthUrl");

    if (trim($response) === '200') {
        writeln("<info>✓</info> Health check passed: $healthUrl");
    } else {
        writeln("<error>✗</error> Health check failed: $healthUrl (HTTP $response)");

        throw new \Exception('Health check failed');
    }
})->desc('Perform application health check');

// Task: Backup database before migration
task('database:backup', function () {
    $timestamp = date('Y-m-d_H-i-s');
    $filename = "hdtickets_backup_$timestamp.sql";

    // Create backup directory if it doesn't exist
    run('mkdir -p {{deploy_path}}/backups');

    // Create database backup
    run("mysqldump --single-transaction --routines --triggers hdtickets | gzip > {{deploy_path}}/backups/$filename.gz");

    // Keep only last 7 backups
    run('cd {{deploy_path}}/backups && ls -t *.gz | tail -n +8 | xargs -r rm --');

    writeln("<info>✓</info> Database backup created: $filename.gz");
})->desc('Create database backup');

// Task: Update file permissions
task('deploy:set_permissions', function () {
    // Set ownership for the entire deployment
    run('sudo chown -R deploy:www-data {{deploy_path}}');

    // Set specific permissions for shared directories
    run('sudo chmod -R 755 {{deploy_path}}/shared/storage');
    run('sudo chmod -R 755 {{deploy_path}}/shared/bootstrap');

    // Ensure web server can write to necessary directories
    run('sudo setfacl -R -m u:www-data:rwX {{deploy_path}}/shared/storage');
    run('sudo setfacl -R -m u:www-data:rwX {{deploy_path}}/shared/bootstrap/cache');

    writeln('<info>✓</info> File permissions updated');
})->desc('Set proper file permissions');

// Custom deployment flow
desc('Deploy HD Tickets to production');
task('deploy', [
    'deploy:prepare',
    'deploy:vendors',
    'deploy:upload-assets',
    'artisan:key:generate',
    'artisan:passport:keys',
    'artisan:storage:link',
    // 'database:backup', // Skip DB backup in CI - handle separately
    'artisan:migrate',
    'artisan:cache:all',
    'deploy:symlink',
    'deploy:set_permissions',
    'php-fpm:reload',
    'artisan:horizon:terminate',
    'horizon:restart',
    'health:check',
    'deploy:cleanup',
    'artisan:up',
    'success',
]);

// Rollback task with health check
desc('Rollback to previous release');
task('rollback', [
    'rollback:rollback',
    'artisan:cache:all',
    'artisan:horizon:terminate',
    'horizon:restart',
    'health:check',
]);

// Task: Deploy with quality checks
desc('Deploy with quality assurance checks');
task('deploy:quality', [
    'local:quality:check',
    'deploy',
]);

// Local quality check task
task('local:quality:check', function () {
    writeln('<info>Running local quality checks...</info>');

    // Check if we're in the right directory
    if (!test('[ -f composer.json ]')) {
        throw new \Exception('Not in Laravel project directory');
    }

    // Run Laravel Pint
    runLocally('./vendor/bin/pint --test');
    writeln('<info>✓</info> Code style check passed');

    // Run PHPStan
    runLocally('./vendor/bin/phpstan analyse --no-progress');
    writeln('<info>✓</info> Static analysis passed');

    // Run tests
    runLocally('./vendor/bin/phpunit --stop-on-failure');
    writeln('<info>✓</info> Tests passed');

    writeln('<info>✓</info> All quality checks passed');
})->desc('Run quality checks locally before deployment')->local();

// Deployment hooks
before('deploy:vendors', 'artisan:down');
after('deploy:failed', 'artisan:up');

// Success message task
task('deploy:success:message', function () {
    writeln('');
    writeln('<info>🚀 HD Tickets deployed successfully!</info>');
    writeln('<info>📊 Sport Events Entry Tickets Monitoring System is now live</info>');
    writeln('<comment>🔗 https://hd-tickets.com</comment>');
    writeln('');
})->desc('Show success message');

// Failure message task
task('deploy:failed:message', function () {
    writeln('');
    writeln('<error>❌ Deployment failed!</error>');
    writeln('<comment>🔔 Consider running: dep rollback production</comment>');
    writeln('');
})->desc('Show failure message');

// Hook success message after success
after('success', 'deploy:success:message');

// Set up failure handling
fail('deploy', 'deploy:failed');
after('deploy:failed', 'deploy:failed:message');
