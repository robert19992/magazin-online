<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Magazin Online</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        <!-- Styles / Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            .bg-image {
                background-image: url('/images/17580.jpg');
                background-size: cover;
                background-position: center;
                background-repeat: no-repeat;
            }
            
            .content-overlay {
                background-color: rgba(29, 27, 85, 0.7);
                width: 100%;
                height: 100%;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
            }
            
            .btn {
                border: 4px solid black;
                font-weight: bold;
                font-size: 1.25rem;
                padding: 1rem 2.5rem;
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3);
                transition: all 0.3s ease;
                background-color: white;
                color: black;
            }
            
            .btn:hover {
                transform: translateY(-3px);
            }
            
            .button-container {
                display: flex;
                justify-content: center;
                align-items: center;
                gap: 2.5rem;
                margin-bottom: 3rem;
            }
            
            .shop-title {
                font-size: 3.5rem;
                font-weight: bold;
                color: white;
                text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
                margin-top: 3rem;
            }
        </style>
    </head>
    <body class="antialiased">
        <div class="min-h-screen bg-image">
            <div class="content-overlay">
                <div class="button-container">
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/dashboard') }}" class="btn rounded-lg">
                                Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="btn rounded-lg">
                                Autentificare
                            </a>

                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="btn rounded-lg">
                                    Înregistrare
                                </a>
                            @endif
                        @endauth
                    @endif
                </div>
                
                <h1 class="shop-title">Magazin Rosca</h1>
            </div>
        </div>
    </body>
</html>
