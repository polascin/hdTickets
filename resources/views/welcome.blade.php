<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

        <!-- Styles -->
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
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
        @endif
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
            
            @if (Route::has('login'))
                @auth
                    <div class="user-info">
                        Welcome back, {{ Auth::user()->name }} {{ Auth::user()->surname ?? '' }}!
                    </div>
                @endauth
            @endif
            
            <div class="links">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}">Dashboard</a>
                        <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                            @csrf
                            <a href="#" class="logout" onclick="event.preventDefault(); this.closest('form').submit();">Logout</a>
                        </form>
                    @else
                        <a href="{{ route('login') }}">Login</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="secondary">Register</a>
                        @endif
                    @endauth
                @endif
            </div>
        </div>
    </body>
</html>
