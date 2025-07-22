<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title><?php echo e(config('app.name', 'Laravel')); ?></title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

        <!-- Styles -->
        <?php if(file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot'))): ?>
            <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
        <?php else: ?>
            <style>
                /* Simple styles for welcome page */
                body {
                    font-family: 'Figtree', sans-serif;
                    margin: 0;
                    padding: 0;
                    background-color: #f8fafc;
                }
                .container {
                    min-height: 100vh;
                    display: flex;
                    flex-direction: column;
                    justify-content: center;
                    align-items: center;
                }
                .title {
                    font-size: 3rem;
                    font-weight: 600;
                    color: #1f2937;
                    margin-bottom: 2rem;
                }
                .links {
                    margin-top: 2rem;
                    display: flex;
                    justify-content: center;
                    gap: 1rem;
                }
                .links a {
                    display: inline-block;
                    margin: 0;
                    padding: 0.75rem 2rem;
                    color: #ffffff;
                    text-decoration: none;
                    background-color: #3b82f6;
                    border: 1px solid #3b82f6;
                    border-radius: 0.5rem;
                    font-weight: 600;
                    font-size: 1rem;
                    transition: all 0.3s ease;
                    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                }
                .links a:hover {
                    background-color: #2563eb;
                    border-color: #2563eb;
                    transform: translateY(-1px);
                    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
                }
                .links a.secondary {
                    background-color: #6b7280;
                    border-color: #6b7280;
                }
                .links a.secondary:hover {
                    background-color: #4b5563;
                    border-color: #4b5563;
                }
                .links a.logout {
                    background-color: #dc2626;
                    border-color: #dc2626;
                }
                .links a.logout:hover {
                    background-color: #b91c1c;
                    border-color: #b91c1c;
                }
                .user-info {
                    margin-bottom: 1rem;
                    color: #4b5563;
                    font-size: 1.1rem;
                    text-align: center;
                }
                .links form {
                    margin: 0;
                    display: inline;
                }
                .header-section {
                    text-align: center;
                }
                .subtitle {
                    color: #6b7280;
                    font-size: 1.2rem;
                    margin-bottom: 1rem;
                }
            </style>
        <?php endif; ?>
    </head>
    <body>
        <div class="container">
            <div class="header-section">
                <div class="title">
                    HD Tickets
                </div>
                <div class="subtitle">
                    Professional Help Desk & Ticket Management System
                </div>
            </div>
            
            <?php if(Route::has('login')): ?>
                <?php if(auth()->guard()->check()): ?>
                    <div class="user-info">
                        Welcome back, <?php echo e(Auth::user()->name); ?> <?php echo e(Auth::user()->surname ?? ''); ?>!
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            
            <div class="links">
                <?php if(Route::has('login')): ?>
                    <?php if(auth()->guard()->check()): ?>
                        <a href="<?php echo e(url('/dashboard')); ?>">Dashboard</a>
                        <form method="POST" action="<?php echo e(route('logout')); ?>" style="display: inline;">
                            <?php echo csrf_field(); ?>
                            <a href="#" class="logout" onclick="event.preventDefault(); this.closest('form').submit();">Logout</a>
                        </form>
                    <?php else: ?>
                        <a href="<?php echo e(route('login')); ?>">Login</a>
                        <?php if(Route::has('register')): ?>
                            <a href="<?php echo e(route('register')); ?>" class="secondary">Register</a>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </body>
</html>
<?php /**PATH C:\Users\polas\OneDrive\www\hdtickets\resources\views/welcome.blade.php ENDPATH**/ ?>