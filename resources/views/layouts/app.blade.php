<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="author" content="{{ config('app.name') }}">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="https://tienedescuento.com/">
    {!! $meta ?? '' !!}
    
    <title>{!! isset($title) ? $title.' - ' : '' !!} {{ config('app.name') }}</title>
    
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="preload" href="https://kit.fontawesome.com/3c033f8319.js" as="script" crossorigin="anonymous">
    <script src="https://kit.fontawesome.com/3c033f8319.js" crossorigin="anonymous"></script>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-9285674270424452" crossorigin="anonymous"></script>
    
    @livewireStyles
</head>
<body class="font-sans antialiased">
    <x-banner />
    <x-login-modal />
    
    <div class="min-h-screen">
        @livewire('navigation-menu')
        
        <!-- Page Content -->
        <main>
            <div class="mx-auto max-w-7xl overflow-hidden px-4 md:px-6 lg:px-8 py-8 lg:py-12 bg-white">
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
        
        <footer class="mt-12 border-t border-gray-200 text-sm">
            <div class="mx-auto max-w-7xl overflow-hidden px-4 md:px-6 lg:px-8 py-8 lg:py-12 bg-white">
                <x-taxonomy-tree />
                <div class="pb-8 lg:pb-12 mb-8 lg:mb-12 lg:flex lg:items-center lg:justify-between lg:space-x-12 border-b border-gray-200">
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 mb-2">@lang('Subscribe to our newsletter')</h3>
                        <p>¿Quieres ahorrar en tus compras? Recibe las mejores ofertas en celulares, tablets y pantallas, descuentos exclusivos en ropa de marca para toda la familia y rebajas por ventas nocturnas en estufas, refrigeradores y más. Te ayudamos a conseguir los mejores precios en tiempo real.</p>
                    </div>
                    <div>
                        <label for="email-address" class="sr-only">Email address</label>
                        <div class="mt-4 sm:mt-0">
                            <x-button primary class="w-full whitespace-nowrap">@lang('Subscribe to newsletter')</x-button>
                        </div>
                    </div>
                </div>
                <p>&copy; {{ date('Y') }} {{ config('app.name') }} - @lang('All rights reserved').</p>
            </div>
        </footer>              
    </div>
    
    @stack('modals')
    
    @livewireScripts
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-annotation@3.1.0/dist/chartjs-plugin-annotation.min.js"></script>
    @stack('scripts')
</body>
</html>
