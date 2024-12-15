<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <script src="https://kit.fontawesome.com/3c033f8319.js" crossorigin="anonymous"></script>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Styles -->
        @livewireStyles
    </head>
    <body class="font-sans antialiased">
        <x-banner />
        <x-login-modal />

        <div class="min-h-screen">
            @livewire('navigation-menu')

            <!-- Page Content -->
            <main>
                <div class="mx-auto max-w-7xl overflow-hidden px-6 lg:px-8 py-8 lg:py-12 bg-white">
                    <!-- Page Heading -->
                    @if (isset($header))
                        <header class="bg-white">
                            <div class="mb-6 md:mb-10">
                                {{ $header }}
                            </div>
                        </header>
                    @endif

                    {{ $slot }}
                </div>
            </main>
        </div>

        @stack('modals')

        @livewireScripts
        
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-annotation@3.1.0/dist/chartjs-plugin-annotation.min.js"></script>
        @stack('scripts')
    </body>
</html>
