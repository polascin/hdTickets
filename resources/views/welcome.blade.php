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
                }
                .links a {
                    margin: 0 1rem;
                    padding: 0.5rem 1rem;
                    color: #374151;
                    text-decoration: none;
                    border: 1px solid #d1d5db;
                    border-radius: 0.375rem;
                    transition: all 0.2s;
                }
                .links a:hover {
                    background-color: #f3f4f6;
                }
            </style>
        @endif
    </head>
    <body>
        <div class="container">
            <div class="title">
                HD Tickets
            </div>
            
            <div class="links">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}">Login</a>

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}">Register</a>
                        @endif
                    @endauth
                @endif
            </div>
        </div>
    </body>
</html>
