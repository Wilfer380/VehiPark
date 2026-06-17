<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>VehiPark</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-900 antialiased">
        @php
            $authMode = request()->routeIs('register') ? 'register' : (request()->routeIs('login') ? 'login' : 'guest');

            $authPanel = [
                'login' => [
                    'brand' => 'VehiPark',
                    'tagline' => 'Sistema de Venta de Vehículos y Gestión de Parqueadero',
                    'headline' => 'Iniciar sesión',
                    'support' => 'Bienvenido, inicia sesión para continuar',
                    'copyright' => '© 2024 VehiPark. Todos los derechos reservados.',
                ],
                'register' => [
                    'brand' => 'VehiPark',
                    'tagline' => 'Crea tu cuenta para empezar',
                    'headline' => 'Crear cuenta',
                    'support' => 'Completa el formulario para registrarte',
                    'copyright' => '© 2024 VehiPark. Todos los derechos reservados.',
                ],
                'guest' => [
                    'brand' => 'VehiPark',
                    'tagline' => 'Acceso al sistema',
                    'headline' => 'Acceder',
                    'support' => 'VehiPark centraliza ventas, parqueadero y pagos en un solo lugar.',
                    'copyright' => '© 2024 VehiPark. Todos los derechos reservados.',
                ],
            ][$authMode];
        @endphp

        <div class="auth-shell auth-shell--{{ $authMode }}">
            <div class="auth-shell__backdrop"></div>

            <main class="auth-shell__content">
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
